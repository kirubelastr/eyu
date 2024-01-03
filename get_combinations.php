<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id, name, rowID, quantity FROM combinations"; // Replace 'combinations' with the actual table name
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $combinations = array();
        while($row = $result->fetch_assoc()) {
            $rowids = explode(",", $row['rowID']);
            $quantities = explode(",", $row['quantity']);
            $minRatio = PHP_INT_MAX;
            for ($i = 0; $i < count($rowids); $i++) {
                $rowid = $rowids[$i];
                $quantityPerItem = $quantities[$i];
            
                // Check the available quantity in the inventory
                $sql = "SELECT quantity FROM inventory WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $rowid);
                $stmt->execute();
                $resultInventory = $stmt->get_result();
                if ($resultInventory->num_rows > 0) {
                    $inventoryRow = $resultInventory->fetch_assoc();
                    $ratioForThisItem = floor($inventoryRow['quantity'] / $quantityPerItem);
                    $minRatio = min($minRatio, $ratioForThisItem);
                } else {
                    error_log("No inventory found for rowid: $rowid");
                }
            }
            
            $combinations[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'maxQuantity' => $minRatio
            );
        }
        header('Content-Type: application/json');
        echo json_encode($combinations);
    } else {
        error_log("No combinations found");
        header('Content-Type: application/json');
        echo json_encode(array());
    }
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);

    $combinationId = $data['combinationId'];
    $quantity = intval($data['quantity']);
    $sellingPrice = floatval($data['sellingPrice']);

    $sql = "SELECT rowID, quantity FROM combinations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $combinationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rowids = explode(",", $row['rowID']);
            $quantities = explode(",", $row['quantity']);
            foreach ($rowids as $index => $rowid) {
                $quantityToDeduct = $quantities[$index] * $quantity;

                $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE id = ?");
                $stmt->bind_param("i", $rowid);
                $stmt->execute();
                $result = $stmt->get_result();
                $inventoryRow = $result->fetch_assoc();
                $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("di", $quantityToDeduct, $rowid);
                $stmt->execute();
            }

            // Insert the selling price into the sales table
            $stmt = $conn->prepare("INSERT INTO sales (item_id, quantity, selling_price) VALUES (?, ?, ?)");
            $stmt->bind_param("sdd", $combinationId, $quantity, $sellingPrice); // Use 'd' for double
            $stmt->execute();
             
            echo json_encode(["message" => "Item sold successfully"]);
        }
        // Fetch the item name from the inventory table
        $stmt = $conn->prepare("SELECT name FROM combinations WHERE id = ?");
        $stmt->bind_param("i", $combinationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $itemName = $row['name'];
        $sell="sell";

        // Insert the data into the sales_and_losses table
        $stmt = $conn->prepare("INSERT INTO sales_and_losses (action, item_name, quantity, selling_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid",  $sell, $itemName, $quantityToDeduct, $sellingPrice);
        $stmt->execute();
    }
}


$conn->close();
?>

<?php
require 'db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id, name, rowID, quantity FROM combinations"; // Replace 'combinations' with the actual table name
    $result = $conn2->query($sql);

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
                $stmt = $conn2->prepare($sql);
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

    $sql = "SELECT rowID, quantity, price FROM combinations WHERE id = ?";
    $stmt = $conn2->prepare($sql);
    $stmt->bind_param("i", $combinationId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $total_difference = 0; // Variable to store the total difference
        while($row = $result->fetch_assoc()) {
                $rowids = explode(",", $row['rowID']);
                $quantities = array_map('floatval', explode(",", $row['quantity'])); // Convert to float
                $prices = array_map('floatval', explode(",", $row['price'])); // Convert to float
                $total_difference = 0;
            foreach ($rowids as $index => $rowid) {
                $quantityToDeduct = $quantities[$index] * $quantity;
    
                $stmt = $conn2->prepare("SELECT quantity, price FROM inventory WHERE id = ?");
                $stmt->bind_param("i", $rowid);
                $stmt->execute();
                $result = $stmt->get_result();
                $inventoryRow = $result->fetch_assoc();
    
                // Calculate the price difference
                $price_difference = $prices[$index] - $inventoryRow['price'];
                // Multiply the price difference by the deducted quantity
                $difference_times_quantity = $price_difference * $quantityToDeduct;
                // Add this to the total difference
                $total_difference += $difference_times_quantity;
    
                $stmt = $conn2->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("di", $quantityToDeduct, $rowid);
                $stmt->execute();
            }
    
            // Insert the total difference into the sales table
            $stmt = $conn2->prepare("INSERT INTO sales (item_id, quantity, selling_price) VALUES (?, ?, ?)");
            $stmt->bind_param("sdd", $combinationId, $quantity, $total_difference); // Use 'd' for double
            $stmt->execute();
                 
            echo json_encode(["message" => "Item sold successfully"]);
        }
        // Fetch the item name from the inventory table
        $stmt = $conn2->prepare("SELECT name FROM combinations WHERE id = ?");
        $stmt->bind_param("i", $combinationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $itemName = $row['name'];
        $sell="sell";
    
        // Insert the data into the sales_and_losses table
        $stmt = $conn2->prepare("INSERT INTO sales_and_losses (action, item_name, quantity, selling_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid",  $sell, $itemName, $quantityToDeduct, $total_difference);
        $stmt->execute();
    }
    
}
$conn->close();
$conn2->close();
?>

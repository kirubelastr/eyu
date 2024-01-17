<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch inventory data for dropdown
    $sql = "SELECT id, name, quantity FROM inventory";
    $result = $conn2->query($sql);

    if ($result->num_rows > 0) {
        $inventory = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($inventory);
    } else {
        echo json_encode([]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle loss record submission
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);

    $rowId = $data['rowid'];
    $quantity = intval($data['quantity']);
    $lossReason = $data['lossReason'];

    // Validate the existence of the item in the inventory
    $stmt = $conn2->prepare("SELECT quantity FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $rowId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $inventoryRow = $result->fetch_assoc();

        // Calculate and update the inventory quantity
        $quantityToDeduct = min($inventoryRow['quantity'], $quantity);
        $stmt = $conn2->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantityToDeduct, $rowId);
        $stmt->execute();

        // Record the loss in the losses table
        $stmt = $conn2->prepare("INSERT INTO losses (item_id, quantity, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $rowId, $quantityToDeduct, $lossReason);
        $stmt->execute();

        // Fetch the item name from the inventory table
        $stmt = $conn2->prepare("SELECT name FROM inventory WHERE id = ?");
        $stmt->bind_param("i", $rowId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $itemName = $row['name'];
        $losses="loss";

        // Insert the data into the sales_and_losses table
        $stmt = $conn2->prepare("INSERT INTO sales_and_losses (action, item_name, quantity, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis",$losses, $itemName, $quantityToDeduct, $lossReason);
        $stmt->execute();


        echo json_encode(["message" => "Loss recorded successfully"]);
    } else {
        echo json_encode(["error" => "Item not found in inventory"]);
    }
}

$conn->close();
$conn2->close();
?>

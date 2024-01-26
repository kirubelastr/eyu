<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $salesId = isset($_GET['salesId']) ? mysqli_real_escape_string($conn2, $_GET['salesId']) : 0;

    if (!$salesId) {
        echo json_encode(['error' => 'Invalid input data']);
        exit;
    }

    // Fetch the sales record
    $sql = "SELECT * FROM sales WHERE id = ?";
    $stmt = $conn2->prepare($sql);
    $stmt->bind_param("i", $salesId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_sales = $result->fetch_assoc();

    $combinationId = $current_sales['item_id'];

    // Fetch the combination record
    $sql = "SELECT rowID, quantity FROM combinations WHERE id = ?";
    $stmt = $conn2->prepare($sql);
    $stmt->bind_param("i", $combinationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_combination = $result->fetch_assoc();

    // Split the rowID and quantity into arrays
    $rowIDs = explode(',', $current_combination['rowID']);
    $quantities = array_map('floatval', explode(',', $current_combination['quantity']));

    $minRatio = PHP_INT_MAX;
    for ($i = 0; $i < count($rowIDs); $i++) {
        $sql = "SELECT quantity FROM inventory WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $rowIDs[$i]);
        $stmt->execute();
        $resultInventory = $stmt->get_result();
        $inventoryRow = $resultInventory->fetch_assoc();

        $ratioForThisItem = floor($inventoryRow['quantity'] / $quantities[$i]);
        $minRatio = min($minRatio, $ratioForThisItem);
    }

    echo json_encode(['maxQuantity' => $minRatio]);
}
$conn->close();
$conn2->close();
?>

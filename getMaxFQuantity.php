<?php
require 'db_connection.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

$sales_id = isset($data->sales_id) ? filter_var($data->sales_id, FILTER_VALIDATE_INT) : 0;

if (!$sales_id) {
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

// Fetch the corresponding sales record
$sql = "SELECT * FROM sales WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sales_id);
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_assoc();

// Fetch the corresponding product record
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sales['product_id']);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

echo json_encode(['max_quantity' => $product['quantity'], 'price' => $product['price']]);

$conn->close();
$conn2->close();
?>

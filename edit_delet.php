<?php
require 'db_connection.php';
// Get the request body
$requestBody = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($requestBody['route'] === 'edit') {
        $id = $requestBody['id'];
        $name = $requestBody['name'];
        $quantity = $requestBody['quantity'];
        $price = $requestBody['price'];

        $sql = "UPDATE inventory SET name='$name', quantity='$quantity', price='$price' WHERE id=$id";

        if ($conn2->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn2->error;
        }
    }
}

$conn2->close();
?>
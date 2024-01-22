<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);

    if (!empty($data) && is_array($data)) {
        $id = mysqli_real_escape_string($conn, $data['id']);
        $name = mysqli_real_escape_string($conn, $data['name']);
        $quantity = mysqli_real_escape_string($conn, $data['quantity']);
        $price = mysqli_real_escape_string($conn, $data['price']);

        // Update the data in the database
        $sql = "UPDATE products SET name='$name', quantity='$quantity', price='$price' WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            // Send a success message as the response
            echo json_encode(['message' => 'Record updated successfully']);
        } else {
            // Send an error message as the response
            echo json_encode(['error' => 'Error updating record: ' . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "No data received or invalid data format"]);
    }
}

$conn->close();
$conn2->close();
?>

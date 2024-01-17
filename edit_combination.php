<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_GET['identifier'] === 'fetchCombinationData') {
        // Your existing code to fetch combination data from the database
        $id = $_GET['id'];

        // Retrieve the row data from the combinations table
        $sql = "SELECT * FROM combinations WHERE id=$id";
        $result = $conn2->query($sql);
        $combinationData = $result->fetch_assoc();

        // Retrieve the corresponding inventory data
        $rowIDs = explode(",", $combinationData['rowID']);
        $inventoryData = [];
        foreach ($rowIDs as $rowID) {
            $sql = "SELECT * FROM inventory WHERE id=$rowID";
            $result = $conn2->query($sql);
            array_push($inventoryData, $result->fetch_assoc());
        }

        // Send the data in JSON format
        echo json_encode([
            'combinationData' => $combinationData,
            'inventoryData' => $inventoryData,
        ]);
    } else if ($_GET['identifier'] === 'fetchAllInventoryData') {
        // Retrieve all the row data from the inventory table
        $sql = "SELECT * FROM inventory";
        $result = $conn2->query($sql);
        $inventoryData = [];
        while ($row = $result->fetch_assoc()) {
            array_push($inventoryData, $row);
        }

        // Send the data in JSON format
        echo json_encode($inventoryData);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);
    if (!empty($data) && is_array($data)) {
        $id = mysqli_real_escape_string($conn2, $data['id'][0]);
        $name = mysqli_real_escape_string($conn2, $data['name'][0]);
        $rowID = implode(",", array_map(function($value) use ($conn2) {
            return mysqli_real_escape_string($conn2, $value);
        }, $data['rowID']));
        $rawMaterial = implode(",", array_map(function($value) use ($conn2) {
            return mysqli_real_escape_string($conn2, $value);
        }, $data['rawMaterial']));
        $quantity = implode(",", array_map('floatval', $data['quantity']));
        $price = implode(",", array_map('floatval', $data['price']));
        
        // Update the data in the database
        $sql = "UPDATE combinations SET name='$name', rowID='$rowID', rawMaterial='$rawMaterial', quantity='$quantity', price='$price' WHERE id=$id";
    
        if ($conn2->query($sql) === TRUE) {
            // Retrieve the updated combination data
            $updatedCombinationSql = "SELECT * FROM combinations WHERE id=$id";
            $updatedCombinationResult = $conn2->query($updatedCombinationSql);
            $updatedCombinationData = $updatedCombinationResult->fetch_assoc();
    
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Record updated successfully', 'updatedCombinationData' => $updatedCombinationData]);
        } else {
            header('Content-Type: application/json');
            http_response_code(500); // Set an appropriate HTTP status code for an internal server error
    
            echo json_encode(['error' => 'Error updating record: ' . $conn2->error]);
        }
    } else {
        echo json_encode(["error" => "No data received or invalid data format"]);
    }
    
}
$conn->close();
$conn2->close();
?>
<?php
require 'db_connection.php';

// Handle actions from the client
$action = $_GET['action'];

switch ($action) {
    case 'getInventoryData':
        getInventoryData();
        break;
    case 'getSalesAndLossesData':
        getSalesAndLossesData();
        break;
    case 'getCombinationData':
        getCombinationData();
        break;
    default:
        echo json_encode(["error" => "Invalid action!"]);
}

// Function to get combination data
function getCombinationData() {
    global $conn2;

    // Implement your logic to retrieve the combination data

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM combinations";
    $result = $conn2->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}

// Function to get inventory data
function getInventoryData() {
    global $conn2;

    // Implement your logic to retrieve the inventory data

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM inventory";
    $result = $conn2->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}

// Function to get sales and losses data
function getSalesAndLossesData() {
    global $conn2;

    // Implement your logic to retrieve the sales and losses data

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM sales_and_losses";
    $result = $conn2->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}


$conn2->close();
?>

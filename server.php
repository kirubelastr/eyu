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
function getSalesAndLossesData() {
    global $conn2;

    // SQL query to join sales_and_losses and inventory tables on item_name and name
    $sql = "SELECT sales_and_losses.*, inventory.price FROM sales_and_losses LEFT JOIN inventory ON sales_and_losses.item_name = inventory.name";
    $result = $conn2->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // If it is a loss, add the price from the inventory table to the selling_price
            if ($row['action'] === 'loss') {
                $row['selling_price'] = $row['price'];
            }
            $data[] = $row;
        }
    }

    echo json_encode($data);
}

$conn->close();
$conn2->close();
?>

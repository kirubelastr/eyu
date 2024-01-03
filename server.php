<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle actions from the client
$action = $_GET['action'];

switch ($action) {
    case 'getInventoryData':
        getInventoryData();
        break;
    case 'getSalesAndLossesData':
        getSalesAndLossesData();
        break;
    default:
        echo json_encode(["error" => "Invalid action!"]);
}

// Function to get inventory data
function getInventoryData() {
    global $conn;

    // Implement your logic to retrieve the inventory data

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM inventory";
    $result = $conn->query($sql);

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
    global $conn;

    // Implement your logic to retrieve the sales and losses data

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM sales_and_losses";
    $result = $conn->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}


$conn->close();
?>

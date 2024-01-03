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
    case 'addRawMaterial':
        addRawMaterial();
        break;
    case 'addCombination':
        addCombination();
        break;
    case 'sellItem':
        sellItem();
        break;
    case 'recordLoss':
        recordLoss();
        break;
    case 'getAvailableQuantity':
        getAvailableQuantity();
        break;
    case 'getInventoryData':
        getInventoryData();
        break;
    case 'getSalesAndLossesData':
        getSalesAndLossesData();
        break;
    case 'getCombinationDetails':
        getCombinationDetails();
        break;
    default:
        echo json_encode(["error" => "Invalid action!"]);
}

// Function to add raw material to the inventory
function addRawMaterial() {
    global $conn;

    $rawMaterialName = $_POST['rawMaterialName'];
    $rawMaterialQuantity = $_POST['rawMaterialQuantity'];

    $sql = "INSERT INTO inventory (name, quantity) VALUES ('$rawMaterialName', $rawMaterialQuantity)";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Raw material added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding raw material: " . $conn->error]);
    }
}

// Function to add combination to the inventory
function addCombination() {
    global $conn;

    $combinationName = $_POST['combinationName'];
    $combinationIngredients = $_POST['combinationIngredients'];

    // Implement your logic to calculate and deduct the quantities of ingredients from the inventory

    // Example SQL query (modify according to your database structure)
    $sql = "INSERT INTO combinations (name, ingredients) VALUES ('$combinationName', '$combinationIngredients')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Combination added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding combination: " . $conn->error]);
    }
}

// Function to sell an item
function sellItem() {
    global $conn;

    $productId = $_POST['productId'];
    $quantitySold = $_POST['quantitySold'];
    $sellingPrice = $_POST['sellingPrice']; // Added selling price

    // Implement your logic to update the inventory and record the sale

    // Example SQL query (modify according to your database structure)
    $sql = "INSERT INTO sales (product_id, quantity_sold, selling_price) VALUES ($productId, $quantitySold, $sellingPrice)";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Item sold successfully"]);
    } else {
        echo json_encode(["error" => "Error selling item: " . $conn->error]);
    }
}

// Function to record a loss
function recordLoss() {
    global $conn;

    $lossItemId = $_POST['lossItemId'];
    $lossQuantity = $_POST['lossQuantity'];
    $lossReason = $_POST['lossReason'];

    // Implement your logic to update the inventory and record the loss

    // Example SQL query (modify according to your database structure)
    $sql = "INSERT INTO losses (item_id, quantity, reason) VALUES ($lossItemId, $lossQuantity, '$lossReason')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Loss recorded successfully"]);
    } else {
        echo json_encode(["error" => "Error recording loss: " . $conn->error]);
    }
}

// Function to get available quantity of an item
function getAvailableQuantity() {
    global $conn;

    $productId = $_POST['productId'];

    // Implement your logic to retrieve the available quantity from the inventory

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT quantity FROM inventory WHERE id = $productId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["quantity" => $row['quantity']]);
    } else {
        echo json_encode(["quantity" => 0]);
    }
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

// Function to get combination details for a product
function getCombinationDetails() {
    global $conn;

    $productId = $_POST['productId'];

    // Implement your logic to retrieve combination details

    // Example SQL query (modify according to your database structure)
    $sql = "SELECT * FROM combinations WHERE product_id = $productId";
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

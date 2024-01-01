<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return inventory data for display
    $inventory = getInventory();
    if ($inventory === false) {
        echo json_encode(array('error' => 'Failed to retrieve inventory.'));
    } else {
        echo json_encode($inventory);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests based on sections
    if (isset($_POST['section'])) {
        switch ($_POST['section']) {
            case 'productSection':
                handleProductSection();
                break;
            case 'salesSection':
                handleSalesSection();
                break;
            case 'lossesSection':
                handleLossesSection();
                break;
            default:
                echo json_encode(array('error' => 'Invalid section.'));
        }
    } else {
        echo json_encode(array('error' => 'Section not specified.'));
    }
}

function getInventory() {
    global $conn;

    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    if ($result === false) {
        return false; // Return false on database query failure
    }

    $inventory = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
    }

    return $inventory;
}

function handleProductSection() {
    // Add item to the inventory
    $itemName = sanitizeInput($_POST['itemName']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    addItem($itemName, $quantity, $price);
}

function handleLossesSection() {
    // Record losses
    $productId = (int)$_POST['productId'];
    $quantityLost = (int)$_POST['quantityLost'];
    $reason = sanitizeInput($_POST['reason']);

    recordLosses($productId, $quantityLost, $reason);
}

function addItem($itemName, $quantity, $price) {
    global $conn;

    // Validation checks
    if ($quantity <= 0 || $price <= 0 ) {
        echo json_encode(array('error' => 'Invalid quantity, price'));
        return;
    }


    $sql = "INSERT INTO products (name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sid", $itemName, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(array('success' => 'Item added successfully.'));
    } else {
        echo json_encode(array('error' => 'Failed to add item.'));
    }

    $stmt->close();
}

function handleSalesSection() {
    // Record sales
    $productId = (int)$_POST['productId'];
    $quantitySold = (int)$_POST['quantitySold'];
    $sellingprice = (float)$_POST['sellingprice'];

    recordSales($productId, $quantitySold, $sellingprice);
}

function recordSales($productId, $quantitySold, $sellingprice) {
    global $conn;

    // Check if there is enough quantity to sell
    $checkQuantity = "SELECT quantity, price FROM products WHERE id = ?";
    $stmt = $conn->prepare($checkQuantity);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentQuantity = $row['quantity'];
        $currentprice = $row['price'];
        $currentprice = ceil($currentprice);
        $roundedsellPrice = ceil($sellingprice);

        if ($currentQuantity >= $quantitySold && $currentprice <= $roundedsellPrice) {
            // Deduct sold quantity from inventory
            $newQuantity = $currentQuantity - $quantitySold;
            $updateQuantity = "UPDATE products SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuantity);
            $stmt->bind_param("ii", $newQuantity, $productId);
            $stmt->execute();
            $stmt->close();

            // Record the sale
            $totalPrice = $sellingprice * $quantitySold;
            $recordSale = "INSERT INTO sales (product_id, quantity_sold, total_price) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($recordSale);
            $stmt->bind_param("iid", $productId, $quantitySold, $totalPrice);

            if ($stmt->execute()) {
                echo json_encode(array('success' => 'Sale recorded successfully.'));
            } else {
                echo json_encode(array('error' => 'Failed to record sale.'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('error' => 'Insufficient quantity or price for the sale.'));
        }
    } else {
        echo json_encode(array('error' => 'Product not found.'));
    }
}



function recordLosses($productId, $quantityLost, $reason) {
    global $conn;

    // Check if there is enough quantity to lose
    $checkQuantity = "SELECT quantity FROM products WHERE id = ?";
    $stmt = $conn->prepare($checkQuantity);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentQuantity = $row['quantity'];

        if ($currentQuantity >= $quantityLost) {
            // Deduct lost quantity from inventory
            $newQuantity = $currentQuantity - $quantityLost;
            $updateQuantity = "UPDATE products SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuantity);
            $stmt->bind_param("ii", $newQuantity, $productId);
            $stmt->execute();
            $stmt->close();

            // Record the loss
            $recordLoss = "INSERT INTO losses (product_id, quantity_lost, reason) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($recordLoss);
            $stmt->bind_param("iis", $productId, $quantityLost, $reason);

            if ($stmt->execute()) {
                echo json_encode(array('success' => 'Loss recorded successfully.'));
            } else {
                echo json_encode(array('error' => 'Failed to record loss.'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('error' => 'Insufficient quantity for the loss.'));
        }
    } else {
        echo json_encode(array('error' => 'Product not found.'));
    }
}

function sanitizeInput($input) {
    global $conn;
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $conn->real_escape_string($input);
}
?>

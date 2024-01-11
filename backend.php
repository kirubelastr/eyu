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
        echo json_encode(array('status' => 'error', 'message' => 'Failed to retrieve inventory.'));
    } else {
        echo json_encode(array('status' => 'success', 'data' => $inventory));
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
                echo json_encode(array('status' => 'error', 'message' => 'Invalid section.'));
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Section not specified.'));
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

    if (isValidQuantity($quantity) && isValidPrice($price)) {
        addItem($itemName, $quantity, $price);
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid quantity or price.'));
    }
}

function handleLossesSection() {
    // Record losses
    $productId = (int)$_POST['productId'];
    $quantityLost = (int)$_POST['quantityLost'];
    $reason = sanitizeInput($_POST['reason']);

    if (isValidQuantity($quantityLost)) {
        recordLosses($productId, $quantityLost, $reason);
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid quantity for losses.'));
    }
}

function handleSalesSection() {
    // Record sales
    $productId = (int)$_POST['productId'];
    $quantitySold = (int)$_POST['quantitySold'];
    $sellingprice = (float)$_POST['sellingprice'];

    if (isValidQuantity($quantitySold) && isValidPrice($sellingprice)) {
        recordSales($productId, $quantitySold, $sellingprice);
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid quantity or price for sales.'));
    }
}

function addItem($itemName, $quantity, $price) {
    global $conn;

    // Validation checks
    if ($quantity <= 0 || $price <= 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid quantity or price'));
        return;
    }
    $sql = "INSERT INTO products (name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sid", $itemName, $quantity, $price);
    
    $sql = "INSERT INTO product_inventory (name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sid", $itemName, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(array('status' => 'success', 'message' => 'Item added successfully.'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to add item.'));
    }

    $stmt->close();
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
                echo json_encode(array('status' => 'success', 'message' => 'Loss recorded successfully.'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to record loss.'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Insufficient quantity for the loss.'));
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Product not found.'));
    }
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
        $currentPrice = $row['price'];

        if ($currentQuantity >= $quantitySold && $currentPrice <= $sellingprice) {
            // Deduct sold quantity from inventory
            $newQuantity = $currentQuantity - $quantitySold;
            $updateQuantity = "UPDATE products SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuantity);
            $stmt->bind_param("ii", $newQuantity, $productId);
            $stmt->execute();
            $stmt->close();

            // Record the sale
            $totalPrice = $sellingprice;
            $recordSale = "INSERT INTO sales (product_id, quantity_sold, total_price) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($recordSale);
            $stmt->bind_param("iid", $productId, $quantitySold, $totalPrice);

            if ($stmt->execute()) {
                echo json_encode(array('status' => 'success', 'message' => 'Sale recorded successfully.'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to record sale.'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Insufficient quantity or price for the sale.'));
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Product not found.'));
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function isValidQuantity($quantity) {
    return $quantity > 0;
}

function isValidPrice($price) {
    return $price > 0;
}
?>
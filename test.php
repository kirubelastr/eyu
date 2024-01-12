<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die("Connection failed: " . $conn->connect_error);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['itemName']) || !isset($_POST['quantity']) || !isset($_POST['price'])) {
        echo json_encode(["error" => "Incomplete data for adding raw material."]);
        return;
    }

    // Sanitize input data
    $itemName = mysqli_real_escape_string($conn, $_POST['itemName']);
    $quantity = floatval($_POST['quantity']); // Assuming quantity is a float
    $price = floatval($_POST['price']); // Assuming price is a float

    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO inventory (name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Error preparing statement: " . $conn->error]);
        return;
    }

    $stmt->bind_param("sdd", $itemName, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Raw material added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding raw material: " . $stmt->error]);
    }

    $stmt->close();

    // Prepare and execute the second statement
    $sql = "INSERT INTO product_inventory (name, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Error preparing statement: " . $conn->error]);
        return;
    }

    $stmt->bind_param("sdd", $itemName, $quantity, $price);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Product added successfully"]);
    } else {
        echo json_encode(["error" => "Error adding product: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>

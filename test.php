<?php
require 'db_connection.php';

$response = array(); // Initialize response array

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    die("Connection failed: " . $conn2->connect_error);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['itemName']) || !isset($_POST['quantity']) || !isset($_POST['price'])) {
        $response[] = ["error" => "Incomplete data for adding raw material."];
    } else {
        // Sanitize input data
        $itemName = mysqli_real_escape_string($conn2, $_POST['itemName']);
        $quantity = floatval($_POST['quantity']); // Assuming quantity is a float
        $price = floatval($_POST['price']); // Assuming price is a float

        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO inventory (name, quantity, price) VALUES (?, ?, ?)";
        $stmt = $conn2->prepare($sql);

        if (!$stmt) {
            $response[] = ["error" => "Error preparing statement: " . $conn2->error];
        } else {
            $stmt->bind_param("sdd", $itemName, $quantity, $price);

            if ($stmt->execute()) {
                $response[] = ["message" => "Raw material added successfully"];
            } else {
                $response[] = ["error" => "Error adding raw material: " . $stmt->error];
            }

            $stmt->close();
        }

        // Prepare and execute the second statement
        $sql = "INSERT INTO product_inventory (name, quantity, price) VALUES (?, ?, ?)";
        $stmt = $conn2->prepare($sql);

        if (!$stmt) {
            $response[] = ["error" => "Error preparing statement: " . $conn2->error];
        } else {
            $stmt->bind_param("sdd", $itemName, $quantity, $price);

            if ($stmt->execute()) {
                $response[] = ["message" => "Product added successfully"];
            } else {
                $response[] = ["error" => "Error adding product: " . $stmt->error];
            }

            $stmt->close();
        }
    }
}

echo json_encode($response); // Echo the response array as a JSON string
$conn->close();
$conn2->close();
?>

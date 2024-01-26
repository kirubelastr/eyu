<?php
require 'db_connection.php';
function getSalesData($conn, $filter) {
    $sql = "";
    switch ($filter) {
        case 'daily':
            $sql = "SELECT s.id, p.name as product_name, s.quantity_sold, s.total_price,s.created_at
                    FROM sales s
                    JOIN products p ON s.product_id = p.id
                    WHERE s.created_at = CURDATE()";
            break;
        case 'weekly':
            $sql = "SELECT s.id, p.name as product_name, s.quantity_sold, s.total_price,s.created_at
                    FROM sales s
                    JOIN products p ON s.product_id = p.id
                    WHERE YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'monthly':
            $sql = "SELECT s.id, p.name as product_name, s.quantity_sold, s.total_price,s.created_at
                    FROM sales s
                    JOIN products p ON s.product_id = p.id
                    WHERE MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE())";
            break;
    }

    $result = $conn->query($sql);

    if (!$result) {
        die(json_encode(['error' => 'Query failed: ' . $conn->error]));
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'product_name' => $row['product_name'],
            'quantity_sold' => $filter === 'daily' ? $row['quantity_sold'] : floatval($row['quantity_sold']),
            'total_price' => $filter === 'daily' ? $row['total_price'] : floatval($row['total_price']),
            'date' =>$row['created_at'],
        ];
    }

    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'monthly';
    $data = getSalesData($conn, $filter);
    echo json_encode($data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    if ($data->action === 'edit') {
        $id = isset($data->id) ? filter_var($data->id, FILTER_VALIDATE_INT) : 0;
            $quantity_sold = isset($data->quantity_sold) ? filter_var($data->quantity_sold, FILTER_VALIDATE_FLOAT) : 0.0;
            $unit_price = isset($data->total_sales) ? filter_var($data->total_sales, FILTER_VALIDATE_FLOAT) : 0.0;

            if (!$id || $quantity_sold < 0 || $unit_price < 0) {
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }

            // Fetch the current sales record
            $sql = "SELECT * FROM sales WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_sales = $result->fetch_assoc();

            // Fetch the corresponding product record
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $current_sales['product_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            // Check if there is enough quantity in the product table
            $new_quantity = $product['quantity'] - $quantity_sold + $current_sales['quantity_sold'];
            if ($new_quantity < 0) {
                echo json_encode(['error' => 'Not enough quantity in stock']);
                exit;
            }

            // Check if the unit price is lower than the product price
            if ($unit_price < $product['price']) {
                echo json_encode(['error' => 'Unit price cannot be lower than the product price']);
                exit;
            }


            // Update the sales record
            $sql = "UPDATE sales SET quantity_sold = ?, total_price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $quantity_sold, $unit_price, $id);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Failed to update sales record']);
                exit;
            }

            // Update the product quantity
            $sql = "UPDATE products SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $new_quantity, $product['id']);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Failed to update product quantity']);
                exit;
            }

            $data = getSalesData($conn, $data->filter);
            echo json_encode($data); 
    } elseif ($data->action === 'delete') {
        $id = isset($data->id) ? mysqli_real_escape_string($conn, $data->id) : 0;

        if (!$id) {
            echo json_encode(['error' => 'Invalid input data']);
            exit;
        }

        // Fetch the current sales record
        $sql = "SELECT * FROM sales WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_sales = $result->fetch_assoc();

        // Delete the sales record
        $sql = "DELETE FROM sales WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Update the product quantity
        $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $current_sales['quantity_sold'], $current_sales['product_id']);
        $stmt->execute();

        $data = getSalesData($conn, $data->filter);
        echo json_encode($data);
    }
}

$conn->close();
$conn2->close();
?>

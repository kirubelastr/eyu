<?php
require 'db_connection.php';
function getSalesData($conn2, $filter) {
    $sql = "";
    switch ($filter) {
        case 'dailyj':
            $sql = "SELECT s.id, p.name as product_name, s.quantity as quantity_sold, s.selling_price as total_price, s.created_at,p.price as price_combination
                    FROM sales s
                    JOIN combinations p ON s.item_id = p.id
                    WHERE s.created_at = CURDATE()
                    GROUP BY s.id";
            break;
        case 'weeklyj':
            $sql = "SELECT s.id, p.name as product_name, s.quantity as quantity_sold, s.selling_price as total_price, s.created_at,p.price as price_combination
                    FROM sales s
                    JOIN combinations p ON s.item_id = p.id
                    WHERE YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)
                    GROUP BY s.id";
            break;
        case 'monthlyj':
            $sql = "SELECT s.id, p.name as product_name, s.quantity as quantity_sold, s.selling_price as total_price, s.created_at,p.price as price_combination
                    FROM sales s
                    JOIN combinations p ON s.item_id = p.id
                    WHERE MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE())
                    GROUP BY s.id";
            break;
    }

    $result = $conn2->query($sql);

    if (!$result) {
        die(json_encode(['error' => 'Query failed: ' . $conn2->error]));
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'idj' => $row['id'],
            'product_namej' => $row['product_name'],
            'quantity_soldj' => $filter === 'daily' ? $row['quantity_sold'] : floatval($row['quantity_sold']),
            'total_pricej' => $filter === 'daily' ? $row['total_price'] : floatval($row['total_price']),
            'price'=> $row['price_combination'],
            'datej' =>$row['created_at'],
        ];
    }

    return $data;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn2, $_GET['filter']) : 'monthly';
    $data = getSalesData($conn2, $filter);
    echo json_encode($data);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    if ($data->action === 'edit') {
        $id = isset($data->id) ? mysqli_real_escape_string($conn2, $data->id) : 0;
        $quantity_sold = isset($data->quantity_sold) ? floatval(mysqli_real_escape_string($conn2, $data->quantity_sold)) : 0;
        $total_sales = isset($data->total_sales) ? floatval(mysqli_real_escape_string($conn2, $data->total_sales)) : 0;
    
        if (!$id || !$quantity_sold || !$total_sales) {
            echo json_encode(['error' => 'Invalid input data']);
            exit;
        }
    
        // Fetch the current sales record
        $sql = "SELECT * FROM sales WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_sales = $result->fetch_assoc();
    
        // Fetch the combination record
        $sql = "SELECT * FROM combinations WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $current_sales['item_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_combination = $result->fetch_assoc();
    
        // Split the rowID and quantity into arrays
        $rowIDs = explode(',', $current_combination['rowID']);
        $quantities = array_map('floatval', explode(',', $current_combination['quantity']));
    
        // Update the inventory quantities
        for ($i = 0; $i < count($rowIDs); $i++) {
            // Fetch the current inventory record
            $sql = "SELECT * FROM inventory WHERE id = ?";
            $stmt = $conn2->prepare($sql);
            $stmt->bind_param("i", $rowIDs[$i]);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_inventory = $result->fetch_assoc();
    
            // Calculate the new quantity
            $new_quantity = $quantity_sold * $quantities[$i];
    
            // Check if there is enough quantity in the inventory
            if ($new_quantity > $current_inventory['quantity']) {
                echo json_encode(['error' => 'Not enough quantity in stock']);
                exit;
            }
    
            // Update the inventory quantity
            if ($new_quantity < $current_sales['quantity']) {
                $sql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
                $remaining_quantity = $current_sales['quantity'] - $new_quantity;
            } else {
                $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
                $remaining_quantity = $new_quantity - $current_sales['quantity'];
            }
            $stmt = $conn2->prepare($sql);
            $stmt->bind_param("di", $remaining_quantity, $rowIDs[$i]);
            $stmt->execute();
        }
    
        // Update the sales record
        $sql = "UPDATE sales SET quantity = ?, selling_price = ? WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("idi", $quantity_sold, $total_sales, $id);
        $stmt->execute();
    
        $data = getSalesData($conn2, $data->filter);
        echo json_encode($data);    
    }elseif ($data->action === 'delete') {
        $id = isset($data->id) ? mysqli_real_escape_string($conn2, $data->id) : 0;
    
        if (!$id) {
            echo json_encode(['error' => 'Invalid input data']);
            exit;
        }
    
        // Fetch the current sales record
        $sql = "SELECT * FROM sales WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_sales = $result->fetch_assoc();
    
        // Fetch the combination record
        $sql = "SELECT * FROM combinations WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $current_sales['item_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_combination = $result->fetch_assoc();
    
        // Split the rowID and quantity into arrays
        $rowIDs = explode(',', $current_combination['rowID']);
        $quantities = array_map('floatval', explode(',', $current_combination['quantity']));
    
        // Update the inventory quantities
        for ($i = 0; $i < count($rowIDs); $i++) {
            $sql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
            $stmt = $conn2->prepare($sql);
            $new_quantity = $current_sales['quantity'] * $quantities[$i];
            $stmt->bind_param("di", $new_quantity, $rowIDs[$i]);
            $stmt->execute();
        }
    
        // Delete the sales record
        $sql = "DELETE FROM sales WHERE id = ?";
        $stmt = $conn2->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    
        $data = getSalesData($conn2, $data->filter);
        echo json_encode($data);
    }
    
    
}
$conn->close();
$conn2->close();
?>

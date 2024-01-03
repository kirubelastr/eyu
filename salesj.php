<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}
function getSalesData($conn, $filter) {
    $sql = "";
    switch ($filter) {
        case 'dailyj':
            $sql = "SELECT p.name as product_name, SUM(s.quantity) as quantity_sold, SUM(s.selling_price) as total_price
                    FROM sales s
                    JOIN inventory p ON s.item_id = p.id
                    WHERE s.created_at = CURDATE()";
            break;
        case 'weeklyj':
            $sql = "SELECT p.name as product_name, SUM(s.quantity) as quantity_sold, SUM(s.selling_price) as total_price
                    FROM sales s
                    JOIN inventory p ON s.item_id = p.id
                    WHERE YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)
                    GROUP BY p.name";
            break;
        case 'monthlyj':
            $sql = "SELECT p.name as product_name, SUM(s.quantity) as quantity_sold, SUM(s.selling_price) as total_price
                    FROM sales s
                    JOIN inventory p ON s.item_id = p.id
                    WHERE MONTH(s.created_at) = MONTH(CURDATE()) AND YEAR(s.created_at) = YEAR(CURDATE())
                    GROUP BY p.name";
            break;
    }

        $result = $conn->query($sql);

        if (!$result) {
            die(json_encode(['error' => 'Query failed: ' . $conn->error]));
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'product_name' => $row['product_name'],
                'quantity_sold' => $filter === 'daily' ? $row['quantity_sold'] : floatval($row['quantity_sold']),
                'total_price' => $filter === 'daily' ? $row['total_price'] : floatval($row['total_price']),
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
        $id = isset($data->id) ? mysqli_real_escape_string($conn, $data->id) : 0;
        $quantity_sold = isset($data->quantity_sold) ? mysqli_real_escape_string($conn, $data->quantity_sold) : 0;
        $total_sales = isset($data->total_sales) ? mysqli_real_escape_string($conn, $data->total_sales) : 0;

        if (!$id || !$quantity_sold || !$total_sales) {
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
        $sql = "SELECT * FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $current_sales['item_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // Check if there is enough quantity in the product table
        if ($product['quantity'] < $quantity_sold) {
            echo json_encode(['error' => 'Not enough quantity in stock']);
            exit;
        }

        // Check if the input value is greater than the price
        if ($total_sales / $quantity_sold <= $product['price']) {
            echo json_encode(['error' => 'Input value must be greater than the price']);
            exit;
        }

        // Update the sales record
        $sql = "UPDATE sales SET quantity = ?, selling_price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idi", $quantity_sold, $total_sales, $id);
        $stmt->execute();

        // Update the product quantity
        $new_quantity = $product['quantity'] - $quantity_sold + $current_sales['quantity_sold'];
        $sql = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_quantity, $product['id']);
        $stmt->execute();

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
        $sql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $current_sales['quantity_sold'], $current_sales['item_id']);
        $stmt->execute();

        $data = getSalesData($conn, $data->filter);
        echo json_encode($data);
    }
}

$conn->close();
?>

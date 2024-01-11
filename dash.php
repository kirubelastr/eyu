<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";
$dbname2 = "mydb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn2 = new mysqli($servername, $username, $password, $dbname2);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
if ($conn2->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
// Fetch data for expenses
$sql_expenses = "SELECT ExpenseType, COUNT(*) as count, COALESCE(SUM(TotalCost), 0) as total_cost FROM expenses GROUP BY ExpenseType";
$result_expenses = $conn->query($sql_expenses);

$data_expenses = array();
foreach ($result_expenses as $row_expense) {
  $data_expenses[] = array(
    'ExpenseType' => $row_expense['ExpenseType'],
    'count' => $row_expense['count'],
    'total_cost' => $row_expense['total_cost']
  );
}

// Fetch data for total sales
$sql_sales = "SELECT COALESCE(SUM(COALESCE(total_price, 0)), 0) as total_sales FROM sales";
$result_sales = $conn->query($sql_sales);
$row_sales = $result_sales->fetch_assoc();
$total_sales = $row_sales['total_sales'];

$sql_sales = "SELECT COALESCE(SUM(s.quantity_sold * (s.total_price - p.price)), 0) as total_difference FROM sales s JOIN products p ON s.product_id = p.id";
$result_sales = $conn->query($sql_sales);
$row_sales = $result_sales->fetch_assoc();
$total_difference = $row_sales['total_difference'];


// Fetch data for total losses
$sql_losses = "SELECT COALESCE(SUM(COALESCE(quantity_lost, 0)), 0) as total_losses FROM losses";
$result_losses = $conn->query($sql_losses);
$row_losses = $result_losses->fetch_assoc();
$total_losses = $row_losses['total_losses'];

// Fetch data for total capital
$sql_capital = "SELECT COALESCE(SUM(COALESCE(quantity, 0) * COALESCE(price, 0)), 0) as total_capital FROM product_inventory";
$result_capital = $conn->query($sql_capital);
$row_capital = $result_capital->fetch_assoc();
$total_capital = $row_capital['total_capital'];


$net = $total_difference - $total_losses;

////////////////////////////////////////////////////////////////
// Fetch data for total sales
// $sql_sales = "SELECT COALESCE(SUM(s.quantity * i.price), 0) as total_sales FROM sales s JOIN combinations c ON s.item_id = c.id JOIN inventory i ON FIND_IN_SET(i.id, c.rowID) > 0";
// $result_sales = $conn2->query($sql_sales);
// $row_sales = $result_sales->fetch_assoc();
// $total_salesj = $row_sales['total_sales'];

/////////////
function calculateTotalSalesJ($conn2) {
    $total_salesj = 0; // Initialize total_salesj

    $sql_sales = "SELECT item_id, quantity as sold_quantity FROM sales";
    $result_sales = $conn2->query($sql_sales);

    while ($row_sales = $result_sales->fetch_assoc()) {
        $item_id = $row_sales['item_id'];
        $sold_quantity = $row_sales['sold_quantity'];

        // Fetch data from combinations table for the current item_id
        $stmt = $conn2->prepare("SELECT rowID, quantity as combo_quantity FROM combinations WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comboRow = $result->fetch_assoc();

        // Split rowID and quantity into arrays
        $rowids = explode(",", $comboRow['rowID']);
        $quantities = array_map('floatval', explode(",", $comboRow['combo_quantity'])); // Convert to float

        foreach ($rowids as $index => $rowid) {
            // Fetch price from inventory table for the current rowid
            $stmt = $conn2->prepare("SELECT price FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $rowid);
            $stmt->execute();
            $result = $stmt->get_result();
            $inventoryRow = $result->fetch_assoc();

            // Calculate the total sales
            $total_salesj += $sold_quantity * $quantities[$index] * $inventoryRow['price'];
        }
    }

    // Multiply the total sales with the quantity from the sales table
    $total_salesj *= $sold_quantity;

    return $total_salesj;
}

// Usage example:
$totalSalesJ = calculateTotalSalesJ($conn2);

/////////////


$sql_sales = "SELECT COALESCE(SUM(selling_price), 0) as total_differencej FROM sales";
$result_sales = $conn2->query($sql_sales);
$row_sales = $result_sales->fetch_assoc();
$total_differencej = $row_sales['total_differencej'];

// Fetch data for total losses
$sql_losses = "SELECT COALESCE(SUM(l.quantity * i.price), 0) as total_losses FROM losses l JOIN inventory i ON l.item_id = i.id";
$result_losses = $conn2->query($sql_losses);
$row_losses = $result_losses->fetch_assoc();
$total_lossesj = $row_losses['total_losses'];

// Fetch data for total capital
$sql_capital = "SELECT COALESCE(SUM(COALESCE(quantity, 0) * COALESCE(price, 0)), 0) as total_capital FROM product_inventory";
$result_capital = $conn2->query($sql_capital);
$row_capital = $result_capital->fetch_assoc();
$total_capitalj = $row_capital['total_capital'];


$netj = $total_differencej - $total_lossesj;
////////////////////////////////////////////////////////////////

// Combine all data into a single array
$data = array(
  'expenses' => $data_expenses,
  'totals' => array(
    'total_sales' => $total_sales,
    'total_losses' => $total_losses,
    'total_capital' => $total_capital,
    'total_difference' => $total_difference,
    'net' => $net
  ),
    'totalsj'=>array(
    'total_salesj' => $totalSalesJ,
    'total_lossesj' => $total_lossesj,
    'total_capitalj' => $total_capitalj,
    'total_differencej' => $total_differencej,
    'netj' => $netj

  )
);

// Set the appropriate header for JSON response
header('Content-Type: application/json');

// Print the JSON-encoded data
echo json_encode($data);
?>

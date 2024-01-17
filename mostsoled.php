<?php
require 'db_connection.php';

// Fetch data for expenses
$sql_expenses = "SELECT ExpenseType, COUNT(*) as count FROM expenses GROUP BY ExpenseType";
$result_expenses = $conn->query($sql_expenses);

$data_expenses = array();
foreach ($result_expenses as $row_expense) {
  $data_expenses[] = $row_expense;
}

// Fetch data for total sales, total losses, total capital, and net
$sql_sales = "SELECT SUM(total_price) as total_sales FROM sales";
$result_sales = $conn->query($sql_sales);
$total_sales = $result_sales->fetch_assoc()['total_sales'];

$sql_losses = "SELECT SUM(quantity_lost) as total_losses FROM losses";
$result_losses = $conn->query($sql_losses);
$total_losses = $result_losses->fetch_assoc()['total_losses'];

$sql_capital = "SELECT SUM(quantity * price) as total_capital FROM products";
$result_capital = $conn->query($sql_capital);
$total_capital = $result_capital->fetch_assoc()['total_capital'];

$net = $total_sales - $total_losses - $total_capital;

$data_totals = array(
  'total_sales' => $total_sales,
  'total_losses' => $total_losses,
  'total_capital' => $total_capital,
  'net' => $net
);

// Combine all data into a single array
$data = array(
  'expenses' => $data_expenses,
  'totals' => $data_totals
);

// Print the JSON-encoded data
print json_encode($data);
$conn->close();
$conn2->close();
?>

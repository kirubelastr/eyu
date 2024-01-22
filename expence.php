<?php
require 'db_connection.php';

// Function to handle SQL injection prevention using prepared statements
function prepareAndExecute($conn, $sql, $params = null) {
    $stmt = $conn->prepare($sql);

    if ($params) {
        $stmt->bind_param(...$params);
    }

    $stmt->execute();
    return $stmt;
}

// Function to insert a new expense
function insertExpense($conn, $expenseType, $expenseReason, $totalCost) {
    $expenseDate = date('Y-m-d'); // current date
    $sql = "INSERT INTO expenses (ExpenseID, ExpenseType, ExpenseReason, ExpenseDate, TotalCost) VALUES (NULL, ?, ?, ?, ?)";
    prepareAndExecute($conn, $sql, ['sssd', $expenseType, $expenseReason, $expenseDate, $totalCost]);
}

// Function to get all expense types
function getExpenseTypes($conn) {
    $expenseTypes = array();
    $sql = "SELECT * FROM expensetypes";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $expenseTypes[] = $row;
        }
    }

    return $expenseTypes;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert new expense
    $expenseType = $_POST['expense-type'];
    $expenseReason = $_POST['expense-reason'];
    $totalCost = $_POST['total-cost'];
    insertExpense($conn, $expenseType, $expenseReason, $totalCost);
} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Fetch all expense types
    $expenseTypes = getExpenseTypes($conn);
    echo json_encode(["expenseTypes" => $expenseTypes]);
}

$conn->close();
$conn2->close();
?>

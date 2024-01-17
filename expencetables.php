<?php
require 'db_connection.php';

function getExpenses($conn, $sql) {
    $expenses = array();
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $expenses[] = $row;
        }
    }

    return $expenses;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
        // Fetch expenses for a specific date interval
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];

        $sql = "SELECT * FROM expenses WHERE ExpenseDate BETWEEN '$startDate' AND '$endDate'";
        $dateIntervalExpenses = getExpenses($conn, $sql);

        echo json_encode(["dateIntervalExpenses" => $dateIntervalExpenses]);
    } else {
        // Fetch daily expenses
        $sql = "SELECT * FROM expenses WHERE DATE(ExpenseDate) = CURDATE()";
        $dailyExpenses = getExpenses($conn, $sql);

        // Fetch weekly expenses for the current week (Sunday to Saturday)
        $sql = "SELECT * FROM expenses WHERE YEARWEEK(ExpenseDate) = YEARWEEK(NOW())";
        $weeklyExpenses = getExpenses($conn, $sql);

        // Fetch monthly expenses for the current month
        $sql = "SELECT * FROM expenses WHERE MONTH(ExpenseDate) = MONTH(CURDATE()) AND YEAR(ExpenseDate) = YEAR(CURDATE())";
        $monthlyExpenses = getExpenses($conn, $sql);

        echo json_encode(["dailyExpenses" => $dailyExpenses, "weeklyExpenses" => $weeklyExpenses, "monthlyExpenses" => $monthlyExpenses]);
    }
}
$conn->close();
$conn2->close();
?>

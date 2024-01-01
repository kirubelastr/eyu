
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle SQL injection prevention using prepared statements
function prepareAndExecute($conn, $sql, $params = null) {
    $stmt = $conn->prepare($sql);

    if ($params) {
        $stmt->bind_param(...$params);
    }

    $stmt->execute();
    return $stmt;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert new expense
    $expenseType = $_POST['expense-type'];
    $expenseReason = $_POST['expense-reason'];
    $totalCost = $_POST['total-cost'];
    $expenseDate = date('Y-m-d'); // current date
    $sql = "INSERT INTO expenses (ExpenseID, ExpenseType, ExpenseReason, ExpenseDate, TotalCost) VALUES (NULL, ?, ?, ?, ?)";
    prepareAndExecute($conn, $sql, ['sssd', $expenseType, $expenseReason, $expenseDate, $totalCost]);
}
function insertExpense($conn, $expenseType, $expenseReason, $totalCost) {
    $expenseDate = date('Y-m-d'); // current date
    $sql = "INSERT INTO expenses (ExpenseID, ExpenseType, ExpenseReason, ExpenseDate, TotalCost) VALUES (NULL, ?, ?, ?, ?)";
    prepareAndExecute($conn, $sql, ['sssd', $expenseType, $expenseReason, $expenseDate, $totalCost]);
}

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
}
elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Fetch all expense types
    $expenseTypes = getExpenseTypes($conn);
    echo json_encode(["expenseTypes" => $expenseTypes]);
}

$conn->close();
?>

<?php
$servername = "localhost";
$dbname = "login_credentials"; // replace with your database name
$username = "root"; // replace with your username
$password = ""; // replace with your password

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $store = mysqli_real_escape_string($conn, $_POST['store']);

    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO users (username, password, store) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        return;
    }

    $stmt->bind_param("sss", $username, $password, $store);

    if ($stmt->execute()) {
        echo "User created successfully!";
    } else {
        echo "Error executing statement: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<body>

<form method="post" action="">
  Username: <input type="text" name="username"><br>
  Password: <input type="password" name="password"><br>
  Store: <select name="store">
    <option value="store1">Store 1</option>
    <option value="store2">Store 2</option>
  </select><br>
  <input type="submit" value="Register">
</form>

</body>
</html>

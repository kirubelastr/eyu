<?php
session_start(); // Start the session

$servername = "localhost";
$alternativeServername = "locahost:3307"; // Add your alternative server name here
$dbname = "login_credentials"; // replace with your database name
$username = "root"; // replace with your username
$password = ""; // replace with your password

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, try the alternative server
    $conn = new mysqli($alternativeServername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$response = array(); // Initialize the response array

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize input data
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $store = mysqli_real_escape_string($conn, $_POST['store']);

        // Use prepared statement to prevent SQL injection
        $sql = "SELECT * FROM users WHERE username = ? AND store = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $response['error'] = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $store);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($_POST['password'], $user['password'])) {
                        $_SESSION['store'] = $store;
                        $response['success'] = true;
                        $response['message'] = "Login successful";
                    } else {
                        $response['success'] = false;
                        $response['message'] = "Invalid password";
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "Invalid credentials";
                }
            } else {
                $response['error'] = "Error executing statement: " . $stmt->error;
            }

            $stmt->close();
        }
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "An error occurred: " . $e->getMessage();
}

$conn->close();

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>

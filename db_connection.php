<?php
session_start(); // Start the session

$servername = "localhost";
$alternativeServername = "localhost:3307"; // Add your alternative server name here
$username = "root";
$password = "";

// Check if the session is not set or is empty
if(!isset($_SESSION['store']) || empty($_SESSION['store'])) {
    header('Location: login.html');
    exit(); // Make sure to exit after redirecting
}

if($_SESSION['store'] == 'store1') {
    $dbname = "inventory_db";
    $dbname2 = "mydb";
} elseif($_SESSION['store'] == 'store2') {
    $dbname = "second_inventory_db";
    $dbname2 = "second_mydb";
}

// Create connections
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // If connection fails, try the alternative server
    $conn = new mysqli($alternativeServername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

$conn2 = new mysqli($servername, $username, $password, $dbname2);
if ($conn2->connect_error) {
    // If connection fails, try the alternative server
    $conn2 = new mysqli($alternativeServername, $username, $password, $dbname2);
    if ($conn2->connect_error) {
        die("Connection failed: " . $conn2->connect_error);
    }
}
?>

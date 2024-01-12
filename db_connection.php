<?php
session_start(); // Start the session

$servername = "localhost";
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
$conn2 = new mysqli($servername, $username, $password, $dbname2);

// Check connections
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($conn2->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

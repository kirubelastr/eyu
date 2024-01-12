<?php
session_start();

if (isset($_SESSION['store'])) {
    // If the session is set, return the session value
    echo json_encode(['sessionSet' => true, 'sessionValue' => $_SESSION['store']]);
} else {
    echo json_encode(['sessionSet' => false]);
}
?>

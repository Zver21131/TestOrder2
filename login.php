<?php
session_start();
include 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM User WHERE login = ? AND password = ?");
    $stmt->bind_param("ss", $login, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['loggedin'] = true;
        header("Location: open_order.php");
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}

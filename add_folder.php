<?php
session_start();
require_once 'database.php';
function checkUserSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function addFolder($conn, $user_id, $folder_name) {
    $stmt = $conn->prepare("INSERT INTO folders (user_id, name) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $folder_name);
    $stmt->execute();
    $stmt->close();
}

checkUserSession();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $folder_name = $_POST['folder_name'];

    if (empty($folder_name)) {
        echo "A mappa neve nem lehet üres!";
        exit();
    }

    $conn = getDatabaseConnection();
    $user_id = $_SESSION['user_id'];
    addFolder($conn, $user_id, $folder_name);
    $conn->close();

    header("Location: dashboard.php");
    exit();
}
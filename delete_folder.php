<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['folder_id'])) {
    $folder_id = $_POST['folder_id'];
    $user_id = $_SESSION['user_id'];
    $conn = getDatabaseConnection();
    $deleteTasksQuery = "DELETE FROM tasks WHERE folder_id = ?";
    $stmt = $conn->prepare($deleteTasksQuery);
    $stmt->bind_param('i', $folder_id);
    $stmt->execute();
    $stmt->close();
    $deleteFolderQuery = "DELETE FROM folders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteFolderQuery);
    $stmt->bind_param('ii', $folder_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}

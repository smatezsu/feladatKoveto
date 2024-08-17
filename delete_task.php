<?php
require_once 'database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task_id'])) {
        $taskId = $_POST['task_id'];
        $conn = getDatabaseConnection();

        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        }
        $stmt->close();
        $conn->close();
    }
}


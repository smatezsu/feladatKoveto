<?php
require_once 'database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task_id']) && isset($_POST['new_status'])) {
        $taskId = $_POST['task_id'];
        $newStatus = $_POST['new_status'];
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $taskId);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error updating status: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<?php
session_start();
require_once 'database.php';
function checkUserSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}



function fetchTask($task_id, $user_id) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $task;
}

function updateTask($task_id, $user_id, $title, $description, $priority, $due_date) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $title, $description, $priority, $due_date, $task_id, $user_id);

    $result = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $result;
}

function sanitizeInput($input) {
    return htmlspecialchars($input);
}

checkUserSession();
$folder_id = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];

    $task = fetchTask($task_id, $user_id);

    if ($task) {
        $title = sanitizeInput($task['title']);
        $description = sanitizeInput($task['description']);
        $priority = sanitizeInput($task['priority']);
        $due_date = sanitizeInput($task['due_date']);
    } else {
        echo "Nem találtunk feladatot!";
        exit();
    }

    if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['priority']) && isset($_POST['due_date'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $priority = sanitizeInput($_POST['priority']);
        $due_date = sanitizeInput($_POST['due_date']);

        if (updateTask($task_id, $user_id, $title, $description, $priority, $due_date)) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Sikertelen frissités";
        }
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bgc">

<nav class="navbar navbar-expand-lg bg-secondary">
  <div class="container-fluid">
   <img src="./img/Task_Manager_Logo_Transparent-removebg-preview(1).png" alt="" style="width: 150px; margin-left: 100px;">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav me-5">
        <li class="nav-item">
          <form action="logout.php" method="post" class="d-inline">
              <button type="submit" name="logout" class="btn btn-danger">Kijelentkezés</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
<div class="row mt-1">
            <a href="dashboard.php">&lt;&lt;Vissza a feladataimhoz</a>
        </div>
    <div class="row">
        <div class="col d-flex justify-content-center">
            <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>">
            <form action="update_task.php" method="post">
                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                <div class="form-group mb-3 text-center">
                    <label for="title" class="form-label d-block fs-3">Cím</label>
                    <input type="text" id="title" name="title" class="border border-black form-control mx-auto" value="<?php echo $title; ?>" required>
                </div>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <div class="description-container">
                <div class="form-group">
                    <label for="description" class="fs-3">Leírás</label>
                    <textarea id="description" name="description" class="border border-black form-control" required style="height: 380px"><?php echo $description; ?></textarea>
                </div>
            </div>
        </div>
        <div class="col">
        <div class="info-container d-flex flex-column justify-content-center">
            <div class="form-group">
                <label for="priority" class="fs-3">Prioritás</label>
                <select id="priority" name="priority" class="border border-black form-select">
                    <option value="Alacsony" <?php echo $priority == 'Alacsony' ? 'selected' : ''; ?>>Alacsony</option>
                    <option value="Közepes" <?php echo $priority == 'Közepes' ? 'selected' : ''; ?>>Közepes</option>
                    <option value="Magas" <?php echo $priority == 'Magas' ? 'selected' : ''; ?>>Magas</option>
                </select>
            </div>
            <div class="form-group">
                <label for="due_date" class="fs-3 mt-5">Határidő</label>
                <input type="date" id="due_date" name="due_date" class="border border-black form-control mb-3" value="<?php echo $due_date; ?>" required>
            </div>
            <div class="row d-flex">
                <div class="col d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary my-5">Mentés</butto>
                </div>
                </form>
                <div class="col d-flex justify-content-center">
                    <form action="delete_task.php" method="post">
                        <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                        <button type="submit" class="btn btn-danger my-5">Törlés</button>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
require_once 'database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentDate = date('Y-m-d');

$folder_id = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $user_id = $_SESSION['user_id'];

    $conn = getDatabaseConnection();

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date, folder_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $user_id, $title, $description, $priority, $due_date, $folder_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
              <form action="logout.php" method="post">
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
            <form action="add_task.php" method="post">
                <input type="hidden" name="folder_id" value="<?php echo $folder_id; ?>">
                <input type="hidden" name="task_id">
                <div class="form-group mb-3 text-center">
                    <label for="title" class="form-label d-block fs-3">Cím</label>
                    <input type="text" id="title" name="title" class="border border-black form-control mx-auto"  required>
                </div>
        </div>
    </div>
    <div class="row">
        <div class="col-8">
            <div class="description-container">
                <div class="form-group">
                    <label for="description" class="fs-3">Leírás</label>
                    <textarea id="description" name="description" class="form-control border border-black" required style="height: 380px"></textarea>
                </div>
            </div>
        </div>
        <div class="col">
        <div class="info-container d-flex flex-column justify-content-center">
            <div class="form-group">
                <label for="priority" class="fs-3">Prioritás</label>
                <select id="priority" name="priority" class="border border-black form-select">
                    <option value="Alacsony" >Alacsony</option>
                    <option value="Közepes" >Közepes</option>
                    <option value="Magas" >Magas</option>
                </select>
            </div>
            <div class="form-group">
                <label for="due_date" class="fs-3 mt-5">Határidő</label>
                <input type="date" id="due_date" name="due_date" value="<?php echo $currentDate; ?>" class="border border-black form-control mb-3">
            </div>
            <div class="col-10 d-flex justify-content-center">
            <button type="submit" name="add_task" class="btn btn-primary my-5">Mentés</button>
        </div>
        </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
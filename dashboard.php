
<?php
session_start();
require 'database.php';
function checkUserSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}



function getSortColumn($sort_by) {
    switch ($sort_by) {
        case 'due_date':
            return 'due_date';
        case 'priority':
            return 'priority';
        default:
            return 'created_at';
    }
}

function getTasks($conn, $user_id, $sort_column) {
    $sql = "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY $sort_column ASC";
    return $conn->query($sql);
}

function trimDesc($description, $length = 60) {
    return (strlen($description) > $length) ? substr($description, 0, $length) . ' ...' : $description;
}

function generateCardHtml($task, $newStatus, $btnColor, $btnText, $show) {
    $description = trimDesc($task['description']);
    $title = htmlspecialchars($task['title']);
    $status = htmlspecialchars($task['status']);
    $priority = htmlspecialchars($task['priority']);
    $dueDate = htmlspecialchars($task['due_date']);
    $taskId = htmlspecialchars($task['id']);

    $card = '
      <div class="card m-3 '.$priority.'" style="width: 18rem;">
        <div class="card-body">
            <h5 class="card-title">' . $title . '</h5>
            <p class="card-text">' . $description . '</p>
            <div class="row d-flex mb-2">
                <div class="col">
                    <p class="card-text">Státusz: <br>' . $status . '</p>
                </div>
                <div class="col">
                    <p class="card-text">Prioritás: <br>' . $priority . '</p>
                </div>
            </div>
            <p class="card-text">Határidő - ' . $dueDate . '</p>
        </div>
        <div class="d-flex flex-row mb-3 ms-3">';
        if ($status == "Befejezett") {
            $card .= '<form action="delete_task.php" method="post" class="me-2">
                <input type="hidden" name="task_id" value="' . $taskId . '">
                <button type="submit" class="btn btn-danger btn-sm">Törlés</button>
            </form>';
        } else {
            $card .= '<form action="update_task.php" method="post" class="me-2">
                <input type="hidden" name="task_id" value="' . $taskId . '">
                <button type="submit" class="btn btn-primary btn-sm">Módositás</button>
            </form>';
        }
        if ($show === true) {
          $card .= '<form action="update_status.php" method="post">
        <input type="hidden" name="task_id" value="' . $taskId . '">
        <input type="hidden" name="new_status" value="'.$newStatus.'">
        <button type="submit" class="btn btn-'. $btnColor .' btn-sm">' . $btnText . '</button>
        </form>
        </div>
        </div>';
        } else {
          $card .= '</div></div>';
        }

    return $card;
}


function getFoldersByUser($conn, $user_id) {
  $query = "SELECT * FROM folders WHERE user_id = '$user_id'";
  $result = $conn->query($query);
  $folders = array();
  while ($row = $result->fetch_assoc()) {
    $folders[] = $row;
  }
  return $folders;
}

function getTasksByFolder($conn, $folder, $sort_column) {
  $folder_id = $folder["id"];
  $query = "SELECT * FROM tasks WHERE folder_id = $folder_id ORDER BY $sort_column ASC";
  $result = $conn->query($query);
  $tasks = array();
  while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
  }
  return $tasks;
}

function generateFolderCards() {
  $user_id = $_SESSION['user_id'];
  $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
  $sort_column = getSortColumn($sort_by);
  $conn = getDatabaseConnection();
  $folders = getFoldersByUser($conn, $user_id);
  $folderCards = '';
  foreach ($folders as $folder) {
      $folderCards .= generateFolderHeader($folder);
      $tasks = getTasksByFolder($conn, $folder, $sort_column);
      $folderCards .= '    <div class="container"><div class="row m-2">';
      if (!empty($tasks)) {
          foreach ($tasks as $task) {
              $folderCards .= generateCardHtml($task, "Befejezett", 'success', 'Feladat befejezése', false);
          }
      } else {
          $folderCards .= '<div class="alert alert-info" role="alert">Nincs feladat a mappában</div>';
      }
      $folderCards .= '</div></div>';
  }
  return $folderCards;
}

function generateFolderHeader($folder) {
  $folderId = htmlspecialchars($folder["id"]);
  $folderName = htmlspecialchars($folder["name"]);
  return '
  <div class="container-fluid bg-secondary my-3 py-4">
      <div class="row">
          <div class="col ms-5 ps-5">
              <h3 class="ps-3">' . $folderName . '</h3>
          </div>
          <div class="col d-flex justify-content-end me-5">
                <form action="delete_folder.php" class="me-5" method="post">
                  <input type="hidden"  name="folder_id" value="' . $folderId . '">
                  <button type="submit" name="delete_folder" class="btn btn-danger text-white">Mappa törlése</button>
              </form>
              <form action="add_task.php" method="post">
                  <input type="hidden" name="folder_id" value="' . $folderId . '">
                  <button type="submit" name="add_to_folder" class="btn btn-primary text-white">Feladat hozzáadása</button>
              </form>
          </div>
      </div>
  </div>';
}

function categorizeTasks($tasks) {
    $inProgressCards = '';
    $completedCards = '';

    if ($tasks->num_rows > 0) {
        while ($row = $tasks->fetch_assoc()) {
            if ($row['status'] == "Folyamatban") {
                $inProgressCards .= generateCardHtml($row, "Befejezett", 'success', 'Feladat befejezése', true);
            } else {
                $completedCards .= generateCardHtml($row, "Folyamatban", 'primary', 'Feladat újranyitása', true);
            }
        }
    } else {
        $inProgressCards = '<div class="alert alert-info" role="alert">Nincsennek feladataid</div>';
        $completedCards = '<div class="alert alert-info" role="alert">Nincs kész feladatod</div>';
    }

    return [$inProgressCards, $completedCards];
}

checkUserSession();

$conn = getDatabaseConnection();
$user_id = $_SESSION['user_id'];
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sort_column = getSortColumn($sort_by);

$tasks = getTasks($conn, $user_id, $sort_column);
$conn->close();

list($inProgressCards, $completedCards) = categorizeTasks($tasks);
?>






<!DOCTYPE html>
<html lang="en">
<head >
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
              <form action="logout.php" method="post" class="d-inline">
                  <button type="submit" name="logout" class="btn btn-danger">Kijelentkezés</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid bg-secondary my-3 py-4">
      <div class="row">
        <div class="col ms-5 ps-5">
          <h3 class="ps-3">Feladataid</h3>
        </div>
        <div class="col d-flex justify-content-end me-5">
          <a href="add_task.php" class="btn btn-primary text-white">Feladat hozzáadása</a>
        </div>
      </div>
    </div>

    <div class="container-fluid">
    <div class="row">
        <div class="col d-flex justify-content-end me-5">
            <form action="" method="get">
                <label for="sort_by" class="me-2">Rendezés szerint:</label>
                <select name="sort_by" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="created_at" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at' ? 'selected' : ''; ?>>Hozzáadás dátuma</option>
                    <option value="priority" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] == 'priority' ? 'selected' : ''; ?>>Prioritás</option>
                    <option value="due_date" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] == 'due_date' ? 'selected' : ''; ?>>Határidő</option>
                </select>
            </form>
        </div>
    </div>
    </div>


    <div class="container">
      <div class="row m-2">
    <?php
    
    if (!empty($inProgressCards)) {
      echo $inProgressCards;
    } else {
      echo '<div class="alert alert-info" role="alert">Nincsennek feladataid</div>';
    }
    ?>
      </div>

    </div>     
    </div>

    <?php echo generateFolderCards(); ?>

    <div class="container-fluid  bg-secondary my-3 py-4">
      <div class="row">
        <div class="col ms-5 ps-5">
          <h3 class="ps-3">Befejezett feladataid</h3>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row m-2">
        <?php
        if (!empty($completedCards)) {
          echo $completedCards;
        } else {
          echo '<div class="alert alert-info" role="alert">Nincs kész feladatod</div>';
        }
        ?>
      </div>
    </div>
    <div class="container-fluid  bg-secondary my-3 mb-5 py-4">
      <div class="row">
        <div class="col ms-5 ps-5">
        <form action="add_folder.php" method="post">
                <div class="mb-3 col-3">
                    <label for="folder_name" class="form-label">Mappa Készítés:</label>
                    <input type="text" class="form-control" id="folder_name" name="folder_name" placeholder="Mappa neve" required>
                </div>
                <button type="submit" class="btn btn-success">Mappa Létrehozása</button>
            </form>
        </div>

      </div>
    </div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="app.js"></script>
</body>
</html>

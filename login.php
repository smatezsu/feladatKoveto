
<?php
session_start();
require 'database.php';

$error = "";
$success = "";

function getUserByUsername(mysqli $conn, string $username): ?array {
    $user = null;
    if ($stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            $stmt->store_result();
            $stmt->bind_result($user_id, $hashed_password);
            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                $user = [
                    'id' => $user_id,
                    'password' => $hashed_password
                ];
            }
        }
        $stmt->close();
    }
    return $user;
}

function authenticateUser($user, $password) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        global $error;
        $error = "Nem helyes felhasználónév vagy jelszó.";
    }
}

function handleLogin($username, $password) {
    $conn = getDatabaseConnection();
    $user = getUserByUsername($conn, $username);
    $conn->close();
    if ($user) {
        authenticateUser($user, $password);
    } else {
        global $error;
        $error = "Nem található felhasználó.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    handleLogin($username, $password);
}

function isUsernameTaken($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $isTaken = $stmt->num_rows > 0;
    $stmt->close();
    return $isTaken;
}

function registerUser($conn, $username, $password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function doPasswordsMatch($password, $passwordrpt) {
    return $password === $passwordrpt;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordrpt = $_POST['passwordrpt'] ?? '';

    if (doPasswordsMatch($password, $passwordrpt)) {
        $conn = getDatabaseConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (isUsernameTaken($conn, $username)) {
            $error = "Felhasználónév foglalt.";
        } else {
            if (registerUser($conn, $username, $hashedPassword)) {
                $success = "Sikeres regisztáció.";
            } else {
                $error = "Sikertelen regisztráció.";
            }
        }

        $conn->close();
    } else {
        $error = 'Jelszavak nem eggyeznek.';
    }
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
<body  class="bgc">
    <nav class="navbar navbar-expand-lg bg-secondary">
      <div class="container-fluid">
       <img src="./img/Task_Manager_Logo_Transparent-removebg-preview(1).png" alt="" style="width: 150px; margin-left: 100px;">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav me-5">
            <li class="nav-item">
              <a class="nav-link" href="index.html">Főoldal</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>


    <div class="container my-2">
        <div class="row">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php elseif (!empty($succes)): ?>
                    <div class="alert alert-success">
                        <?php echo $succes; ?>
                    </div>
            <?php endif; ?>
            <div class="col">
                <form method="post" action="login.php">
                  <div class="mb-3">
                    <label for="username" class="form-label">Felhasználónév </label>
                    <input type="text" name="username" class="form-control" id="username">
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Jelszó</label>
                    <input type="password" id="password" name="password" class="form-control">
                  </div>
                  <button type="submit" class="btn btn-primary" value="Login" name="Login">Bejelentkezés</button>
                </form>
            </div>

            <div class="col">
                <form method="post" action="login.php">
                  <div class="mb-3">
                    <label for="examp" class="form-label">Felhasználónév</label>
                    <input type="text" name="username" class="form-control" id="examp">
                  </div>
                  <div class="mb-3">
                    <label for="exampl" class="form-label">Jelszó</label>
                    <input type="password" name="password" class="form-control" id="exampl" required>
                  </div>
                  <div class="mb-3">
                    <label for="example" class="form-label">Jelszó ismétlés</label>
                    <input type="password" name="passwordrpt" class="form-control" id="example" required>
                  </div>
                  <button type="submit" class="btn btn-primary" value="register" name="register">Regisztráció</button>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
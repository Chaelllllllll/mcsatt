<?php
include 'database.php';

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored_password = $row['password'];

            if (preg_match('/^\$2[ayb]\$.{56}$/', $stored_password)) {
                if (password_verify($password, $stored_password)) {
                    $_SESSION['logged_in'] = true; 
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['id'] = $row['id'];
                    header("Location: admin");
                    exit();
                } else {
                    $error_message = "Invalid email or password. Please try again.";
                }
            } else {
                if ($password === $stored_password) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['id'] = $row['id'];
                    header("Location: admin");
                    exit();
                } else {
                    $error_message = "Invalid email or password. Please try again.";
                }
            }
        } else {
            $error_message = "Invalid email or password. Please try again.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}


$pdo = null;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Login</title>
    <link rel="shortcut icon" href="https://i.ibb.co/SB5ZvFh/images.jpg" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            margin-bottom: 20px;
        }
        .card {
            background-color: #FFD6D6;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: transparent;
            border-bottom: none;
            font-size: 24px;
            font-weight: bold;
            color: #A82D2D;
        }
        .card-body {
            padding: 25px;
        }
        .form-control {
            border: 1px solid #E89A9A;
            border-radius: 8px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #A82D2D;
        }
        .btn {
            background-color: #A82D2D;
            color: #FFD6D6;
            border-radius: 8px;
            border: none;
        }
        .btn:hover {
            color: #FFD6D6;
            background-color: #8E2626;
        }
        .text-danger {
            color: #A82D2D !important;
        }
        .password-container {
            position: relative;
        }
        .eye-icon {
            position: absolute;
            top: 67%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #A82D2D;
        }
        a {
            color: #A82D2D;
        }
        a:hover {
            text-decoration: underline;
        }
        .alert {
            background-color: #A82D2D;
            color: #FFD6D6;
            border: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <b>Admin Login</b>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert" id="errorAlert" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                        <div class="form-group mb-3 password-container">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                            <i class="fas fa-eye eye-icon" id="togglePassword"></i>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Login</button>
                        <hr>
                        <p class="text-center">Back to <a href="login" class="text-danger text-decoration-none">Student Login</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    setTimeout(() => {
        const alert = document.getElementById('errorAlert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 2000);
</script>
</body>
</html>

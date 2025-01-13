<?php
require 'database.php';
require 'phpqrcode/qrlib.php'; 

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: student_dashboard"); 
    exit();
}

$error_message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required!";
        } else {
            $stmt = $pdo->prepare("SELECT id, password, role, id_number, qr_code_path, qr_code_path_studentsinfo, is_verified FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_verified'] != 1) {
                    $error_message = "Your email is not verified. Please check your email to verify.";
                } else {
                    if (empty($user['id_number'])) {
                        do {
                            $id_number = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id_number = :id_number");
                            $stmt->execute([':id_number' => $id_number]);
                            $exists = $stmt->fetchColumn();
                        } while ($exists > 0);

                        $qrContent = $id_number;
                        $qrFilePath = 'qrcodes/' . $id_number . '.png';
                        QRcode::png($qrContent, $qrFilePath);

                        $qrContentStudentsInfo = "https://localhost/mcsatt/student_info?idnumber=" . $id_number;
                        $qrFilePathStudentsInfo = 'studentsinfo/' . $id_number . '.png';
                        QRcode::png($qrContentStudentsInfo, $qrFilePathStudentsInfo);

                        $updateStmt = $pdo->prepare("UPDATE users SET id_number = :id_number, qr_code_path = :qr_code_path, qr_code_path_studentsinfo = :qr_code_path_studentsinfo WHERE id = :id");
                        $updateStmt->execute([
                            ':id_number' => $id_number,
                            ':qr_code_path' => $qrFilePath,
                            ':qr_code_path_studentsinfo' => $qrFilePathStudentsInfo,
                            ':id' => $user['id']
                        ]);
                    }

                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: student_dashboard");
                    exit();
                }
            } else {
                $error_message = "Invalid email or password!";
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

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
            top: 50%;
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
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    Login
                </div>
                <div class="card-body">

                <?php if (!empty($error_message)): ?>
                    <div class="alert" id="errorAlert" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                        <?php echo $error_message; ?>
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
                            <p class="text-left">
                                <a href="forgot_password" class="text-decoration-none">Forgot your password?</a>
                            </p>
                        </div>
                        <button type="submit" class="btn w-100">Login</button>
                    </form>
                    <hr>
                    <p class="text-center">Don't have an account? <a href="index" class="text-danger text-decoration-none">Register</a></p>
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

<?php
require 'database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error_message = '';
$success_message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $classType = $_POST['classType'];

        if (empty($email) || empty($password)) {
            $error_message = "Email and password are required!";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $error_message = "This email is already registered. Please use a different email.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Generate a 6-digit unique verification code
                $verificationCode = random_int(100000, 999999);

                // Insert user data into database with verification code
                $stmt = $pdo->prepare("INSERT INTO users (email, password, name, verification_token, class_type) VALUES (:email, :password, :name, :code, :class)");
                $stmt->execute([
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':name' => $name,
                    ':code' => $verificationCode,
                    ':class' => $classType
                ]);

                // Send email with verification code
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'muntinlupacosmopolitans@gmail.com';
                    $mail->Password   = 'djkf ppkg anck qntj'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('muntinlupacosmopolitans@gmail.com', 'Muntinlupa Cosmopolitan School');
                    $mail->addAddress($email);

                    $mail->SMTPOptions = array(
                        'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                        )
                    );

                    $mail->isHTML(true);
                    $mail->Subject = 'Verification Code';
                    $mail->Body    = "
                        <html>
                        <body>
                            <p>Thank you for registering! Your verification code is:</p>
                            <h2>$verificationCode</h2>
                            <p>Please enter this code on the verification page to complete your registration.</p>
                        </body>
                        </html>
                    ";

                    $mail->send();
                    $success_message = "A verification code has been sent to your email address. Please check your inbox.";

                    header("Location: verify_code?email=" . urlencode($email));
                    exit();
                } catch (Exception $e) {
                    $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success_message = "A verification code has been sent to your email address. Please check your inbox.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Register</title>
    <link rel="shortcut icon" href="https://i.ibb.co/SB5ZvFh/images.jpg" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 10px;
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
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    Register
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div id="alertMessage" class="alert alert-danger" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div id="alertMessage" class="alert alert-success" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control" id="name" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                        <div class="form-group mb-3 password-container">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                            <i class="fas fa-eye eye-icon" id="togglePassword"></i>
                        </div>
                        <div class="form-group mb-3">
                            <label for="classType">Class Type</label>
                            <select class="form-select" id="classType" name="classType" required>
                                <option value="1">Morning Class</option>
                                <option value="0">Afternoon Class</option>
                            </select>
                        </div>
                        <button type="submit" class="btn w-100">Register</button>
                    </form>
                    <hr>
                    <p class="text-center">Already have an account? <a href="login" class="text-danger text-decoration-none">Login</a></p>
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

    //setTimeout(() => {
        //const alert = document.getElementById('alertMessage');
        //if (alert) {
            //alert.style.transition = "opacity 0.5s ease";
            //alert.style.opacity = "0";
            //setTimeout(() => alert.remove(), 500);
        //}
   // }, 2000);
</script>
</body>
</html>


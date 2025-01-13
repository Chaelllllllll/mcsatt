<?php
require 'database.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    if (empty($email)) {
        $message = "Please enter your email!";
    } else {
        // Delete any existing reset token for the user
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute([':email' => $email]);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (:email, :token, :expiry)");
            $stmt->execute([':email' => $email, ':token' => $token, ':expiry' => $expiry]);

            $resetLink = "http://localhost/mcsatt/reset_password.php?token=$token";
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
                $mail->Subject = 'Reset your Password';
                $mail->Body    = "
                    <html>
                    <head>
                        <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
                        <style>
                            .email-container {
                                background-color: #FDEDEE;
                                padding: 20px;
                                border-radius: 5px;
                                font-family: Arial, sans-serif;
                            }
                            .email-header {
                                background-color: #FFD6D6;
                                color: #A82D2D;
                                padding: 20px;
                                border-radius: 5px 5px 0 0;
                                text-align: center;
                            }
                            .email-body {
                                padding: 20px;
                                background-color: white;
                                border-radius: 0 0 5px 5px;
                            }
                            .email-body a {
                                color: #A82D2D;
                                text-decoration: underline;
                            }
                            .verify-btn {
                                background-color: #FFD6D6;
                                color: #A82D2D;
                                padding: 10px 20px;
                                border-radius: 5px;
                                text-decoration: none;
                                display: inline-block;
                                margin-top: 20px;
                                text-align: center;
                                width: 100%;
                            }
                            .verify-btn:hover {
                                background-color: #FFD6D6;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='email-container'>
                                <div class='email-header'>
                                    <h1>MCS | Reset Password</h1>
                                </div>
                                <div class='email-body'>
                                    <p>Please click the button below to reset your password.</p>
                                    <a href='$resetLink' class='verify-btn' style='text-decoration: none;'>Reset Password</a>
                                    <hr>
                                    <p style='margin-top: 20px;'>If the button doesn't work, copy and paste the following link in your browser:</p>
                                    <p><a href='$resetLink'>$resetLink</a></p>
                                </div>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                $mail->send();
                $message = "Password reset instructions have been sent to your email.";
                $_SESSION['message'] = $message;
                header("Location: forgot_password?sent=success");
                exit(); 
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Email not found!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Forgot Password</title>
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
                    Forgot Password
                </div>

                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info" role="alert" id="errorAlert" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info" role="alert" id="errorAlert" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message']); // Clear the message after displaying it ?>
                    <?php endif; ?>

                    <form action="forgot_password.php" method="post">
                        <div class="form-group mb-3">
                            <label for="email">Enter your email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                        <button type="submit" class="btn w-100">Submit</button>
                        <hr>
                        <p class="text-center">
                            <a href="login" class="text-decoration-none">Back to Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(() => {
        const alert = document.getElementById('errorAlert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 1000);
        }
    }, 2000);
</script>
</body>
</html>

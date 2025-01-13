<?php
include 'database.php';

$message = "";

// Capture the token from the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token format (optional)
    if (empty($token)) {
        $message = "Invalid token.";
    } else {
        try {
            // Check if the token exists in the database
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = :token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Token is valid, allow the user to reset the password
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];

                    if (empty($password) || empty($confirm_password)) {
                        $message = "Please fill out both password fields.";
                    } elseif ($password !== $confirm_password) {
                        $message = "Passwords do not match.";
                    } else {
                        // Get the email associated with the token
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $email = $row['email'];

                        // Hash the new password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Update password in the users table
                        $update_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
                        $update_stmt->bindParam(':password', $hashed_password);
                        $update_stmt->bindParam(':email', $email);
                        $update_stmt->execute();

                        // Delete the token after successful reset
                        $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
                        $delete_stmt->bindParam(':token', $token);
                        $delete_stmt->execute();

                        $message = "Your password has been reset successfully. You can now log in with your new password.";
                    }
                }
            } else {
                // Invalid or expired token
                $message = "Invalid or expired reset token.";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
} else {
    // No token in the URL
    $message = "Token is missing.";
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Reset Password</title>
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
                    <b>Reset Password</b>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div id="errorAlert" class="alert alert-info" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['token'])): ?>
                        <form action="" method="post"> <!-- Changed action to empty -->
                            <div class="form-group mb-3 password-container">
                                <label for="password">New Password</label>
                                <input type="password" name="password" class="form-control" id="password" required>
                                <i class="fas fa-eye eye-icon" id="togglePassword1"></i>
                            </div>
                            <div class="form-group mb-3 password-container">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                                <i class="fas fa-eye eye-icon" id="togglePassword2"></i>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Reset Password</button>
                        </form>
                    <?php endif; ?>
                    <hr>
                    <p class="text-center"><a href="login" class="text-decoration-none text-danger text-center">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('togglePassword1').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const currentTypePassword = passwordField.getAttribute('type');
        passwordField.setAttribute('type', currentTypePassword === 'password' ? 'text' : 'password');
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    document.getElementById('togglePassword2').addEventListener('click', function () {
        const confirmPasswordField = document.getElementById('confirm_password');
        const currentTypeConfirmPassword = confirmPasswordField.getAttribute('type');
        confirmPasswordField.setAttribute('type', currentTypeConfirmPassword === 'password' ? 'text' : 'password');
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

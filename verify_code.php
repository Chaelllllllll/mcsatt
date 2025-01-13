<?php
require 'database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $code = $_POST['verification_code'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND verification_token = :code");
        $stmt->execute([':email' => $email, ':code' => $code]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = :email");
            $stmt->execute([':email' => $email]);

            $success_message = "Your email has been verified successfully!";
        } else {
            $error_message = "Invalid verification code. Please try again.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MCS | Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        a {
            color: #A82D2D;
        }
        a:hover {
            text-decoration: underline;
        }
        #alertDiv {
            background-color: #A82D2D;
            color: #FFD6D6;
            border: none;
        }

        #successDiv {
            background-color:rgb(178, 255, 142);
            color:rgb(0, 0, 0);
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
                        Email Verification
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div id="alertDiv" class="alert alert-danger" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div id="successDiv" class="alert alert-success" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
                            <div class="form-group mb-3">
                                <label for="verification_code">Verification Code</label>
                                <input type="text" name="verification_code" class="form-control" required>
                            </div>
                            <button type="submit" class="btn w-100">Verify</button>
                            <hr>
                            <p class="text-center">Back to <a class="text-danger text-decoration-none" href="login">Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(() => {
        const alert = document.getElementById('alertDiv');
        const success = document.getElementById('successDiv');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        } else if (success) {
            success.style.transition = "opacity 0.5s ease";
            success.style.opacity = "0";
            setTimeout(() => success.remove(), 500);
        }
    }, 2000);
    </script>
</body>
</html>


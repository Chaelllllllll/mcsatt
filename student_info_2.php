<?php
require 'database.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login");
    exit();
}

$user_role = $_SESSION['role'];

if ($user_role !== "Guard") {
    switch ($user_role) {
        case "Admin":
            header("Location: admin");
            break;
        case "student":
            header("Location: student_dashboard");
            break;
        default:
            header("Location: others/403");
            break;
    }
    exit();
}

$id_number = '';
$user_info = null;
$error_message = '';

if (isset($_GET['idnumber'])) {
    $id_number = $_GET['idnumber'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = :id_number LIMIT 1");
        $stmt->execute([':id_number' => $id_number]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_info) {
            $error_message = "No user found with ID number: $id_number";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
} else {
    $error_message = "ID number is missing.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Student Information</title>
    <link rel="shortcut icon" href="https://i.ibb.co/SB5ZvFh/images.jpg" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }
        .card {
            background-color: #FFD6D6;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .info li{
            border-radius: 20px; 
            margin-top: 1%;
            color: #A82D2D;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #E89A9A;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <b>Student Information</b>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert" role="alert" style="background-color: #A82D2D; color: white">
                            <?php echo $error_message; ?>
                        </div>
                    <?php else: ?>
                        <?php if ($user_info): ?>
                            <div class="text-center mb-3">
                                <?php if (!empty($user_info['fblink'])): ?>
                                    <a href="<?= htmlspecialchars($user_info['fblink']); ?>">
                                        <img 
                                            src="<?= !empty($user_info['image_path']) ? htmlspecialchars($user_info['image_path']) : 'https://i.ibb.co/s68CT2w/Nice-Png-watsapp-icon-png-9332131.png'; ?>" 
                                            alt="Profile Image" 
                                            class="profile-img img-thumbnail">
                                    </a>
                                <?php else: ?>
                                    <img 
                                        src="<?= !empty($user_info['image_path']) ? htmlspecialchars($user_info['image_path']) : 'https://i.ibb.co/s68CT2w/Nice-Png-watsapp-icon-png-9332131.png'; ?>" 
                                        alt="Profile Image" 
                                        class="profile-img img-thumbnail">
                                <?php endif; ?>
                            </div>


                            <ul class="list-group mb-4 info">
                                <li class="list-group-item text-start">
                                    <strong>Name:</strong> <?php echo htmlspecialchars($user_info['name']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Age:</strong> <?php echo htmlspecialchars($user_info['age']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Grade Level:</strong> <?php echo htmlspecialchars($user_info['grade']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Teacher:</strong> <?php echo htmlspecialchars($user_info['teacher']); ?>
                                </li>
                                <hr>
                                <p class="text-start mb-3">Guardian Information</p>
                                <li class="list-group-item text-start">
                                    <strong>Name:</strong> <?php echo htmlspecialchars($user_info['pname']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Mobile Number:</strong> <?php echo htmlspecialchars($user_info['pnumber']); ?>
                                </li>
                                <li class="list-group-item text-start">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($user_info['pemail']); ?>
                                </li>
                            </ul>
                            <a href="attendance" class="btn w-100" style="background-color: #A82D2D; color: white;">Scan Another</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


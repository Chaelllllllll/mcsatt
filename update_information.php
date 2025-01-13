<?php
require 'database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
        $grade = filter_input(INPUT_POST, 'grade', FILTER_SANITIZE_STRING);
        $teacher = filter_input(INPUT_POST, 'teacher', FILTER_SANITIZE_STRING);
        $pname = filter_input(INPUT_POST, 'pname', FILTER_SANITIZE_STRING);
        $prelationship = filter_input(INPUT_POST, 'prelationship', FILTER_SANITIZE_STRING);
        $pnumber = filter_input(INPUT_POST, 'pnumber', FILTER_SANITIZE_STRING);
        $pemail = filter_input(INPUT_POST, 'pemail', FILTER_SANITIZE_EMAIL);
        $fblink = filter_input(INPUT_POST, 'fblink', FILTER_VALIDATE_URL);

        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET email = :email, 
                name = :name, 
                age = :age, 
                grade = :grade, 
                teacher = :teacher, 
                pname = :pname, 
                prelationship = :prelationship, 
                pnumber = :pnumber, 
                pemail = :pemail , 
                fblink = :fblink 
            WHERE id = :id
        ");
        
        $updateStmt->execute([
            ':email' => $email,
            ':name' => $name,
            ':age' => $age,
            ':grade' => $grade,
            ':teacher' => $teacher,
            ':pname' => $pname,
            ':prelationship' => $prelationship,
            ':pnumber' => $pnumber,
            ':pemail' => $pemail,
            ':fblink' => $fblink,
            ':id' => $user_id
        ]);

        header("Location: student_dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit();
    }
}
?>

<?php
include 'database.php';

session_start(); 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $position = $_POST['position'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($position)) {
        echo "Error: All fields are required.";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $stmt = $pdo->prepare("INSERT INTO staff (name, email, password, role, position) VALUES (:name, :email, :password, :role, :position)");

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $position);
        $stmt->bindParam(':position', $position);

        if ($stmt->execute()) {
            echo "Staff created successfully.";
        } else {
            echo "Error: Failed to create staff.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>

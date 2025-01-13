<?php
session_start();

$user_role = $_SESSION['role'];

if ($user_role === "Admin" || $user_role === "Guard" || $user_role === "Teacher"){
    session_unset();
    session_destroy();
    header("Location: admin_login");
    exit();
} else if ($user_role === "student") {
    session_unset();
    session_destroy();
    header("Location: login");
    exit();
}

?>
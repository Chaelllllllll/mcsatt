<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "Attendance record deleted successfully.";
    } else {
        echo "Failed to delete attendance record.";
    }
} else {
    echo "Invalid request.";
}
?>

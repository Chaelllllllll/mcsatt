<?php
include 'database.php';

if (isset($_POST['id_number'])) {
    $id_number = $_POST['id_number'];

    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE id_number = :id_number");
    $stmt->bindParam(':id_number', $id_number);
    
    $stmt->execute();

    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($attendanceRecords);
} else {
    echo json_encode([]);
}
?>

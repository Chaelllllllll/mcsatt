<?php
require 'database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['id'];

$stmt = $pdo->prepare("DELETE FROM users WHERE id_number = :id");
$stmt->bindParam(':id', $student_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not delete student.']);
}
?>

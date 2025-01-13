<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Something went wrong.'];

    try {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $newPassword = $_POST['new_password'] ?? '';
        $position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);

        if (!$id || !$name || !$email || !$position) {
            $response['message'] = "All required fields must be filled.";
        } else {
            $query = "UPDATE staff SET name = :name, email = :email, position = :position";
            $params = [':name' => $name, ':email' => $email, ':position' => $position, ':id' => $id];

            if (!empty($newPassword)) {
                $query .= ", password = :password";
                $params[':password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            }

            $query .= " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $response['status'] = $stmt->rowCount() ? 'success' : 'error';
            $response['message'] = $stmt->rowCount() ? "Staff member updated successfully." : "No changes made.";
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $response['message'] = "An error occurred while processing the request.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

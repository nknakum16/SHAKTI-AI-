<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$servername = "localhost";
$username = "root";
$password = '';
$dbname = "shakti";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID missing']);
    exit;
}

$user_id = $data['user_id'];
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
}

$stmt->close();
$conn->close();
?>


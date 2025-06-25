<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shakti";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = intval($data['user_id']);
$otp = $conn->real_escape_string($data['otp']);

$sql = "SELECT otp FROM users WHERE id=? AND otp_verified=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if ($row['otp'] == $otp) {
        $conn->query("UPDATE users SET otp_verified=1, otp=NULL WHERE id=$user_id");
        echo json_encode(['status' => 'success', 'message' => 'OTP verified']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found or already verified']);
}
$stmt->close();
$conn->close();
?>




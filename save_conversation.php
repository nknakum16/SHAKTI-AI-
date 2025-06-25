<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shakti";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['message']) || !isset($data['role'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid input data'
    ]);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($data['user_id']) ? $data['user_id'] : null);
$message = $data['message'];
$role = $data['role'];

$stmt = $conn->prepare("INSERT INTO chat_history (user_id, role, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user_id, $role, $message);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Conversation saved'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save conversation: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT role, message, timestamp FROM chat_history WHERE user_id = ? ORDER BY timestamp ASC");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    echo json_encode([
        'status' => 'success',
        'conversations' => $conversations
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
?>
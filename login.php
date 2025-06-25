<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$servername = "localhost";
$username = "root";
$password = '';
$dbname = "shakti";
    
try {


    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $input = file_get_contents('php://input');
        $postData = json_decode($input, true);
        
        if (empty($postData)) {
            $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
        } else {
            $email = isset($postData['email']) ? $conn->real_escape_string($postData['email']) : '';
            $password = isset($postData['password']) ? $postData['password'] : '';
        }


        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }

        $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                throw new Exception("Invalid password");
            }
        } else {
            throw new Exception("User not found");
        }
        
        $stmt->close();
    } else {
        throw new Exception("Invalid request method");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
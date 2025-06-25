<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "shakti";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        if ($password === false) {
            throw new Exception('Password hashing failed');
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception('Email already registered');
        }
        $check->close();

        $otp = rand(100000, 999999);

        $sql = "INSERT INTO users (full_name, email, password, otp, otp_verified) VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $full_name, $email, $password, $otp);

        if ($stmt->execute()) {
            // Send OTP email
            $mail = new PHPMailer(true);
            try {

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nknakum16@gmail.com';  
                $mail->Password   = 'ofuv psaf shjh iegx';    
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;


                $mail->setFrom('nknakum16@gmail.com', 'Shakti AI');
                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code for Shakti AI';
                $mail->Body    = "
    <html>
    <head>
      <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container {
          background-color: #f4f4f4;
          padding: 20px;
          border-radius: 8px;
          max-width: 500px;
          margin: auto;
        }
        .otp {
          font-size: 28px;
          font-weight: bold;
          color: #4CAF50;
          margin: 20px 0;
        }
        .footer {
          font-size: 12px;
          color: #888;
          margin-top: 20px;
        }
      </style>
    </head>
    <body>
      <div class='container'>
        <h2>Welcome to Shakti AI</h2>
        <p>Hi $full_name,</p>
        <p>Use the OTP below to complete your signup:</p>
        <div class='otp'>$otp</div>
        <p>This OTP is valid for 1 minute. Do not share it with anyone.</p>
        <p class='footer'>Shakti AI • Empowering Intelligence</p>
      </div>
    </body>
    </html>
    ";



                $mail->send();
            } catch (Exception $e) {
                throw new Exception('OTP email could not be sent. Mailer Error: ' . $mail->ErrorInfo);
            }
            session_start();
            $_SESSION['otp_time'] = time();

            $user_id = $stmt->insert_id;
            $response = [
                'status' => 'otp_sent',
                'message' => 'OTP sent to your email. Please verify.',
                'user_id' => $user_id
            ];
        } 
        else {
            throw new Exception('Registration failed: ' . $stmt->error);
        }
        $stmt->close();
    }
} 

catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
} 

finally {
    if (isset($conn)) {
        $conn->close();
    }
    echo json_encode($response);
}


?>


<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$servername = "localhost";
$username = "root";
$password = '';
$dbname = "shakti";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$api_calls_today = 0;
$today = date('Y-m-d');
$result = $conn->query("SELECT calls_today FROM api_stats WHERE stat_date = '$today'");

if ($row = $result->fetch_assoc()) {
    $api_calls_today = (int)$row['calls_today'];
}


$health = 0;

$total_users = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM users");   

if ($row = $result->fetch_assoc()) {
    $total_users = (int)$row['cnt'];
} 

else {
    $total_users = 0;
}

if (isset($_GET['action']) && $_GET['action'] === 'system_health') {

    require_once 'mychat.php';
    if (class_exists('ShaktiAI')) {
        $shakti = new ShaktiAI();
        $health_status = $shakti->check_model_health();

        foreach ($health_status as $model => &$status) {
            
            if (isset($status['response_time'])) {
                $status['response_time'] = round($status['response_time'], 2);
            }
        }
        echo json_encode([
            'status' => 'success',
            'system_health' => $health_status
        ]);
        exit;

    } 
    
    else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ShaktiAI class not found.'
        ]);
        exit;
    }
}

$api_call_all_time = 0;
$result = $conn->query("SELECT sum(calls_today) as Total FROM api_stats");
if ($row = $result->fetch_assoc()) {
    $api_call_all_time = (int)$row['Total'];
}



$conn->close();


echo json_encode([
    'status' => 'success',
    'stats' => [
        'total_users' => $total_users,
        'active_sessions' => 80,
        'ai_conversations' => 22,
        'api_calls_today' => $api_calls_today ,
        'All_Time_API_Call' => $api_call_all_time,
        'system_health' => $health_status
    ]
]); 

?>
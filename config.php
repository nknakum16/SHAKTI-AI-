<?php


$configFile = __DIR__ . '/config.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['default_model'])) {
        file_put_contents($configFile, json_encode(['default_model' => $input['default_model']]));
        echo json_encode(['status' => 'success']);
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'No model provided']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        echo json_encode(['status' => 'success', 'default_model' => $config['default_model'] ?? 'shakti']);
        exit;
    }
    echo json_encode(['status' => 'success', 'default_model' => 'shakti']);
    exit;
}
?>


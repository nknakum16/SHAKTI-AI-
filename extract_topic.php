<?php
// Suppress all PHP errors and warnings from being displayed
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

class TopicExtractor {
    private $api_url = "";
    private $headers = [
        'Content-Type: application/json',
        'Authorization: '
    ];

    public function extractTopic($chatHistory) {
        try {
            // Limit chat history to last 10 messages to avoid token limits
            $recentMessages = array_slice($chatHistory, -10);
            
            // Create a concise conversation summary
            $conversationText = "";
            foreach ($recentMessages as $message) {
                if ($message['role'] === 'user') {
                    $conversationText .= "User: " . $message['content'] . "\n";
                } else if ($message['role'] === 'assistant') {
                    $conversationText .= "Assistant: " . $message['content'] . "\n";
                }
            }

            if (strlen($conversationText) < 50) {
                return [
                    'success' => true,
                    'topic' => 'New Conversation',
                    'icon' => 'bi-chat-dots'
                ];
            }

            $prompt = "What is the main topic of this conversation? Return just the topic in 2-3 words. Conversation:\n\n$conversationText";

            $payload = [
                "model" => "gemini-1.5-flash",
                "messages" => [
                    ["role" => "user", "content" => $prompt]
                ],
                "max_tokens" => 50,
                "temperature" => 0.3
            ];

            $ch = curl_init($this->api_url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $this->headers,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $http_code !== 200) {
                throw new Exception("API request failed");
            }

            $response_data = json_decode($response, true);
            if (!isset($response_data['choices'][0]['message']['content'])) {
                throw new Exception("Invalid response format");
            }

            $topic = trim($response_data['choices'][0]['message']['content']);
            
            $topic = trim($topic, '"\'.,!?');
            
            if (strlen($topic) > 30) {
                $topic = substr($topic, 0, 27) . '...';
            }

            $icon = $this->getTopicIcon($topic);

            return [
                'success' => true,
                'topic' => $topic,
                'icon' => $icon
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'topic' => 'General Chat',
                'icon' => 'bi-chat-dots'
            ];
        }
    }

    private function getTopicIcon($topic) {
        $topic_lower = strtolower($topic);
        
        $icon_mappings = [
            'code' => 'bi-code-slash',
            'programming' => 'bi-code-slash',
            'javascript' => 'bi-code-slash',
            'python' => 'bi-code-slash',
            'php' => 'bi-code-slash',
            'html' => 'bi-code-slash',
            'css' => 'bi-code-slash',
            'database' => 'bi-database',
            'sql' => 'bi-database',
            'api' => 'bi-diagram-3',
            'integration' => 'bi-diagram-3',
            'bug' => 'bi-bug',
            'error' => 'bi-bug',
            'debug' => 'bi-bug',
            'design' => 'bi-palette',
            'ui' => 'bi-palette',
            'ux' => 'bi-palette',
            'css' => 'bi-palette',
            'styling' => 'bi-palette',
            'machine learning' => 'bi-robot',
            'ai' => 'bi-robot',
            'artificial intelligence' => 'bi-robot',
            'server' => 'bi-server',
            'backend' => 'bi-server',
            'architecture' => 'bi-server',
            'terminal' => 'bi-terminal',
            'command' => 'bi-terminal',
            'script' => 'bi-terminal',
            'help' => 'bi-question-circle',
            'question' => 'bi-question-circle',
            'support' => 'bi-headset',
            'chat' => 'bi-chat-dots',
            'conversation' => 'bi-chat-dots',
            'general' => 'bi-chat-dots'
        ];

        foreach ($icon_mappings as $keyword => $icon) {
            if (strpos($topic_lower, $keyword) !== false) {
                return $icon;
            }
        }

        return 'bi-chat-dots'; 
    }

    public function saveTopic($userId, $topic, $icon) {
        try {
            $servername = "localhost";
            $username = "root";
            $password = '';
            $dbname = "shakti";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                throw new Exception("Database connection failed");
            }

            $currentTime = date('Y-m-d H:i:s');
            
            // First, check if topic already exists for this user
            $stmt = $conn->prepare("SELECT id FROM conversation_topics WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
            // Get the ID of the most recent topic for this user
                $row = $result->fetch_assoc();
                $topicId = $row['id'];
                
                // Update existing topic using the ID
                $stmt = $conn->prepare("UPDATE conversation_topics SET topic = ?, icon = ?, updated_at = ? WHERE id = ?");
                $stmt->bind_param("sssi", $topic, $icon, $currentTime, $topicId);
            } else {
                // Insert new topic
                $stmt = $conn->prepare("INSERT INTO conversation_topics (user_id, topic, icon, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $userId, $topic, $icon, $currentTime, $currentTime);
            }

            $success = $stmt->execute();
            
            if (!$success) {
                throw new Exception("Database operation failed: " . $stmt->error);
            }
            
            $stmt->close();
            $conn->close();

            return $success;

        } catch (Exception $e) {
            error_log("Topic save error: " . $e->getMessage());
            return false;
        }
    }

    public function getTopic($userId) {
        try {
            $servername = "localhost";
            $username = "root";
            $password = '';
            $dbname = "shakti";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                throw new Exception("Database connection failed");
            }

            $stmt = $conn->prepare("SELECT topic, icon FROM conversation_topics WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                return [
                    'success' => true,
                    'topic' => $row['topic'],
                    'icon' => $row['icon']
                ];
            } else {
                $stmt->close();
                $conn->close();
                return [
                    'success' => true,
                    'topic' => 'New Conversation',
                    'icon' => 'bi-chat-dots'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'topic' => 'New Conversation',
                'icon' => 'bi-chat-dots'
            ];
        }
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    $extractor = new TopicExtractor();

    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'extract':
                if (!isset($data['chatHistory'])) {
                    throw new Exception('Chat history required for topic extraction');
                }
                
                $result = $extractor->extractTopic($data['chatHistory']); 
                
                
                if ($result['success'] && isset($data['userId'])) {
                    $saveResult = $extractor->saveTopic($data['userId'], $result['topic'], $result['icon']);
                    if (!$saveResult) {
                        error_log("Failed to save topic for user: " . $data['userId']);
                    }
                }
                
                echo json_encode($result);
                break;

            case 'get':
                if (!isset($data['userId'])) {
                    throw new Exception('User ID required');
                }
                
                $result = $extractor->getTopic($data['userId']);
                echo json_encode($result);
                break;

            default:
                throw new Exception('Invalid action specified');
        }
    } else {
        throw new Exception('Action parameter required');
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage()
    ]);
}

ob_end_flush();
?> 


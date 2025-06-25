<?php
// Database setup script for conversation topics
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shakti";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create conversation_topics table
    $sql = "CREATE TABLE IF NOT EXISTS `conversation_topics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(255) NOT NULL,
        `topic` varchar(100) NOT NULL,
        `icon` varchar(50) NOT NULL DEFAULT 'bi-chat-dots',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Conversation topics table created successfully or already exists!\n";
    } else {
        echo "❌ Error creating table: " . $conn->error . "\n";
    }

    $conn->close();
    echo "🎉 Database setup completed!\n";

} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
}
?> 
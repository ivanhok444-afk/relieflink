<?php
require_once __DIR__ . '/../config/Database.php';

class BusinessChat {
    private $connection;

    public function __construct() {
        $database = new Database();
        $this->connection = $database->connect();
    }

    public function getOrCreateConversation($userId) {
        $statement = $this->connection->prepare(
            'INSERT INTO chat_conversations (user_id) VALUES (?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $statement->execute([$userId]);
        return (int) $this->connection->lastInsertId();
    }

    public function getUserConversation($userId) {
        $statement = $this->connection->prepare(
            'SELECT id, user_id FROM chat_conversations WHERE user_id = ?'
        );
        $statement->execute([$userId]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function getConversationById($conversationId) {
        $statement = $this->connection->prepare(
            'SELECT c.id, c.user_id, u.username, u.first_name, u.last_name
             FROM chat_conversations c
             JOIN users u ON u.id = c.user_id
             WHERE c.id = ?'
        );
        $statement->execute([$conversationId]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminConversations() {
        $statement = $this->connection->query(
            'SELECT c.id, c.user_id, u.username, u.first_name, u.last_name,
                    latest.message AS last_message, latest.created_at AS last_message_at,
                    (SELECT COUNT(*) FROM chat_messages unread
                     WHERE unread.conversation_id = c.id
                       AND unread.sender_id = c.user_id
                       AND unread.read_at IS NULL) AS unread_count
             FROM chat_conversations c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN chat_messages latest ON latest.id = (
                 SELECT MAX(m.id) FROM chat_messages m WHERE m.conversation_id = c.id
             )
             ORDER BY COALESCE(latest.created_at, c.updated_at) DESC'
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessages($conversationId, $afterId = 0) {
        $statement = $this->connection->prepare(
            'SELECT m.id, m.conversation_id, m.sender_id, m.message, m.created_at, m.read_at,
                    u.username AS sender_name
             FROM chat_messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.id ASC
             LIMIT 200'
        );
        $statement->execute([$conversationId, $afterId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendMessage($conversationId, $senderId, $message) {
        $this->connection->beginTransaction();
        try {
            $insert = $this->connection->prepare(
                'INSERT INTO chat_messages (conversation_id, sender_id, message) VALUES (?, ?, ?)'
            );
            $insert->execute([$conversationId, $senderId, $message]);
            $messageId = (int) $this->connection->lastInsertId();
            $update = $this->connection->prepare(
                'UPDATE chat_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?'
            );
            $update->execute([$conversationId]);
            $this->connection->commit();
            return $messageId;
        } catch (Throwable $error) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    public function markMessagesRead($conversationId, $readerId) {
        $statement = $this->connection->prepare(
            'UPDATE chat_messages SET read_at = CURRENT_TIMESTAMP
             WHERE conversation_id = ? AND sender_id <> ? AND read_at IS NULL'
        );
        $statement->execute([$conversationId, $readerId]);
    }
}

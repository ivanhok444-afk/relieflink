<?php
require_once __DIR__ . '/../config/SessionHandler.php';
require_once __DIR__ . '/../models/BusinessChat.php';

AppSessionHandler::requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$userId = (int) AppSessionHandler::getUserId();
$isAdmin = AppSessionHandler::isAdmin();
$chat = new BusinessChat();

function chatRespond($status, $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'messages';

    if ($isAdmin && $action === 'conversations') {
        chatRespond(200, ['conversations' => $chat->getAdminConversations()]);
    }

    if ($isAdmin) {
        $conversationId = filter_var($_GET['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        $conversation = $conversationId ? $chat->getConversationById($conversationId) : false;
    } else {
        $conversationId = (int) $chat->getOrCreateConversation($userId);
        $conversation = $chat->getUserConversation($userId);
    }

    if (!$conversation) {
        chatRespond(404, ['error' => 'Conversation not found.']);
    }

    $afterId = filter_var($_GET['after_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($afterId === false || $afterId < 0) {
        chatRespond(400, ['error' => 'Invalid message cursor.']);
    }

    $conversationId = (int) $conversation['id'];
    $chat->markMessagesRead($conversationId, $userId);
    chatRespond(200, [
        'conversation_id' => $conversationId,
        'messages' => $chat->getMessages($conversationId, $afterId)
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    chatRespond(405, ['error' => 'Method not allowed.']);
}

AppSessionHandler::validateCsrfToken();

$message = $_POST['message'] ?? '';
if (!is_string($message)) {
    chatRespond(400, ['error' => 'Enter a valid message.']);
}
$message = trim($message);
$characterCount = preg_match_all('/./us', $message, $unusedMatches);
if ($message === '' || $characterCount === false || $characterCount > 2000) {
    chatRespond(400, ['error' => 'Messages must contain 1 to 2,000 characters.']);
}

if ($isAdmin) {
    $conversationId = filter_var($_POST['conversation_id'] ?? null, FILTER_VALIDATE_INT);
    $conversation = $conversationId ? $chat->getConversationById($conversationId) : false;
} else {
    $conversationId = (int) $chat->getOrCreateConversation($userId);
    $conversation = $chat->getUserConversation($userId);
}

if (!$conversation) {
    chatRespond(404, ['error' => 'Conversation not found.']);
}

$messageId = $chat->sendMessage((int) $conversation['id'], $userId, $message);
chatRespond(201, ['message_id' => $messageId]);

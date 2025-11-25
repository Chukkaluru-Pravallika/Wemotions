<?php
session_start();
require_once 'comment_functions.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wemotions";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $post_id = intval($_POST['post_id']);
                $content = trim($_POST['content']);
                
                if (empty($content)) {
                    $response['message'] = 'Comment content cannot be empty';
                    break;
                }
                
                if (strlen($content) > 1000) {
                    $response['message'] = 'Comment must be less than 1000 characters';
                    break;
                }
                
                $comment_id = addComment($post_id, $user_id, $content, $conn);
                if ($comment_id) {
                    $comment = getCommentById($comment_id, $conn);
                    $response['success'] = true;
                    $response['message'] = 'Comment added successfully';
                    $response['comment'] = [
                        'id' => $comment['id'],
                        'content' => htmlspecialchars($comment['content']),
                        'username' => $comment['username'],
                        'display_name' => $comment['display_name'],
                        'created_at' => date('M j, Y g:i A', strtotime($comment['created_at']))
                    ];
                } else {
                    $response['message'] = 'Failed to add comment';
                }
                break;
                
            case 'edit':
                $comment_id = intval($_POST['comment_id']);
                $content = trim($_POST['content']);
                
                if (empty($content)) {
                    $response['message'] = 'Comment content cannot be empty';
                    break;
                }
                
                if (updateComment($comment_id, $content, $user_id, $conn)) {
                    $response['success'] = true;
                    $response['message'] = 'Comment updated successfully';
                    $response['content'] = htmlspecialchars($content);
                } else {
                    $response['message'] = 'Failed to update comment or comment not found';
                }
                break;
                
            case 'delete':
                $comment_id = intval($_POST['comment_id']);
                
                if (deleteComment($comment_id, $user_id, $conn)) {
                    $response['success'] = true;
                    $response['message'] = 'Comment deleted successfully';
                } else {
                    $response['message'] = 'Failed to delete comment or comment not found';
                }
                break;
                
            default:
                $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'Invalid request method';
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
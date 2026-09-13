<?php
// delete_comment.php
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Validate comment_id
if (!isset($_POST['comment_id']) || !is_numeric($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = intval($_POST['comment_id']);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Begin transaction
    $conn->begin_transaction();

    // First verify the comment exists and belongs to the user
    $verify_stmt = $conn->prepare("
        SELECT c.anime_id, c.user_id 
        FROM comments c 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $verify_stmt->bind_param("ii", $comment_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Comment not found or unauthorized");
    }

    $comment_data = $result->fetch_assoc();
    $anime_id = $comment_data['anime_id'];
    $verify_stmt->close();

    // Delete the comment
    $delete_stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $comment_id, $user_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete comment");
    }
    $delete_stmt->close();

    // Recalculate average rating
    $avg_stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating 
        FROM comments 
        WHERE anime_id = ?
    ");
    $avg_stmt->bind_param("i", $anime_id);
    $avg_stmt->execute();
    $avg_result = $avg_stmt->get_result();
    $new_average = $avg_result->fetch_assoc()['avg_rating'];
    $avg_stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Comment deleted successfully',
        'average_rating' => $new_average ? round($new_average, 1) : 'N/A'
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_error === null) {
        $conn->rollback();
    }
    error_log("Delete comment error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Error deleting comment: " . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
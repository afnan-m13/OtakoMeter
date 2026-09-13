<?php
// get_comments.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['anime_id']) || !is_numeric($_GET['anime_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid anime ID']);
    exit;
}

$anime_id = intval($_GET['anime_id']);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Fetch comments with additional fields for deletion functionality
    $comments_stmt = $conn->prepare("
        SELECT 
            c.id as comment_id,
            u.username,
            c.user_id,
            c.rating,
            c.comment_text,
            c.created_at 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.anime_id = ?
        ORDER BY c.created_at DESC
    ");
    
    $comments_stmt->bind_param("i", $anime_id);
    $comments_stmt->execute();
    $result = $comments_stmt->get_result();

    $comments = [];
    $total_rating = 0;
    $rating_count = 0;

    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => $row['comment_id'],
            'username' => htmlspecialchars($row['username']),
            'user_id' => $row['user_id'],
            'rating' => $row['rating'],
            'comment' => htmlspecialchars($row['comment_text']),
            'created_at' => $row['created_at']
        ];
        $total_rating += $row['rating'];
        $rating_count++;
    }

    $average_rating = $rating_count > 0 ? round($total_rating / $rating_count, 1) : 'N/A';

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'average_rating' => $average_rating
    ]);

} catch (Exception $e) {
    error_log("Get comments error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Error fetching comments: " . $e->getMessage()
    ]);
} finally {
    if (isset($comments_stmt)) {
        $comments_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
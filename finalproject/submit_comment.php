<?php
session_start();
include('db.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to comment']);
    exit();
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Validate inputs
$anime_id = isset($_POST['anime_id']) ? intval($_POST['anime_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment_text = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($anime_id <= 0 || $rating < 1 || $rating > 10 || empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (anime_id, user_id, rating, comment_text) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $anime_id, $_SESSION['user_id'], $rating, $comment_text);

if ($stmt->execute()) {
    // Fetch updated comments for this anime
    $comments_stmt = $conn->prepare("
        SELECT u.username, c.rating, c.comment_text, c.created_at 
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
            'username' => htmlspecialchars($row['username']),
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
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit comment']);
}

$stmt->close();
$conn->close();
?>
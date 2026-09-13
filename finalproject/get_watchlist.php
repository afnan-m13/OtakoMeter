
<?php
// get_watchlist.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_database);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("
        SELECT a.* 
        FROM animes a 
        JOIN watchlist w ON a.id = w.anime_id 
        WHERE w.user_id = ?
        ORDER BY w.id DESC
    ");

    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception("SQL execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Failed to get result set: " . $stmt->error);
    }

    $watchlist = [];
    while ($row = $result->fetch_assoc()) {
        $row['genres'] = explode(',', $row['genres']);
        $watchlist[] = $row;
    }

    // Check if watchlist is empty
    if (empty($watchlist)) {
        echo json_encode(['success' => true, 'watchlist' => [], 'message' => 'No items in watchlist']);
    } else {
        echo json_encode(['success' => true, 'watchlist' => $watchlist]);
    }

} catch (Exception $e) {
    error_log("Watchlist Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch watchlist: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
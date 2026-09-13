
<?php
// update_watchlist.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if (!isset($_POST['anime_id'])) {
    echo json_encode(['success' => false, 'message' => 'No anime ID provided']);
    exit();
}

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_database);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $anime_id = intval($_POST['anime_id']);
    $user_id = $_SESSION['user_id'];

    // Check if the anime exists
    $check_anime = $conn->prepare("SELECT id FROM animes WHERE id = ?");
    $check_anime->bind_param("i", $anime_id);
    $check_anime->execute();
    $anime_result = $check_anime->get_result();
    
    if ($anime_result->num_rows === 0) {
        throw new Exception("Anime not found");
    }

    // Check if already in watchlist
    $check_stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND anime_id = ?");
    $check_stmt->bind_param("ii", $user_id, $anime_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Remove from watchlist
        $delete_stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND anime_id = ?");
        $delete_stmt->bind_param("ii", $user_id, $anime_id);
        $delete_stmt->execute();
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to watchlist
        $insert_stmt = $conn->prepare("INSERT INTO watchlist (user_id, anime_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $anime_id);
        $insert_stmt->execute();
        echo json_encode(['success' => true, 'action' => 'added']);
    }

} catch (Exception $e) {
    error_log("Watchlist Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($delete_stmt)) $delete_stmt->close();
    if (isset($insert_stmt)) $insert_stmt->close();
    if (isset($conn)) $conn->close();
}
?>
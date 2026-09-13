<?php
session_start();
include('db.php');

header('Content-Type: application/json');

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'results' => []
    ]);
    exit();
}

// Get search query parameter
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($searchTerm)) {
    echo json_encode([
        'success' => false,
        'message' => 'No search term provided',
        'results' => []
    ]);
    exit();
}

// Prepare the search query
$searchTerm = $conn->real_escape_string($searchTerm);
$sql = "SELECT 
            id,
            title,
            image_path,
            type,
            score,
            description,
            genres,
            anime_status,
            start_airing,
            episodes,
            trailer_url
        FROM animes 
        WHERE title LIKE ?
        LIMIT 10";

$stmt = $conn->prepare($sql);
$searchPattern = "%{$searchTerm}%";
$stmt->bind_param("s", $searchPattern);
$stmt->execute();
$result = $stmt->get_result();

$searchResults = [];
while ($row = $result->fetch_assoc()) {
    // Convert genres from stored format (assuming comma-separated string)
    // to array for JSON response
    $row['genres'] = explode(',', $row['genres']);
    
    // Ensure all necessary fields are present
    $searchResults[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'image_path' => $row['image_path'],
        'type' => $row['type'],
        'score' => $row['score'],
        'description' => $row['description'],
        'genres' => $row['genres'],
        'anime_status' => $row['anime_status'],
        'start_airing' => $row['start_airing'],
        'episodes' => $row['episodes'],
        'trailer_url' => $row['trailer_url']
    ];
}

echo json_encode([
    'success' => true,
    'message' => count($searchResults) > 0 ? 'Results found' : 'No results found',
    'results' => $searchResults
]);

$stmt->close();
$conn->close();
?>
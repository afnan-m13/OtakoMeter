<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: users.php");
    exit();
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_database);

if ($conn->connect_errno) {
    error_log("Failed to connect to MySQL: " . $conn->connect_error);
    die("Sorry, there was a problem connecting to our database.");
}

$stmt = $conn->prepare("SELECT id, title, score, image_path, genres, description, 
                       duration, start_airing, trailer_url 
                       FROM animes WHERE type = 'movie'");
$stmt->execute();
$result = $stmt->get_result();

$moviesData = array();
while ($row = $result->fetch_assoc()) {
    $row['genres'] = explode(',', $row['genres']);
    $moviesData[] = $row;
}

$watchlist = array();
$watchlist_stmt = $conn->prepare("SELECT anime_id FROM watchlist WHERE user_id = ?");
$watchlist_stmt->bind_param("i", $_SESSION['user_id']);
$watchlist_stmt->execute();
$result = $watchlist_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $watchlist[] = $row['anime_id'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap and JavaScript links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <title>All Anime Movies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css">
    <style>
        .all-movies-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--primary-color);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }

        .back-btn i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <div class="logo-container">
                <a class="custom-logo">OTAKU</a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="Home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Home.php">Top 10</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="series.php">Series</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="movies.php">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Home.php">Watchlist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#footer">Contact</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <div class="searchBox me-3">
                        <div class="searchToggle">
                            <i class="bx bx-x cancel"></i>
                            <i class="bx bx-search search"></i>
                        </div>
                        <div class="search-field">
                            <input type="text" placeholder="Search anime..." />
                            <i class="bx bx-search"></i>
                        </div>
                    </div>

                    <div class="user-dropdown">
                        <div class="user-icon">
                            <i class="bx bx-user"></i>
                        </div>
                        <div class="user-dropdown-menu">
                            <div class="user-profile">
                                <div class="user-profile-image">
                                    <i class="bx bx-user"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                                    <div style="font-size: 0.8rem; color: #888;">
                                        <?php echo htmlspecialchars($_SESSION['email']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-item" onclick="openUpdateProfile()">
                                <i class="bx bx-user-circle me-2"></i>Update Profile
                            </div>
                            <div class="dropdown-item text-danger" onclick="logout()">
                                <i class="bx bx-log-out me-2"></i>Logout
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="page-header">
            <a href="Home.php" class="back-btn">
                <i class='bx bx-arrow-back'></i> Back to Home
            </a>
            <h1>All Anime Movies</h1>
        </div>

        <div class="all-movies-container" id="moviesContainer"></div>

        <!-- Modal -->
        <div id="anime-modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>

                <div class="modal-header">
                    <div class="modal-poster">
                        <img id="modal-anime-poster" src="" alt="Anime Poster">
                        <div id="average-rating">
                            Average Rating: <span>N/A</span>
                        </div>
                    </div>

                    <div class="modal-info">
                        <h2 id="modal-anime-name"></h2>
                        <div class="anime-details">
                            <p><strong>Genre:</strong> <span id="modal-anime-genre"></span></p>
                            <p><strong>Description:</strong> <span id="modal-anime-description"></span></p>
                            <p><strong id="status-label">Status:</strong> <span id="modal-anime-animeStatus"></span></p>
                            <p id="airing-release-label"><strong>Release Date:</strong> <span
                                    id="modal-anime-startAiring"></span></p>
                            <p><strong id="episodes-label">Duration:</strong> <span id="modal-anime-episodes"></span>
                            </p>
                            <p><strong>Trailer:</strong> <a id="modal-anime-trailer" href="#" target="_blank">Watch
                                    Trailer</a></p>
                        </div>
                    </div>
                </div>

                <div class="comments-section">
                    <h3>Comments & Ratings</h3>
                    <div class="comment-input-container">
                        <div class="star-rating-container">
                            <p id="rating-message">Rate Your Experience</p>
                            <div class="stars">
                                <div class="star-container" data-rating="1">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">1</span>
                                </div>
                                <div class="star-container" data-rating="2">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">2</span>
                                </div>
                                <div class="star-container" data-rating="3">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">3</span>
                                </div>
                                <div class="star-container" data-rating="4">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">4</span>
                                </div>
                                <div class="star-container" data-rating="5">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">5</span>
                                </div>
                                <div class="star-container" data-rating="6">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">6</span>
                                </div>
                                <div class="star-container" data-rating="7">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">7</span>
                                </div>
                                <div class="star-container" data-rating="8">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">8</span>
                                </div>
                                <div class="star-container" data-rating="9">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">9</span>
                                </div>
                                <div class="star-container" data-rating="10">
                                    <i class="fa-regular fa-star"></i>
                                    <span class="number">10</span>
                                </div>
                            </div>
                        </div>
                        <textarea id="comment-input" class="comment-input" placeholder="Write a comment..."></textarea>
                        <button id="submit-comment" class="submit-comment-btn">Submit</button>
                    </div>
                    <div id="comments-list" class="comments-list"></div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <a name="footer"></a>
        <div class="footer-container">
            <div class="social-icons">
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Visit Twitter X Profile">
                    <i class="fas fa-xmark"></i>
                </a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Google Plus"><i class="fab fa-google-plus-g"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
            <div class="footer-nav">
                <ul>
                    <li><a href="Home.php">Home</a></li>
                    <li><a href="Home.php">News</a></li>
                    <li><a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw=="
                            target="_blank">About</a></li>
                    <li><a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw=="
                            target="_blank">Contact Us</a></li>
                    <li><a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw=="
                            target="_blank">Our Team</a></li>
                </ul>
            </div>
            <div class="copyright">
                Copyright &copy; 2024. Designed by <span class="designer">OtakuMeter</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const moviesData = <?php echo json_encode($moviesData); ?>;
        const userWatchlist = <?php echo json_encode($watchlist); ?>;
        const currentUsername = '<?php echo htmlspecialchars($_SESSION['username']); ?>';

        // User dropdown menu controls
        const userIcon = document.querySelector(".user-icon");
        const userDropdownMenu = document.querySelector(".user-dropdown-menu");

        userIcon.addEventListener("click", () => {
            userDropdownMenu.classList.toggle("show");
        });

        // Close dropdown on outside click
        document.addEventListener("click", (event) => {
            if (!userIcon.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                userDropdownMenu.classList.remove("show");
            }
        });

        function openUpdateProfile() {
            window.location.href = 'users.php?form=profileForm';
        }

    </script>
    <script src="movies.js"></script>
</body>

</html>
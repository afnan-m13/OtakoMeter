<!-- Home.php -->
<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: users.php");
    exit();
}

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 7200)) {
    session_destroy();
    header("Location: users.php?timeout=1");
    exit();
}


$conn = new mysqli(
    $db_host,
    $db_user,
    $db_pass,
    $db_database
);

if ($conn->connect_errno) {
    error_log("Failed to connect to MySQL: " . $conn->connect_error);
    die("Sorry, there was a problem connecting to our database.");
}


function fetchAnimesByType($type)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, title, score, image_path, genres, description, 
                            episodes, start_airing, anime_status, trailer_url, 
                            duration, type FROM animes WHERE type = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();

    // Add error checking
    if ($stmt->errno) {
        error_log("MySQL Error: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();

    $animes = [];
    while ($row = $result->fetch_assoc()) {
        $row['genres'] = explode(',', $row['genres']); // Convert genres to array
        $animes[] = $row;
    }

    $stmt->close();

    return $animes;
}

// Fetch anime data
$topAnimeData = fetchAnimesByType('top');
$seriesData = fetchAnimesByType('series');
$moviesData = fetchAnimesByType('movie');

// Check if already in watchlist
$user_watchlist = []; // Default empty array
$watchlist_stmt = $conn->prepare("SELECT anime_id FROM watchlist WHERE user_id = ?");
$watchlist_stmt->bind_param("i", $_SESSION['user_id']);
$watchlist_stmt->execute();
$result = $watchlist_stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_watchlist[] = $row['anime_id'];
}

if (isset($_POST['anime_id'])) {
    $anime_id = intval($_POST['anime_id']);
    $user_id = $_SESSION['user_id'];

    // Add detailed error logging
    error_log("Attempting watchlist update: User ID = $user_id, Anime ID = $anime_id");

    try {
        $conn->begin_transaction(); // Start transaction for better error handling

        // Check existing watchlist entry
        $check_stmt = $conn->prepare("SELECT * FROM watchlist WHERE user_id = ? AND anime_id = ?");
        $check_stmt->bind_param("ii", $user_id, $anime_id);

        if (!$check_stmt->execute()) {
            throw new Exception("Check query failed: " . $check_stmt->error);
        }

        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Remove from watchlist
            $delete_stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND anime_id = ?");
            $delete_stmt->bind_param("ii", $user_id, $anime_id);

            if (!$delete_stmt->execute()) {
                throw new Exception("Delete query failed: " . $delete_stmt->error);
            }

            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Add to watchlist
            $insert_stmt = $conn->prepare("INSERT INTO watchlist (user_id, anime_id) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $user_id, $anime_id);

            if (!$insert_stmt->execute()) {
                throw new Exception("Insert query failed: " . $insert_stmt->error);
            }

            echo json_encode(['success' => true, 'action' => 'added']);
        }

        $conn->commit(); // Commit transaction if all queries succeed
    } catch (Exception $e) {
        $conn->rollback(); // Rollback in case of any error
        error_log("Watchlist Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Fetch comments with username
$comments_stmt = $conn->prepare("SELECT u.username, c.rating, c.comment_text, c.created_at 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.anime_id = ?
    ORDER BY c.created_at DESC");

// Check if you have a specific anime ID to fetch comments for
if (isset($_GET['anime_id'])) {
    $anime_id = intval($_GET['anime_id']);
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

    $comments_stmt->close();
    exit();
}

// Get user data from session
$username = htmlspecialchars($_SESSION['username']);
$email = htmlspecialchars($_SESSION['email']);
?>

<html>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap and JavaScript links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- boxicon CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css" />
    <title>Otako Meter</title>
</head>

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <div class="logo-container">
                <a class="custom-logo">OTAKU</a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Navigation links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#top">Top 10</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="series.php">Series</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#Watchlist">Watchlist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#footer">Contact</a>
                    </li>
                </ul>

                <!-- Search and User Dropdown -->
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

                    <!-- User Dropdown -->
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
    <div class="container-fluid mt-3 ">
        <a name="slider"></a>
        <div class="slider">
            <div class="slides">
                <div class="slide">
                    <img src="Photo/S2.gif" alt="Slide 1">
                    <div class="slide-title">DAN DA DAN</div>
                </div>
                <div class="slide">
                    <img src="Photo/S33.gif" alt="Slide 2">
                    <div class="slide-title">blue Lock</div>
                </div>
                <div class="slide">
                    <img src="Photo/S4.gif" alt="Slide 3">
                    <div class="slide-title">Arcane</div>
                </div>
            </div>

            <div class="slider-controls">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
                <span class="dot" data-slide="2"></span>
            </div>
        </div>
    </div>
    <a name="top"></a>


    <main>

        <!-- Top 10 Anime -->
        <div class="carousel-container">
            <h2 class="category-title">Top 10 Anime This Week</h2>
            <div class="arrow arrow-left" onclick="moveCarousel(-1, 'carousel1')">←</div>
            <div class="arrow arrow-right" onclick="moveCarousel(1, 'carousel1')">→</div>
            <div class="carousel" id="carousel1"></div>
        </div>

        <!-- Empty Watchlist -->
        <div class="carousel-container">
            <a name="Watchlist"></a>
            <h2 class="category-title">Your Watchlist</h2>
            <div class="arrow arrow-left" onclick="moveCarousel(-1, 'watchlist-carousel')">←</div>
            <div class="arrow arrow-right" onclick="moveCarousel(1, 'watchlist-carousel')">→</div>
            <div class="carousel" id="watchlist-carousel"></div>
        </div>

        <!-- Series -->
        <div class="carousel-container">
            <h2 class="category-title">Series</h2>
            <div class="arrow arrow-left" onclick="moveCarousel(-1, 'carousel2')">←</div>
            <div class="arrow arrow-right" onclick="moveCarousel(1, 'carousel2')">→</div>
            <div class="carousel" id="carousel2"></div>
        </div>

        <!-- Movies -->
        <div class="carousel-container">
            <h2 class="category-title">Movies</h2>
            <div class="arrow arrow-left" onclick="moveCarousel(-1, 'carousel3')">←</div>
            <div class="arrow arrow-right" onclick="moveCarousel(1, 'carousel3')">→</div>
            <div class="carousel" id="carousel3"></div>
        </div>

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
                            <p id="airing-release-label"><strong>Start Airing:</strong> <span
                                    id="modal-anime-startAiring"></span></p>
                            <p><strong id="episodes-label">Episodes:</strong> <span id="modal-anime-episodes"></span>
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


        <script src="Home1.js"></script>
        <script>
            const topAnimeData = <?php echo json_encode($topAnimeData); ?>;
            const seriesData = <?php echo json_encode($seriesData); ?>;
            const moviesData = <?php echo json_encode($moviesData); ?>;
            const currentUsername = '<?php echo htmlspecialchars($_SESSION['username']); ?>';

            function openUpdateProfile() {
                window.location.href = 'users.php?form=profileForm';
            }

            class SimpleSlider {
                constructor(sliderElement) {
                    this.slider = sliderElement;
                    this.slides = this.slider.querySelector('.slides');
                    this.dots = this.slider.querySelectorAll('.dot');
                    this.currentSlide = 0;
                    this.timer = null;

                    this.init();
                }

                init() {
                    this.dots.forEach(dot => {
                        dot.addEventListener('click', () => {
                            this.goToSlide(parseInt(dot.dataset.slide));
                        });
                    });

                    this.startAutoSlide();
                    this.setupHoverListeners();
                }

                goToSlide(index) {
                    this.currentSlide = index;
                    this.slides.style.transform = `translateX(-${index * 100}%)`;

                    this.dots.forEach(dot => dot.classList.remove('active'));
                    this.dots[index].classList.add('active');
                }

                startAutoSlide() {
                    this.timer = setInterval(() => {
                        this.currentSlide = (this.currentSlide + 1) % this.dots.length;
                        this.goToSlide(this.currentSlide);
                    }, 3000);
                }

                setupHoverListeners() {
                    this.slider.addEventListener('mouseenter', () => {
                        clearInterval(this.timer);
                    });

                    this.slider.addEventListener('mouseleave', () => {
                        this.startAutoSlide();
                    });
                }
            }

            // Initialize the slider
            document.addEventListener('DOMContentLoaded', () => {
                new SimpleSlider(document.querySelector('.slider'));
            });
        </script>
    </main>


    <footer>
        <div class="footer-container">
            <a name="footer"></a>
            <div class="social-icons">
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    taria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="X"><i class="fab fa-X"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="Google Plus"><i class="fab fa-google-plus-g"></i></a>
                <a href="https://www.instagram.com/otak.u2606/profilecard/?igsh=MTZ6N3FlbW1hMTRyMw==" target="_blank"
                    aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
            <div class="footer-nav">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#slider">News</a></li>
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

</body>

</html>
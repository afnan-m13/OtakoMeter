// Search toggle functionality
const searchToggle = document.querySelector(".searchToggle");
const searchField = document.querySelector(".search-field");

searchToggle.addEventListener("click", () => {
    searchToggle.classList.toggle("active");
    searchField.style.display = searchToggle.classList.contains("active") ? "block" : "none";
});

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


// ===============================
// Carousel positions tracking
let positions = { 'carousel1': 0, 'carousel2': 0, 'carousel3': 0, 'watchlist-carousel': 0 };
let user_watchlist = [];


// Modal-related elements
const animeModal = document.getElementById('anime-modal');
const modalClose = document.querySelector('.modal-close');
let currentAnimeData = null;

// Comment system elements
const commentInput = document.getElementById('comment-input');
const submitCommentBtn = document.getElementById('submit-comment');
const commentsList = document.getElementById('comments-list');
const averageRatingSpan = document.querySelector('#average-rating span');

// Rating system elements
const starContainers = document.querySelectorAll('.star-container');
const ratingMessage = document.getElementById('rating-message');
let currentRating = 0;

// Search-related elements
const searchInput = document.querySelector('.search-field input');
const searchResults = document.createElement('div');
searchResults.className = 'search-results';
document.querySelector('.search-field').appendChild(searchResults);




// Rating messages mapping
const ratingMessages = {
    0: 'Rate Your Experience',
    1: 'Terrible',
    2: 'Bad',
    3: 'Poor',
    4: 'Fair',
    5: 'Average',
    6: 'Good',
    7: 'Very Good',
    8: 'Great',
    9: 'Excellent',
    10: 'Perfect'
};

// ===============================
//Creates carousel cards for anime content Handles watchlist button integration Adds "View More" card for series/movies
function generateCarouselContent(carouselId, data) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;

    if (typeof user_watchlist === 'undefined') {
        user_watchlist = [];
    }

    carousel.innerHTML = '';

    if (carouselId === 'watchlist-carousel' && (!data || data.length === 0)) {
        carousel.innerHTML = `
            <div class="empty-watchlist">
                <i class='bx bx-list-plus'></i>
                <p>Your watchlist is currently empty</p>
                <span>Add some anime to get started!</span>
            </div>`;
        return;
    }

    const maxVisibleCards = 8; // Show 8 cards + view more card
    data.forEach((item, index) => {
        if (carouselId !== 'carousel1' && carouselId !== 'watchlist-carousel' && index >= maxVisibleCards) {
            return;
        }

        const isInWatchlist = user_watchlist.includes(parseInt(item.id));
        const card = document.createElement('div');
        card.classList.add('card');
        card.innerHTML = `
            <div class="card-image">
                <img src="${item.image_path}" alt="${item.title}" 
                     onerror="this.src='placeholder.jpg'">
                <div class="rating">
                    <div class="score">${item.score}</div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-title">${item.title}</div>
                <button class="watchlist-btn ${isInWatchlist ? 'in-watchlist' : ''}" 
                        data-anime-id="${item.id}">
                    <i class="fas ${isInWatchlist ? 'fa-check' : 'fa-plus'}"></i>
                    <span class="text">
                        ${isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist'}
                    </span>
                </button>
            </div>
        `;

        card.querySelector('.card-image').addEventListener('click', (e) => {
            if (!e.target.closest('.watchlist-btn')) {
                openAnimeModal(item);
            }
        });

        carousel.appendChild(card);
    });

    // Add view more card for series and movies carousels
    if ((carouselId === 'carousel2' || carouselId === 'carousel3') && data.length > maxVisibleCards) {
        const viewMoreCard = document.createElement('a');
        viewMoreCard.href = carouselId === 'carousel2' ? 'series.php' : 'movies.php';
        viewMoreCard.classList.add('card', 'view-more-card');
        viewMoreCard.innerHTML = `
            <div class="card-content view-more">
                <div class="view-more-text">View More</div>
                <i class="bx bx-chevron-right"></i>
            </div>
        `;
        carousel.appendChild(viewMoreCard);
    }

    carousel.style.transform = 'translateX(0)';
    positions[carouselId] = 0;
}


// Handles carousel navigation Calculates proper positioning Prevents overflow scrolling
function moveCarousel(direction, carouselId) {
    const carousel = document.getElementById(carouselId);
    const cardWidth = carousel.querySelector('.card').offsetWidth + 20;
    const visibleCards = Math.floor(carousel.parentElement.offsetWidth / cardWidth);
    
    if (carouselId === 'carousel2' || carouselId === 'carousel3') {
        const displayedCards = 9; // 8 regular cards + 1 view more card
        const maxPosition = -(displayedCards - visibleCards) * cardWidth;
        
        positions[carouselId] += (-direction) * cardWidth;
        if (positions[carouselId] > 0) positions[carouselId] = 0;
        if (positions[carouselId] < maxPosition) positions[carouselId] = maxPosition;
    } else {
        const maxPosition = -(carousel.children.length - visibleCards) * cardWidth;
        positions[carouselId] += (-direction) * cardWidth;
        if (positions[carouselId] > 0) positions[carouselId] = 0;
        if (positions[carouselId] < maxPosition) positions[carouselId] = maxPosition;
    }

    carousel.style.transform = `translateX(${positions[carouselId]}px)`;
}

// ===============================
//Displays anime details Populates all modal field Handles different content types (movie/series) Fetches and displays comments
function openAnimeModal(anime) {
    currentAnimeData = anime;
    const isInWatchlist = user_watchlist.includes(parseInt(anime.id));

    document.getElementById('modal-anime-poster').src = anime.image_path;
    document.getElementById('modal-anime-name').textContent = anime.title;
    document.getElementById('modal-anime-genre').textContent = anime.genres.join(', ');
    document.getElementById('modal-anime-description').textContent = anime.description;
    document.getElementById('modal-anime-trailer').href = anime.trailer_url;

    // Add watchlist button to modal header
    const modalInfo = document.querySelector('.modal-info');

    // Remove existing watchlist button if present
    const existingBtn = modalInfo.querySelector('.watchlist-btn');
    if (existingBtn) {
        existingBtn.remove();
    }

    // Create new watchlist button
    const watchlistBtn = document.createElement('button');
    watchlistBtn.className = `watchlist-btn modal-watchlist-btn ${isInWatchlist ? 'in-watchlist' : ''}`;
    watchlistBtn.setAttribute('data-anime-id', anime.id);
    watchlistBtn.innerHTML = `
        <i class="fas ${isInWatchlist ? 'fa-check' : 'fa-plus'}"></i>
        <span class="text">${isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist'}</span>
    `;
    
    modalInfo.insertBefore(watchlistBtn, modalInfo.firstChild);

    // Rest of the existing modal code...
    const airingReleaseLabel = document.querySelector('#airing-release-label strong');
    const episodesLabel = document.querySelector('#episodes-label');

    if (anime.type === 'movie') {
        document.getElementById('modal-anime-animeStatus').textContent = 'Movie';
        airingReleaseLabel.textContent = 'Release Date:';
        document.getElementById('modal-anime-startAiring').textContent = anime.start_airing;
        episodesLabel.textContent = 'Duration:';
        document.getElementById('modal-anime-episodes').textContent = anime.duration;
    } else {
        document.getElementById('modal-anime-animeStatus').textContent = anime.anime_status || 'N/A';
        airingReleaseLabel.textContent = 'Start Airing:';
        document.getElementById('modal-anime-startAiring').textContent = anime.start_airing;
        episodesLabel.textContent = 'Episodes:';
        document.getElementById('modal-anime-episodes').textContent = anime.episodes;
    }

    // Initialize with empty ratings
    const ratings = [];
    averageRatingSpan.textContent = calculateAverageRating(ratings);

    // Populate comments
    const comments = [];
    populateComments({ comments, ratings });

    animeModal.style.display = 'flex';
    setTimeout(() => {
        animeModal.classList.add('active');
    }, 10);

    // Fetch comments for this specific anime
    fetch(`get_comments.php?anime_id=${anime.id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCommentsList(data.comments, currentUsername);
                averageRatingSpan.textContent = data.average_rating;
            } else {
                commentsList.innerHTML = '<div class="comment">Failed to load comments</div>';
            }
        })
        .catch(error => {
            console.error('Failed to fetch comments:', error);
            commentsList.innerHTML = '<div class="comment">Failed to load comments</div>';
        });
}


// Modal close events
modalClose.addEventListener('click', () => {
    animeModal.classList.remove('active');
    setTimeout(() => {
        animeModal.style.display = 'none';
    }, 300);
});

animeModal.addEventListener('click', (e) => {
    if (e.target === animeModal) {
        animeModal.classList.remove('active');
        setTimeout(() => {
            animeModal.style.display = 'none';
        }, 300);
    }
});


// ===============================
//Computes average from rating array Handles empty ratings case
function calculateAverageRating(ratings) {
    return ratings.length
        ? (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1)
        : 'N/A';
}

//Resets stars to initial state Clears current rating Resets rating message
function resetStarRating() {
    currentRating = 0;
    ratingMessage.textContent = ratingMessages[0];
    starContainers.forEach(star => {
        star.classList.remove('active');
        star.querySelector('i').className = 'fa-star fa-regular';
    });
}

// Star rating event listeners
starContainers.forEach(container => {
    container.addEventListener('click', () => {
        const rating = parseInt(container.dataset.rating);
        currentRating = rating;

        starContainers.forEach(star => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= rating) {
                star.classList.add('active');
                star.querySelector('i').className = 'fa-star fa-solid';
            } else {
                star.classList.remove('active');
                star.querySelector('i').className = 'fa-star fa-regular';
            }
        });

        ratingMessage.textContent = ratingMessages[rating];
    });
});

// ===============================
//Creates comment HTML structure Handles current user styling Includes delete functionality for own comments
function createCommentElement(comment, isNewComment = false) {
    const isCurrentUser = comment.username === currentUsername;
    return `
        <div class="comment ${isCurrentUser ? 'current-user-comment' : ''} ${isNewComment ? 'new-comment' : ''}">
            <div class="comment-header">
                <div class="comment-user">
                    <i class='bx bx-user'></i>
                    <div class="user-info">
                        <span class="username">${comment.username}</span>
                        ${isCurrentUser ? '<span class="current-user-tag">You</span>' : ''}
                    </div>
                </div>
                <div class="comment-rating">
                    ${Array(10).fill().map((_, i) =>
        `<i class="fa-star ${i < comment.rating ? 'fa-solid' : 'fa-regular'}" style="color: ${i < comment.rating ? '#ffd700' : '#ccc'}"></i>`
    ).join('')}
                    <span class="rating-number">${comment.rating}/10</span>
                </div>
            </div>
            <div class="comment-content">
                <p class="comment-text">${comment.comment}</p>
            </div>
            <div class="comment-footer">
                <span class="comment-time">${formatCommentDate(comment.created_at)}</span>
                ${isCurrentUser ? `<button class="delete-comment" data-comment-id="${comment.id}">
                    <i class="fa-solid fa-trash"></i>
                </button>` : ''}
            </div>
        </div>
    `;
}

//Formats dates into relative time Handles different time ranges
function formatCommentDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    // Less than 1 minute
    if (diff < 60000) {
        return 'Just now';
    }
    // Less than 1 hour
    if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
    }
    // Less than 1 day
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
    }
    // Less than 7 days
    if (diff < 604800000) {
        const days = Math.floor(diff / 86400000);
        return `${days} day${days !== 1 ? 's' : ''} ago`;
    }

    // More than 7 days
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

//Initializes comment section Handles empty comments case
function populateComments(anime) {
    const comments = anime.comments;
    commentsList.innerHTML = comments.length ?
        comments.map(comment => `
    <div class="comment">
        ${comment.includes('/10')
                ? `<strong>Rating: ${comment.split('★')[0].trim()}</strong>`
                : ''}
        <p>${comment.includes('★')
                ? comment.split('★')[1].trim()
                : comment}</p>
    </div>
    `).join('') :
        '<div class="comment">No comments yet</div>';
}

//Updates complete comments list Sorts by date Handles empty state
function updateCommentsList(comments, currentUsername) {
    if (!comments || comments.length === 0) {
        commentsList.innerHTML = `
                <div class="no-comments">
                    <i class='bx bx-message-square-detail'></i>
                    <p>No comments yet. Be the first to share your thoughts!</p>
                </div>
            `;
        return;
    }

    const commentsHTML = comments
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .map(comment => createCommentElement(comment))
        .join('');

    commentsList.innerHTML = commentsHTML;
}

// Updated submit comment event listener
submitCommentBtn.addEventListener('click', () => {
    const comment = commentInput.value.trim();

    if (!comment) {
        alert('Please write a comment');
        return;
    }

    if (!currentRating) {
        alert('Please provide a rating');
        return;
    }

    if (!currentAnimeData) {
        alert('Error: No anime selected');
        return;
    }

    const formData = new FormData();
    formData.append('anime_id', currentAnimeData.id);
    formData.append('rating', currentRating);
    formData.append('comment', comment);

    submitCommentBtn.disabled = true;
    submitCommentBtn.textContent = 'Submitting...';

    fetch('submit_comment.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newComment = {
                    id: data.comment_id, // Make sure your PHP returns this
                    username: currentUsername,
                    rating: currentRating,
                    comment: comment,
                    created_at: new Date().toISOString()
                };

                const currentComments = Array.from(commentsList.children);
                if (currentComments[0]?.classList.contains('no-comments')) {
                    commentsList.innerHTML = '';
                }

                const newCommentHTML = createCommentElement(newComment, true);
                commentsList.insertAdjacentHTML('afterbegin', newCommentHTML);

                if (data.average_rating) {
                    averageRatingSpan.textContent = data.average_rating;
                }

                commentInput.value = '';
                currentRating = 0;
                resetStarRating();

                showNotification('Comment posted successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to submit comment', 'error');
            }
        })
        .catch(error => {
            console.error('Comment submission error:', error);
            showNotification('Failed to submit comment. Please try again.', 'error');
        })
        .finally(() => {
            submitCommentBtn.disabled = false;
            submitCommentBtn.textContent = 'Submit';
        });
});


// Function to handle comment deletion
function deleteComment(event) {
    const deleteButton = event.target.closest('.delete-comment');
    if (!deleteButton) return;

    const commentId = deleteButton.dataset.commentId;
    const commentElement = deleteButton.closest('.comment');

    if (!commentId || !commentElement) {
        showNotification('Error: Could not identify comment', 'error');
        return;
    }

    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    // Optimistically remove the comment from UI
    commentElement.style.opacity = '0.5';
    deleteButton.disabled = true;

    fetch('delete_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `comment_id=${commentId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the comment with animation
                commentElement.style.height = commentElement.offsetHeight + 'px';
                commentElement.style.marginTop = '0';
                commentElement.style.marginBottom = '0';

                setTimeout(() => {
                    commentElement.style.height = '0';
                    commentElement.style.padding = '0';
                    commentElement.style.margin = '0';

                    setTimeout(() => {
                        commentElement.remove();

                        // Update average rating
                        if (data.average_rating !== undefined) {
                            const averageRatingSpan = document.querySelector('#average-rating span');
                            if (averageRatingSpan) {
                                averageRatingSpan.textContent = data.average_rating;
                            }
                        }

                        // Check if there are no more comments
                        if (commentsList.children.length === 0) {
                            commentsList.innerHTML = `
                            <div class="no-comments">
                                <i class='bx bx-message-square-detail'></i>
                                <p>No comments yet. Be the first to share your thoughts!</p>
                            </div>
                        `;
                        }

                        showNotification('Comment deleted successfully', 'success');
                    }, 300);
                }, 50);
            } else {
                // Revert UI changes if deletion failed
                commentElement.style.opacity = '1';
                deleteButton.disabled = false;
                showNotification(data.message || 'Failed to delete comment', 'error');
            }
        })
        .catch(error => {
            console.error('Delete comment error:', error);
            // Revert UI changes
            commentElement.style.opacity = '1';
            deleteButton.disabled = false;
            showNotification('Failed to delete comment. Please try again.', 'error');
        });
}

// Event listener for debugging
// Event listener for comment deletion
document.addEventListener('DOMContentLoaded', () => {
    const commentsList = document.getElementById('comments-list');
    if (commentsList) {
        commentsList.addEventListener('click', (e) => {
            const deleteButton = e.target.closest('.delete-comment');
            if (deleteButton) {
                deleteComment(e);
            }
        });
    }
});

// ===============================
//Limits API call frequency Improves performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to perform the search
const performSearch = debounce(async (searchTerm) => {
    if (!searchTerm.trim()) {
        searchResults.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`search.php?q=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && Array.isArray(data.results)) {
            if (data.results.length > 0) {
                searchResults.innerHTML = data.results
                    .map(anime => `
                        <div class="search-result-item" data-anime-id="${anime.id}">
                            <img src="${anime.image_path || 'placeholder.jpg'}" 
                                 alt="${anime.title}"
                                 onerror="this.src='placeholder.jpg'">
                            <div class="search-result-info">
                                <div class="search-result-title">${anime.title}</div>
                                <div class="search-result-meta">
                                    ${anime.type || 'Anime'} • Score: ${anime.score || 'N/A'}
                                </div>
                            </div>
                        </div>
                    `).join('');

                // Add click event listeners to search results
                searchResults.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const animeId = item.dataset.animeId;
                        const animeData = data.results.find(
                            anime => anime.id.toString() === animeId
                        );

                        if (animeData) {
                            openAnimeModal(animeData);
                            // Clear search
                            searchResults.style.display = 'none';
                            searchInput.value = '';
                            if (searchToggle.classList.contains('active')) {
                                searchToggle.click();
                            }
                        }
                    });
                });
            } else {
                searchResults.innerHTML = `
                    <div class="no-results">
                        <p>No results found for "${searchTerm}"</p>
                    </div>
                `;
            }
        } else {
            throw new Error(data.message || 'Invalid response format');
        }

        searchResults.style.display = 'block';

    } catch (error) {
        console.error('Search error:', error);
        searchResults.innerHTML = `
            <div class="search-error">
                <p>Error performing search</p>
                <p>Please try again later</p>
            </div>
        `;
        searchResults.style.display = 'block';
    }
}, 300);

// Search input event listener
searchInput.addEventListener('input', function () {
    const searchTerm = this.value.trim();
    if (searchTerm.length >= 2) { // Only search if at least 2 characters
        performSearch(searchTerm);
    } else {
        searchResults.style.display = 'none';
    }
});

// Close search results when clicking outside
document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

// Clear search when closing search box
searchToggle.addEventListener('click', function () {
    if (!searchToggle.classList.contains('active')) {
        searchResults.style.display = 'none';
        searchInput.value = '';
    }
});


// ===============================
// Add watchlist functionality
document.addEventListener('click', async (e) => {
    const watchlistBtn = e.target.closest('.watchlist-btn');
    if (!watchlistBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const animeId = watchlistBtn.dataset.animeId;
    watchlistBtn.disabled = true; // Prevent double-clicks

    try {
        const response = await fetch('update_watchlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `anime_id=${animeId}`
        });

        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();

        if (data.success) {
            // Update UI buttons
            document.querySelectorAll(`.watchlist-btn[data-anime-id="${animeId}"]`).forEach(btn => {
                btn.classList.toggle('in-watchlist');
                const icon = btn.querySelector('i');
                icon.className = `fas ${data.action === 'added' ? 'fa-check' : 'fa-plus'}`;
                btn.querySelector('.text').textContent = data.action === 'added' ? 'Remove from Watchlist' : 'Add to Watchlist';
            });

            // Update the watchlist array
            if (data.action === 'added') {
                if (!user_watchlist.includes(parseInt(animeId))) {
                    user_watchlist.push(parseInt(animeId));
                }
            } else {
                const index = user_watchlist.indexOf(parseInt(animeId));
                if (index > -1) user_watchlist.splice(index, 1);
            }

            // Immediately refresh watchlist carousel
            try {
                const watchlistResponse = await fetch('get_watchlist.php');
                const watchlistData = await watchlistResponse.json();

                if (watchlistData.success) {
                    generateCarouselContent('watchlist-carousel', watchlistData.watchlist);
                    showNotification(`${data.action === 'added' ? 'Added to' : 'Removed from'} watchlist`, 'success');
                }
            } catch (watchlistError) {
                console.error('Failed to refresh watchlist:', watchlistError);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to update watchlist', 'error');
    } finally {
        watchlistBtn.disabled = false;
    }
});

function toggleWatchlist(animeId) {
    fetch('Home.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `anime_id=${animeId}`
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Watchlist response:', data); // Log full response
            if (data.success) {
                const watchlistBtn = document.querySelector(`[data-anime-id="${animeId}"]`);
                if (data.action === 'added') {
                    watchlistBtn.classList.add('in-watchlist');
                } else if (data.action === 'removed') {
                    watchlistBtn.classList.remove('in-watchlist');
                }
            } else {
                console.error('Watchlist error:', data.message);
                alert(data.message || 'Failed to update watchlist');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Network or server error occurred');
        });
}



// ===============================
//Creates notification element Handles animation Auto-removes after delay Supports success/error types
function showNotification(message, type) {
    // Remove any existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;

    // Add icon based on type
    const icon = type === 'success' ? '✓' : '⚠';
    notification.innerHTML = `
        <span class="notification-icon">${icon}</span>
        <span class="notification-message">${message}</span>
    `;

    // Add to document
    document.body.appendChild(notification);

    // Add visible class for animation
    setTimeout(() => {
        notification.classList.add('visible');
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('visible');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ===============================
// Initialize carousels
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Fetch watchlist first
        const watchlistResponse = await fetch('get_watchlist.php');
        const watchlistData = await watchlistResponse.json();

        if (watchlistData.success) {
            // Initialize user_watchlist array with existing watchlist items
            user_watchlist = watchlistData.watchlist.map(item => parseInt(item.id));
        }

        // Then generate all carousels
        generateCarouselContent('carousel1', topAnimeData || []);
        generateCarouselContent('carousel2', seriesData || []);
        generateCarouselContent('carousel3', moviesData || []);
        generateCarouselContent('watchlist-carousel', watchlistData.watchlist || []);
    } catch (error) {
        console.error('Error initializing watchlist:', error);
    }
});


// ===============================
// Logout function
function logout() {
    fetch('logout.php', {
        method: 'POST'
    }).then(response => {
        if (response.ok) {
            window.location.href = 'users.php';
        }
    });
}

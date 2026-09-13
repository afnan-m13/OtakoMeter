// Initialize variables
const animeModal = document.getElementById('anime-modal');
const modalClose = document.querySelector('.modal-close');
const commentInput = document.getElementById('comment-input');
const submitCommentBtn = document.getElementById('submit-comment');
const commentsList = document.getElementById('comments-list');
const averageRatingSpan = document.querySelector('#average-rating span');
const starContainers = document.querySelectorAll('.star-container');
const ratingMessage = document.getElementById('rating-message');
const searchToggle = document.querySelector(".searchToggle");
const searchField = document.querySelector(".search-field");
const searchInput = document.querySelector('.search-field input');
const searchResults = document.createElement('div');
searchResults.className = 'search-results';
document.querySelector('.search-field').appendChild(searchResults);

let currentRating = 0;
let currentAnimeData = null;
let user_watchlist = [];

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

// Initialize movies grid
function initializeMoviesGrid() {
    const container = document.getElementById('moviesContainer');
    container.innerHTML = '';
    
    moviesData.forEach(movie => {
        const isInWatchlist = user_watchlist.includes(parseInt(movie.id));
        
        const card = document.createElement('div');
        card.classList.add('card');
        
        card.innerHTML = `
            <div class="card-image">
                <img src="${movie.image_path}" alt="${movie.title}" 
                     onerror="this.src='placeholder.jpg'">
                <div class="rating">
                    <div class="score">${movie.score}</div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-title">${movie.title}</div>
                <button class="watchlist-btn ${isInWatchlist ? 'in-watchlist' : ''}" 
                        data-anime-id="${movie.id}">
                    <i class="fas ${isInWatchlist ? 'fa-check' : 'fa-plus'}"></i>
                    <span class="text">
                        ${isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist'}
                    </span>
                </button>
            </div>
        `;
        
        card.querySelector('.card-image').addEventListener('click', (e) => {
            if (!e.target.closest('.watchlist-btn')) {
                openAnimeModal(movie);
            }
        });
        
        container.appendChild(card);
    });
}

// Modal functions
function openAnimeModal(anime) {
    currentAnimeData = anime;
    const isInWatchlist = user_watchlist.includes(parseInt(anime.id));

    document.getElementById('modal-anime-poster').src = anime.image_path;
    document.getElementById('modal-anime-name').textContent = anime.title;
    document.getElementById('modal-anime-genre').textContent = anime.genres.join(', ');
    document.getElementById('modal-anime-description').textContent = anime.description;
    document.getElementById('modal-anime-trailer').href = anime.trailer_url;
    document.getElementById('modal-anime-startAiring').textContent = anime.start_airing;
    document.getElementById('modal-anime-episodes').textContent = anime.duration;
    document.getElementById('modal-anime-animeStatus').textContent = 'Movie';

    // Update modal watchlist button
    const modalInfo = document.querySelector('.modal-info');
    const existingBtn = modalInfo.querySelector('.watchlist-btn');
    if (existingBtn) {
        existingBtn.remove();
    }

    const watchlistBtn = document.createElement('button');
    watchlistBtn.className = `watchlist-btn modal-watchlist-btn ${isInWatchlist ? 'in-watchlist' : ''}`;
    watchlistBtn.setAttribute('data-anime-id', anime.id);
    watchlistBtn.innerHTML = `
        <i class="fas ${isInWatchlist ? 'fa-check' : 'fa-plus'}"></i>
        <span class="text">${isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist'}</span>
    `;
    modalInfo.insertBefore(watchlistBtn, modalInfo.firstChild);

    resetStarRating();
    commentInput.value = '';
    
    fetch(`get_comments.php?anime_id=${anime.id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCommentsList(data.comments, currentUsername);
                averageRatingSpan.textContent = data.average_rating;
            }
        });
    
    animeModal.style.display = 'flex';
    setTimeout(() => animeModal.classList.add('active'), 10);
}

// Star rating functions
function resetStarRating() {
    currentRating = 0;
    ratingMessage.textContent = ratingMessages[0];
    starContainers.forEach(star => {
        star.classList.remove('active');
        star.querySelector('i').className = 'fa-star fa-regular';
    });
}

// Watchlist functions
document.addEventListener('click', async (e) => {
    const watchlistBtn = e.target.closest('.watchlist-btn');
    if (!watchlistBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const animeId = watchlistBtn.dataset.animeId;
    watchlistBtn.disabled = true;

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
            document.querySelectorAll(`.watchlist-btn[data-anime-id="${animeId}"]`).forEach(btn => {
                btn.classList.toggle('in-watchlist');
                const icon = btn.querySelector('i');
                icon.className = `fas ${data.action === 'added' ? 'fa-check' : 'fa-plus'}`;
                btn.querySelector('.text').textContent = data.action === 'added' ? 'Remove from Watchlist' : 'Add to Watchlist';
            });

            if (data.action === 'added') {
                if (!user_watchlist.includes(parseInt(animeId))) {
                    user_watchlist.push(parseInt(animeId));
                }
            } else {
                const index = user_watchlist.indexOf(parseInt(animeId));
                if (index > -1) user_watchlist.splice(index, 1);
            }

            showNotification(`${data.action === 'added' ? 'Added to' : 'Removed from'} watchlist`, 'success');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to update watchlist', 'error');
    } finally {
        watchlistBtn.disabled = false;
    }
});


// Search functionality
const performSearch = debounce(async (searchTerm) => {
    if (!searchTerm.trim()) {
        searchResults.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`search.php?q=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();

        if (data.success && Array.isArray(data.results)) {
            if (data.results.length > 0) {
                searchResults.innerHTML = data.results.map(anime => `
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

                searchResults.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const animeData = data.results.find(
                            anime => anime.id.toString() === item.dataset.animeId
                        );

                        if (animeData) {
                            openAnimeModal(animeData);
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

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function formatCommentDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return `${Math.floor(diff / 60000)} minute${Math.floor(diff / 60000) !== 1 ? 's' : ''} ago`;
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} hour${Math.floor(diff / 3600000) !== 1 ? 's' : ''} ago`;
    if (diff < 604800000) return `${Math.floor(diff / 86400000)} day${Math.floor(diff / 86400000) !== 1 ? 's' : ''} ago`;

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showNotification(message, type) {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span class="notification-icon">${type === 'success' ? '✓' : '⚠'}</span>
        <span class="notification-message">${message}</span>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.classList.add('visible'), 10);
    setTimeout(() => {
        notification.classList.remove('visible');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const watchlistResponse = await fetch('get_watchlist.php');
        const watchlistData = await watchlistResponse.json();
        
        if (watchlistData.success) {
            user_watchlist = watchlistData.watchlist.map(item => parseInt(item.id));
        }
        
        initializeMoviesGrid();
    } catch (error) {
        console.error('Error initializing watchlist:', error);
    }

    // Modal close handlers
    modalClose.addEventListener('click', () => {
        animeModal.classList.remove('active');
        setTimeout(() => animeModal.style.display = 'none', 300);
    });

    animeModal.addEventListener('click', (e) => {
        if (e.target === animeModal) {
            animeModal.classList.remove('active');
            setTimeout(() => animeModal.style.display = 'none', 300);
        }
    });

    // Search events
    searchToggle.addEventListener("click", () => {
        searchToggle.classList.toggle("active");
        searchField.style.display = searchToggle.classList.contains("active") ? "block" : "none";
    });

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length >= 2) {
            performSearch(searchTerm);
        } else {
            searchResults.style.display = 'none';
        }
    });

    // Star rating
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

    // Comment submission
    submitCommentBtn.addEventListener('click', () => {
        const comment = commentInput.value.trim();

        if (!comment) {
            showNotification('Please write a comment', 'error');
            return;
        }

        if (!currentRating) {
            showNotification('Please provide a rating', 'error');
            return;
        }

        if (!currentAnimeData) {
            showNotification('Error: No anime selected', 'error');
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
                    id: data.comment_id,
                    username: currentUsername,
                    rating: currentRating,
                    comment: comment,
                    created_at: new Date().toISOString()
                };

                const currentComments = Array.from(commentsList.children);
                if (currentComments[0]?.classList.contains('no-comments')) {
                    commentsList.innerHTML = '';
                }

                commentsList.insertAdjacentHTML('afterbegin', createCommentElement(newComment, true));
                
                if (data.average_rating) {
                    averageRatingSpan.textContent = data.average_rating;
                }

                commentInput.value = '';
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

    // Comment deletion
    commentsList.addEventListener('click', (e) => {
        const deleteButton = e.target.closest('.delete-comment');
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
                commentElement.style.height = commentElement.offsetHeight + 'px';
                commentElement.style.marginTop = '0';
                commentElement.style.marginBottom = '0';

                setTimeout(() => {
                    commentElement.style.height = '0';
                    commentElement.style.padding = '0';
                    commentElement.style.margin = '0';

                    setTimeout(() => {
                        commentElement.remove();

                        if (data.average_rating !== undefined) {
                            averageRatingSpan.textContent = data.average_rating;
                        }

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
                commentElement.style.opacity = '1';
                deleteButton.disabled = false;
                showNotification(data.message || 'Failed to delete comment', 'error');
            }
        })
        .catch(error => {
            console.error('Delete comment error:', error);
            commentElement.style.opacity = '1';
            deleteButton.disabled = false;
            showNotification('Failed to delete comment. Please try again.', 'error');
        });
    });

    // Outside click handlers
    document.addEventListener('click', (event) => {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Clear search when closing search box
    searchToggle.addEventListener('click', function() {
        if (!searchToggle.classList.contains('active')) {
            searchResults.style.display = 'none';
            searchInput.value = '';
        }
    });
});

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

    commentsList.innerHTML = comments
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .map(comment => createCommentElement(comment))
        .join('');
}

function logout() {
    fetch('logout.php', {
        method: 'POST'
    }).then(response => {
        if (response.ok) {
            window.location.href = 'users.php';
        }
    });
}
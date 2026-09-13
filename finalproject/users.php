<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Security functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function forceLogout($message = '', $isError = false)
{
    session_unset();
    session_destroy();
    session_start();
    if ($message) {
        if ($isError) {
            $_SESSION['login_error'] = $message;
        } else {
            $_SESSION['login_success'] = $message;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?form=loginForm");
    exit();
}

// Initialize form state
$show_form = 'loginForm';

if (isset($_GET['form'])) {
    $valid_forms = ['loginForm', 'signupForm', 'profileForm', 'forgotPasswordForm'];
    if (in_array($_GET['form'], $valid_forms)) {
        $show_form = $_GET['form'];
    }
}

// Profile form security
if ($show_form === 'profileForm') {
    if (!isLoggedIn()) {
        forceLogout();
    }
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include('db.php');

$con = new mysqli($db_host, $db_user, $db_pass, $db_database);

if ($con->connect_errno) {
    error_log("Failed to connect to MySQL: " . $con->connect_error);
    die("Sorry, there was a problem connecting to our database.");
}

$login_error = null;
$signup_error = null;
$login_success = null;

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $con->prepare("SELECT id, email, password, username FROM users WHERE email = ?");

    if ($stmt === false) {
        $login_error = "Database error. Please try again.";
        error_log("Login prepare error: " . $con->error);
    } else {
        $stmt->bind_param('s', $email);

        if (!$stmt->execute()) {
            $login_error = "Database error during execution.";
            error_log("Login execute error: " . $stmt->error);
        } else {
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $login_error = "Invalid email or password";
                $show_form = 'loginForm';
            } else {
                $user = $result->fetch_assoc();

                if (!password_verify($pass, $user['password'])) {
                    $login_error = "Invalid email or password";
                    $show_form = 'loginForm';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['username'] = $user['username'];
                    session_regenerate_id(true);
                    $_SESSION['login_time'] = time();

                    header("Location: Home.php");
                    exit();
                }
            }
        }
        $stmt->close();
    }
}

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $show_form = 'signupForm';

    if (empty($username)) {
        $signup_error = "Username is required";
    } else {
        $check_email = $con->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param('s', $email);
        $check_email->execute();
        $check_email->store_result();

        $check_username = $con->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->bind_param('s', $username);
        $check_username->execute();
        $check_username->store_result();

        if ($check_email->num_rows > 0) {
            $signup_error = "Email already exists";
        } else if ($check_username->num_rows > 0) {
            $signup_error = "Username already exists";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_error = "Invalid email format";
        } else if (strlen($password) < 8) {
            $signup_error = "Password must be at least 8 characters long";
        } else if ($password !== $confirm_password) {
            $signup_error = "Passwords do not match";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $con->prepare($insert_query);

            if (!$stmt) {
                die("Prepare failed: " . $con->error);
            }

            $stmt->bind_param('sss', $username, $email, $hashed_password);

            try {
                if ($stmt->execute()) {
                    $_SESSION['login_success'] = "Account created successfully. Please log in.";
                    header("Location: " . $_SERVER['PHP_SELF'] . "?form=loginForm");
                    exit();
                } else {
                    if ($stmt->errno == 1062) {
                        $signup_error = "Username or email is already in use";
                    } else {
                        $signup_error = "Error creating account: " . $stmt->error;
                    }
                }
            } catch (Exception $e) {
                $signup_error = "An error occurred: " . $e->getMessage();
            }

            $stmt->close();
        }

        $check_email->close();
        $check_username->close();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!isLoggedIn()) {
        forceLogout();
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $show_form = 'profileForm';

    if (empty($new_username) || empty($new_email) || empty($current_password)) {
        $profile_error = "All fields are required";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = "Invalid email format";
    } else {
        $check_password = $con->prepare("SELECT password FROM users WHERE id = ?");
        $check_password->bind_param('i', $_SESSION['user_id']);
        $check_password->execute();
        $result = $check_password->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password'])) {
            $profile_error = "Incorrect current password";
        } else {
            $check_duplicate = $con->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $check_duplicate->bind_param('ssi', $new_email, $new_username, $_SESSION['user_id']);
            $check_duplicate->execute();
            $check_duplicate->store_result();

            if ($check_duplicate->num_rows > 0) {
                $profile_error = "Username or email already exists";
            } else {
                $update_successful = true;

                if (!empty($new_password)) {
                    if (strlen($new_password) < 8) {
                        $profile_error = "New password must be at least 8 characters";
                        $update_successful = false;
                    } elseif ($new_password !== $confirm_password) {
                        $profile_error = "New passwords do not match";
                        $update_successful = false;
                    } else {
                        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_password = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_password->bind_param('si', $hashed_new_password, $_SESSION['user_id']);
                        $update_password->execute();
                        $update_password->close();
                    }
                }

                if ($update_successful) {
                    $update_profile = $con->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $update_profile->bind_param('ssi', $new_username, $new_email, $_SESSION['user_id']);

                    if ($update_profile->execute()) {
                        forceLogout("Profile updated successfully. Please login again.", false);
                    } else {
                        $profile_error = "Error updating profile";
                    }
                    $update_profile->close();
                }
            }
            $check_duplicate->close();
        }
        $check_password->close();
    }
}

$con->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <link rel="stylesheet" href="user.css" />
</head>

<body>
    <div class="logo-container">
        <a class="custom-logo">OTAKU</a>
    </div>

    <!-- Login Form -->
    <div class="login-box" id="loginForm">
        <div class="login-header">
            <header>Login</header>
            <?php if (isset($login_success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($login_success); ?></div>
            <?php endif; ?>
            <?php if (isset($login_error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['login_success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_SESSION['login_success']); ?></div>
                <?php unset($_SESSION['login_success']); ?>
            <?php endif; ?>
        </div>

        <form action="?form=loginForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="login">
            <div class="input-box">
                <input type="text" class="input-field" placeholder="Email" autocomplete="off" required name="email">
            </div>
            <div class="input-box">
                <input type="password" class="input-field" placeholder="Password" autocomplete="off" required
                    name="password">
            </div>
            <div class="forgot">
                <section>
                    <input type="checkbox" id="check" name="remember" value="1">
                    <label for="check">Remember me</label>
                </section>
                <section>
                    <a href="#" onclick="toggleForms('forgotPasswordForm')">Forgot password</a>
                </section>
            </div>
            <div class="input-submit">
                <button type="submit" class="submit-btn" id="submit"></button>
                <label for="submit">Sign In</label>
            </div>
        </form>

        <div class="sign-up-link">
            <p>Don't have account? <a href="#" onclick="toggleForms('signupForm')">Sign Up</a></p>
        </div>
    </div>

    <!-- Signup Form -->
    <div class="login-box" id="signupForm">
        <div class="login-header">
            <header>Sign Up</header>
            <?php if (isset($signup_error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($signup_error); ?></div>
            <?php endif; ?>
        </div>

        <form action="?form=signupForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="signup">
            <div class="input-box">
                <input type="text" class="input-field" placeholder="User Name" autocomplete="off" required
                    name="username">
            </div>
            <div class="input-box">
                <input type="email" class="input-field" placeholder="Email" autocomplete="off" required name="email">
            </div>
            <div class="input-box">
                <input type="password" class="input-field" placeholder="Password" autocomplete="off" required
                    name="password">
            </div>
            <div class="input-box">
                <input type="password" class="input-field" placeholder="Confirm Password" autocomplete="off" required
                    name="confirm_password">
            </div>
            <div class="terms">
                <section>
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the Terms & Conditions</label>
                </section>
            </div>
            <div class="input-submit">
                <button type="submit" class="submit-btn" id="submit-signup"></button>
                <label for="submit-signup">Create Account</label>
            </div>
        </form>

        <div class="sign-up-link">
            <p>Already have an account? <a href="#" onclick="toggleForms('loginForm')">Login</a></p>
        </div>
    </div>

    <!-- Profile Update Form -->
    <div class="login-box" id="profileForm">
        <div class="login-header">
            <header>Update Profile</header>
            <?php if (isset($profile_error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($profile_error); ?></div>
            <?php endif; ?>
        </div>

        <form action="?form=profileForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_profile">
            <div class="input-box">
                <input type="text" class="input-field" name="username" placeholder="Username"
                    value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>"
                    required>
            </div>
            <div class="input-box">
                <input type="email" class="input-field" name="email" placeholder="Email"
                    value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
                    required>
            </div>
            <div class="input-box">
                <input type="password" class="input-field" name="current_password" placeholder="Current Password"
                    required>
            </div>
            <div class="input-box">
                <input type="password" class="input-field" name="new_password" placeholder="New Password">
            </div>
            <div class="input-box">
                <input type="password" class="input-field" name="confirm_password" placeholder="Confirm New Password">
            </div>
            <div class="input-submit">
                <button type="submit" class="submit-btn" id="update-profile"></button>
                <label for="update-profile">Update Profile</label>
            </div>
        </form>

        <div class="sign-up-link">
            <p>Want to go back? <a href="#" onclick="forceLogoutOnCancel()">Cancel</a></p>
        </div>
    </div>

    <!-- Forgot Password Form -->
    <div class="login-box" id="forgotPasswordForm">
        <div class="login-header">
            <header>Reset Password</header>
            <p>Enter your email address and we'll send you instructions to reset your password.</p>
        </div>
        <form action="?form=forgotPasswordForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="input-box">
                <input type="email" class="input-field" name="forgot_email" placeholder="Enter your email"
                    autocomplete="off" required>
            </div>
            <div class="input-submit">
                <button type="submit" class="submit-btn" id="submit-reset"></button>
                <label for="submit-reset">Send Reset Link</label>
            </div>
        </form>
        <div class="sign-up-link">
            <p>Remember your password? <a href="#" onclick="toggleForms('loginForm')">Login</a></p>
        </div>
    </div>

    <script>
        function toggleForms(showFormId) {
            const forms = ['loginForm', 'signupForm', 'forgotPasswordForm', 'profileForm'];
            forms.forEach(formId => {
                document.getElementById(formId).style.display = 'none';
            });
            document.getElementById(showFormId).style.display = 'block';

            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('form', showFormId);
            window.history.pushState({}, '', url);
        }

        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            const formToShow = urlParams.get('form') || '<?php echo $show_form; ?>';
            toggleForms(formToShow);
        }

        window.onpopstate = function () {
            const urlParams = new URLSearchParams(window.location.search);
            const formToShow = urlParams.get('form') || '<?php echo $show_form; ?>';
            toggleForms(formToShow);
        }

        function forceLogoutOnCancel() {
            // Force logout and redirect to login
            window.location.href = 'logout.php';
        }
    </script>
</body>

</html>
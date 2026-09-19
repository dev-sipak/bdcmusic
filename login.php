<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Redirect if already logged in
if ( isset( $_SESSION['user_id'] ) && isset( $_SESSION['user_role'] ) ) {
    if ( $_SESSION['user_role'] === 'admin' ) {
        header( 'Location: ' . $basePath . 'bdc-admin/' );
    } else {
        header( 'Location: ' . $basePath . 'dashboard/customer-dashboard' );
    }
    exit;
}

$pageTitle       = 'Sign In - BDC Music Studio';
$metaDescription = 'Sign in or create your BDC Music Studio account to manage orders, track services, and access your dashboard.';
$currentPage     = 'login';
include_once __DIR__ . '/header.php';
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 id="auth-title">Welcome to BDC Music Portal</h1>
                <p id="auth-subtitle">Sign in to your account to continue</p>
            </div>

            <div class="auth-notice notice success d-none" id="login-success">
                <i class="fa-solid fa-check-circle"></i>
                <span id="success-message">Account created successfully! Redirecting...</span>
            </div>

            <div class="auth-notice notice error d-none" id="login-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <span id="error-message">Please fill in all fields.</span>
            </div>

            <form id="auth-form" class="auth-form" novalidate>
                <div class="form-field d-none" id="name-field">
                    <label for="auth-name">Full Name</label>
                    <input type="text" id="auth-name" placeholder="Enter your full name">
                </div>

                <div class="form-field d-none" id="phone-field">
                    <label for="auth-phone">Contact Number</label>
                    <input type="tel" id="auth-phone" placeholder="Enter your phone number" pattern="[0-9]{10,15}">
                </div>

                <div class="form-field">
                    <label for="auth-email">Email Address</label>
                    <input type="email" id="auth-email" placeholder="you@example.com" required>
                </div>

                <div class="form-field">
                    <label for="auth-password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="auth-password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pass" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-field d-none" id="confirm-field">
                    <label for="auth-confirm">Confirm Password</label>
                    <input type="password" id="auth-confirm" placeholder="Confirm your password">
                </div>

                <div class="auth-options" id="auth-options">
                    <label class="remember-me">
                        <input type="checkbox" checked> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn auth-btn" id="auth-submit">
                    Sign In
                </button>
            </form>

            <p class="auth-switch">
                <span id="switch-text">Don't have an account?</span>
                <button type="button" class="switch-btn" id="auth-toggle">Sign Up</button>
            </p>

            <!-- Removed: Admin Login link -->
        </div>
    </div>
</section>

<script>
var loginPageConfig = {
    authUrl: '<?php echo $basePath; ?>includes/auth.php',
    basePath: '<?php echo $basePath; ?>'
};
</script>
<script src="<?php echo $assetPath; ?>js/login.js"></script>

<?php include_once __DIR__ . '/footer.php'; ?>

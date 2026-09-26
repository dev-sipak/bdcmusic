<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/includes/panel-guard.php';

$panelPage = 'profile';

$pageTitle       = 'Customer Dashboard - BDC Music Studio';
$metaDescription = 'Manage your orders, track services, update your profile, and view order history from your BDC customer dashboard.';
$currentPage     = 'dashboard/customer-dashboard';
include_once __DIR__ . '/../header.php';

$userPhone = '';
$userSince = '';
$userPic   = '';
try {
    $pdo    = db_connect();
    $uStmt = $pdo->prepare( 'SELECT mobile, profile_picture, DATE_FORMAT(created_at, "%M %Y") AS member_since FROM users WHERE id = :uid LIMIT 1' );
    $uStmt->execute( [ ':uid' => $_SESSION['user_id'] ] );
    $uRow = $uStmt->fetch();
    if ( $uRow ) {
        $userPhone = $uRow['mobile'] ?? '';
        $userSince = $uRow['member_since'] ?? '';
        $userPic   = $uRow['profile_picture'] ?? '';
    }
} catch ( Exception $e ) {
    $userPhone = '';
}
$userPicUrl = $userPic ? ( $basePath . $userPic ) : '';
?>

<section class="panel-page">
    <div class="container">
        <div class="panel-layout">

            <?php include __DIR__ . '/includes/panel-nav.php'; ?>

            <main class="panel-main">

                <div class="panel-mobile-toggle">
                    <button type="button" class="btn panel-menu-btn" id="dash-menu-toggle">
                        <i class="fa-solid fa-bars"></i> Menu
                    </button>
                </div>

                <div class="panel-tab active" id="tab-profile">
                    <div class="panel-tab-header">
                        <h2>My Profile</h2>
                        <p>Manage your personal information and preferences.</p>
                    </div>

                    <div class="auth-notice notice success d-none" id="profile-success">
                        <i class="fa-solid fa-check-circle"></i>
                        <span id="profile-success-msg"></span>
                    </div>
                    <div class="auth-notice notice error d-none" id="profile-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span id="profile-error-msg"></span>
                    </div>

                    <div class="panel-profile-card">
                        <div class="panel-avatar-wrap">
                            <?php if ( $userPicUrl ) : ?>
                                <img src="<?php echo htmlspecialchars( $userPicUrl ); ?>" alt="Profile" class="panel-avatar-img" id="profile-avatar-img">
                            <?php else : ?>
                                <div class="panel-avatar-img panel-avatar-default" id="profile-avatar-img">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <label class="panel-avatar-upload" id="avatar-upload-label" title="Change profile picture">
                                <i class="fa-solid fa-camera"></i>
                                <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" hidden>
                            </label>
                        </div>
                        <div class="panel-avatar-status d-none" id="avatar-status"></div>
                        <form id="profile-form" class="panel-profile-details" novalidate>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="profile-name">Full Name</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-user" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="text" id="profile-name" class="panel-input" style="padding-left:34px;" value="<?php echo htmlspecialchars( $userName ); ?>" required>
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label">Email</label>
                                <span class="panel-value"><?php echo htmlspecialchars( $userEmail ); ?></span>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="profile-phone">Phone</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-phone" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="tel" id="profile-phone" class="panel-input" style="padding-left:34px;" value="<?php echo htmlspecialchars( $userPhone ); ?>" placeholder="Enter phone number" pattern="[0-9]{10,15}">
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <span class="panel-label">Member Since</span>
                                <span class="panel-value"><?php echo htmlspecialchars( $userSince ?: 'N/A' ); ?></span>
                            </div>
                            <button type="submit" class="btn panel-edit-btn" id="profile-save-btn">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    <div class="panel-profile-card" style="margin-top:1.5rem;">
                        <h3 style="margin-bottom:1rem;"><i class="fa-solid fa-lock"></i> Change Password</h3>
                        <div class="auth-notice notice success d-none" id="pass-success">
                            <i class="fa-solid fa-check-circle"></i>
                            <span id="pass-success-msg"></span>
                        </div>
                        <div class="auth-notice notice error d-none" id="pass-error">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span id="pass-error-msg"></span>
                        </div>
                        <form id="password-form" class="panel-profile-details" novalidate>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="current-password">Current Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="current-password" class="panel-input" style="padding-left:34px;" placeholder="Enter current password" required>
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="new-password">New Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="new-password" class="panel-input" style="padding-left:34px;" placeholder="Min 6 characters" required minlength="6">
                                </div>
                            </div>
                            <div class="panel-detail-row">
                                <label class="panel-label" for="confirm-password">Confirm New Password</label>
                                <div style="position:relative;flex:1;">
                                    <i class="fa-solid fa-lock" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:0.85rem;pointer-events:none;z-index:1;"></i>
                                    <input type="password" id="confirm-password" class="panel-input" style="padding-left:34px;" placeholder="Re-enter new password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn panel-edit-btn" id="pass-save-btn">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/panel-config.php'; ?>

<script src="<?php echo $assetPath; ?>js/shared.js"></script>
<script src="<?php echo $assetPath; ?>js/customer-dashboard.js"></script>

<?php include_once __DIR__ . '/../footer.php'; ?>

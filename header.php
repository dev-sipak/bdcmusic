<?php
if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
}
require_once __DIR__ . '/includes/config.php';

$pageTitle       = isset( $pageTitle ) ? $pageTitle : 'BDC Music Studio';
$metaDescription = isset( $metaDescription ) ? $metaDescription : 'Professional Recording Studio';
$canonicalPath   = $currentPage !== '' ? $currentPage . '/' : '';
$canonicalUrl    = isset( $canonicalUrl ) ? $canonicalUrl : $basePath . $canonicalPath;
$ogTitle         = isset( $ogTitle ) ? $ogTitle : $pageTitle;
$ogDescription   = isset( $ogDescription ) ? $ogDescription : $metaDescription;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars( $pageTitle ); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars( $metaDescription ); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars( $canonicalUrl ); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars( $ogTitle ); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars( $ogDescription ); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars( $canonicalUrl ); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT@0,9..144,706,53.5;1,9..144,706,53.5&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>dist/main.css">
    <link rel="icon" href="<?php echo $assetPath; ?>images/favicon.png" type="image/png">
</head>

<body>

<header class="header">
    <div class="container">
        <a href="<?php echo $basePath; ?>" class="logo">
            <img src="<?php echo $assetPath; ?>images/logo.svg" alt="BDC Music Studio">
        </a>
        <nav class="nav">
            <ul>
                <li>
                    <a href="<?php echo $basePath; ?>"
                       class="<?php echo ( $currentPage == '' || $currentPage == 'index' ) ? 'active' : ''; ?>">
                        Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>about-bdc-music"
                       class="<?php echo ( $currentPage == 'about-bdc-music' ) ? 'active' : ''; ?>">
                        About
                    </a>
                </li>
                <li class="nav-item has-dropdown">
                    <button class="nav-link dropdown-toggle <?php echo $isServicesPage ? 'active' : ''; ?>"
                        type="button"
                        aria-expanded="false"
                        aria-controls="services-dropdown">
                        Services
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <ul class="dropdown-menu" id="services-dropdown">
                        <li><a href="<?php echo $basePath; ?>services/bdc-artists-marketplace/">BDC Artists Marketplace</a></li>
                        <li><a href="<?php echo $basePath; ?>services/audio-video-services/">Audio & Video Services</a></li>
                        <li><a href="<?php echo $basePath; ?>services/online-offline-classes/">Online/Offline Classes</a></li>
                        <li><a href="<?php echo $basePath; ?>services/digital-music-distribution/">Digital Music Distribution</a></li>
                        <li><a href="<?php echo $basePath; ?>services/promotion-services/">Promotion Services</a></li>
                        <li><a href="<?php echo $basePath; ?>services/iprs-services/">IPRS Services</a></li>
                    </ul>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>contact"
                       class="<?php echo ( $currentPage == 'contact' ) ? 'active' : ''; ?>">
                        Contact
                    </a>
                </li>
            </ul>
        </nav>
        <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
        <?php if ( isset( $_SESSION['user_id'] ) ) : ?>
            <a href="<?php echo $basePath; ?>includes/logout.php" class="btn">Logout</a>
        <?php else : ?>
            <a href="<?php echo $basePath; ?>login" class="btn">Sign In</a>
        <?php endif; ?>
    </div>
</header>

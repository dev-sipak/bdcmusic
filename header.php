<?php
if ( session_status() === PHP_SESSION_NONE ) {
require_once __DIR__ . '/includes/session.php';
}
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';

$pageTitle       = isset( $pageTitle ) ? $pageTitle : 'BDC Music';
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT@0,9..144,706,53.5;1,9..144,706,53.5&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
	<?php // A page can name one extra stylesheet, loaded before main.css so the
	// compiled theme can override it. Vendored libraries use this rather than a CDN. ?>
	<?php if ( ! empty( $extraStylesheet ) ) : ?>
		<link rel="stylesheet" href="<?php echo $assetPath . htmlspecialchars( $extraStylesheet ); ?>">
	<?php endif; ?>
	<link rel="stylesheet" href="<?php echo $assetPath; ?>dist/main.css">
	<link rel="icon" href="<?php echo $assetPath; ?>images/favicon.png" type="image/png">

	<!--
	  GOOGLE ANALYTICS (gtag) - NOT YET ENABLED.

	  No analytics tag is loaded on this site today, and cookie-policy.php says
	  so, so the two stay consistent. When analytics is switched on:

		1. Add the GA4 measurement id and the secret below from a server-side
		   env var, never as a literal, and add GA_MEASUREMENT_ID /
		   GA_API_SECRET to .env and .env.example (see includes/config.php).
		2. Paste the gtag.js snippet from the Google admin into this block,
		   after the stylesheets so it never blocks rendering.
		3. Update the "Analytics Cookies We Will Use" section of
		   cookie-policy.php to drop the "not yet switched on" wording.
		4. The consent banner still has to be added if analytics is loaded
		   before consent, which is what GDPR and the DPDP Act require.

	  config.php has no GA_MEASUREMENT_ID define yet, so nothing here is live.
	-->

</head>

<body>

<header class="header">
	<div class="container">
		<a href="<?php echo $basePath; ?>" class="logo">
			<img src="<?php echo $assetPath; ?>images/logo.svg" alt="BDC Music">
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
						<i data-lucide="chevron-down" aria-hidden="true"></i>
					</button>
					<ul class="dropdown-menu" id="services-dropdown">
						<?php
						// The child links are rendered from a list so the entry that
						// matches the open route can be marked active and announced
						// with aria-current. Static markup cannot know which service
						// page is being viewed, which is why the dropdown never lit up.
						$serviceNavItems = [
							'services/bdc-artists-marketplace'  => 'BDC Artists Marketplace',
							'services/audio-video-services'      => 'Audio & Video Services',
							'services/online-offline-classes'    => 'Online/Offline Classes',
							'services/digital-music-distribution' => 'Digital Music Distribution',
							'services/promotion-services'        => 'Promotion Services',
							'services/iprs-services'             => 'IPRS Services',
						];

						foreach ( $serviceNavItems as $servicePath => $serviceNavLabel ) :
							$isCurrentService = ( $currentPage === $servicePath );
							?>
							<li><a href="<?php echo $basePath . $servicePath . '/'; ?>" class="<?php echo $isCurrentService ? 'active' : ''; ?>"<?php echo $isCurrentService ? 'aria-current="page"' : ''; ?>><?php echo htmlspecialchars( $serviceNavLabel ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li class="nav-item has-dropdown">
					<button class="nav-link dropdown-toggle <?php echo $isArtistsPage ? 'active' : ''; ?>"
						type="button"
						aria-expanded="false"
						aria-controls="artists-dropdown">
						Artists
						<i data-lucide="chevron-down" aria-hidden="true"></i>
					</button>
					<ul class="dropdown-menu" id="artists-dropdown">
						<?php
						$artistCats = [];
						try {
							$apdo   = db_connect();
							$acStmt = $apdo->query( 'SELECT name, slug FROM artist_categories WHERE is_active = 1 ORDER BY sort_order' );
							$artistCats = $acStmt->fetchAll();
						} catch ( Throwable $e ) {
							app_log( 'header', 'artist categories unavailable', $e );
							$artistCats = [];
						}

						// /artists/<category>/ and the artist detail page below it
						// (/artists/<category>/<artist>/) both belong to that category,
						// so the category link stays current on the detail page too.
						$artistCategorySlug = '';

						if ( strpos( $currentPage, 'artists/' ) === 0 ) {
							$artistPathParts    = array_values( array_filter( explode( '/', $currentPage ) ) );
							$artistCategorySlug = $artistPathParts[1] ?? '';
						}

						foreach ( $artistCats as $ac ) :
							$isCurrentCategory = ( $artistCategorySlug === $ac['slug'] );
							?>
							<li><a href="<?php echo $basePath; ?>artists/<?php echo htmlspecialchars( $ac['slug'] ); ?>/" class="<?php echo $isCurrentCategory ? 'active' : ''; ?>"<?php echo $isCurrentCategory ? 'aria-current="page"' : ''; ?>><?php echo htmlspecialchars( $ac['name'] ); ?></a></li>
						<?php endforeach; ?>
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
			<i data-lucide="menu"></i>
		</button>
		<?php if ( isset( $_SESSION['user_id'] ) ) : ?>
			<?php if ( isset( $_SESSION['user_role'] ) && $_SESSION['user_role'] === 'admin' ) : ?>
				<a href="<?php echo $basePath; ?>bdc-admin/" class="btn">Admin Panel</a>
			<?php else : ?>
				<a href="<?php echo $basePath; ?>dashboard/customer-dashboard" class="btn">My Profile</a>
			<?php endif; ?>
		<?php else : ?>
			<a href="<?php echo $basePath; ?>login" class="btn">Sign In</a>
		<?php endif; ?>
	</div>
</header>

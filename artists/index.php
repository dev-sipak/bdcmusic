<?php
$pageTitle = 'Artists | BDC Music Studio';
$metaDescription = 'Browse talented artists by category on the BDC Music Studio marketplace.';
include_once '../header.php';
require_once __DIR__ . '/../includes/pagination.php';

$categorySlug = '';
$categoryName = '';
$artists = [];
$categoryDescription = '';
$pagination = null;
$perPage = 8;

$categorySubtitles = [
    'singer'            => 'Vocal Artists',
    'actor'             => 'On-Screen Talent',
    'actress'           => 'On-Screen Talent',
    'dancer'            => 'Performers',
    'composer'          => 'Music Creators',
    'director'          => 'Creative Directors',
    'music-producer'    => 'Production Experts',
    'producer'          => 'Production Experts',
    'instrument-player' => 'Instrumentalists',
    'music-band'        => 'Musical Groups',
    'reels-stars'       => 'Social Media Creators',
    'writer'            => 'Creative Writers',
];

try {
    $pdo = db_connect();

    $allCats = [];
    $allStmt = $pdo->query( 'SELECT name, slug FROM artist_categories WHERE is_active = 1 ORDER BY sort_order' );
    $allCats = $allStmt->fetchAll();

    $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $path = trim( str_replace( '/bdcmusic', '', $path ), '/' );
    $parts = array_values( array_filter( explode( '/', $path ) ) );
    $lastSegment = end( $parts );
    $categorySlug = ( $lastSegment === 'artists' || $lastSegment === '' ) ? '' : $lastSegment;

    // Handle ?page= parameter for clean URL pagination
    $currentPage = max( 1, (int) ( $_GET['page'] ?? 1 ) );

    if ( ! empty( $categorySlug ) ) {
        $catStmt = $pdo->prepare( 'SELECT id, name, slug FROM artist_categories WHERE slug = :slug AND is_active = 1' );
        $catStmt->execute( [ ':slug' => $categorySlug ] );
        $cat = $catStmt->fetch();

        if ( $cat ) {
            $categoryName = $cat['name'];
            $pageTitle = $categoryName . ' Artists | BDC Music Studio';

            $baseSql = 'SELECT a.id, a.name, a.slug, a.image, a.location, a.bio,
                               ac.name AS category_name, ac.slug AS category_slug
                        FROM artists a
                        JOIN artist_categories ac ON a.category_id = ac.id
                        WHERE a.category_id = :cid AND a.is_active = 1
                        ORDER BY a.name';
            $params = [ ':cid' => $cat['id'] ];
        }
    }

    if ( ! isset( $baseSql ) ) {
        $baseSql = 'SELECT a.id, a.name, a.slug, a.image, a.location, a.bio,
                           ac.name AS category_name, ac.slug AS category_slug
                    FROM artists a
                    JOIN artist_categories ac ON a.category_id = ac.id
                    WHERE a.is_active = 1
                    ORDER BY a.name';
        $params = [];
    }

    $pagination = paginate( $pdo, $baseSql, $params, $currentPage, $perPage );
    $artists = $pagination['items'];

} catch ( Exception $e ) {
    $artists = [];
    $allCats = [];
    $pagination = null;
}

$subtitleKey = ! empty( $categorySlug ) ? $categorySlug : '';
$heroSubtitle = isset( $categorySubtitles[ $subtitleKey ] ) ? $categorySubtitles[ $subtitleKey ] : 'Creative Talent';
?>

<section class="page-hero pb-0">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <?php if ( ! empty( $categoryName ) ) : ?>
                <a href="<?php echo $basePath; ?>artists/">Artists</a>
                <span>/</span>
                <span><?php echo htmlspecialchars( $categoryName ); ?> Artists</span>
            <?php else : ?>
                <span>Artists</span>
            <?php endif; ?>
        </div>

        <div class="artist-section-hero reveal">
            <div class="section-hero-content">
                <h1><?php echo ! empty( $categoryName ) ? 'Discover ' . htmlspecialchars( $categoryName ) . 's &amp; ' . htmlspecialchars( $heroSubtitle ) : 'Browse Our Artists'; ?></h1>
                <p>Explore leading creative professionals</p>
            </div>
        </div>

        <?php if ( ! empty( $allCats ) ) : ?>
        <div class="artist-category-nav">
            <a href="<?php echo $basePath; ?>artists/" class="artist-category-nav__link <?php echo empty( $categorySlug ) ? 'artist-category-nav__link--active' : ''; ?>">All</a>
            <?php foreach ( $allCats as $ac ) : ?>
                <a href="<?php echo $basePath; ?>artists/<?php echo htmlspecialchars( $ac['slug'] ); ?>/"
                   class="artist-category-nav__link <?php echo ( $categorySlug === $ac['slug'] ) ? 'artist-category-nav__link--active' : ''; ?>">
                    <?php echo htmlspecialchars( $ac['name'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <section class="section-block py-10">
            <div class="artist-grid" id="artistGrid">
                <?php if ( empty( $artists ) ) : ?>
                    <p style="text-align:center;color:var(--muted);grid-column:1/-1;">No artists found<?php echo ! empty( $categoryName ) ? ' in this category' : ''; ?>.</p>
                <?php else : ?>
                    <?php foreach ( $artists as $artist ) : ?>
                        <?php
                        $cardCatSlug = ! empty( $categorySlug ) ? $categorySlug : $artist['category_slug'];
                        $cardCatName = ! empty( $categoryName ) ? $categoryName : $artist['category_name'];
                        ?>
                        <a href="<?php echo $basePath; ?>artists/<?php echo htmlspecialchars( $cardCatSlug ); ?>/<?php echo htmlspecialchars( $artist['slug'] ); ?>/" class="artist-grid-card">
                            <div class="artist-grid-card__img">
                                <?php if ( $artist['image'] ) : ?>
                                    <?php $imgSrc = strpos( $artist['image'], 'http' ) === 0 ? $artist['image'] : $basePath . $artist['image']; ?>
                                    <img src="<?php echo htmlspecialchars( $imgSrc ); ?>" alt="<?php echo htmlspecialchars( $artist['name'] ); ?>">
                                <?php else : ?>
                                    <div class="artist-grid-card__img-placeholder">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="artist-grid-card__badge"><?php echo htmlspecialchars( $cardCatName ); ?></span>
                            </div>
                            <div class="artist-grid-card__body">
                                <h3 class="artist-grid-card__name"><?php echo htmlspecialchars( $artist['name'] ); ?></h3>
                                <?php if ( $artist['location'] ) : ?>
                                    <div class="artist-grid-card__location">
                                        <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars( $artist['location'] ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $artist['bio'] ) : ?>
                                    <p class="artist-grid-card__bio"><?php echo htmlspecialchars( $artist['bio'] ); ?></p>
                                <?php endif; ?>
                                <span class="artist-grid-card__cta">View Profile <i class="fa-solid fa-arrow-right"></i></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ( $pagination && $pagination['totalRecords'] > 0 ) : ?>
                <?php
                // Build the pagination base URL
                $paginationBaseUrl = ! empty( $categorySlug )
                    ? $basePath . 'artists/' . $categorySlug . '/'
                    : $basePath . 'artists/';
                echo render_pagination( $pagination, $paginationBaseUrl );
                ?>
            <?php endif; ?>
        </section>

        <section class="artist-cta">
            <h2>Looking for the Right Talent?</h2>
            <p>Post your project and connect with the perfect artists for your production.</p>
            <a href="<?php echo $basePath; ?>contact/" class="btn btn--primary">Enquire Now</a>
        </section>
    </div>
</section>

<?php include_once '../footer.php'; ?>

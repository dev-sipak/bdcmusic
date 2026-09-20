<?php
$pageTitle = 'Artist | BDC Music Studio';
$metaDescription = 'Artist profile and details on BDC Music Studio.';
include_once '../header.php';

$artist = null;
$categoryName = '';
$categorySlug = '';
$pricing = [];
$allCats = [];

try {
    $pdo = db_connect();

    $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $path = trim( str_replace( '/bdcmusic', '', $path ), '/' );
    $filteredParts = array_values( array_filter( explode( '/', $path ) ) );

    $catSlug = '';
    $artistSlug = '';
    if ( count( $filteredParts ) >= 2 ) {
        $catSlug = $filteredParts[ count( $filteredParts ) - 2 ];
        $artistSlug = $filteredParts[ count( $filteredParts ) - 1 ];
    } elseif ( count( $filteredParts ) === 1 && $filteredParts[0] !== 'artists' ) {
        $artistSlug = $filteredParts[0];
    }

    if ( ! empty( $artistSlug ) ) {
        $aStmt = $pdo->prepare(
            'SELECT a.id, a.name, a.slug, a.image, a.location, a.bio,
                    ac.name AS category_name, ac.slug AS category_slug
             FROM artists a
             JOIN artist_categories ac ON a.category_id = ac.id
             WHERE a.slug = :slug AND a.is_active = 1'
        );
        $aStmt->execute( [ ':slug' => $artistSlug ] );
        $artist = $aStmt->fetch();

        if ( $artist ) {
            $categoryName = $artist['category_name'];
            $categorySlug = $artist['category_slug'];
            $pageTitle = $artist['name'] . ' | BDC Music Studio';
            $metaDescription = $artist['bio'] ? mb_substr( $artist['bio'], 0, 160 ) : $artist['name'] . ' - ' . $categoryName;

            $pStmt = $pdo->prepare( 'SELECT service_type, price FROM artist_pricing WHERE artist_id = :aid ORDER BY sort_order' );
            $pStmt->execute( [ ':aid' => $artist['id'] ] );
            $pricing = $pStmt->fetchAll();
        }
    }

    $allStmt = $pdo->query( 'SELECT name, slug FROM artist_categories WHERE is_active = 1 ORDER BY sort_order' );
    $allCats = $allStmt->fetchAll();
} catch ( Exception $e ) {
    $artist = null;
}
?>

<section class="page-hero pb-0">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo $basePath; ?>artists/">Artists</a>
            <span>/</span>
            <?php if ( ! empty( $categoryName ) ) : ?>
                <a href="<?php echo $basePath; ?>artists/<?php echo htmlspecialchars( $categorySlug ); ?>/"><?php echo htmlspecialchars( $categoryName ); ?></a>
                <span>/</span>
            <?php endif; ?>
            <span><?php echo htmlspecialchars( $artist['name'] ?? 'Artist' ); ?></span>
        </div>
    </div>
</section>

<?php if ( $artist ) : ?>
<section class="section-block py-10">
    <div class="container">
        <div class="artist-detail">
            <div class="artist-detail__img-wrap">
                <?php if ( $artist['image'] ) : ?>
                    <?php $imgSrc = strpos( $artist['image'], 'http' ) === 0 ? $artist['image'] : $basePath . $artist['image']; ?>
                    <img src="<?php echo htmlspecialchars( $imgSrc ); ?>" alt="<?php echo htmlspecialchars( $artist['name'] ); ?>">
                <?php else : ?>
                    <div class="profile-card__img-placeholder" style="min-height:400px;">
                        <i class="fa-solid fa-user" style="font-size:4rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="artist-detail__info">
                <h1 class="artist-detail__name"><?php echo htmlspecialchars( $artist['name'] ); ?></h1>
                <p class="artist-detail__category"><?php echo htmlspecialchars( $categoryName ); ?></p>
                <?php if ( $artist['location'] ) : ?>
                    <span class="artist-detail__location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars( $artist['location'] ); ?></span>
                <?php endif; ?>
                <?php if ( $artist['bio'] ) : ?>
                    <p class="artist-detail__bio"><?php echo nl2br( htmlspecialchars( $artist['bio'] ) ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $pricing ) ) : ?>
                <div class="artist-pricing">
                    <h3>Pricing</h3>
                    <table class="artist-pricing__table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $pricing as $p ) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars( $p['service_type'] ); ?></td>
                                <td>₹<?php echo number_format( $p['price'], 2 ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <button type="button" class="btn btn-md" id="open-enquiry-modal" style="margin-top:24px;display:inline-flex;align-items:center;gap:8px;width:fit-content;cursor:pointer;">
                    Enquiry Now
                </button>
            </div>
        </div>
    </div>
</section>
<?php else : ?>
<section class="section-block">
    <div class="container" style="text-align:center;padding:60px 0;">
        <h2>Artist Not Found</h2>
        <p style="color:var(--muted);">The artist you are looking for does not exist or is no longer active.</p>
        <a href="<?php echo $basePath; ?>artists/" class="btn" style="margin-top:20px;">Browse Artists</a>
    </div>
</section>
<?php endif; ?>

<!-- Enquiry Modal -->
<div id="enquiry-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
    <div style="background:var(--card,#1a1a2e);border-radius:16px;max-width:480px;width:90%;padding:32px;position:relative;box-shadow:0 25px 60px rgba(0,0,0,.5);">
        <button type="button" id="close-enquiry-modal" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--muted,#888);font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
        <h3 style="margin:0 0 4px;font-size:1.25rem;">Enquiry for <?php echo htmlspecialchars( $artist['name'] ?? '' ); ?></h3>
        <p style="margin:0 0 20px;color:var(--muted,#888);font-size:0.875rem;">Fill in your details and we'll get back to you.</p>
        <form id="enquiry-form" novalidate>
            <input type="hidden" id="enquiry-artist-id" value="<?php echo intval( $artist['id'] ?? 0 ); ?>">
            <div style="margin-bottom:14px;">
                <label style="display:block;margin-bottom:4px;font-size:0.85rem;font-weight:500;">Name <span style="color:red;">*</span></label>
                <input type="text" id="enquiry-name" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#333);background:var(--secondary,#12122a);color:var(--text,#e2e8f0);font-size:0.9rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;margin-bottom:4px;font-size:0.85rem;font-weight:500;">Email <span style="color:red;">*</span></label>
                <input type="email" id="enquiry-email" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#333);background:var(--secondary,#12122a);color:var(--text,#e2e8f0);font-size:0.9rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;margin-bottom:4px;font-size:0.85rem;font-weight:500;">Phone</label>
                <input type="tel" id="enquiry-phone" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#333);background:var(--secondary,#12122a);color:var(--text,#e2e8f0);font-size:0.9rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:4px;font-size:0.85rem;font-weight:500;">Message</label>
                <textarea id="enquiry-message" rows="3" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#333);background:var(--secondary,#12122a);color:var(--text,#e2e8f0);font-size:0.9rem;box-sizing:border-box;resize:vertical;"></textarea>
            </div>
            <div id="enquiry-error" style="display:none;color:#ef4444;font-size:0.85rem;margin-bottom:12px;"></div>
            <div id="enquiry-success" style="display:none;color:#10b981;font-size:0.85rem;margin-bottom:12px;"></div>
            <button type="submit" class="btn btn-md" style="width:100%;justify-content:center;" id="enquiry-submit-btn">Submit Enquiry</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('enquiry-modal');
    var openBtn = document.getElementById('open-enquiry-modal');
    var closeBtn = document.getElementById('close-enquiry-modal');
    var form = document.getElementById('enquiry-form');
    var errorDiv = document.getElementById('enquiry-error');
    var successDiv = document.getElementById('enquiry-success');
    var submitBtn = document.getElementById('enquiry-submit-btn');

    if (!modal || !openBtn) return;

    openBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';

        var name = document.getElementById('enquiry-name').value.trim();
        var email = document.getElementById('enquiry-email').value.trim();
        var phone = document.getElementById('enquiry-phone').value.trim();
        var message = document.getElementById('enquiry-message').value.trim();
        var artistId = document.getElementById('enquiry-artist-id').value;

        if (!name || !email) {
            errorDiv.textContent = 'Please enter your name and email.';
            errorDiv.style.display = 'block';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch('<?php echo $basePath; ?>includes/artist-enquiry-submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ artist_id: parseInt(artistId), name: name, email: email, phone: phone, message: message })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                successDiv.textContent = data.message;
                successDiv.style.display = 'block';
                form.reset();
            } else {
                errorDiv.textContent = data.message;
                errorDiv.style.display = 'block';
            }
        })
        .catch(function() {
            errorDiv.textContent = 'Something went wrong. Please try again.';
            errorDiv.style.display = 'block';
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Enquiry';
        });
    });
});
</script>

<?php include_once '../footer.php'; ?>

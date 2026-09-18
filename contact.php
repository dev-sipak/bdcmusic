<?php
$pageTitle       = 'Contact BDC Music Studio';
$metaDescription = 'Contact BDC Music Studio for recording, production, distribution, promotion, IPRS, classes, and artist service enquiries.';
$ogTitle         = $pageTitle;
$ogDescription   = $metaDescription;
include_once 'header.php';
?>

<section class="contact contact-redesign">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <span>Contact</span>
        </div>

        <div class="contact-hero">
            <div class="contact-copy">
                <span class="eyebrow">CONTACT US</span>
                <h1>Let us help shape your next music project.</h1>
                <p>Reach the BDC Music team for studio bookings, music production, promotion, distribution, classes, and artist services.</p>
                <a class="contact-email" href="mailto:info@bdcmusic.in">info@bdcmusic.in</a>
            </div>
            <div class="contact-illustration" aria-hidden="true">
                <svg viewBox="0 0 520 380" role="img">
                    <rect x="40" y="52" width="440" height="276" rx="32" fill="transparent" stroke="" stroke-width="4"/>
                    <circle cx="172" cy="178" r="70" fill="#e4002b" opacity=".12"/>
                    <path d="M196 119v117c0 28-20 47-49 47-24 0-42-14-42-34 0-22 20-36 46-31 9 2 17 5 23 10v-91l132-25v97c0 28-20 47-49 47-24 0-42-14-42-34 0-22 20-36 46-31 9 2 17 5 23 10v-61l-88 17z" fill="#e4002b"/>
                    <path d="M338 102h62M338 138h92M338 174h70" stroke="#000" stroke-width="12" stroke-linecap="round" opacity=".75"/>
                    <path d="M105 318c36-30 72-30 108 0s72 30 108 0 72-30 108 0" fill="none" stroke="#e4002b" stroke-width="8" stroke-linecap="round" opacity=".35"/>
                </svg>
            </div>
        </div>

        <div class="contact-methods">
            <div class="contact-card">
                <h3>Email</h3>
                <p><a href="mailto:info@bdcmusic.in">info@bdcmusic.in</a></p>
            </div>
            <div class="contact-card">
                <h3>Phone</h3>
                <p><a href="tel:+919599665531">+91 9599665531</a></p>
                <p><a href="tel:+919911144662">+91 9911144662</a></p>
            </div>
            <div class="contact-card">
                <h3>Business Enquiries</h3>
                <p>Studio bookings, artist services, distribution, promotion, and music education support.</p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>

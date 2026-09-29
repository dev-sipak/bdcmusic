<?php
$pageTitle = 'IPRS Services | BDC Music Studio';
$metaDescription = 'Get professional IPRS services for music registration, rights management, and royalty support for artists, composers, and lyricists.';
$ogTitle = 'IPRS Services | BDC Music Studio';
$ogDescription = 'Get assistance with IPRS registration, music rights protection, and royalty management for your original works.';

// The membership prices used to live only in <option> labels inside a form that
// posted to a JSON file. They are now rendered from service_plans, the same table
// the checkout charges from, so the price a customer reads is the price they pay.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/booking-marketing.php';

include_once '../header.php';
?>

<section class="page-hero pb-0">
    <div class="container">

        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo $siteUrl; ?>all-services">Services</a>
            <span>/</span>
            <span>IPRS Services</span>
        </div>

        <div class="section-hero iprs-bg">

            <div class="section-hero-content">

                <span class="heading-tag">
                    MUSIC RIGHTS &amp; ROYALTY SERVICES
                </span>

                <h1>
                    IPRS Services for Music Rights Protection and Royalty Management
                </h1>

                <p>
                    Protect your musical creations and manage your rights
                    with professional IPRS support for composers,
                    lyricists, publishers, and independent artists.
                </p>

                <a
                    href="<?php echo booking_esc( booking_preselect_url( 'iprs' ) ); ?>"
                    class="btn"
                >
                    Get IPRS Support
                </a>

            </div>

        </div>

        <section class="section-block">

            <h2>About the Service</h2>

            <p class="section-intro">
                Our IPRS services help music creators understand and manage their performance rights.
                We provide support with IPRS registration, music ownership documentation, royalty guidance,
                and rights management solutions to help artists receive the value they deserve from their creative work.
            </p>

        </section>

        <div class="section-block pt-0">

            <h2>Benefits</h2>

            <div class="card-grid-2">

                <!-- Benefit 1 -->
                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>Protect Your Music Rights</h3>

                        <p>
                            Register and manage your original compositions,
                            lyrics, and musical works with proper rights documentation.
                        </p>

                    </div>

                </div>

                <!-- Benefit 2 -->
                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-indian-rupee-sign"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>Receive Fair Royalties</h3>

                        <p>
                            Get guidance on royalty collection opportunities
                            from public performances and music usage.
                        </p>

                    </div>

                </div>

                <!-- Benefit 3 -->
                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>Expert Guidance</h3>

                        <p>
                            Professional support for IPRS registration
                            and rights management processes.
                        </p>

                    </div>

                </div>

                <!-- Benefit 4 -->
                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>Trusted &amp; Reliable</h3>

                        <p>
                            Work with an authorised partner committed
                            to protecting creators' rights.
                        </p>

                    </div>

                </div>

            </div>

        </div>

   
        <!-- IPRS Membership -->

<section class="section-block pt-0 iprs-package" id="choose-package">

            <h2>
                IPRS Membership
            </h2>

            <p class="section-intro">
                Choose the membership that matches how you earn from your work.
                Both include our full registration and documentation support.
            </p>

            <?php
            // The membership prices used to appear only as <option> labels inside
            // the form below, so a visitor had to open a dropdown to find out what
            // anything cost. They are now shown as cards and rendered from
            // service_plans, the same table the checkout charges from.
            echo booking_plan_grid( 'iprs' );
            ?>

        </section>

    </div>
</section>

<?php include_once '../footer.php'; ?>

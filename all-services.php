<?php

$pageTitle       = 'Services | BDC Music Studio';
$metaDescription = 'Explore BDC Music Studio services including artist marketplace, audio and video production, online and offline classes, digital music distribution, promotion, and IPRS services.';
$ogTitle         = 'Services | BDC Music Studio';
$ogDescription   = 'Discover complete music services at BDC Music Studio, from artist discovery and production to distribution, promotion, education, and music rights support.';

include_once 'header.php';

?>

<!-- SERVICES -->
<section class="services mbs-8 py-9" id="services">

    <div class="container">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <span>Services</span>
        </div>

        <!-- Section Heading -->
        <div class="section-heading">

            <span>OUR SERVICES</span>

            <h2>
                Everything Artists Need to Create, Release &amp; Grow
            </h2>

            <p>
                From discovering opportunities and producing music to learning,
                distribution, promotion and rights support, BDC Music Studio
                provides a complete ecosystem for artists and music professionals.
            </p>

        </div>

        <!-- Service Categories -->
        <div class="category-block">

            <div class="feature-grid">

                <!-- BDC Artists Marketplace -->
                <article class="card">

                    <i class="fa-solid fa-microphone-lines"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/bdc-artists-marketplace/">
                            BDC Artists Marketplace
                        </a>
                    </h3>

                    <p>
                        Discover, showcase and connect with talented artists,
                        musicians and creative professionals across the music industry.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/bdc-artists-marketplace/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>


                <!-- Audio & Video Services -->
                <article class="card">

                    <i class="fa-solid fa-photo-film"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/audio-video-services/">
                            Audio &amp; Video Services
                        </a>
                    </h3>

                    <p>
                        Professional recording, mixing, mastering, music production
                        and video content services for artists, brands and creators.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/audio-video-services/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>


                <!-- Online / Offline Classes -->
                <article class="card">

                    <i class="fa-solid fa-graduation-cap"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/online-offline-classes/">
                            Online/Offline Classes
                        </a>
                    </h3>

                    <p>
                        Learn vocals, instruments, music production and other creative
                        skills through flexible online and offline learning programs.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/online-offline-classes/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>


                <!-- Digital Music Distribution -->
                <article class="card">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/digital-music-distribution/">
                            Digital Music Distribution
                        </a>
                    </h3>

                    <p>
                        Get your music delivered to major digital streaming platforms
                        with release management and distribution support.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/digital-music-distribution/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>


                <!-- Promotion Services -->
                <article class="card">

                    <i class="fa-solid fa-bullhorn"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/promotion-services/">
                            Promotion Services
                        </a>
                    </h3>

                    <p>
                        Grow your audience and build your music brand through digital
                        promotion, content campaigns and strategic marketing.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/promotion-services/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>


                <!-- IPRS Services -->
                <article class="card">

                    <i class="fa-solid fa-copyright"></i>

                    <h3>
                        <a href="<?php echo $basePath; ?>services/iprs-services/">
                            IPRS Services
                        </a>
                    </h3>

                    <p>
                        Get support with music rights, royalty registration and
                        IPRS-related services to help manage and protect your creative work.
                    </p>

                    <a
                        class="read-more"
                        href="<?php echo $basePath; ?>services/iprs-services/"
                    >
                        Read More <span>&rarr;</span>
                    </a>

                </article>

            </div>

        </div>

    </div>

</section>

<?php include_once 'footer.php'; ?>
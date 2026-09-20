<?php
$pageTitle       = 'About | BDC Music Studio';
$metaDescription = 'Explore production, distribution, and promotion services for artists, labels, and content creators at BDC Music Studio.';
$ogTitle         = 'About | BDC Music Studio';
$ogDescription   = 'Explore production, distribution, and promotion services for artists, labels, and content creators at BDC Music Studio.';
include_once 'header.php';
require_once __DIR__ . '/includes/config.php';

?>
<!-- ABOUT -->
<section class="about" id="about">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <span>About BDC Music</span>
        </div>
        <div class="about-grid">
            <div class="about-images">
                <img src="<?php echo $assetPath; ?>images/studio.webp" alt="Studio Image">
            </div>
            <div class="about-content">
                <span class="section-tag"> ABOUT BDC MUSIC </span>
                <h2> Creative Space Built For Every Musician </h2>
                <p>
                    <span class="highlight-text">BDC Music</span> is a leading music company dedicated to
                    <span class="highlight-text">promoting artists, distributing music, and connecting creators with audiences worldwide.</span>
                    Founded in <span class="highlight-text">2015 by Bhupesh Dev Chand</span>, BDC Music was created with a vision to provide a professional platform for talented artists to showcase their creativity.
                </p>

                <p>
                    Founder <span class="highlight-text">Bhupesh Dev Chand</span> started his musical journey by learning singing from
                    <span class="highlight-text">renowned guru Gunjan Jha</span>. His passion for music inspired him to build a platform that supports and encourages emerging talent.
                </p>
                <div class="features">
                    <div class="feature">
                        <i class="fa-solid fa-microphone"></i>
                        <div>
                            <h4>Recording</h4>
                            <p>Professional Recording</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fa-solid fa-headphones"></i>
                        <div>
                            <h4>Mixing</h4>
                            <p>Studio Quality Mixing</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fa-solid fa-compact-disc"></i>
                        <div>
                            <h4>Mastering</h4>
                            <p>Streaming Ready Audio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container extra-content about-content">
        <p>
            BDC Music provides fast and reliable music distribution services across major streaming platforms including
            <span class="highlight-text">Gaana, JioSaavn, Wynk, Hungama, Apple Music, iTunes, Spotify, and more.</span>
            We also provide <span class="highlight-text">Caller Tune services, single-track distribution, and music video distribution</span> on platforms like
            <span class="highlight-text">Tata Sky, Airtel, TCL, MX Player, and other digital networks.</span>
        </p>

        <p>
            BDC Music is an excellent platform where artists can share their talent, reach new audiences, and build their musical journey.
            We support creative professionals including
            <span class="highlight-text">singers, actors, dancers, lyricists, comedians, musicians, writers, and directors.</span>
        </p>

        <p>
            With a dedicated team that believes in respecting and supporting artists and their talent, BDC Music continues to grow as a trusted name in the music industry.
            Our mission is to help artists
            <span class="highlight-text">distribute their music globally and achieve greater recognition.</span>
        </p>

        <p>
            Join <span class="highlight-text">BDC Music</span> and become part of a growing community that celebrates
            <span class="highlight-text">music, creativity, and new talent.</span>
            Subscribe to our <span class="highlight-text"><a href="https://www.youtube.com/@bdcmusic37">BDC Music Channel</a></span> for upcoming songs, music videos, and latest releases.
        </p>
    </div>
</section>

<section class="counter">
    <div class="container">
        <div class="counter-grid">
            <div>
                <h2>500+</h2>
                <p>Projects</p>
            </div>
            <div>
                <h2>120+</h2>
                <p>Artists</p>
            </div>
            <div>
                <h2>15+</h2>
                <p>Awards</p>
            </div>
            <div>
                <h2>11+</h2>
                <p>Years</p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>

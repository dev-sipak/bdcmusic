<?php
session_start();
$pageTitle = 'Digital Music Distribution Services | BDC Music Studio';
$metaDescription = 'Release your music worldwide on Spotify, Apple Music, YouTube Music and major streaming platforms with BDC Music Studio.';

$ogTitle = 'Digital Music Distribution Services | BDC Music Studio';
$ogDescription = 'Distribute your songs globally with professional music release support.';

$successMessage = '';
$errors = [];

function sanitizeDistributionValue( $value ) {
    return trim( strip_tags( (string) $value ) );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['music_distribution_submit'] ) ) {
    $releaseType = sanitizeDistributionValue( $_POST['release_type'] ?? '' );
    $artistName  = sanitizeDistributionValue( $_POST['artist_name'] ?? '' );
    $releaseTitle = sanitizeDistributionValue( $_POST['release_title'] ?? '' );
    $genre       = sanitizeDistributionValue( $_POST['genre'] ?? '' );
    $language    = sanitizeDistributionValue( $_POST['language'] ?? '' );
    $isrc        = sanitizeDistributionValue( $_POST['isrc'] ?? '' );
    $upc         = sanitizeDistributionValue( $_POST['upc'] ?? '' );
    $copyrightHelp = sanitizeDistributionValue( $_POST['copyright_help'] ?? '' );
    $releaseDate = sanitizeDistributionValue( $_POST['release_date'] ?? '' );
    $notes       = sanitizeDistributionValue( $_POST['notes'] ?? '' );
    $termsAccepted = isset( $_POST['terms'] );

    if ( $artistName === '' ) {
        $errors[] = 'Please enter the artist name.';
    }
    if ( $releaseTitle === '' ) {
        $errors[] = 'Please enter the release title.';
    }
    if ( $genre === '' ) {
        $errors[] = 'Please select a genre.';
    }
    if ( $language === '' ) {
        $errors[] = 'Please select a language.';
    }
    if ( empty( $_FILES['audio_file']['name'] ) ) {
        $errors[] = 'Please upload the audio file.';
    }
    if ( empty( $_FILES['cover_artwork']['name'] ) ) {
        $errors[] = 'Please upload the cover artwork.';
    }
    if ( ! $termsAccepted ) {
        $errors[] = 'Please accept the privacy policy and terms.';
    }

    if ( empty( $errors ) ) {
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/file-upload.php';

        $bookingId = 'DIST-' . time();
        $uploadDir = get_upload_dir( 'digital-distribution', $bookingId );
        $storedFiles = [];

        if ( ! empty( $_FILES['audio_file']['name'] ) ) {
            $result = process_file_upload( $_FILES['audio_file'], $uploadDir, get_allowed_mime_types( 'digital-distribution' ), get_max_file_size( 'digital-distribution' ) );
            if ( $result ) {
                $result['field'] = 'audio_file';
                $storedFiles[] = $result;
            }
        }
        if ( ! empty( $_FILES['cover_artwork']['name'] ) ) {
            $result = process_file_upload( $_FILES['cover_artwork'], $uploadDir, get_allowed_mime_types( 'digital-distribution' ), get_max_file_size( 'digital-distribution' ) );
            if ( $result ) {
                $result['field'] = 'cover_artwork';
                $storedFiles[] = $result;
            }
        }
        if ( ! empty( $_FILES['metadata_file']['name'] ) ) {
            $result = process_file_upload( $_FILES['metadata_file'], $uploadDir, get_allowed_mime_types( 'digital-distribution' ), get_max_file_size( 'digital-distribution' ) );
            if ( $result ) {
                $result['field'] = 'metadata_file';
                $storedFiles[] = $result;
            }
        }

        $booking = [
            'booking_id'     => $bookingId,
            'created_at'     => date( 'Y-m-d H:i:s' ),
            'service'        => 'Digital Music Distribution',
            'release_type'   => $releaseType,
            'artist_name'    => $artistName,
            'release_title'  => $releaseTitle,
            'genre'          => $genre,
            'language'       => $language,
            'isrc'           => $isrc,
            'upc'            => $upc,
            'copyright_help' => $copyrightHelp,
            'release_date'   => $releaseDate,
            'notes'          => $notes,
            'files'          => $storedFiles,
            'status'         => 'Pending',
        ];

        $dataDir = dirname( __DIR__ ) . '/data';
        if ( ! is_dir( $dataDir ) ) {
            mkdir( $dataDir, 0755, true );
        }
        $bookingsPath = $dataDir . '/distribution_bookings.json';
        $bookings = [];
        if ( file_exists( $bookingsPath ) ) {
            $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
        }
        $bookings[] = $booking;
        file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

        $successMessage = 'Release submitted successfully! Booking ID: ' . $bookingId;
    }
}

include_once '../header.php';
?>

<section class="page-hero">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo $siteUrl; ?>all-services">Services</a>
            <span>/</span>
            <span>Digital Music Distribution</span>
        </div>
        <!-- Hero -->
        <div class="section-hero music-distribution-bg reveal">
            <div class="section-hero-content">
                <span class="heading-tag"> DISTRIBUTION SERVICES </span>
                <h1> Digital Music Distribution Services for Independent Artists </h1>
                <p> Release your music globally across Spotify, Apple Music, YouTube Music, Amazon Music and other streaming platforms with complete distribution support. </p>
                <a href="#music-distribution-enquiry" class="btn"> Start Distribution </a>
            </div>
        </div>
        <!-- About -->
        <section class="section-block">
            <h2> About Digital Music Distribution </h2>
            <p class="section-intro"> BDC Music Studio helps independent artists distribute their music worldwide. From release preparation, metadata management, artwork, audio checks, and platform delivery, we support every step of your music release journey. </p>
        </section>
        <!-- Process -->
        <section class="section-block pt-0">
            <h2> How Digital Distribution Works </h2>
            <div class="card-grid-2">
                <div class="category-block">
                    <h4> 01. Submit Your Release </h3>
                    <p> Provide your song details, artist information, artwork and audio files. </p>
                </div>
                <div class="category-block">
                    <h4> 02. Quality Review </h4>
                    <p> Our team checks audio quality, metadata accuracy and release requirements. </p>
                </div>
                <div class="category-block">
                    <h4> 03. Platform Delivery </h4>
                    <p> Your music is delivered to major digital streaming platforms worldwide. </p>
                </div>
                <div class="category-block">
                    <h4> 04. Track Performance </h4>
                    <p> Monitor your release and manage royalties from your distributed music. </p>
                </div>
            </div>
        </section>
    </div><!-- /.container -->
    <div class="container-fluid">
        <!-- Platforms -->
		<section class="section-block pt-0">

			<h2>Where Your Music Gets Distributed</h2>

			<p class="section-intro">
				Release your music on the world's leading streaming platforms
				and reach listeners globally.
			</p>

			<div class="platform-marquee">

				<!-- Row 1: Left to Right -->
				<div class="platform-track platform-track-right">

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<!-- Duplicate for seamless loop -->
					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

				</div>


				<!-- Row 2: Right to Left -->
				<div class="platform-track platform-track-left">

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

					<!-- Duplicate for seamless loop -->
					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/pandora.png" alt="Pandora">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/tidal.png" alt="TIDAL">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/deezer.png" alt="Deezer">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/gaana.png" alt="Gaana">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/jiosaavn.png" alt="JioSaavn">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/amazon-music.png" alt="Amazon Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/youtube-music.png" alt="YouTube Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/apple-music.png" alt="Apple Music">
					</div>

					<div class="platform-card">
						<img src="<?php echo $siteUrl; ?>assets/images/platforms/spotify.png" alt="Spotify">
					</div>

				</div>

			</div>

		</section>
    </div><!-- /.container-fluid -->
    <div class="container">
        <!-- Upload Requirements -->
        <section class="section-block pt-0">
            <h2> Release Upload Requirements </h2>
            <div class="card-grid-2">
                <div class="category-block">
                    <h3> Audio Files </h3>
                    <ul class="feature-list">
                        <li>Supported formats: WAV / MP3</li>
                        <li>Maximum file size: 300MB</li>
                        <li>High quality master audio recommended</li>
                    </ul>
                </div>
                <div class="category-block">
                    <h3> Cover Artwork </h3>
                    <ul class="feature-list">
                        <li>3000 x 3000 px recommended</li>
                        <li>JPG format required</li>
                        <li>Professional artwork matching metadata</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- Pricing -->
        <section class="section-block pt-0">
            <h2> Distribution Plans </h2>
            <div class="plans-grid">
                <div class="plan-card">
                    <h3> Release Plan </h3>
                    <span class="price"> ₹199 </span>
                    <ul class="feature-list">
                        <li>Single song distribution</li>
                        <li>Global music platforms</li>
                        <li>Quarterly royalty payments</li>
                        <li>90% streaming revenue share</li>
                    </ul>
                </div>
                <div class="plan-card">
                    <h3> Artist Unlimited </h3>
                    <span class="price"> ₹1,199 / Year </span>
                    <ul class="feature-list">
                        <li>Unlimited song releases</li>
                        <li>One artists</li>
                        <li>YouTube Content ID</li>
                        <li>80% streaming revenue share</li>
                    </ul>
                </div>
                <div class="plan-card">
                    <h3> PRO Label </h3>
                    <span class="price"> ₹7,999 </span>
                    <ul class="feature-list">
                        <li>Label registration</li>
                        <li>Unlimited releases</li>
                        <li>Unlimited artists</li>
                        <li>90% streaming revenue share</li>
                    </ul>
                </div>
                <div class="plan-card">
                    <h3> Limitless Label </h3>
                    <span class="price"> ₹14,999 / Year </span>
                    <ul class="feature-list">
                        <li>Unlimited song release</li>
                        <li>Monthly royalty payment</li>
                        <li>Label support</li>
                        <li>80% revenue share</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- Distribution Form -->
        <section class="section-block pt-0">
            <div class="booking-section" id="music-distribution-enquiry">
                <h2>
                    Submit Your Music Release
                </h2>
                <p class="section-intro">
                    Complete your release details, upload your music files,
                    select platforms and submit your track for distribution.
                </p>
                <?php if ( ! empty( $errors ) ) : ?>
                    <div class="notice error"><strong>Please review:</strong><ul><?php foreach ( $errors as $error ) : ?><li><?php echo htmlspecialchars( $error ); ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if ( $successMessage !== '' ) : ?>
                    <div class="notice success"><?php echo htmlspecialchars( $successMessage ); ?></div>
                <?php endif; ?>
                <div class="booking-content">
                    <form class="form-panel premium-booking-form"
                        method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" name="music_distribution_submit" value="1">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-field">
                                    <label>
                                        Release Type <span class="required-star">*</span>
                                    </label>
                                    <div class="radio-group">
                                        <label class="radio-card">
                                            <input
                                                type="radio"
                                                name="release_type"
                                                value="Single"
                                                required>
                                            Single
                                        </label>
                                        
                                        <label class="radio-card">
                                            <input
                                                type="radio"
                                                name="release_type"
                                                value="Album">
                                            Album
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Artist Name <span class="required-star">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="artist_name"
                                        placeholder="Artist Name"
                                        required>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Release Title <span class="required-star">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="release_title"
                                        placeholder="Song / Album Name"
                                        required>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Genre
                                    </label>
                                    <select name="genre" required>
									<option value="" disabled selected>Select Genre</option>
									<option value="Pop">Pop</option>
									<option value="Rock">Rock</option>
									<option value="Hip Hop">Hip Hop</option>
									<option value="Rap">Rap</option>
									<option value="R&B">R&B</option>
									<option value="Classical">Classical</option>
									<option value="Folk">Folk</option>
									<option value="Devotional">Devotional</option>
									<option value="Electronic">Electronic</option>
									<option value="Jazz">Jazz</option>
									<option value="Instrumental">Instrumental</option>
									<option value="Bollywood">Bollywood</option>
									<option value="Other">Other</option>
								</select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Language
                                    </label>
                                    <select name="language" required>
									<option value="" disabled selected>Select Language</option>
									<option value="Hindi">Hindi</option>
									<option value="English">English</option>
									<option value="Odia">Odia</option>
									<option value="Bengali">Bengali</option>
									<option value="Telugu">Telugu</option>
									<option value="Tamil">Tamil</option>
									<option value="Kannada">Kannada</option>
									<option value="Malayalam">Malayalam</option>
									<option value="Marathi">Marathi</option>
									<option value="Punjabi">Punjabi</option>
									<option value="Gujarati">Gujarati</option>
									<option value="Urdu">Urdu</option>
									<option value="Assamese">Assamese</option>
									<option value="Other">Other</option>
								</select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Need ISRC?
                                    </label>
                                    <select name="isrc">
                                        <option value="Yes">
                                            Yes
                                        </option>
                                        <option value="No">
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Need UPC?
                                    </label>
                                    <select name="upc">
                                        <option value="Yes">
                                            Yes
                                        </option>
                                        <option value="No">
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Copyright Help?
                                    </label>
                                    <select name="copyright_help">
                                        <option value="Yes">
                                            Yes
                                        </option>
                                        <option value="No">
                                            No
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Upload Audio <span class="required-star">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        name="audio_file"
                                        accept=".wav,.mp3"
                                        required>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Cover Artwork <span class="required-star">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        name="cover_artwork"
                                        accept=".jpg,.jpeg,.png"
                                        required>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-field">
                                    <label>
                                        Metadata File
                                    </label>
                                    <input
                                        type="file"
                                        name="metadata_file">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-field">
                                    <label>
                                        Release Date
                                    </label>
                                    <input
                                        type="date"
                                        name="release_date">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-field">
                                    <label>
                                        Additional Notes
                                    </label>
                                    <textarea
                                        name="notes"
                                        placeholder="Tell us about your release"></textarea>
                                </div>
                            </div>
							<div class="col-12">
								<label class="policy-check">
									<input type="checkbox" name="terms" required>
									<span>I have read the <a href="<?php echo $siteUrl; ?>privacy-policy">privacy policy</a> and <a href="<?php echo $siteUrl; ?>terms-and-conditions">terms and conditions</a>.</span>
								</label>
							</div>
                        </div>
                        <div class="form-actions">
                            <button
                                class="btn"
                                type="submit">
                                Submit Release
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="faq " id="faq">
            <div class="container">
                <div class="section-heading reveal">
                    <h2> Frequently Asked Questions </h2>
                    <p> Find answers about music distribution, release process, platform delivery, royalties, and submission requirements. </p>
                </div>
                <div class="faq-wrapper">
                    <details class="faq-item reveal" open>
                        <summary class="faq-question"> How long does music distribution take? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> Most releases are delivered to streaming platforms within 2-5 working days after approval and quality checks. </p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question"> Do I keep ownership of my music? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> Yes. Artists keep ownership and rights of their original music content. </p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question"> Which platforms receive my music release? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> Your release can be distributed to Spotify, Apple Music, YouTube Music, Amazon Music, JioSaavn, Gaana, Deezer, TIDAL and other digital platforms. </p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question"> Which audio format is required? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> We support high-quality WAV and MP3 audio files. WAV format is recommended for professional releases. </p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question"> Can I distribute Singles, EPs and Albums? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> Yes. We support single releases, EP releases, and complete album distribution. </p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question"> Do I need ISRC and UPC codes? <i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p> ISRC and UPC codes can be arranged depending on your release requirements and distribution plan. </p>
                        </div>
                    </details>
                </div>
            </div>
        </section>
    </div>
</section>
<?php
include_once '../footer.php';
?>

<?php
session_start();
$pageTitle = 'IPRS Services | BDC Music Studio';
$metaDescription = 'Get professional IPRS services for music registration, rights management, and royalty support for artists, composers, and lyricists.';
$ogTitle = 'IPRS Services | BDC Music Studio';
$ogDescription = 'Get assistance with IPRS registration, music rights protection, and royalty management for your original works.';

$successMessage = '';
$errors = [];

function sanitizeIprsValue( $value ) {
    return trim( strip_tags( (string) $value ) );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['iprs_submit'] ) ) {
    $name          = sanitizeIprsValue( $_POST['name'] ?? '' );
    $email         = sanitizeIprsValue( $_POST['email'] ?? '' );
    $phone         = sanitizeIprsValue( $_POST['phone'] ?? '' );
    $applicantType = sanitizeIprsValue( $_POST['applicant_type'] ?? '' );
    $songReleased  = sanitizeIprsValue( $_POST['song_released'] ?? '' );
    $songLinks     = sanitizeIprsValue( $_POST['song_links'] ?? '' );
    $songTitle     = sanitizeIprsValue( $_POST['song_title'] ?? '' );
    $artistName    = sanitizeIprsValue( $_POST['artist_name'] ?? '' );
    $accountHolder = sanitizeIprsValue( $_POST['account_holder'] ?? '' );
    $accountNumber = sanitizeIprsValue( $_POST['account_number'] ?? '' );
    $ifsc          = sanitizeIprsValue( $_POST['ifsc'] ?? '' );
    $bankName      = sanitizeIprsValue( $_POST['bank_name'] ?? '' );
    $membershipType = sanitizeIprsValue( $_POST['membership_type'] ?? '' );
    $message       = sanitizeIprsValue( $_POST['message'] ?? '' );
    $termsAccepted = isset( $_POST['terms'] );

    if ( $name === '' ) { $errors[] = 'Please enter your full name.'; }
    if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) { $errors[] = 'Please enter a valid email address.'; }
    if ( $phone === '' ) { $errors[] = 'Please enter your mobile number.'; }
    if ( $applicantType === '' ) { $errors[] = 'Please select an applicant type.'; }
    if ( $songReleased === '' ) { $errors[] = 'Please indicate if you have released a song.'; }
    if ( $songLinks === '' ) { $errors[] = 'Please provide your released song links.'; }
    if ( empty( $_FILES['pan_card']['name'] ) ) { $errors[] = 'Please upload your PAN card.'; }
    if ( empty( $_FILES['address_proof']['name'] ) ) { $errors[] = 'Please upload your address proof.'; }
    if ( empty( $_FILES['photo']['name'] ) ) { $errors[] = 'Please upload your passport size photo.'; }
    if ( $accountHolder === '' ) { $errors[] = 'Please enter the account holder name.'; }
    if ( $accountNumber === '' ) { $errors[] = 'Please enter the bank account number.'; }
    if ( $ifsc === '' ) { $errors[] = 'Please enter the IFSC code.'; }
    if ( $bankName === '' ) { $errors[] = 'Please enter the bank name.'; }
    if ( $membershipType === '' ) { $errors[] = 'Please select a membership type.'; }
    if ( ! $termsAccepted ) { $errors[] = 'Please accept the privacy policy and terms.'; }

    if ( empty( $errors ) ) {
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/file-upload.php';

        $bookingId = 'IPRS-' . time();
        $uploadDir = get_upload_dir( 'iprs', $bookingId );
        $storedFiles = [];

        $fileFields = ['pan_card', 'address_proof', 'photo', 'song_proof'];
        foreach ( $fileFields as $field ) {
            if ( ! empty( $_FILES[ $field ]['name'] ) ) {
                $result = process_file_upload( $_FILES[ $field ], $uploadDir, get_allowed_mime_types( 'iprs' ), get_max_file_size( 'iprs' ) );
                if ( $result ) {
                    $result['field'] = $field;
                    $storedFiles[] = $result;
                }
            }
        }

        $booking = [
            'booking_id'      => $bookingId,
            'created_at'      => date( 'Y-m-d H:i:s' ),
            'service'         => 'IPRS Services',
            'name'            => $name,
            'email'           => $email,
            'phone'           => $phone,
            'applicant_type'  => $applicantType,
            'song_released'   => $songReleased,
            'song_links'      => $songLinks,
            'song_title'      => $songTitle,
            'artist_name'     => $artistName,
            'account_holder'  => $accountHolder,
            'account_number'  => $accountNumber,
            'ifsc'            => $ifsc,
            'bank_name'       => $bankName,
            'membership_type' => $membershipType,
            'message'         => $message,
            'files'           => $storedFiles,
            'status'          => 'Pending',
        ];

        $dataDir = dirname( __DIR__ ) . '/data';
        if ( ! is_dir( $dataDir ) ) {
            mkdir( $dataDir, 0755, true );
        }
        $bookingsPath = $dataDir . '/iprs_bookings.json';
        $bookings = [];
        if ( file_exists( $bookingsPath ) ) {
            $bookings = json_decode( file_get_contents( $bookingsPath ), true ) ?: [];
        }
        $bookings[] = $booking;
        file_put_contents( $bookingsPath, json_encode( $bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

        $successMessage = 'IPRS registration submitted successfully! Booking ID: ' . $bookingId;
    }
}

include_once '../header.php';
?>

<section class="page-hero">
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
                    href="#iprs-registration"
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

   
        <!-- IPRS Registration Form -->

        <section class="section-block pt-0">

            <div
                class="booking-section"
                id="iprs-registration"
            >

                <h2>
                    IPRS Registration
                </h2>

                <p class="section-intro">
                    Start your IPRS registration with BDC Music. Submit your details and required documents, and our team will assist you throughout the registration and documentation process.
                </p>

                <?php if ( ! empty( $errors ) ) : ?>
                    <div class="notice error"><strong>Please review:</strong><ul><?php foreach ( $errors as $error ) : ?><li><?php echo htmlspecialchars( $error ); ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if ( $successMessage !== '' ) : ?>
                    <div class="notice success"><?php echo htmlspecialchars( $successMessage ); ?></div>
                <?php endif; ?>

                <div class="booking-content">

                    <form
                        class="form-panel premium-booking-form"
                        method="post"
                        enctype="multipart/form-data"
                    >
                        <input type="hidden" name="iprs_submit" value="1">

                        <div class="row">

                            <!-- Applicant Details -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Full Name <span class="required-star">*</span></label>

                                    <input
                                        type="text"
                                        name="name"
                                        placeholder="Enter your full name"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Email <span class="required-star">*</span></label>

                                    <input
                                        type="email"
                                        name="email"
                                        placeholder="Enter your email address"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Mobile Number <span class="required-star">*</span></label>

                                    <input
                                        type="tel"
                                        name="phone"
                                        placeholder="Enter your mobile number"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Applicant Type <span class="required-star">*</span></label>

                                    <select
                                        name="applicant_type"
                                        required
                                    >
                                        <option value="">
                                            Select Applicant Type
                                        </option>

                                        <option value="author">
                                            Author / Lyricist
                                        </option>

                                        <option value="composer">
                                            Composer
                                        </option>

                                        <option value="author-composer">
                                            Author &amp; Composer
                                        </option>

                                        <option value="publisher">
                                            Music Publisher
                                        </option>

                                        <option value="artist">
                                            Independent Artist
                                        </option>
                                    </select>

                                </div>

                            </div>

                            <!-- Release Eligibility -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Have you released at least one song? <span class="required-star">*</span>
                                    </label>

                                    <select
                                        name="song_released"
                                        id="song-released"
                                        required
                                    >
                                        <option value="">
                                            Select an option
                                        </option>

                                        <option value="yes">
                                            Yes, I have released a song
                                        </option>

                                        <option value="no">
                                            No, I have not released a song
                                        </option>
                                    </select>

                                </div>

                            </div>

                            <!-- Released Song Information -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Released Song / Music Links <span class="required-star">*</span>
                                    </label>

                                    <textarea
                                        name="song_links"
                                        placeholder="Paste YouTube, Spotify, Apple Music, JioSaavn or other released music links"
                                        required
                                    ></textarea>

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Song / Work Title</label>

                                    <input
                                        type="text"
                                        name="song_title"
                                        placeholder="Enter your released song title"
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Artist Name</label>

                                    <input
                                        type="text"
                                        name="artist_name"
                                        placeholder="Enter your artist name"
                                    >

                                </div>

                            </div>

                            <!-- Identity Documents -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>PAN Card <span class="required-star">*</span></label>

                                    <input
                                        type="file"
                                        name="pan_card"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Aadhaar / Address Proof <span class="required-star">*</span></label>

                                    <input
                                        type="file"
                                        name="address_proof"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Passport Size Photo <span class="required-star">*</span></label>

                                    <input
                                        type="file"
                                        name="photo"
                                        accept=".jpg,.jpeg,.png"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Released Song Proof</label>

                                    <input
                                        type="file"
                                        name="song_proof"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                    >

                                </div>

                            </div>

                            <!-- Bank Details -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Account Holder Name <span class="required-star">*</span></label>

                                    <input
                                        type="text"
                                        name="account_holder"
                                        placeholder="Enter account holder name"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Bank Account Number <span class="required-star">*</span></label>

                                    <input
                                        type="text"
                                        name="account_number"
                                        placeholder="Enter bank account number"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>IFSC Code <span class="required-star">*</span></label>

                                    <input
                                        type="text"
                                        name="ifsc"
                                        placeholder="Enter IFSC code"
                                        required
                                    >

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="form-field">

                                    <label>Bank Name <span class="required-star">*</span></label>

                                    <input
                                        type="text"
                                        name="bank_name"
                                        placeholder="Enter bank name"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- Membership -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Membership Type <span class="required-star">*</span>
                                    </label>

                                    <select
                                        name="membership_type"
                                        required
                                    >
                                        <option value="">
                                            Select Membership Type
                                        </option>

                                        <option value="author-composer">
                                            Author / Composer — ₹2,499
                                        </option>

                                        <option value="publisher">
                                            Publisher — ₹4,999
                                        </option>
                                    </select>

                                </div>

                            </div>

                            <!-- Additional Information -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Additional Information
                                    </label>

                                    <textarea
                                        name="message"
                                        placeholder="Tell us about your music, works or any questions regarding IPRS registration"
                                    ></textarea>

                                </div>

                            </div>

                            <!-- Consent -->

                            <div class="col-12">

                                <label class="policy-check">
									<input type="checkbox" name="terms" required>
									<span>I have read the <a href="<?php echo $siteUrl; ?>privacy-policy">privacy policy</a> and <a href="<?php echo $siteUrl; ?>terms-and-conditions">terms and conditions</a>.</span>
								</label>

                            </div>

                        </div>

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="btn"
                            >
                                Submit IPRS Registration
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </section>

        <section class="faq" id="faq">

            <div class="container">

                <div class="section-heading reveal">

                    <h2>
                        Frequently Asked Questions
                    </h2>

                    <p>
                        Find answers about music promotion, video marketing,
                        content reach, campaigns and promotional strategies.
                    </p>

                </div>

                <div class="faq-wrapper">

                    <details class="faq-item reveal" open>

                        <summary class="faq-question">

                            What promotion services do you provide?

                            <i class="fa-solid fa-plus"></i>

                        </summary>

                        <div class="faq-answer">

                            <p>
                                We provide music video promotion, reels promotion,
                                social media marketing, audience growth strategies,
                                and promotional campaigns for artists and brands.
                            </p>

                        </div>

                    </details>

                    <details class="faq-item reveal">

                        <summary class="faq-question">

                            Can you promote my music release?

                            <i class="fa-solid fa-plus"></i>

                        </summary>

                        <div class="faq-answer">

                            <p>
                                Yes. We help artists promote singles, EPs,
                                albums, music videos and new releases through
                                targeted promotional strategies.
                            </p>

                        </div>

                    </details>

                    <details class="faq-item reveal">

                        <summary class="faq-question">

                            Which platforms do you promote on?

                            <i class="fa-solid fa-plus"></i>

                        </summary>

                        <div class="faq-answer">

                            <p>
                                We promote content across YouTube,
                                Instagram, Facebook and other digital platforms
                                where your audience is active.
                            </p>

                        </div>

                    </details>

                    <details class="faq-item reveal">

                        <summary class="faq-question">

                            How long does a promotion campaign take?

                            <i class="fa-solid fa-plus"></i>

                        </summary>

                        <div class="faq-answer">

                            <p>
                                Campaign duration depends on your goals,
                                content type and selected promotion package.
                            </p>

                        </div>

                    </details>

                    <details class="faq-item reveal">

                        <summary class="faq-question">

                            Can I promote old content?

                            <i class="fa-solid fa-plus"></i>

                        </summary>

                        <div class="faq-answer">

                            <p>
                                Yes. Existing music videos, reels and
                                brand content can also be promoted to improve
                                reach and engagement.
                            </p>

                        </div>

                    </details>

                </div>

            </div>

        </section>

    </div>
</section>

<?php include_once '../footer.php'; ?>

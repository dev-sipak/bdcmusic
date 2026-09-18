<?php
session_start();
$pageTitle = 'Audio & Video Services | BDC Music Studio';
$metaDescription = 'Book professional audio and video services for recording, music production, mixing, mastering, editing, music videos, reels, events, and corporate videos.';
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;
include_once '../header.php';

$successMessage = '';
$errors = [];

function sanitizeBookingValue( $value ) {
    return trim( strip_tags( (string) $value ) );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['audio_video_booking_submit'] ) ) {
    $serviceCategory = sanitizeBookingValue( $_POST['service_category'] ?? '' );
    $serviceType = sanitizeBookingValue( $_POST['service_type'] ?? '' );
    $clientName = sanitizeBookingValue( $_POST['client_name'] ?? '' );
    $clientEmail = sanitizeBookingValue( $_POST['client_email'] ?? '' );
    $clientPhone = sanitizeBookingValue( $_POST['client_phone'] ?? '' );
    $deliveryFormats = is_array( $_POST['delivery_format'] ?? null ) ? array_map( 'sanitizeBookingValue', $_POST['delivery_format'] ) : [];
    $deadline = sanitizeBookingValue( $_POST['deadline'] ?? '' );
    $budget = sanitizeBookingValue( $_POST['budget'] ?? '' );
    $notes = sanitizeBookingValue( $_POST['notes'] ?? '' );
    $termsAccepted = isset( $_POST['terms'] );

    if ( $clientName === '' ) {
        $errors[] = 'Please share your name.';
    }

    if ( ! filter_var( $clientEmail, FILTER_VALIDATE_EMAIL ) ) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ( $serviceType === '' ) {
        $errors[] = 'Please choose a service.';
    }

    if ( $deadline === '' ) {
        $errors[] = 'Please choose a timeline.';
    }

    if ( $budget === '' ) {
        $errors[] = 'Please choose a budget.';
    }

    if ( ! $termsAccepted ) {
        $errors[] = 'Please accept the privacy policy and terms.';
    }

    if ( empty( $errors ) ) {
        session_start();
        require_once __DIR__ . '/../includes/helpers.php';
        require_once __DIR__ . '/../includes/file-upload.php';

        $storedFiles = [];
        if ( ! empty( $_FILES['project_upload']['name'][0] ) ) {
            $bookingId = 'AV-' . time();
            $uploadDir = get_upload_dir( 'audio-video', $bookingId );
            $storedFiles = process_multiple_uploads(
                $_FILES['project_upload'],
                $uploadDir,
                get_allowed_mime_types( 'audio-video' ),
                get_max_file_size( 'audio-video' )
            );
            $_SESSION['pending_upload_dir'] = $uploadDir;
            $_SESSION['pending_upload_files'] = $storedFiles;
        }

        $bookingPayload = [
            'created_at' => date( 'Y-m-d H:i:s' ),
            'service_category' => $serviceCategory,
            'service_type' => $serviceType,
            'client_name' => $clientName,
            'client_email' => $clientEmail,
            'client_phone' => $clientPhone,
            'delivery_formats' => $deliveryFormats,
            'deadline' => $deadline,
            'budget' => $budget,
            'notes' => $notes,
            'files' => $storedFiles,
        ];

        // Create Razorpay order
        $amount = 5000 * 100; // Default amount; adjust based on service
        $receipt = 'av-' . time();
        $keyId = getenv( 'RAZORPAY_KEY_ID' ) ?: 'rzp_test_demo';
        $keySecret = getenv( 'RAZORPAY_KEY_SECRET' ) ?: 'demo_secret';

        if ( 'rzp_test_demo' !== $keyId && 'demo_secret' !== $keySecret && function_exists( 'curl_init' ) ) {
            $ch = curl_init();
            curl_setopt_array( $ch, [
                CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode( $keyId . ':' . $keySecret ),
                ],
                CURLOPT_POSTFIELDS => json_encode( [
                    'amount' => $amount,
                    'currency' => 'INR',
                    'receipt' => $receipt,
                ] ),
            ] );
            $response = curl_exec( $ch );
            $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );

            if ( 200 <= $httpCode && 300 > $httpCode ) {
                $orderData = json_decode( $response, true );
                $bookingPayload['razorpay_order_id'] = $orderData['id'] ?? '';
            }
        }

        if ( empty( $bookingPayload['razorpay_order_id'] ) ) {
            $bookingPayload['razorpay_order_id'] = 'order_demo_' . time();
        }

        // Store pending booking in session
        $_SESSION['pending_booking'] = $bookingPayload;
        $_SESSION['pending_booking_type'] = 'audio_video';

        $successMessage = 'PAYMENT_REQUIRED';
    }
}

$audioPricing = [
    ['Recording', 'Rs.1,000/hr', 'Rs.2,500/session', 'Rs.5,000/session', 'Custom Quote'],
    ['Music Production', 'Rs.5,000', 'Rs.10,000', 'Rs.15,000', 'Rs.40,000'],
    ['Mixing', 'Rs.3,000', 'Rs.6,000', 'Rs.9,000', 'Rs.20,000'],
    ['Mastering', 'Rs.2,500', 'Rs.5,000', 'Rs.7,500', 'Rs.15,000'],
];

$videoPricing = [
    ['Video Production', 'Rs.10,000', 'Rs.25,000', 'Rs.50,000', 'Rs.1,00,000'],
    ['Video Editing', 'Rs.3,000', 'Rs.8,000', 'Rs.15,000', 'Rs.30,000'],
    ['Music Video', 'Rs.20,000', 'Rs.50,000', 'Rs.1,00,000', 'Rs.2,50,000'],
    ['Social Media Videos', 'Rs.1,500', 'Rs.3,500', 'Rs.7,500', 'Rs.15,000'],
    ['Motion Graphics', 'Rs.5,000', 'Rs.12,000', 'Rs.25,000', 'Rs.50,000'],
    ['YouTube Services', 'Rs.3,000', 'Rs.8,000', 'Rs.15,000', 'Rs.30,000'],
    ['Event Videos', 'Rs.15,000', 'Rs.35,000', 'Rs.70,000', 'Rs.1,50,000'],
    ['Corporate Videos', 'Rs.20,000', 'Rs.50,000', 'Rs.1,00,000', 'Rs.2,00,000'],
];
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo $siteUrl; ?>all-services">Services</a>
            <span>/</span>
            <span>Audio & Video Services</span>
        </div>

        <div class="section-hero audio-video-bg">
            <div class="section-hero-content">
                <span class="heading-tag">
                    AUDIO & VIDEO SERVICES
                </span>
                <h1>
                    Recording, Production,<br>
                    Editing & Creative<br>
                    <span class="highlight">Studio Solutions</span>
                </h1>
                <p>
                    Professional recording, music production, mixing, mastering,
                    video editing and creative services — all under one premium
                    studio workflow.
                </p>
                <div class="hero-actions">
                    <a href="#audio-video-enquiry" class="btn">
                        Start Booking
                    </a>
                </div>
            </div>
        </div>

        <section class="section-block">
            <h2>Package Comparison</h2>

            <p class="section-intro">
                Compare our Audio and Video service packages to find the
                perfect solution for your recording, production and editing
                requirements.
            </p>

            <div class="comparison-wrap">
                <!-- ==========================================
                     AUDIO SERVICES
                =========================================== -->
                <div class="category-block">
                    <h3>Audio Production Packages</h3>
                    <p>
                        Professional recording, music production, mixing,
                        mastering and studio sessions for artists,
                        creators and commercial projects.
                    </p>

                    <div class="table-scroll">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Basic</th>
                                    <th>Standard</th>
                                    <th>Premium</th>
                                    <th>Enterprise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $audioPricing as $row ) : ?>
                                    <tr>
                                        <?php foreach ( $row as $cell ) : ?>
                                            <td><?= htmlspecialchars( $cell ); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="plans-grid">
                        <div class="plan-card">
                            <h3>Basic</h3>
                            <span class="price">Rs.11,500</span>
                            <ul class="feature-list">
                                <li>Studio access</li>
                                <li>Recording engineer</li>
                                <li>Basic microphone setup</li>
                                <li>RAW audio files</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Standard</h3>
                            <span class="price">Rs.23,500</span>
                            <ul class="feature-list">
                                <li>Professional microphone</li>
                                <li>Multiple recording takes</li>
                                <li>Studio access included</li>
                                <li>Basic audio editing</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Premium</h3>
                            <span class="price">Rs.36,500</span>
                            <ul class="feature-list">
                                <li>Premium studio setup</li>
                                <li>Professional equipment</li>
                                <li>Vocal comping</li>
                                <li>Basic editing included</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Enterprise</h3>
                            <span class="price">Rs.75,000</span>
                            <ul class="feature-list">
                                <li>Complete studio booking</li>
                                <li>Dedicated sound engineer</li>
                                <li>Priority support</li>
                                <li>Custom production workflow</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                     VIDEO SERVICES
                =========================================== -->
                <div class="category-block">
                    <h3>Video Production Packages</h3>
                    <p>
                        Professional video production, editing,
                        music videos, corporate films,
                        social media content and event coverage.
                    </p>

                    <div class="table-scroll">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Basic</th>
                                    <th>Standard</th>
                                    <th>Premium</th>
                                    <th>Enterprise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $videoPricing as $row ) : ?>
                                    <tr>
                                        <?php foreach ( $row as $cell ) : ?>
                                            <td><?= htmlspecialchars( $cell ); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="plans-grid">
                        <div class="plan-card">
                            <h3>Basic</h3>
                            <ul class="feature-list">
                                <li>1 Professional Camera</li>
                                <li>Full HD Recording</li>
                                <li>1 Free Revision</li>
                                <li>Delivery in 5–7 Working Days</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Standard</h3>
                            <ul class="feature-list">
                                <li>1–2 Professional Cameras</li>
                                <li>Full HD / 4K Recording</li>
                                <li>2 Free Revisions</li>
                                <li>Delivery in 3–5 Working Days</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Premium</h3>
                            <ul class="feature-list">
                                <li>Multi-camera Setup</li>
                                <li>4K Ultra HD Delivery</li>
                                <li>Unlimited Minor Revisions</li>
                                <li>Priority Delivery</li>
                            </ul>
                        </div>

                        <div class="plan-card">
                            <h3>Enterprise</h3>
                            <ul class="feature-list">
                                <li>Custom Camera Setup</li>
                                <li>Dedicated Project Manager</li>
                                <li>Priority Timeline</li>
                                <li>Commercial Production Workflow</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-block pt-0" id="audio-video-enquiry">
            <h2>Select Required Service(s)</h2>
            <?php if ( ! empty( $errors ) ) : ?>
                <div class="notice error"><strong>Please review:</strong><ul><?php foreach ( $errors as $error ) : ?><li><?php echo htmlspecialchars( $error ); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <?php if ( $successMessage !== '' && $successMessage !== 'PAYMENT_REQUIRED' ) : ?>
                <div class="notice success"><?php echo htmlspecialchars( $successMessage ); ?></div>
            <?php endif; ?>
            <?php if ( $successMessage === 'PAYMENT_REQUIRED' && ! empty( $_SESSION['pending_booking'] ) ) : ?>
                <div class="notice success" id="rzp-payment-box">
                    <h3>Complete Payment</h3>
                    <p>Click the button below to pay securely with Razorpay.</p>
                    <button class="btn" type="button" id="rzp-button">Pay Now</button>
                    <p id="payment-message" style="margin-top:12px;"></p>
                </div>
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var btn = document.getElementById('rzp-button');
                    var msg = document.getElementById('payment-message');
                    if (!btn) return;
                    var orderId = '<?= htmlspecialchars( $_SESSION["pending_booking"]["razorpay_order_id"] ?? "" ) ?>';
                    var options = {
                        key: 'rzp_test_demo',
                        amount: '<?= intval( 5000 * 100 ) ?>',
                        currency: 'INR',
                        name: 'BDC Music',
                        description: 'Audio & Video Service Booking',
                        order_id: orderId,
                        handler: function (resp) {
                            msg.innerText = 'Verifying payment...';
                            btn.disabled = true;
                            fetch('/bdcmusic/includes/razorpay-verify.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    razorpay_order_id: resp.razorpay_order_id,
                                    razorpay_payment_id: resp.razorpay_payment_id,
                                    razorpay_signature: resp.razorpay_signature
                                })
                            })
                            .then(function (r) { return r.json(); })
                            .then(function (d) {
                                if (d.success) {
                                    msg.innerText = 'Payment verified. Booking confirmed!';
                                    setTimeout(function () { window.location.href = '/bdcmusic/booking-thank-you.php?booking_id=' + encodeURIComponent(d.booking_id); }, 1500);
                                } else {
                                    msg.innerText = 'Verification failed: ' + (d.error || 'Unknown error');
                                    btn.disabled = false;
                                }
                            })
                            .catch(function () {
                                msg.innerText = 'Error verifying payment.';
                                btn.disabled = false;
                            });
                        },
                        prefill: {
                            name: '<?= htmlspecialchars( $_SESSION["pending_booking"]["client_name"] ?? "" ) ?>',
                            email: '<?= htmlspecialchars( $_SESSION["pending_booking"]["client_email"] ?? "" ) ?>',
                            contact: '<?= htmlspecialchars( $_SESSION["pending_booking"]["client_phone"] ?? "" ) ?>'
                        },
                        theme: { color: '#f59e0b' }
                    };
                    var rzp = new Razorpay(options);
                    rzp.on('payment.failed', function () {
                        msg.innerText = 'Payment failed. Please try again.';
                        btn.disabled = false;
                    });
                    btn.addEventListener('click', function () {
                        msg.innerText = 'Opening Razorpay checkout...';
                        rzp.open();
                    });
                });
                </script>
            <?php endif; ?>

            <div class="booking-content">
                <form class="form-panel premium-booking-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="audio_video_booking_submit" value="1">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-field">
                                <label>Service Category <span class="required-star">*</span></label>
                                <div class="radio-group">
                                    <label class="radio-card"><input type="radio" name="service_category" value="Audio" checked> Audio Service</label>
                                    <label class="radio-card"><input type="radio" name="service_category" value="Video"> Video Service</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="service_type">Required Service <span class="required-star">*</span></label>
                                <select id="service_type" name="service_type" required>
                                    <option value="">Select Service</option>
                                    <option data-category="Audio">Recording</option>
                                    <option data-category="Audio">Music Production</option>
                                    <option data-category="Audio">Mixing</option>
                                    <option data-category="Audio">Mastering</option>
                                    <option data-category="Video">Video Production</option>
                                    <option data-category="Video">Video Editing</option>
                                    <option data-category="Video">Music Video</option>
                                    <option data-category="Video">Social Media Videos</option>
                                    <option data-category="Video">Motion Graphics</option>
                                    <option data-category="Video">YouTube Services</option>
                                    <option data-category="Video">Event Videos</option>
                                    <option data-category="Video">Corporate Videos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="project_upload">Upload Audio/Video</label>
                                <input id="project_upload" name="project_upload[]" type="file" accept=".wav,.mp3,.flac,.mp4,.mov" multiple>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-field">
                                <label>Delivery Format</label>
                                <div class="checkbox-group">
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="WAV"> WAV</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="MP3"> MP3</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="FLAC"> FLAC</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="MP4"> MP4</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="MOV"> MOV</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="4K"> 4K</label>
                                    <label class="checkbox-card"><input type="checkbox" name="delivery_format[]" value="Full HD"> Full HD</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="deadline">Deadline <span class="required-star">*</span></label>
                                <input id="deadline" name="deadline" type="text" placeholder="Example: 7 working days" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="budget">Budget <span class="required-star">*</span></label>
                                <input id="budget" name="budget" type="text" placeholder="Example: Rs.15,000" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="client_name">Full Name <span class="required-star">*</span></label>
                                <input id="client_name" name="client_name" type="text" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="client_email">Email <span class="required-star">*</span></label>
                                <input id="client_email" name="client_email" type="email" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-field">
                                <label for="client_phone">Contact Number</label>
                                <input id="client_phone" name="client_phone" type="tel">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-field">
                                <label for="notes">Additional Notes</label>
                                <textarea id="notes" name="notes"></textarea>
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
                        <button type="submit" class="btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
  
		<section class="faq" id="faq">
			<div class="container">
				<div class="section-heading reveal">
					<h2>Frequently Asked Questions</h2>
					<p>Find answers about our audio recording, music production, video production, editing, delivery, and booking process.</p>
				</div>

				<div class="faq-wrapper">
					<details class="faq-item reveal" open>
						<summary class="faq-question">What audio and video services do you provide?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>We provide professional audio recording, music production, mixing, mastering, video production, video editing, music videos, social media content, corporate videos, event videos, and custom media solutions.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">Can I book only one service instead of a complete package?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Yes. You can select any individual service based on your project requirements. We also create custom packages for combined audio and video projects.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">Which audio and video formats do you deliver?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Audio projects can be delivered in WAV, MP3, and FLAC formats. Video projects can be delivered in MP4, MOV, Full HD, and 4K formats depending on your requirement.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">How long does project delivery take?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Delivery timelines depend on project complexity, revisions, and workload. Standard projects are usually completed within the agreed timeline shared during booking confirmation.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">Can you handle commercial and enterprise projects?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Yes. We handle commercial productions, brand videos, corporate projects, multi-day shoots, and large-scale audio requirements with customized workflows and pricing.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">Can I upload my reference files or project materials?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Yes. You can upload your audio or video files while submitting the enquiry form. You can also add additional instructions in the notes section.</p>
						</div>
					</details>

					<details class="faq-item reveal">
						<summary class="faq-question">Do you provide revisions after delivery?<i class="fa-solid fa-plus"></i></summary>
						<div class="faq-answer">
							<p>Yes. Revision options depend on the selected package. Additional revisions or major changes can be discussed based on project requirements.</p>
						</div>
					</details>
				</div>
			</div>
		</section>

  </div>
</section>
<script src="<?php echo $assetPath; ?>js/audio-video-services.js"></script>
<?php include_once '../footer.php'; ?>

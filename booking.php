<?php
session_start();

function ensureStorageDirectory() {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir;
}

function loadJsonFile($path, $default = []) {
    if (!file_exists($path)) {
        return $default;
    }

    $contents = file_get_contents($path);
    if ($contents === false || trim($contents) === '') {
        return $default;
    }

    $decoded = json_decode($contents, true);
    return is_array($decoded) ? $decoded : $default;
}

function saveJsonFile($path, $data) {
    ensureStorageDirectory();
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function createCustomerAccount($name, $email, $mobile, $password) {
    $customersPath = ensureStorageDirectory() . '/customers.json';
    $customers = loadJsonFile($customersPath, []);

    $normalizedEmail = strtolower(trim($email));
    foreach ($customers as $customer) {
        if (($customer['email'] ?? '') === $normalizedEmail) {
            return ['exists' => true, 'customer' => $customer];
        }
    }

    $customer = [
        'id' => 'CUST-' . strtoupper(substr(md5($normalizedEmail . time()), 0, 8)),
        'name' => $name,
        'email' => $normalizedEmail,
        'mobile' => $mobile,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $customers[] = $customer;
    saveJsonFile($customersPath, $customers);

    return ['exists' => false, 'customer' => $customer];
}

function createBookingReference() {
    return 'BDC-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function saveBooking($booking) {
    $bookingsPath = ensureStorageDirectory() . '/bookings.json';
    $bookings = loadJsonFile($bookingsPath, []);
    $bookings[] = $booking;
    saveJsonFile($bookingsPath, $bookings);
    return $booking;
}

function sendBookingEmail($to, $subject, $message) {
    $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpPort = getenv('SMTP_PORT') ?: 587;
    $smtpUsername = getenv('SMTP_USERNAME') ?: '';
    $smtpPassword = getenv('SMTP_PASSWORD') ?: '';
    $fromEmail = getenv('SMTP_FROM') ?: 'bdcmusic37@gmail.com';
    $fromName = getenv('SMTP_FROM_NAME') ?: 'BDC Music';

    if (empty($smtpUsername) || empty($smtpPassword)) {
        return false;
    }

    $socket = @stream_socket_client('tcp://' . $smtpHost . ':' . $smtpPort, $errno, $errstr, 30, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return false;
    }

    $read = function () use ($socket) {
        $response = fgets($socket, 515);
        return $response ?: '';
    };

    $write = function ($command) use ($socket) {
        fwrite($socket, $command . "\r\n");
    };

    $expect = function ($expectedCode) use ($read) {
        $response = $read();
        return strpos($response, $expectedCode) === 0;
    };

    $write('EHLO localhost');
    if (!$expect('250')) {
        fclose($socket);
        return false;
    }

    $write('STARTTLS');
    if (!$expect('220')) {
        fclose($socket);
        return false;
    }

    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    $write('EHLO localhost');
    if (!$expect('250')) {
        fclose($socket);
        return false;
    }

    $write('AUTH LOGIN');
    if (!$expect('334')) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($smtpUsername));
    if (!$expect('334')) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($smtpPassword));
    if (!$expect('235')) {
        fclose($socket);
        return false;
    }

    $boundary = '----BDC' . md5(time());
    $body = "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 7bit\r\n\r\n"
        . $message . "\r\n"
        . "--{$boundary}--\r\n";

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary=' . $boundary;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'To: ' . $to;

    $write('MAIL FROM:<' . $fromEmail . '>');
    if (!$expect('250')) {
        fclose($socket);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect('250')) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    if (!$expect('354')) {
        fclose($socket);
        return false;
    }

    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
    $result = $expect('250');
    $write('QUIT');
    fclose($socket);

    return $result;
}

function notifyBooking($booking, $customer) {
    $ownerEmail = getenv('BOOKING_OWNER_EMAIL') ?: 'bdcmusic37@gmail.com';
    $customerSubject = 'Your BDC Music booking is confirmed';
    $ownerSubject = 'New BDC Music booking received';

    $customerMessage = '<h3>Thank you for booking with BDC Music</h3>'
        . '<p>Hello ' . htmlspecialchars($customer['name']) . ',</p>'
        . '<p>Your booking has been received. Below are your booking details:</p>'
        . '<ul>'
        . '<li><strong>Booking ID:</strong> ' . htmlspecialchars($booking['booking_id']) . '</li>'
        . '<li><strong>Invoice No:</strong> ' . htmlspecialchars($booking['invoice_no']) . '</li>'
        . '<li><strong>Service:</strong> ' . htmlspecialchars($booking['service']) . '</li>'
        . '<li><strong>Price:</strong> ₹' . number_format($booking['price']) . '</li>'
        . '<li><strong>Status:</strong> ' . htmlspecialchars($booking['status']) . '</li>'
        . '</ul>';

    $ownerMessage = '<h3>New booking request received</h3>'
        . '<p><strong>Customer:</strong> ' . htmlspecialchars($customer['name']) . '</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars($customer['email']) . '</p>'
        . '<p><strong>Service:</strong> ' . htmlspecialchars($booking['service']) . '</p>'
        . '<p><strong>Booking ID:</strong> ' . htmlspecialchars($booking['booking_id']) . '</p>'
        . '<p><strong>Invoice No:</strong> ' . htmlspecialchars($booking['invoice_no']) . '</p>'
        . '<p><strong>Price:</strong> ₹' . number_format($booking['price']) . '</p>';

    sendBookingEmail($customer['email'], $customerSubject, $customerMessage);
    sendBookingEmail($ownerEmail, $ownerSubject, $ownerMessage);
}

function sanitize($value) {
    return trim(strip_tags($value));
}

function serviceMeta($service) {
    $services = [
        'Artist Management Services' => [
            'label' => 'Artist Management Services',
            'price' => 5000,
            'subtitle' => 'Ideal for artist branding, strategy, and growth plans.'
        ],
        'Audio and Video Services' => [
            'label' => 'Audio and Video Services',
            'price' => 12000,
            'subtitle' => 'Perfect for music videos, reels, promos, and creative production.'
        ],
        'Online Classes' => [
            'label' => 'Online Classes',
            'price' => 3000,
            'subtitle' => 'For lessons, coaching, or structured training sessions.'
        ],
        'Recording Services' => [
            'label' => 'Recording Services',
            'price' => 7000,
            'subtitle' => 'For studio booking, vocal sessions, or full-track recording.'
        ],
    ];

    return $services[$service] ?? $services['Recording Services'];
}

function createRazorpayOrder($amount, $receipt, $currency = 'INR') {
    $keyId = getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_demo';
    $keySecret = getenv('RAZORPAY_KEY_SECRET') ?: 'demo_secret';

    if ($keyId === 'rzp_test_demo' || $keySecret === 'demo_secret' || !function_exists('curl_init')) {
        return [
            'id' => 'order_demo_' . time(),
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => $receipt,
            'status' => 'created',
            'note' => 'Replace the demo Razorpay credentials with your real key id and secret.'
        ];
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($keyId . ':' . $keySecret)
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => $receipt,
            'notes' => [
                'project' => 'BDC Music Booking'
            ]
        ])
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }

    return [
        'id' => 'order_demo_' . time(),
        'amount' => $amount,
        'currency' => $currency,
        'receipt' => $receipt,
        'status' => 'created',
        'note' => 'Razorpay order creation failed. Using demo mode.'
    ];
}

$errors = [];
$successMessage = '';
$submittedData = [];
$paymentOrder = null;
$service = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_submit'])) {
    $subject = sanitize($_POST['subject'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $service = sanitize($_POST['service'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    $serviceDetails = [
        'artist_goal' => sanitize($_POST['artist_goal'] ?? ''),
        'audio_video_type' => sanitize($_POST['audio_video_type'] ?? ''),
        'audio_video_format' => sanitize($_POST['audio_video_format'] ?? ''),
        'online_class_type' => sanitize($_POST['online_class_type'] ?? ''),
        'online_level' => sanitize($_POST['online_level'] ?? ''),
        'recording_package' => sanitize($_POST['recording_package'] ?? ''),
        'recording_session' => sanitize($_POST['recording_session'] ?? ''),
        'preferred_date' => sanitize($_POST['preferred_date'] ?? ''),
        'preferred_time' => sanitize($_POST['preferred_time'] ?? ''),
        'notes' => sanitize($_POST['service_notes'] ?? ''),
    ];

    $password = sanitize($_POST['customer_password'] ?? '');
    $confirmPassword = sanitize($_POST['customer_password_confirm'] ?? '');

    $requiredFields = [
        'subject' => $subject,
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'country' => $country,
        'state' => $state,
        'service' => $service,
        'password' => $password,
    ];

    foreach ($requiredFields as $field => $value) {
        if (empty($value)) {
            $errors[] = ucfirst($field) . ' is required.';
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Please create a password with at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if ($service === 'Audio and Video Services') {
        if (empty($serviceDetails['audio_video_type'])) {
            $errors[] = 'Please select the project type.';
        }
        if (empty($serviceDetails['audio_video_format'])) {
            $errors[] = 'Please select the delivery format.';
        }
    }

    if ($service === 'Online Classes') {
        if (empty($serviceDetails['online_class_type'])) {
            $errors[] = 'Please select the class type.';
        }
        if (empty($serviceDetails['online_level'])) {
            $errors[] = 'Please select the level.';
        }
    }

    if ($service === 'Recording Services') {
        if (empty($serviceDetails['recording_package'])) {
            $errors[] = 'Please select a recording package.';
        }
        if (empty($serviceDetails['recording_session'])) {
            $errors[] = 'Please select the session type.';
        }
    }

    if ($service === 'Artist Management Services' && empty($serviceDetails['artist_goal'])) {
        $errors[] = 'Please share your artist goal.';
    }

    if (empty($errors)) {
        $meta = serviceMeta($service);
        $amount = $meta['price'] * 100;
        $receipt = 'bdc-' . time();
        $paymentOrder = createRazorpayOrder($amount, $receipt);

        $customerResult = createCustomerAccount($name, $email, $mobile, $password);
        if ($customerResult['exists']) {
            $errors[] = 'An account with this email already exists. Please use another email address.';
        } else {
            $bookingId = createBookingReference();
            $invoiceNo = 'INV-' . strtoupper(substr($bookingId, -6));
            $booking = [
                'booking_id' => $bookingId,
                'invoice_no' => $invoiceNo,
                'subject' => $subject,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_mobile' => $mobile,
                'country' => $country,
                'state' => $state,
                'service' => $service,
                'service_details' => $serviceDetails,
                'message' => $message,
                'price' => $meta['price'],
                'status' => 'Pending Confirmation',
                'payment_status' => 'Awaiting Payment',
                'created_at' => date('Y-m-d H:i:s')
            ];

            saveBooking($booking);
            notifyBooking($booking, $customerResult['customer']);

            $_SESSION['booking_success'] = $booking;
            header('Location: booking-thank-you.php?booking_id=' . urlencode($bookingId));
            exit;
        }
    }
}

if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $successMessage = 'Payment completed successfully. We will contact you shortly.';
}

include_once 'header.php';
?>

<style>
    .booking-page {
        padding: 120px 0 80px;
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(245,245,245,0.9));
    }

    .booking-shell {
        max-width: 1100px;
        margin: 0 auto;
        background: #fff;
        border-radius: 30px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .booking-header {
        background: linear-gradient(135deg, var(--primary), #111);
        color: #fff;
        padding: 40px 40px 30px;
    }

    .booking-header h1 {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .booking-body {
        padding: 35px 40px 40px;
    }

    .stepper {
        display: flex;
        gap: 12px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .step {
        padding: 10px 15px;
        border-radius: 999px;
        background: #f2f2f2;
        color: #555;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .step.active {
        background: var(--primary);
        color: #fff;
    }

    .booking-form .step-panel {
        display: none;
    }

    .booking-form .step-panel.active {
        display: block;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        font-weight: 600;
        color: #222;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #d6d6d6;
        border-radius: 12px;
        padding: 12px 14px;
        font: inherit;
        background: #fff;
    }

    .field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .services-list {
        display: grid;
        gap: 10px;
    }

    .service-card {
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .service-card input {
        width: auto;
        margin-right: 8px;
    }

    .service-card strong {
        display: block;
        margin-bottom: 4px;
    }

    .extra-section {
        display: none;
        margin-top: 16px;
        border: 1px solid #ebebeb;
        background: #fafafa;
        padding: 18px;
        border-radius: 16px;
    }

    .extra-section.active {
        display: block;
    }

    .summary-box {
        background: #f8f8f8;
        border: 1px solid #ececec;
        padding: 18px;
        border-radius: 16px;
        margin-top: 18px;
    }

    .price-badge {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #fff3db;
        color: #8f4b00;
        font-weight: 700;
    }

    .btn-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
        flex-wrap: wrap;
    }

    .error-list {
        background: #fff4f4;
        border: 1px solid #f5c7c7;
        padding: 12px 14px;
        border-radius: 12px;
        color: #8b2f2f;
        margin-bottom: 20px;
    }

    .success-box {
        background: #f1fff4;
        border: 1px solid #bfe8c9;
        padding: 12px 14px;
        border-radius: 12px;
        color: #2d6a3e;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .booking-header,
        .booking-body {
            padding-left: 22px;
            padding-right: 22px;
        }
    }
</style>

<main class="booking-page">
    <div class="container">
        <div class="booking-shell">
            <div class="booking-header">
                <p class="eyebrow">BOOK A SESSION</p>
                <h1>Reserve your studio or production service</h1>
                <p>Complete the form in a few simple steps and pay securely with Razorpay.</p>
            </div>

            <div class="booking-body">
                <?php if (!empty($errors)) : ?>
                    <div class="error-list">
                        <strong>Please fix these issues:</strong>
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)) : ?>
                    <div class="success-box">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="stepper">
                    <div class="step active">1. Your Details</div>
                    <div class="step">2. Choose Service</div>
                    <div class="step">3. Review & Pay</div>
                </div>

                <form class="booking-form" method="post" action="booking.php">
                    <input type="hidden" name="booking_submit" value="1">

                    <div class="step-panel active" data-step="1">
                        <div class="form-grid">
                            <div class="field">
                                <label for="subject">Subject *</label>
                                <input id="subject" name="subject" type="text" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="name">Name *</label>
                                <input id="name" name="name" type="text" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="email">Email *</label>
                                <input id="email" name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="mobile">Mobile *</label>
                                <input id="mobile" name="mobile" type="text" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="country">Country *</label>
                                <input id="country" name="country" type="text" value="<?= htmlspecialchars($_POST['country'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="state">State *</label>
                                <input id="state" name="state" type="text" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label for="customer_password">Create Password *</label>
                                <input id="customer_password" name="customer_password" type="password" required>
                            </div>
                            <div class="field">
                                <label for="customer_password_confirm">Confirm Password *</label>
                                <input id="customer_password_confirm" name="customer_password_confirm" type="password" required>
                            </div>
                            <div class="field full">
                                <small>Your account will be created automatically so you can manage future bookings.</small>
                            </div>
                        </div>

                        <div class="btn-row">
                            <span></span>
                            <button class="btn" type="button" onclick="goToStep(2)">Next</button>
                        </div>
                    </div>

                    <div class="step-panel" data-step="2">
                        <div class="field full">
                            <label>Select Service *</label>
                            <div class="services-list">
                                <?php
                                $services = [
                                    'Artist Management Services',
                                    'Audio and Video Services',
                                    'Online Classes',
                                    'Recording Services'
                                ];
                                foreach ($services as $item) {
                                    $checked = (($service ?? '') === $item) ? 'checked' : '';
                                ?>
                                    <label class="service-card">
                                        <span>
                                            <input type="radio" name="service" value="<?= htmlspecialchars($item) ?>" <?= $checked ?>>
                                            <strong><?= htmlspecialchars($item) ?></strong>
                                            <small><?= htmlspecialchars(serviceMeta($item)['subtitle']) ?></small>
                                        </span>
                                        <strong>₹<?= number_format(serviceMeta($item)['price']) ?></strong>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="extra-section active" id="extra-artist-management">
                            <div class="field full">
                                <label for="artist_goal">What is your artist goal? *</label>
                                <textarea id="artist_goal" name="artist_goal"><?= htmlspecialchars($_POST['artist_goal'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="extra-section" id="extra-audio-video">
                            <div class="form-grid">
                                <div class="field">
                                    <label for="audio_video_type">Project Type *</label>
                                    <select id="audio_video_type" name="audio_video_type">
                                        <option value="">Select</option>
                                        <option value="Music Video" <?= (($_POST['audio_video_type'] ?? '') === 'Music Video') ? 'selected' : '' ?>>Music Video</option>
                                        <option value="Short Film" <?= (($_POST['audio_video_type'] ?? '') === 'Short Film') ? 'selected' : '' ?>>Short Film</option>
                                        <option value="Reel / Promo" <?= (($_POST['audio_video_type'] ?? '') === 'Reel / Promo') ? 'selected' : '' ?>>Reel / Promo</option>
                                        <option value="Podcast / Interview" <?= (($_POST['audio_video_type'] ?? '') === 'Podcast / Interview') ? 'selected' : '' ?>>Podcast / Interview</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="audio_video_format">Delivery Format *</label>
                                    <select id="audio_video_format" name="audio_video_format">
                                        <option value="">Select</option>
                                        <option value="4K / HD" <?= (($_POST['audio_video_format'] ?? '') === '4K / HD') ? 'selected' : '' ?>>4K / HD</option>
                                        <option value="Social Media Reels" <?= (($_POST['audio_video_format'] ?? '') === 'Social Media Reels') ? 'selected' : '' ?>>Social Media Reels</option>
                                        <option value="Broadcast Master" <?= (($_POST['audio_video_format'] ?? '') === 'Broadcast Master') ? 'selected' : '' ?>>Broadcast Master</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="extra-section" id="extra-online-classes">
                            <div class="form-grid">
                                <div class="field">
                                    <label for="online_class_type">Class Type *</label>
                                    <select id="online_class_type" name="online_class_type">
                                        <option value="">Select</option>
                                        <option value="Vocal" <?= (($_POST['online_class_type'] ?? '') === 'Vocal') ? 'selected' : '' ?>>Vocal</option>
                                        <option value="Music Production" <?= (($_POST['online_class_type'] ?? '') === 'Music Production') ? 'selected' : '' ?>>Music Production</option>
                                        <option value="Mixing & Mastering" <?= (($_POST['online_class_type'] ?? '') === 'Mixing & Mastering') ? 'selected' : '' ?>>Mixing & Mastering</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="online_level">Level *</label>
                                    <select id="online_level" name="online_level">
                                        <option value="">Select</option>
                                        <option value="Beginner" <?= (($_POST['online_level'] ?? '') === 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                                        <option value="Intermediate" <?= (($_POST['online_level'] ?? '') === 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                                        <option value="Advanced" <?= (($_POST['online_level'] ?? '') === 'Advanced') ? 'selected' : '' ?>>Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="extra-section" id="extra-recording">
                            <div class="form-grid">
                                <div class="field">
                                    <label for="recording_package">Studio Package *</label>
                                    <select id="recording_package" name="recording_package">
                                        <option value="">Select</option>
                                        <option value="Basic Session" <?= (($_POST['recording_package'] ?? '') === 'Basic Session') ? 'selected' : '' ?>>Basic Session</option>
                                        <option value="Full Day" <?= (($_POST['recording_package'] ?? '') === 'Full Day') ? 'selected' : '' ?>>Full Day</option>
                                        <option value="Mix & Master" <?= (($_POST['recording_package'] ?? '') === 'Mix & Master') ? 'selected' : '' ?>>Mix & Master</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="recording_session">Session Type *</label>
                                    <select id="recording_session" name="recording_session">
                                        <option value="">Select</option>
                                        <option value="Vocal" <?= (($_POST['recording_session'] ?? '') === 'Vocal') ? 'selected' : '' ?>>Vocal</option>
                                        <option value="Instrumental" <?= (($_POST['recording_session'] ?? '') === 'Instrumental') ? 'selected' : '' ?>>Instrumental</option>
                                        <option value="Live Band" <?= (($_POST['recording_session'] ?? '') === 'Live Band') ? 'selected' : '' ?>>Live Band</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field full">
                            <span id="selected-price" class="price-badge">Selected service price: ₹<?= number_format(serviceMeta($service ?: 'Recording Services')['price']) ?></span>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="preferred_date">Preferred Date</label>
                                <input id="preferred_date" name="preferred_date" type="date" value="<?= htmlspecialchars($_POST['preferred_date'] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label for="preferred_time">Preferred Time</label>
                                <input id="preferred_time" name="preferred_time" type="time" value="<?= htmlspecialchars($_POST['preferred_time'] ?? '') ?>">
                            </div>
                            <div class="field full">
                                <label for="service_notes">Additional Notes</label>
                                <textarea id="service_notes" name="service_notes"><?= htmlspecialchars($_POST['service_notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="btn-row">
                            <button class="btn btn-outline" type="button" onclick="goToStep(1)">Back</button>
                            <button class="btn" type="button" onclick="goToStep(3)">Next</button>
                        </div>
                    </div>

                    <div class="step-panel" data-step="3">
                        <div class="field full">
                            <label for="message">Your message</label>
                            <textarea id="message" name="message"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <?php if (!empty($submittedData)) : ?>
                            <div class="summary-box">
                                <h3>Booking Summary</h3>
                                <p><strong>Name:</strong> <?= htmlspecialchars($submittedData['name']) ?></p>
                                <p><strong>Service:</strong> <?= htmlspecialchars($submittedData['service']) ?></p>
                                <p><strong>Estimated Price:</strong> ₹<?= number_format($submittedData['price']) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="btn-row">
                            <button class="btn btn-outline" type="button" onclick="goToStep(2)">Back</button>
                            <button class="btn" type="submit">Submit & Pay</button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($paymentOrder)) : ?>
                    <div class="summary-box">
                        <h3>Pay Now</h3>
                        <p>Click the button below to complete your booking securely with Razorpay.</p>
                        <button class="btn" type="button" id="rzp-button">Pay ₹<?= number_format($submittedData['price']) ?></button>
                        <p id="payment-message" style="margin-top:12px;color:#555;"></p>
                    </div>

                    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const button = document.getElementById('rzp-button');
                            const message = document.getElementById('payment-message');
                            if (!button) return;

                            const options = {
                                key: 'rzp_test_demo',
                                amount: '<?= intval($paymentOrder['amount']) ?>',
                                currency: '<?= htmlspecialchars($paymentOrder['currency']) ?>',
                                name: 'BDC Music',
                                description: '<?= htmlspecialchars($submittedData['service']) ?> Booking',
                                order_id: '<?= htmlspecialchars($paymentOrder['id']) ?>',
                                handler: function (response) {
                                    message.innerText = 'Payment received. Your request is confirmed.';
                                    window.location.href = 'booking.php?payment=success&order_id=' + encodeURIComponent(response.razorpay_order_id);
                                },
                                prefill: {
                                    name: '<?= htmlspecialchars($submittedData['name']) ?>',
                                    email: '<?= htmlspecialchars($submittedData['email']) ?>',
                                    contact: '<?= htmlspecialchars($submittedData['mobile']) ?>'
                                },
                                theme: { color: '#f59e0b' }
                            };

                            const rzp = new Razorpay(options);
                            button.addEventListener('click', function () {
                                message.innerText = 'Opening Razorpay checkout...';
                                rzp.open();
                            });
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    function goToStep(stepNumber) {
        const panels = document.querySelectorAll('.booking-form .step-panel');
        const steps = document.querySelectorAll('.stepper .step');

        panels.forEach(panel => {
            panel.classList.toggle('active', Number(panel.getAttribute('data-step')) === stepNumber);
        });

        steps.forEach((step, index) => {
            step.classList.toggle('active', index + 1 === stepNumber);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const serviceInputs = document.querySelectorAll('input[name="service"]');
        const priceBadge = document.getElementById('selected-price');
        const priceMap = {
            'Artist Management Services': 5000,
            'Audio and Video Services': 12000,
            'Online Classes': 3000,
            'Recording Services': 7000
        };
        const extraSections = {
            'Artist Management Services': document.getElementById('extra-artist-management'),
            'Audio and Video Services': document.getElementById('extra-audio-video'),
            'Online Classes': document.getElementById('extra-online-classes'),
            'Recording Services': document.getElementById('extra-recording')
        };

        function toggleExtraSections() {
            const selected = document.querySelector('input[name="service"]:checked')?.value;
            Object.entries(extraSections).forEach(([serviceName, section]) => {
                if (section) {
                    section.classList.toggle('active', selected === serviceName);
                }
            });

            if (priceBadge) {
                const selectedPrice = priceMap[selected] ?? 7000;
                priceBadge.textContent = 'Selected service price: ₹' + selectedPrice.toLocaleString();
            }
        }

        serviceInputs.forEach(input => input.addEventListener('change', toggleExtraSections));
        toggleExtraSections();
    });
</script>

<?php include_once 'footer.php'; ?>

<?php
$pageTitle = 'Online & Offline Music Classes | BDC Music Studio';
$metaDescription = 'Join BDC Music Studio online and offline classes for singing, music production, instruments, and video editing with clear packages and enquiry support.';
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;

include_once '../header.php';

$courses = [
    'Singing'         => [
        'Basic'      => 'Rs.2,999',
        'Standard'   => 'Rs.8,999',
        'Premium'    => 'Rs.17,999',
        'Enterprise' => 'Rs.32,999',
    ],
    'Music Production' => [
        'Basic'      => 'Rs.3,999',
        'Standard'   => 'Rs.7,999',
        'Premium'    => 'Rs.14,999',
        'Enterprise' => 'Rs.29,999',
    ],
    'Instrument'      => [
        'Basic'      => 'Rs.2,999',
        'Standard'   => 'Rs.5,999',
        'Premium'    => 'Rs.9,999',
        'Enterprise' => 'Rs.19,999',
    ],
    'Video Editing'   => [
        'Basic'      => 'Rs.3,999',
        'Standard'   => 'Rs.7,999',
        'Premium'    => 'Rs.14,999',
        'Enterprise' => 'Rs.29,999',
    ],
];

$planDetails = [

    'Singing' => [

        'Basic' => [
            'Duration: 1 Month',
            'Classes: 8',
            'Mode: Online / Offline',
            'Vocal warm-up',
            'Breathing techniques',
            'Basic voice training',
            'Alankars',
            'Pitch and rhythm',
            'Beginner singing exercises',
            'Practice material',
            'Certificate',
            'WhatsApp support',
        ],

        'Standard' => [
            'Duration: 3 Months',
            'Classes: 24',
            'Voice training',
            'Bollywood singing',
            'Classical basics',
            'Song practice',
            'Performance techniques',
            'Priority WhatsApp support',
        ],

        'Premium' => [
            'Duration: 6 Months',
            'Classes: 48',
            'Professional vocal training',
            'Advanced techniques',
            'Semi classical',
            'Stage performance',
            'Studio recording',
            'Artist grooming',
            'Personalized practice',
            'Premium support',
        ],

        'Enterprise' => [
            'Duration: 12 Months',
            '96+ classes',
            'Complete professional training',
            'Recording sessions',
            'Live performance',
            'Artist grooming',
            'Portfolio building',
            'Career guidance',
            'Dedicated mentor',
        ],
    ],

    'Music Production' => [

        'Basic' => [
            'DAW introduction',
            'Beat making',
            'MIDI basics',
            'Mixing',
            'Practice projects',
        ],

        'Standard' => [
            'Advanced beat making',
            'Melody',
            'Chords',
            'Drum programming',
            'Recording',
            'Mastering basics',
        ],

        'Premium' => [
            'Advanced mixing',
            'Mastering',
            'Sound design',
            'Vocal processing',
            'Music arrangement',
            'Portfolio projects',
        ],

        'Enterprise' => [
            'Industry-level production',
            'Film music',
            'OTT music',
            'Dolby Atmos basics',
            'Music release strategy',
            'Client projects',
            'Career guidance',
            'Dedicated mentor',
        ],
    ],

    'Instrument Courses' => [

        'Basic' => [
            'Posture and hand positioning',
            'Scale practice',
            'Rhythm basics',
            'Beginner songs',
            'Practice routine',
        ],

        'Standard' => [
            'Chords and progressions',
            'Finger exercises',
            'Notation basics',
            'Song practice',
            'Performance confidence',
        ],

        'Premium' => [
            'Advanced techniques',
            'Improvisation',
            'Genre-based practice',
            'Recording readiness',
            'Personalized feedback',
        ],

        'Enterprise' => [
            'Professional repertoire',
            'Stage performance',
            'Studio preparation',
            'Portfolio building',
            'Dedicated mentor',
        ],
    ],

    'Video Editing' => [

        'Basic' => [
            'Software introduction',
            'Timeline editing',
            'Cuts and transitions',
            'Audio sync',
            'Export settings',
        ],

        'Standard' => [
            'Story flow',
            'Color correction',
            'Text and titles',
            'Reels and shorts editing',
            'Project workflow',
        ],

        'Premium' => [
            'Advanced color grading',
            'Motion graphics basics',
            'Music video editing',
            'Sound design',
            'Portfolio projects',
        ],

        'Enterprise' => [
            'Commercial editing workflow',
            'Multi-camera editing',
            'Brand video packaging',
            'Client projects',
            'Career guidance',
        ],
    ],
];

$instruments = [
    'Guitar',
    'Keyboard',
    'Piano',
    'Violin',
    'Drums',
    'Tabla',
    'Dholak',
    'Flute',
    'Bass Guitar',
    'Electric Guitar',
    'Ukulele',
    'Harmonium',
    'Saxophone',
];
?>

<section class="page-hero">

    <div class="container">

        <!-- BREADCRUMB -->
        <div class="breadcrumb">

            <a href="<?php echo $siteUrl; ?>">
                Home
            </a>

            <span>/</span>

            <a href="<?php echo $siteUrl; ?>services">
                Services
            </a>

            <span>/</span>

            <span>
                Online & Offline Classes
            </span>

        </div>

        <!-- HERO -->
        <div class="section-hero online-offline-bg">

            <div class="section-hero-content">

                <span class="heading-tag">
                    ONLINE & OFFLINE CLASSES
                </span>

                <h1>
                    Learn Music,
                    Production &
                    <span class="highlight">Creative Skills</span>
                </h1>

                <p>
                    Learn from experienced trainers with flexible online
                    and offline classes for singing, instruments,
                    music production and video editing.
                </p>

                <div class="hero-actions">

                    <a
                        href="#classes-enquiry"
                        class="btn"
                    >
                        Start Your Learning Journey
                    </a>

                </div>

            </div>

        </div>

        <!-- PROGRAM SELECTION -->
        <div class="section-block">

            <h2>
                Choose Your Learning Program
            </h2>

            <p class="section-intro">
                Whether you're starting from scratch or looking to build
                professional-level skills, choose a program designed
                around your goals.
            </p>

            <div class="instrument-cloud">

                <a
                    href="#singing"
                >
                    Singing
                </a>

                <a
                    href="#music-production"
                >
                    Music Production
                </a>

                <a
                    href="#instrument-courses"
                >
                    Instrument Training
                </a>

                <a
                    href="#video-editing"
                >
                    Video Editing
                </a>

            </div>

        </div>

        <!-- PACKAGE COMPARISON -->
        <div class="section-block pt-0">

            <h2>
                Choose Your Package
            </h2>

            <p class="section-intro">
                Compare our professionally designed learning programs
                and choose the package that best matches your goals,
                experience and learning level.
            </p>

            <div class="comparison-wrap">

                <?php foreach ( $planDetails as $courseName => $plans ) : ?>

                    <?php

                    $priceKey = (
                        $courseName === 'Instrument Courses'
                    )
                        ? 'Instrument'
                        : $courseName;

                    $sectionId = strtolower(
                        str_replace(
                            ' ',
                            '-',
                            $courseName
                        )
                    );

                    ?>

                    <!-- COURSE CATEGORY -->
                    <div
                        class="category-block"
                        id="<?php echo htmlspecialchars( $sectionId ); ?>"
                    >

                        <h3>
                            <?php echo htmlspecialchars( $courseName ); ?>
                        </h3>

                        <?php if ( $courseName === 'Singing' ) : ?>

                            <p>
                                Learn professional vocal techniques,
                                breathing, pitch control, rhythm and
                                performance skills through structured
                                online and offline classes.
                            </p>

                        <?php elseif ( $courseName === 'Music Production' ) : ?>

                            <p>
                                Master beat making, recording, mixing,
                                mastering and professional music production
                                workflows using industry-standard software.
                            </p>

                        <?php elseif ( $courseName === 'Instrument Courses' ) : ?>

                            <p>
                                Learn your favourite instrument through
                                practical lessons designed for beginners
                                and advanced musicians.
                            </p>

                            <div class="instruments">

                                <?php foreach ( $instruments as $instrument ) : ?>

                                    <span>
                                        <?php echo htmlspecialchars( $instrument ); ?>
                                    </span>

                                <?php endforeach; ?>

                            </div>

                        <?php elseif ( $courseName === 'Video Editing' ) : ?>

                            <p>
                                Build professional editing skills including
                                storytelling, colour grading, motion graphics,
                                social media content and commercial workflows.
                            </p>

                        <?php endif; ?>

                        <!-- PLANS -->
                        <div class="details-grid">

                            <?php foreach ( $plans as $planName => $features ) : ?>

                                <?php

                                $isRecommended =
                                    ( $planName === 'Standard' );

                                ?>

                                <div
                                    class="detail-card"
                                    <?php
                                    if ( $isRecommended ) {
                                        echo 'data-recommended="true"';
                                    }
                                    ?>
                                >

                                    <?php if ( $isRecommended ) : ?>

                                        <span class="most-popular">
                                            MOST POPULAR
                                        </span>

                                    <?php endif; ?>

                                    <h3>
                                        <?php echo htmlspecialchars( $planName ); ?>
                                    </h3>

                                    <span class="price">
                                        <?php
                                        echo htmlspecialchars(
                                            $courses[ $priceKey ][ $planName ]
                                        );
                                        ?>
                                    </span>

                                    <ul class="feature-list">

                                        <?php foreach ( $features as $feature ) : ?>

                                            <li>
                                                <?php
                                                echo htmlspecialchars( $feature );
                                                ?>
                                            </li>

                                        <?php endforeach; ?>

                                    </ul>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <!-- ASSISTANCE CTA -->
        <div class="section-block pt-0">

            <h2>
                Not Sure Which Package to Choose?
            </h2>

            <p class="section-intro">
                Tell us about your goals and preferred class mode.
                Our team can help you choose the right program
                and learning plan.
            </p>

            <div class="hero-actions text-center">

                <a
                    href="#classes-enquiry"
                    class="btn"
                >
                    Talk to Our Team
                </a>

            </div>

        </div>

        <!-- CLASS ENQUIRY -->
        <div
            class="section-block pt-0"
            id="classes-enquiry"
        >

            <h2>
                Ready to Start Learning?
            </h2>

            <p class="section-intro">
                Fill in your details and tell us your preferred class mode.
                Our team will help you get started.
            </p>

            <form
                class="form-panel classes-enquiry-form"
                method="post"
                action="<?php echo $siteUrl; ?>booking"
            >

                <input
                    type="hidden"
                    name="booking_submit"
                    value="1"
                >

                <input
                    type="hidden"
                    name="course_mode"
                    value="Online Classes"
                    data-course-mode
                >

                <!-- CLASS MODE -->
                <div class="row">

                    <div class="col-12">

                        <div class="form-field">

                            <label>
                                Class Mode <span class="required-star">*</span>
                            </label>

                            <div class="radio-group">

                                <label class="radio-card">

                                    <input
                                        type="radio"
                                        name="class_mode_selection"
                                        value="Online Classes"
                                        checked
                                    >

                                    Online Classes

                                </label>

                                <label class="radio-card">

                                    <input
                                        type="radio"
                                        name="class_mode_selection"
                                        value="Offline Classes"
                                    >

                                    Offline Classes

                                </label>

                            </div>

                        </div>

                    </div>

                    <!-- COURSE -->
                    <div class="col-12" data-course-col>

                        <div class="form-field">

                            <label for="selected_course">
                                Choose Course <span class="required-star">*</span>
                            </label>

                            <select
                                id="selected_course"
                                name="selected_course"
                                required
                                data-selected-course
                            >

                                <option value="" selected disabled>
                                    Select a course
                                </option>

                                <?php foreach ( $courses as $courseName => $coursePlans ) : ?>

                                    <option
                                        value="<?php echo htmlspecialchars( $courseName ); ?>"
                                    >
                                        <?php echo htmlspecialchars( $courseName ); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <!-- PACKAGE -->
                    <div class="col-6 d-none" data-package-col>

                        <div class="form-field">

                            <label for="selected_plan">
                                Choose Package <span class="required-star">*</span>
                            </label>

                            <select
                                id="selected_plan"
                                name="selected_plan"
                                required
                                data-selected-plan
                                disabled
                            >

                                <option value="" selected disabled>
                                    Select a package
                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- NAME -->
                    <div class="col-6">

                        <div class="form-field">

                            <label for="name">
                                Full Name <span class="required-star">*</span>
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>

                    <!-- EMAIL -->
                    <div class="col-6">

                        <div class="form-field">

                            <label for="email">
                                Email <span class="required-star">*</span>
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>

                    <!-- MOBILE -->
                    <div class="col-6">

                        <div class="form-field">

                            <label for="mobile">
                                Contact Number <span class="required-star">*</span>
                            </label>

                            <input
                                id="mobile"
                                name="mobile"
                                type="tel"
                                autocomplete="tel"
                                required
                            >

                        </div>

                    </div>

                    <!-- AGE -->
                    <div class="col-6">

                        <div class="form-field">

                            <label for="age">
                                Age <span class="required-star">*</span>
                            </label>

                            <input
                                id="age"
                                name="age"
                                type="number"
                                min="4"
                                required
                            >

                        </div>

                    </div>

                    <!-- PRIVACY -->
                    <div class="col-12">
						<label class="policy-check">
							<input type="checkbox" name="terms" required>
							<span>I have read the <a href="<?php echo $siteUrl; ?>privacy-policy">privacy policy</a> and <a href="<?php echo $siteUrl; ?>terms-and-conditions">terms and conditions</a>.</span>
						</label>
					</div>

                </div>

                <!-- SUBMIT -->
                <div class="btn-row">

                    <button
                        class="btn"
                        type="submit"
                    >
                        Get Started
                    </button>

                </div>

            </form>

        </div>

        <!-- FAQ -->
        <div class="faq" id="faq">

            <div class="section-block reveal">

                <h2>
                    Frequently Asked Questions
                </h2>

                <p class="section-intro">
                    Common questions about online and offline classes,
                    courses, packages and learning options.
                </p>

            </div>

            <div class="faq-wrapper">

                <details class="faq-item reveal" open>

                    <summary class="faq-question">

                        Can I join classes online or offline?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            Yes. BDC Music Studio offers both online and
                            offline classes. You can choose the mode that
                            best suits your location and learning preference.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        Do I need prior experience to join?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            No. Beginners are welcome. Our basic programs
                            are designed to help students build strong
                            foundations with guided lessons and practice.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        Which courses are available?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            We offer courses in singing, music production,
                            instruments and video editing. Instrument
                            training includes guitar, keyboard, piano,
                            violin, drums, tabla, flute and other instruments.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        How do I choose the right package?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            Choose a package based on your experience,
                            learning goals and desired training level.
                            If you're unsure, submit an enquiry and our
                            team can help you select the right program.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        Can I switch between online and offline classes?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            Yes. You can discuss your preferred class mode
                            with our team. Changes are subject to trainer
                            and batch availability.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        Do the courses include certificates?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            Certificate support is available for the
                            learning packages where it is specifically
                            included in the package details.
                        </p>

                    </div>

                </details>

                <details class="faq-item reveal">

                    <summary class="faq-question">

                        How can I enquire about a course?

                        <i class="fa-solid fa-plus"></i>

                    </summary>

                    <div class="faq-answer">

                        <p>
                            Select your preferred class mode, fill in the
                            class enquiry form and submit your details.
                            Our team will contact you regarding the next steps.
                        </p>

                    </div>

                </details>

            </div>

        </div>

    </div>

</section>

<script src="<?php echo $assetPath; ?>js/online-offline-classes-form.js"></script>
<?php include_once '../footer.php'; ?>

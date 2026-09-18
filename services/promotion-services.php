<?php
$pageTitle = ' Promotion Services | BDC Music Studio';
$metaDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';
$ogTitle = ' Promotion Services | BDC Music Studio';
$ogDescription = 'Promote your music videos, reels, and brand content with targeted video promotion strategies for better reach and engagement.';
include_once '../header.php';
?>

<section class="page-hero">
    <div class="container">

        <div class="breadcrumb">

            <a href="<?php echo $siteUrl; ?>">Home</a>

            <span>/</span>

            <a href="<?php echo $siteUrl; ?>all-services">Services</a>

            <span>/</span>

            <span>Promotion Services</span>

        </div>

        <div class="section-hero promotional-bg">

            <div class="section-hero-content">

                <span class="heading-tag">
                    MARKETING &amp; PROMOTION
                </span>

                <h1>
                    Audio &amp; Video Promotion Services to Grow Reach and Engagement
                </h1>

                <p>
                    Drive visibility for your reels, short films,
                    music videos, and promotional content with a strategy
                    built for discovery and audience growth.
                </p>

                <a
                    href="#promotion-enquiry"
                    class="btn"
                >
                    Book Promotion Support
                </a>

            </div>

        </div>

        <section class="section-block">

            <h2>
                About the Service
            </h2>

            <p class="section-intro">
                Our promo services help artists and brands gain traction by positioning video content in front of the right audience. Whether you are launching a new track or building a social presence, we make sure your content is seen and remembered.
            </p>

        </section>

        <div class="section-block pt-0">

            <h2>
                Benefits
            </h2>

            <div class="card-grid-2">

                <!-- Benefit 1 -->

                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-eye"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>
                            Better Visibility
                        </h3>

                        <p>
                            Increase reach on YouTube, Instagram Reels, and other short-form platforms.
                        </p>

                    </div>

                </div>

                <!-- Benefit 2 -->

                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>
                            Audience Growth
                        </h3>

                        <p>
                            Reach viewers with stronger targeting and better content positioning.
                        </p>

                    </div>

                </div>

                <!-- Benefit 3 -->

                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>
                            Targeted Promotion
                        </h3>

                        <p>
                            Connect your music and video content with audiences who are more likely to engage with your work.
                        </p>

                    </div>

                </div>

                <!-- Benefit 4 -->

                <div class="card-inline">

                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <div class="benefit-content">

                        <h3>
                            Stronger Engagement
                        </h3>

                        <p>
                            Build greater attention and engagement around your music releases, videos, reels, and promotional content.
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- PROMOTION BOOKING FORM -->

        <section class="section-block pt-0">

            <div
                class="booking-section"
                id="promotion-enquiry"
            >

                <h2>
                    Book Promotion Support
                </h2>

                <p class="section-intro">
                    Share your project details and promotion requirements.
                    Our team will contact you with the best strategy.
                </p>

                <div class="booking-content">

                    <form
                        class="form-panel premium-booking-form"
                        method="post"
                    >

                        <input
                            type="hidden"
                            name="promotion_booking_submit"
                            value="1"
                        >

                        <div class="row">

                            <!-- Content Type -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Content Type <span class="required-star">*</span>
                                    </label>

                                    <select
                                        name="content_type"
                                        required
                                    >

                                        <option value="">
                                            Select Content
                                        </option>

                                        <option>
                                            Music Video
                                        </option>

                                        <option>
                                            Instagram Reels
                                        </option>

                                        <option>
                                            Short Film
                                        </option>

                                        <option>
                                            Brand Video
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- Promotion Goal -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Promotion Goal <span class="required-star">*</span>
                                    </label>

                                    <select
                                        name="promotion_goal"
                                        required
                                    >

                                        <option value="">
                                            Select Goal
                                        </option>

                                        <option>
                                            More Views
                                        </option>

                                        <option>
                                            Audience Growth
                                        </option>

                                        <option>
                                            Brand Awareness
                                        </option>

                                        <option>
                                            Release Promotion
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- Name -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Name <span class="required-star">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="client_name"
                                        placeholder="Your Name"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- Email -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Email <span class="required-star">*</span>
                                    </label>

                                    <input
                                        type="email"
                                        name="client_email"
                                        placeholder="Email Address"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- Phone -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Phone
                                    </label>

                                    <input
                                        type="tel"
                                        name="client_phone"
                                        placeholder="Contact Number"
                                    >

                                </div>

                            </div>

                            <!-- Budget -->

                            <div class="col-6">

                                <div class="form-field">

                                    <label>
                                        Budget
                                    </label>

                                    <input
                                        type="text"
                                        name="budget"
                                        placeholder="Example: ₹10,000"
                                    >

                                </div>

                            </div>

                            <!-- Project Link -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Project Link
                                    </label>

                                    <input
                                        type="url"
                                        name="project_link"
                                        placeholder="YouTube / Instagram Link"
                                    >

                                </div>

                            </div>

                            <!-- Additional Details -->

                            <div class="col-12">

                                <div class="form-field">

                                    <label>
                                        Additional Details
                                    </label>

                                    <textarea
                                        name="notes"
                                        placeholder="Tell us about your project"
                                    ></textarea>

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

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="btn"
                            >
                                Submit Request
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </section>

        <!-- FAQ SECTION -->

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

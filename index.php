<?php include_once 'header.php'; ?>
    <main>
        <section class="hero hero-bg" id="home">
            <div class="hero-ambient ambient-b"></div>
            <div class="container hero-grid">
                <div class="hero-content reveal">
                    <span class="heading-tag">All-in-one music platform</span>
                    <h1>From Your First Track to Global Recognition</h1>
                    <p>Build your music career with BDC—music distribution, promotion, classes, audio & video services, artist marketplace and IPRS services, all under one roof.</p>
                    <div class="hero-buttons">
                        <a href="mailto:info@bdcmusic.in" class="btn">Get In Touch</a>
                        <a href="<?php echo $basePath; ?>all-services" class="btn btn-outline">Explore Services</a>
                    </div>
                    <div class="hero-trust" aria-label="BDC Music trust indicators">
                        <div class="hero-trust-item">
                            <span class="trust-rating">★ 4.9/5</span>
                            <span>Trusted by independent creators</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="swiper hero-card-slider">
                        <div class="swiper-wrapper">

                            <div class="swiper-slide">
                                <div class="hero-card">
                                    <img src="<?php echo $assetPath; ?>images/hero-1.png" alt="BDC Music Studio">
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="hero-card">
                                    <img src="<?php echo $assetPath; ?>images/hero-2.png" alt="Music Production">
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="hero-card">
                                    <img src="<?php echo $assetPath; ?>images/hero-3.png" alt="Recording Studio">
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="hero-card">
                                    <img src="<?php echo $assetPath; ?>images/hero-4.png" alt="Recording Studio">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="trust-bar" aria-label="BDC Music highlights">
            <div class="container trust-grid">
                <div class="trust-item reveal">
                    <h6>500+ Artists</h6>
                    <p>Supported</p>
                </div>
                <div class="trust-item reveal">
                    <h6>1000+ Releases</h6>
                    <p>Managed</p>
                </div>
                <div class="trust-item reveal">
                    <h6>Global Distribution</h6>
                    <p>Across major platforms</p>
                </div>
                <div class="trust-item reveal">
                    <h6>Professional Production</h6>
                    <p>From recording to mastering</p>
                </div>
            </div>
        </section>

        <section class="brand-positioning section-surface py-9" id="about">
            <div class="container brand-grid">
                <div class="brand-media reveal">
                    <div class="brand-image-stack">
                        <img src="<?php echo $assetPath; ?>images/platform-service.png" alt="BDC Music service platform" loading="lazy">
                        <div class="brand-card glass-card">
                            <span class="heading-tag">Creative ecosystem</span>
                            <h3>One platform for every stage of your music career</h3>
                        </div>
                    </div>
                </div>
                <div class="brand-content reveal">
                    <span class="heading-tag">Why BDC Music</span>
                    <h2>Everything Artists Need To Build A Music Career</h2>
                    <p>BDC Music brings together multiple independent services so artists can pick the support they need, whether that means management, production, distribution, branding, education or community access.</p>
                    <div class="brand-points">
                        <div class="brand-point">
                            <i class="fa-solid fa-layer-group"></i>
                            <div>
                                <h3>Flexible creative support</h3>
                                <p>Choose services that match your goals without being locked into one path.</p>
                            </div>
                        </div>
                        <div class="brand-point">
                            <i class="fa-solid fa-bolt"></i>
                            <div>
                                <h3>Built for modern artists</h3>
                                <p>Premium strategy, polished production and audience growth under one roof.</p>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo $basePath; ?>all-services" class="btn">See All Services</a>
                </div>
            </div>
        </section>

        <section class="ecosystem section-dark py-9" id="services">
			<div class="container">
				<div class="section-heading reveal">
					<span class="heading-tag">Services ecosystem</span>
					<h2>Everything you need to create, release and grow your music</h2>
					<p>From artist discovery and production to education, distribution and rights management, BDC Music provides the services artists need to build sustainable careers.</p>
				</div>

				<div class="ecosystem-grid">

					<!-- BDC Artists Marketplace -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<img src="<?php echo $assetPath; ?>images/singer.svg" class="red-svg" alt="BDC Artists Marketplace">
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/bdc-artists-marketplace/">
								BDC Artists Marketplace
							</a>
						</h3>
						<p>Discover, showcase and connect with talented artists, musicians and creative professionals.</p>
					</article>

					<!-- Audio & Video Services -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<svg viewBox="0 0 24 24">
								<rect x="3" y="10" width="2" height="4"/>
								<rect x="7" y="6" width="2" height="12"/>
								<rect x="11" y="3" width="2" height="18"/>
								<rect x="15" y="7" width="2" height="10"/>
								<rect x="19" y="9" width="2" height="6"/>
							</svg>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/audio-video-services/">
								Audio &amp; Video Services
							</a>
						</h3>
						<p>Professional recording, mixing, mastering, music production and video services for your next release.</p>
					</article>

					<!-- Online/Offline Classes -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<svg viewBox="0 0 24 24">
								<path d="M2 8l10-5 10 5-10 5z"/>
								<path d="M6 10v5c0 2 3 4 6 4s6-2 6-4v-5"/>
							</svg>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/online-offline-classes/">
								Online/Offline Classes
							</a>
						</h3>
						<p>Learn vocals, instruments, music production and other creative skills through flexible learning options.</p>
					</article>

					<!-- Digital Music Distribution -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<svg viewBox="0 0 24 24">
								<circle cx="12" cy="12" r="9"/>
								<path d="M3 12h18"/>
								<path d="M12 3a14 14 0 0 1 0 18"/>
								<path d="M12 3a14 14 0 0 0 0 18"/>
							</svg>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/digital-music-distribution/">
								Digital Music Distribution
							</a>
						</h3>
						<p>Deliver your music to major streaming platforms with release management and digital distribution support.</p>
					</article>

					<!-- Promotion Services -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<svg viewBox="0 0 24 24">
								<path d="M4 18l5-5 3 3 8-8"/>
								<path d="M15 8h5v5"/>
							</svg>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/promotion-services/">
								Promotion Services
							</a>
						</h3>
						<p>Build awareness and reach new audiences through strategic digital marketing, campaigns and music promotion.</p>
					</article>

					<!-- IPRS Services -->
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<svg viewBox="0 0 24 24">
								<path d="M6 3h12v18H6z"/>
								<path d="M9 7h6"/>
								<path d="M9 11h6"/>
								<path d="M9 15h4"/>
							</svg>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/iprs-services/">
								IPRS Services
							</a>
						</h3>
						<p>Support for music rights, royalty registration and copyright-related services to help protect your creative work.</p>
					</article>

				</div>
			</div>
		</section>

        <section class="people py-9" id="people">
            <div class="container">
                <div class="section-heading reveal">
                    <span class="heading-tag">Featured artists</span>
                    <h2>Creators building their next chapter with BDC Music</h2>
                </div>
                <div class="swiper person-slider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="person-card reveal">
                                <img src="<?php echo $assetPath; ?>images/artist/rohit-tiwari.webp" alt="Rohit Tiwari" loading="lazy">
                                <div class="person-content">
                                    <span class="person-tag">Music Producer</span>
                                    <h3>Rohit Tiwari</h3>
                                    <span class="location">Chhatarpur, Delhi</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card reveal">
                                <img src="<?php echo $assetPath; ?>images/artist/ayush-sachdeva.webp" alt="Ayush Sachdeva" loading="lazy">
                                <div class="person-content">
                                    <span class="person-tag">Reel Star</span>
                                    <h3>Ayush Sachdeva</h3>
                                    <span class="location">Ghaziabad, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card reveal">
                                <img src="<?php echo $assetPath; ?>images/artist/alaap-gahlaut.webp" alt="Alaap Gahlaut" loading="lazy">
                                <div class="person-content">
                                    <span class="person-tag">Singer</span>
                                    <h3>Alaap Gahlaut</h3>
                                    <span class="location">New Delhi, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card reveal">
                                <img src="<?php echo $assetPath; ?>images/artist/nishaad.webp" alt="Nishaad" loading="lazy">
                                <div class="person-content">
                                    <span class="person-tag">Singer</span>
                                    <h3>Nishaad</h3>
                                    <span class="location">Haryana, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card reveal">
                                <img src="<?php echo $assetPath; ?>images/artist/sitara.webp" alt="Sitara" loading="lazy">
                                <div class="person-content">
                                    <span class="person-tag">Reel Star</span>
                                    <h3>Sitara</h3>
                                    <span class="location">Noida, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card">
                                <img src="<?php echo $assetPath; ?>images/artist/gunjan-jha.webp" alt="Gunjan Jha">
                                <div class="person-content">
                                    <span class="person-tag">Singer</span>
                                    <h3>Gunjan Jha</h3>
                                    <span class="location">Delhi, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card">
                                <img src="<?php echo $assetPath; ?>images/artist/amit-sati.webp" alt="Amit Sati">
                                <div class="person-content">
                                    <span class="person-tag">Director</span>
                                    <h3>Amit Sati</h3>
                                    <span class="location">Uttarakhand, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card">
                                <img src="<?php echo $assetPath; ?>images/artist/rajendra-rajawat.webp" alt="Rajendra Rajawat">
                                <div class="person-content">
                                    <span class="person-tag">Actor</span>
                                    <h3>Rajendra Rajawat</h3>
                                    <span class="location">Delhi, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card">
                                <img src="<?php echo $assetPath; ?>images/artist/lalit-thakur.webp" alt="Lalit Thakur">
                                <div class="person-content">
                                    <span class="person-tag">Director</span>
                                    <h3>Lalit Thakur</h3>
                                    <span class="location">Delhi, India</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="person-card">
                                <img src="<?php echo $assetPath; ?>images/artist/vishal-kumar.webp" alt="Vishal Kumar">
                                <div class="person-content">
                                    <span class="person-tag">Guitarist</span>
                                    <h3>Vishal Kumar</h3>
                                    <span class="location">Delhi, India</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>

        <section class="academy pt-0 py-9" id="academy">
			<div class="container">
				<div class="section-heading reveal">
					<span class="heading-tag">Education hub</span>
					<h2>Learn the craft through premium online classes</h2>
				</div>

				<div class="academy-grid">

					<article class="course-card glass-card reveal">
						<img src="<?php echo $assetPath; ?>images/vocal.jpg" alt="Singing classes" loading="lazy">
						<div>
							<h3>Singing</h3>
							<p>Develop your voice, pitch, breathing and vocal confidence with guided singing lessons.</p>
						</div>
					</article>

					<article class="course-card glass-card reveal">
						<img src="<?php echo $assetPath; ?>images/music-production.jpg" alt="Music Production classes" loading="lazy">
						<div>
							<h3><a href="<?php echo $basePath; ?>services/audio-video-services/">Music Production</a></h3>
							<p>Learn music arrangement, recording, mixing and modern production techniques for professional results.</p>
						</div>
					</article>

					<article class="course-card glass-card reveal">
						<img src="<?php echo $assetPath; ?>images/guitar.jpg" alt="Instrument courses" loading="lazy">
						<div>
							<h3>Instrument Courses</h3>
							<p>Master instruments like guitar, piano and harmonium through practical, step-by-step lessons.</p>
						</div>
					</article>

					<article class="course-card glass-card reveal">
						<img src="<?php echo $assetPath; ?>images/video-editing.webp" alt="Video Editing classes" loading="lazy">
						<div>
							<h3>Video Editing</h3>
							<p>Learn professional video editing, storytelling, transitions, effects and content creation workflows.</p>
						</div>
					</article>

				</div>
			</div>
		</section>

        <section class="testimonials" id="testimonials">
            <div class="container">
                <div class="section-heading reveal">
                    <span class="heading-tag">Creator reviews</span>
                    <h2>What artists say after working with BDC Music</h2>
                </div>
                <div class="swiper testimonial-slider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="testimonial-card reveal">
                                <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                                <p>BDC Music transformed my first release with seamless distribution and promotion support.</p>
                                <div class="testimonial-user">
                                    <img src="<?php echo $assetPath; ?>images/testimonial.webp" alt="Rohit Sharma" loading="lazy">
                                    <div>
                                        <h4>Rohit Sharma</h4>
                                        <span>Independent Singer</span>
                                    </div>
                                </div>
                                <div class="testimonial-meta">
                                    <span class="rating">★★★★★</span>
                                    <span class="release-tag">Released on 4 platforms</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testimonial-card reveal">
                                <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                                <p>The BDC Music's Online Classes helped me improve quickly. The lessons were practical, focused and easy to follow.</p>
                                <div class="testimonial-user">
                                    <img src="<?php echo $assetPath; ?>images/testimonial.webp" alt="Priya Verma" loading="lazy">
                                    <div>
                                        <h4>Priya Verma</h4>
                                        <span>Vocal Student</span>
                                    </div>
                                </div>
                                <div class="testimonial-meta">
                                    <span class="rating">★★★★★</span>
                                    <span class="release-tag">Improved vocal range in 6 weeks</span>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testimonial-card reveal">
                                <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                                <p>From artist management to creative strategy, every step felt intentional and supportive. The growth was visible fast.</p>
                                <div class="testimonial-user">
                                    <img src="<?php echo $assetPath; ?>images/testimonial.webp" alt="Aman Singh" loading="lazy">
                                    <div>
                                        <h4>Aman Singh</h4>
                                        <span>Music Producer</span>
                                    </div>
                                </div>
                                <div class="testimonial-meta">
                                    <span class="rating">★★★★★</span>
                                    <span class="release-tag">Expanded client roster</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>

        <section class="home-page faq pt-0" id="faq" style="background: #f7f7f7;">
            <div class="container">
                <div class="section-heading reveal">
                    <span class="heading-tag">FAQ</span>
                    <h2>Frequently asked questions</h2>
                    <p>Everything artists want to know before joining BDC Music.</p>
                </div>
                <div class="faq-wrapper">
                    <details class="faq-item reveal" open>
                        <summary class="faq-question">What services does BDC Music provide?<i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p>We offer artist management, music production, distribution, promotion, online education and creative networking for independent artists.</p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question">Which platforms do you distribute to?<i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p>We support leading streaming platforms such as Spotify, Apple Music, YouTube Music, Amazon Music, JioSaavn and Gaana.</p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question">Do I keep ownership of my music?<i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p>Yes. Artists keep ownership of their work while we support release, visibility and career growth.</p>
                        </div>
                    </details>
                    <details class="faq-item reveal">
                        <summary class="faq-question">Can beginners join the academy?<i class="fa-solid fa-plus"></i></summary>
                        <div class="faq-answer">
                            <p>Absolutely. Our academy is designed for beginners, intermediate learners and experienced creators.</p>
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <section class="cta cta-modern py-9">
            <div class="container">
                <div class="cta-copy reveal">
                    <h2>Ready To Build Your Music Career?</h2>
                    <p>Join BDC Music and access a complete creative ecosystem built for independent artists.</p>
                </div>
                <div class="cta-actions reveal">
                    <a href="<?php echo $basePath; ?>services/bdc-artists-marketplace" class="btn">Join BDC Music</a>
                    <a href="mailto:info@bdcmusic.in" class="btn btn-outline-light">Enquiry Now</a>
                </div>
            </div>
        </section>
    </main>
<?php include_once 'footer.php'; ?>

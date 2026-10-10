<?php

$pageTitle       = 'BDC Music | Artist Management, Music Production & Distribution';
$metaDescription = 'Discover BDC Music for artist promotion, music production, audio and video services, online music classes, digital distribution, and music rights support.';
$ogTitle         = 'BDC Music — Your Complete Music Partner';
$ogDescription   = 'Explore BDC Music for artist discovery, music production, digital distribution, promotion, music education, and IPRS services.';

include_once 'header.php';

?>
	<main>
		<section class="hero hero-bg" id="home">
			<div class="hero-ambient ambient-b"></div>
			<div class="container hero-grid">
				<div class="hero-content reveal">
					<span class="heading-tag">All-in-one music platform</span>
					<h1>From Your First Track to Global Recognition</h1>
					<p>Build your music career with BDC-music distribution, promotion, classes, audio & video services, artist marketplace and IPRS services, all under one roof.</p>
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
									<img src="<?php echo $assetPath; ?>images/hero-1.png" alt="BDC Music">
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
		<section class="trust-bar" aria-label="BDC Music achievements">
			<div class="container trust-grid">
				<div class="trust-item reveal is-visible">
					<h6>300+</h6>
					<p>Artists Supported</p>
				</div>
				<div class="trust-item reveal is-visible">
					<h6>200+</h6>
					<p>Music Releases</p>
				</div>
				<div class="trust-item reveal is-visible">
					<h6>100+</h6>
					<p>Promotions Completed</p>
				</div>
				<div class="trust-item reveal is-visible">
					<h6>10+</h6>
					<p>Years of Experience</p>
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
							<i data-lucide="layers"></i>
							<div>
								<h3>Flexible creative support</h3>
								<p>Choose services that match your goals without being locked into one path.</p>
							</div>
						</div>
						<div class="brand-point">
							<i data-lucide="zap"></i>
							<div>
								<h3>Built for modern artists</h3>
								<p>Premium strategy, polished production and audience growth under one roof.</p>
							</div>
						</div>
					</div>
					<a href="<?php echo $basePath; ?>all-services" class="btn">Explore BDC Services</a>
				</div>
			</div>
		</section>

		<section class="ecosystem section-dark py-9" id="services">
			<div class="container">
				<div class="section-heading reveal">
					<span class="heading-tag">Our Services</span>
					<h2>Everything artists need to <span class="highlight-text">create, release</span> and <span class="highlight-text">grow</span> their music</h2>
				</div>

				<div class="ecosystem-grid">
					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="users"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/bdc-artists-marketplace/">BDC Artists Marketplace</a>
						</h3>
						<p>Discover, showcase and connect with talented artists, musicians and creative professionals.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/bdc-artists-marketplace/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>

					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="audio-lines"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/audio-video-services/">Audio &amp; Video Services</a>
						</h3>
						<p>Professional recording, mixing, mastering, music production and video services for your next release.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/audio-video-services/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>

					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="graduation-cap"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/online-offline-classes/">Online/Offline Classes</a>
						</h3>
						<p>Learn vocals, instruments, music production and other creative skills through flexible learning options.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/online-offline-classes/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>

					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="globe-2"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/digital-music-distribution/">Digital Music Distribution</a>
						</h3>
						<p>Deliver your music to major streaming platforms with release management and digital distribution support.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/digital-music-distribution/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>

					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="megaphone"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/promotion-services/">Promotion Services</a>
						</h3>
						<p>Build awareness and reach new audiences through strategic digital marketing, campaigns and music promotion.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/promotion-services/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>

					<article class="eco-card glass-card reveal">
						<span class="eco-icon">
							<i data-lucide="copyright"></i>
						</span>
						<h3>
							<a href="<?php echo $basePath; ?>services/iprs-services/">IPRS Services</a>
						</h3>
						<p>Support for music rights, royalty registration and copyright-related services to help protect your creative work.</p>
						<span class="eco-arrow" aria-hidden="true">
							<a href="<?php echo $basePath; ?>services/iprs-services/"><i data-lucide="arrow-up-right"></i></a>
						</span>
					</article>
				</div>
			</div>
		</section>
		
		<section class="success section" id="success">
			<div class="container">
				<div class="section-heading reveal is-visible">
					<span class="heading-tag">SUCCESS STORIES</span>
					<h2>Real artists. Real <span class="highlight-text">growth.</span> Real results.</h2>
				</div>

				<div class="success-grid">
					<article class="success-card reveal in" style="transition-delay: 0ms;">
						<img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=900&amp;q=85" alt="Riya Sharma">
						<div>
							<h3>Riya Sharma</h3>
							<small>Singer</small>
							<div>
								<b>420K+<small>YouTube Views</small></b>
								<b>92K+<small>Spotify Streams</small></b>
								<b>18K+<small>New Followers</small></b>
								<b>12<small>Songs Released</small></b>
							</div>
						</div>
					</article>

					<article class="success-card reveal in" style="transition-delay: 90ms;">
						<img src="https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?w=900&amp;q=85" alt="Arjun Mehta">
						<div>
							<h3>Arjun Mehta</h3>
							<small>Producer</small>
							<div>
								<b>1.2M+<small>YouTube Views</small></b>
								<b>210K+<small>Spotify Streams</small></b>
								<b>25K+<small>New Followers</small></b>
								<b>18<small>Songs Released</small></b>
							</div>
						</div>
					</article>

					<article class="success-card reveal in" style="transition-delay: 180ms;">
						<img src="https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=900&amp;q=85" alt="The Midnight Drive">
						<div>
							<h3>The Midnight Drive</h3>
							<small>Indie Band</small>
							<div>
								<b>2.8M+<small>YouTube Views</small></b>
								<b>320K+<small>Spotify Streams</small></b>
								<b>42K+<small>New Followers</small></b>
								<b>24<small>Songs Released</small></b>
							</div>
						</div>
					</article>

					<article class="success-card reveal in" style="transition-delay: 270ms;">
						<img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=900&amp;q=85" alt="Sneha Iyer">
						<div>
							<h3>Sneha Iyer</h3>
							<small>Singer</small>
							<div>
								<b>950K+<small>YouTube Views</small></b>
								<b>150K+<small>Spotify Streams</small></b>
								<b>21K+<small>New Followers</small></b>
								<b>15<small>Songs Released</small></b>
							</div>
						</div>
					</article>
				</div>
			</div>
		</section>

		<section class="people py-9" id="people">
			<div class="container">
				<div class="section-heading reveal">
					<span class="heading-tag">Discover BDC Talent</span>
					<h2>Discover amazing artists for your <span class="highlight-text">next project</span></h2>
				</div>
				<div class="swiper person-slider">
					<div class="swiper-wrapper">
						<div class="swiper-slide">
							<div class="person-card reveal">
								<img src="<?php echo $assetPath; ?>images/artist/rohit-tiwari.webp" alt="Rohit Tiwari" loading="lazy">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/music-producer/" class="person-tag">Music Producer</a>
									<h3><a href="<?php echo $basePath; ?>artists/music-producer/rohit-tiwari/">Rohit Tiwari</a></h3>
									<span class="location">Chhatarpur, Delhi</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card reveal">
								<img src="<?php echo $assetPath; ?>images/artist/ayush-sachdeva.webp" alt="Ayush Sachdeva" loading="lazy">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/reels-stars/" class="person-tag">Reel Star</a>
									<h3><a href="<?php echo $basePath; ?>artists/reels-stars/ayush-sachdeva/">Ayush Sachdeva</a></h3>
									<span class="location">Ghaziabad, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card reveal">
								<img src="<?php echo $assetPath; ?>images/artist/alaap-gahlaut.webp" alt="Alaap Gahlaut" loading="lazy">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/singer/" class="person-tag">Singer</a>
									<h3><a href="<?php echo $basePath; ?>artists/singer/alaap-gahlaut/">Alaap Gahlaut</a></h3>
									<span class="location">New Delhi, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card reveal">
								<img src="<?php echo $assetPath; ?>images/artist/nishaad.webp" alt="Nishaad" loading="lazy">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/singer/" class="person-tag">Singer</a>
									<h3><a href="<?php echo $basePath; ?>artists/singer/nishaad/">Nishaad</a></h3>
									<span class="location">Haryana, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card reveal">
								<img src="<?php echo $assetPath; ?>images/artist/sitara.webp" alt="Sitara" loading="lazy">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/reels-stars/" class="person-tag">Reel Star</a>
									<h3><a href="<?php echo $basePath; ?>artists/reels-stars/sitara/">Sitara</a></h3>
									<span class="location">Noida, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card">
								<img src="<?php echo $assetPath; ?>images/artist/gunjan-jha.webp" alt="Gunjan Jha">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/singer/" class="person-tag">Singer</a>
									<h3><a href="<?php echo $basePath; ?>artists/singer/gunjan-jha/">Gunjan Jha</a></h3>
									<span class="location">Delhi, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card">
								<img src="<?php echo $assetPath; ?>images/artist/amit-sati.webp" alt="Amit Sati">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/director/" class="person-tag">Director</a>
									<h3><a href="<?php echo $basePath; ?>artists/director/amit-sati/">Amit Sati</a></h3>
									<span class="location">Uttarakhand, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card">
								<img src="<?php echo $assetPath; ?>images/artist/rajendra-rajawat.webp" alt="Rajendra Rajawat">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/actor/" class="person-tag">Actor</a>
									<h3><a href="<?php echo $basePath; ?>artists/actor/rajendra-rajawat/">Rajendra Rajawat</a></h3>
									<span class="location">Delhi, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card">
								<img src="<?php echo $assetPath; ?>images/artist/lalit-thakur.webp" alt="Lalit Thakur">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/director/" class="person-tag">Director</a>
									<h3><a href="<?php echo $basePath; ?>artists/director/lalit-thakur/">Lalit Thakur</a></h3>
									<span class="location">Delhi, India</span>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div class="person-card">
								<img src="<?php echo $assetPath; ?>images/artist/vishal-kumar.webp" alt="Vishal Kumar">
								<div class="person-content">
									<a href="<?php echo $basePath; ?>artists/instrument-player/" class="person-tag">Guitarist</a>
									<h3><a href="<?php echo $basePath; ?>artists/instrument-player/vishal-kumar/">Vishal Kumar</a></h3>
									<span class="location">Delhi, India</span>
								</div>
							</div>
						</div>
					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
		</section>
		<section class="academy pt-0 pb-9" id="academy">
			<div class="container">
				<div class="section-heading reveal">
					<span class="heading-tag">Education hub</span>
					<h2>Learn the craft. <br/> Build the career<span style="color: var(--primary);">.</span></h2>
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

			<span class="testimonial-label">
				Artist Voices
			</span>

			<div class="testimonial-slider swiper">

				<div class="swiper-wrapper">

					<div class="swiper-slide">
						<div class="testimonial-content reveal">

							<p class="testimonial-quote">
								“BDC Music helped me turn my music into something bigger than a release.”
							</p>

							<div class="testimonial-user">
								<img
									src="<?php echo $assetPath; ?>images/testimonial.webp"
									alt="Rohit Sharma"
									loading="lazy"
								>

								<div class="testimonial-user-info">
									<h4>Rohit Sharma</h4>
									<span>Independent Creator</span>
								</div>
							</div>

						</div>
					</div>


					<div class="swiper-slide">
						<div class="testimonial-content reveal">

							<p class="testimonial-quote">
								“BDC Music made learning music simple, practical, and inspiring. I could see my progress with every lesson.”
							</p>

							<div class="testimonial-user">
								<img
									src="<?php echo $assetPath; ?>images/testimonial.webp"
									alt="Priya Verma"
									loading="lazy"
								>

								<div class="testimonial-user-info">
									<h4>Priya Verma</h4>
									<span>Independent Artist</span>
								</div>
							</div>

						</div>
					</div>


					<div class="swiper-slide">
						<div class="testimonial-content reveal">

							<p class="testimonial-quote">
								“From strategy to release, every step felt intentional and genuinely supportive.”
							</p>

							<div class="testimonial-user">
								<img
									src="<?php echo $assetPath; ?>images/testimonial.webp"
									alt="Aman Singh"
									loading="lazy"
								>

								<div class="testimonial-user-info">
									<h4>Aman Singh</h4>
									<span>Music Producer</span>
								</div>
							</div>

						</div>
					</div>

				</div>

				<div class="swiper-pagination"></div>

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
						<summary class="faq-question">What services does BDC Music provide?<i data-lucide="plus"></i></summary>
						<div class="faq-answer">
							<p>We offer artist management, music production, distribution, promotion, online education and creative networking for independent artists.</p>
						</div>
					</details>
					<details class="faq-item reveal">
						<summary class="faq-question">Which platforms do you distribute to?<i data-lucide="plus"></i></summary>
						<div class="faq-answer">
							<p>We support leading streaming platforms such as Spotify, Apple Music, YouTube Music, Amazon Music, JioSaavn and Gaana.</p>
						</div>
					</details>
					<details class="faq-item reveal">
						<summary class="faq-question">Do I keep ownership of my music?<i data-lucide="plus"></i></summary>
						<div class="faq-answer">
							<p>Yes. Artists keep ownership of their work while we support release, visibility and career growth.</p>
						</div>
					</details>
					<details class="faq-item reveal">
						<summary class="faq-question">Can beginners join the academy?<i data-lucide="plus"></i></summary>
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
					<a href="mailto:info@bdcmusic.in" class="btn btn-outline-light">Talk to BDC</a>
				</div>
			</div>
		</section>
	</main>
<?php include_once 'footer.php'; ?>

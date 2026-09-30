<?php
/**
 * Booking field registry - per-service form configuration.
 *
 * The checkout renders whatever this file declares for the selected slug and
 * the validator walks the same definitions, so a field can never be displayed
 * but not validated, or validated but not stored. `key` values are the
 * `bookings.meta` keys the seed data and `includes/service-fields.php` already
 * use, which is why both dashboards render them unchanged.
 *
 * Field definition keys:
 *   key         meta key, and the input name
 *   label       visible label
 *   type        text|textarea|email|tel|url|date|number
 *               |select|radio|checkbox|file|files
 *   step        the step the control belongs to; `details` when omitted. A
 *               service that asks the customer to narrow the job down first
 *               puts that field on its own step, and the step machine then
 *               cannot reach the details form until it is answered.
 *   required    bool
 *   options     value => label, for select/radio/checkbox
 *   placeholder input placeholder
 *   icon        Lucide icon name
 *   accept      file extension filter
 *   multiple    allow more than one file
 *   help        hint text under the control
 *   depends_on  array( 'key' => ..., 'equals' => ... ) - shown only when the
 *               named field currently holds that value
 *   half        render at half width, matching the page's col-6 layout
 *   cards       render a radio/checkbox group as full selectable cards
 *   option_groups  value => list of option values, for a checkbox group whose
 *               options only apply to one answer of another field. Options
 *               outside the chosen group are hidden by booking-checkout.js and
 *               dropped by the validator, so an incompatible format can never
 *               be posted or stored.
 *   group_source    the field whose answer selects the group above
 *   media_source    for a file field: the radio whose answer relabels it and
 *               narrows its accepted types
 *   media_labels    answer => label text for media_source
 *   media_accept    answer => `accept` attribute for media_source
 *
 * Requires: includes/helpers.php, includes/file-upload.php
 */

if ( ! defined( 'BOOKING_REGISTRY_LOADED' ) ) {
	define( 'BOOKING_REGISTRY_LOADED', true );
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/file-upload.php';

/**
 * Which upload rules apply to a service.
 *
 * The keys are the ones `get_allowed_mime_types()` / `get_max_file_size()` in
 * `includes/file-upload.php` already understand.
 *
 * @param string $slug services.slug.
 * @return string
 */
function booking_upload_service( $slug ) {
	$map = array(
		'audio-video'            => 'audio-video',
		'digital-distribution'   => 'digital-distribution',
		'iprs'                   => 'iprs',
	);

	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'general';
}

/**
 * Every field definition for a service, in display order.
 *
 * @param string $slug services.slug.
 * @return array
 */
function booking_fields( $slug ) {    static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$defs = array(

		// Source: services/bdc-artists-marketplace.php:320-442 and the meta keys
		// both dashboards display (includes/service-fields.php:66-72).

		'artists-marketplace' => array(
			array( 'key' => 'artist_category', 'label' => 'Category', 'type' => 'select', 'required' => true, 'icon' => 'table', 'half' => true, 'options' => array(
				'singer' => 'Singer',
				'music-producer' => 'Music Producer',
				'lyricist' => 'Lyricist',
				'composer' => 'Composer',
				'instrumentalist' => 'Instrumentalist',
				'dj' => 'DJ',
				'mixing-mastering-engineer' => 'Mixing & Mastering Engineer',
				'video-editor' => 'Video Editor',
				'videographer' => 'Videographer',
				'photographer' => 'Photographer',
				'graphic-designer' => 'Graphic Designer',
				'actor-model' => 'Actor / Model',
				'dancer-choreographer' => 'Dancer / Choreographer',
				'voice-over-artist' => 'Voice Over Artist',
				'podcast-editor' => 'Podcast Editor',
				'social-media-manager' => 'Social Media Manager',
				'digital-marketing-expert' => 'Digital Marketing Expert',
				'music-video-director' => 'Music Video Director',
				'live-band-artist' => 'Live Band Artist',
				'session-musician' => 'Session Musician',
			) ),
			array( 'key' => 'experience', 'label' => 'Experience', 'type' => 'text', 'icon' => 'building-2', 'half' => true, 'placeholder' => 'e.g. 3 Years, Senior Level' ),
			array( 'key' => 'artist_goal', 'label' => 'Artist Goal', 'type' => 'textarea', 'icon' => 'target', 'placeholder' => 'What do you want to achieve through the marketplace?' ),
			array( 'key' => 'event_type', 'label' => 'Event Type', 'type' => 'select', 'icon' => 'calendar-days', 'half' => true, 'options' => array(
				'Wedding' => 'Wedding',
				'Corporate Event' => 'Corporate Event',
				'Concert / Live Show' => 'Concert / Live Show',
				'Festival' => 'Festival',
				'Private Party' => 'Private Party',
				'Film / Theatre' => 'Film / Theatre',
				'Other' => 'Other',
			) ),
			array( 'key' => 'event_date', 'label' => 'Event Date', 'type' => 'date', 'icon' => 'calendar', 'half' => true ),
			array( 'key' => 'event_location', 'label' => 'Event Location', 'type' => 'text', 'icon' => 'map-pin', 'placeholder' => 'City or venue' ),
			array( 'key' => 'budget', 'label' => 'Budget', 'type' => 'text', 'icon' => 'indian-rupee', 'placeholder' => 'Example: Rs.25,000' ),
			array( 'key' => 'portfolio', 'label' => 'Portfolio Link', 'type' => 'url', 'icon' => 'link', 'placeholder' => 'https://yourportfolio.com' ),
			array( 'key' => 'portfolio_file', 'label' => 'Upload Portfolio', 'type' => 'file', 'icon' => 'cloud-upload', 'accept' => '.jpg,.jpeg,.png,.pdf' ),
			array( 'key' => 'about', 'label' => 'About Yourself', 'type' => 'textarea', 'icon' => 'menu', 'placeholder' => 'Tell us a little about your background and achievements...' ),
		),

		// Source: services/audio-video-services.php:428-500.
		//
		// There is deliberately no `service_type` field here. The sub-service is
		// chosen on the package step, where it is also priced, and
		// booking-create.php writes meta['service_type'] from the groups on the
		// order. Asking again on this form would let a customer pay for Mixing
		// and type "Mastering", and the dashboards would report the typed answer
		// rather than the one that was charged.
		//
		// Audio and Video are sold as two different jobs, so the side is settled
		// first, on its own step, and the details form is only reachable once it
		// has been answered.
		'audio-video' => array(
			array( 'key' => 'service_category', 'label' => 'Service Category', 'type' => 'radio', 'required' => true, 'step' => 'category', 'icon' => 'layers', 'cards' => true, 'options' => array(
				'Audio' => 'Audio Service',
				'Video' => 'Video Service',
			) ),
		// The upload is relabelled and re-filtered by the answer to Service
		// Category, so the same field serves both sides. The `accept` below is
		// the union, and is what the server still checks.

			array( 'key' => 'project_upload', 'label' => 'Upload Audio / Video', 'type' => 'files', 'icon' => 'cloud-upload', 'half' => true, 'multiple' => true, 'accept' => '.wav,.mp3,.flac,.mp4,.mov', 'media_source' => 'service_category', 'media_labels' => array(
				'Audio' => 'Upload Audio',
				'Video' => 'Upload Video',
			), 'media_accept' => array(
				'Audio' => '.wav,.mp3,.flac',
				'Video' => '.mp4,.mov',
			) ),
		// The page posted delivery_format[]; both dashboards label
		// delivery_formats, so that is the key stored in meta.

			array( 'key' => 'delivery_formats', 'label' => 'Delivery Format', 'type' => 'checkbox', 'icon' => 'file-audio', 'cards' => true, 'group_source' => 'service_category', 'option_groups' => array(
				'Audio' => array( 'WAV', 'MP3', 'FLAC' ),
				'Video' => array( 'MP4', 'MOV' ),
			), 'options' => array(
				'WAV' => 'WAV',
				'MP3' => 'MP3',
				'FLAC' => 'FLAC',
				'MP4' => 'MP4',
				'MOV' => 'MOV',
			) ),
			array( 'key' => 'deadline', 'label' => 'Deadline', 'type' => 'text', 'required' => true, 'icon' => 'clock', 'half' => true, 'placeholder' => 'Example: 7 working days' ),
			array( 'key' => 'budget', 'label' => 'Budget', 'type' => 'text', 'required' => true, 'icon' => 'indian-rupee', 'half' => true, 'placeholder' => 'Example: Rs.15,000' ),
			array( 'key' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'icon' => 'message-square', 'placeholder' => 'Anything else we should know about the project?' ),
		),

		// Source: services/online-offline-classes.php:598-700. The course and
		// the package are chosen on the package step, from service_plans, and
		// mirrored into meta so the dashboards keep rendering them.

		'online-offline-classes' => array(
			array( 'key' => 'class_mode', 'label' => 'Class Mode', 'type' => 'radio', 'required' => true, 'icon' => 'signal', 'options' => array(
				'Online' => 'Online',
				'Offline' => 'Offline',
			) ),
			array( 'key' => 'level', 'label' => 'Level', 'type' => 'text', 'icon' => 'signal', 'half' => true, 'placeholder' => 'Beginner / Intermediate / Advanced' ),
			array( 'key' => 'age', 'label' => 'Age', 'type' => 'text', 'icon' => 'user', 'half' => true, 'placeholder' => 'Student age' ),
		),

		// Source: services/digital-music-distribution.php:440-660.

		'digital-distribution' => array(
			array( 'key' => 'release_type', 'label' => 'Release Type', 'type' => 'select', 'required' => true, 'icon' => 'disc', 'half' => true, 'options' => array(
				'Single' => 'Single',
				'Album' => 'Album',
			) ),
			array( 'key' => 'release_title', 'label' => 'Release Title', 'type' => 'text', 'required' => true, 'icon' => 'music', 'half' => true ),
			array( 'key' => 'artist_name', 'label' => 'Artist Name', 'type' => 'text', 'required' => true, 'icon' => 'user' ),
			array( 'key' => 'genre', 'label' => 'Genre', 'type' => 'select', 'required' => true, 'icon' => 'tags', 'half' => true, 'options' => array(
				'Pop' => 'Pop', 'Rock' => 'Rock', 'Hip Hop' => 'Hip Hop', 'Rap' => 'Rap',
				'R&B' => 'R&B', 'Classical' => 'Classical', 'Folk' => 'Folk',
				'Devotional' => 'Devotional', 'Electronic' => 'Electronic', 'Jazz' => 'Jazz',
				'Instrumental' => 'Instrumental', 'Bollywood' => 'Bollywood', 'Other' => 'Other',
			) ),
			array( 'key' => 'language', 'label' => 'Language', 'type' => 'select', 'required' => true, 'icon' => 'languages', 'half' => true, 'options' => array(
				'Hindi' => 'Hindi', 'English' => 'English', 'Odia' => 'Odia',
				'Bengali' => 'Bengali', 'Telugu' => 'Telugu', 'Tamil' => 'Tamil',
				'Kannada' => 'Kannada', 'Malayalam' => 'Malayalam', 'Marathi' => 'Marathi',
				'Punjabi' => 'Punjabi', 'Gujarati' => 'Gujarati', 'Urdu' => 'Urdu',
				'Assamese' => 'Assamese', 'Other' => 'Other',
			) ),
			array( 'key' => 'release_date', 'label' => 'Preferred Release Date', 'type' => 'date', 'icon' => 'calendar' ),
			array( 'key' => 'isrc', 'label' => 'Do you have an ISRC code?', 'type' => 'radio', 'icon' => 'hash', 'half' => true, 'options' => array(
				'Yes' => 'Yes', 'No' => 'No',
			) ),
			array( 'key' => 'existing_isrc', 'label' => 'Existing ISRC Code', 'type' => 'text', 'required' => true, 'icon' => 'hash', 'half' => true, 'placeholder' => 'e.g. IN-R5S-23-00001', 'depends_on' => array( 'key' => 'isrc', 'equals' => 'Yes' ) ),
			array( 'key' => 'upc', 'label' => 'Do you have a UPC code?', 'type' => 'radio', 'icon' => 'barcode', 'half' => true, 'options' => array(
				'Yes' => 'Yes', 'No' => 'No',
			) ),
			array( 'key' => 'existing_upc', 'label' => 'Existing UPC Code', 'type' => 'text', 'required' => true, 'icon' => 'barcode', 'half' => true, 'placeholder' => 'e.g. 123456789012', 'depends_on' => array( 'key' => 'upc', 'equals' => 'Yes' ) ),
			array( 'key' => 'copyright_help', 'label' => 'Do you need copyright help?', 'type' => 'radio', 'icon' => 'copyright', 'half' => true, 'options' => array(
				'Yes' => 'Yes', 'No' => 'No',
			) ),
			array( 'key' => 'youtube_link', 'label' => 'YouTube Link', 'type' => 'url', 'required' => true, 'icon' => 'link', 'placeholder' => 'https://youtube.com/watch?v=...', 'depends_on' => array( 'key' => 'copyright_help', 'equals' => 'Yes' ) ),
			array( 'key' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'icon' => 'message-square', 'placeholder' => 'Anything else about the release?' ),
			array( 'key' => 'audio_file', 'label' => 'Audio File', 'type' => 'file', 'required' => true, 'icon' => 'cloud-upload', 'accept' => '.wav,.mp3' ),
			array( 'key' => 'cover_artwork', 'label' => 'Cover Artwork', 'type' => 'file', 'required' => true, 'icon' => 'image', 'accept' => '.jpg,.jpeg,.png' ),
			array( 'key' => 'metadata_file', 'label' => 'Metadata File', 'type' => 'file', 'icon' => 'file-spreadsheet', 'accept' => '.csv,.xls,.xlsx' ),
		),

		// Source: services/promotion-services.php:200-380 plus the meta keys
		// both dashboards display (includes/service-fields.php:168-172).

		'promotion' => array(
			array( 'key' => 'content_type', 'label' => 'Content Type', 'type' => 'select', 'required' => true, 'icon' => 'film', 'half' => true, 'options' => array(
				'Music Video' => 'Music Video',
				'Instagram Reels' => 'Instagram Reels',
				'Short Film' => 'Short Film',
				'Brand Video' => 'Brand Video',
			) ),
			array( 'key' => 'promotion_goal', 'label' => 'Promotion Goal', 'type' => 'select', 'required' => true, 'icon' => 'target', 'half' => true, 'options' => array(
				'More Views' => 'More Views',
				'Audience Growth' => 'Audience Growth',
				'Brand Awareness' => 'Brand Awareness',
				'Release Promotion' => 'Release Promotion',
			) ),
			array( 'key' => 'campaign_type', 'label' => 'Campaign Type', 'type' => 'select', 'icon' => 'megaphone', 'half' => true, 'options' => array(
				'Social Media' => 'Social Media',
				'YouTube Ads' => 'YouTube Ads',
				'Instagram / Reels' => 'Instagram / Reels',
				'Music Video Promotion' => 'Music Video Promotion',
				'Release Launch' => 'Release Launch',
				'Other' => 'Other',
			) ),
			array( 'key' => 'target_platform', 'label' => 'Target Platform', 'type' => 'text', 'icon' => 'megaphone', 'half' => true, 'placeholder' => 'e.g. Instagram, YouTube' ),
			array( 'key' => 'release_title', 'label' => 'Release / Content', 'type' => 'text', 'icon' => 'music', 'half' => true ),
			array( 'key' => 'campaign_duration', 'label' => 'Duration', 'type' => 'text', 'icon' => 'clock', 'half' => true, 'placeholder' => 'e.g. 2 weeks' ),
			array( 'key' => 'budget', 'label' => 'Budget', 'type' => 'text', 'icon' => 'indian-rupee', 'placeholder' => 'Example: Rs.10,000' ),
			array( 'key' => 'project_link', 'label' => 'Project Link', 'type' => 'url', 'icon' => 'link', 'placeholder' => 'https://youtube.com/watch?v=...' ),
			array( 'key' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'icon' => 'message-square', 'placeholder' => 'Tell us about the campaign' ),
		),

		// Source: services/iprs-services.php:340-700. membership_type is no
		// longer asked here: it is a service_plans row chosen on the package
		// step, mirrored into meta from the chosen plan.

		'iprs' => array(
			array( 'key' => 'applicant_type', 'label' => 'Applicant Type', 'type' => 'select', 'required' => true, 'icon' => 'user', 'half' => true, 'options' => array(
				'author' => 'Author',
				'composer' => 'Composer',
				'author-composer' => 'Author / Composer',
				'publisher' => 'Publisher',
				'artist' => 'Artist',
			) ),
			array( 'key' => 'song_released', 'label' => 'Have you released a song?', 'type' => 'radio', 'required' => true, 'icon' => 'circle-play', 'half' => true, 'options' => array(
				'yes' => 'Yes', 'no' => 'No',
			) ),
			array( 'key' => 'song_title', 'label' => 'Song / Work Title', 'type' => 'text', 'icon' => 'music', 'half' => true ),
			array( 'key' => 'artist_name', 'label' => 'Artist / Author', 'type' => 'text', 'icon' => 'user', 'half' => true ),
			array( 'key' => 'song_links', 'label' => 'Released Song Links', 'type' => 'url', 'icon' => 'link', 'placeholder' => 'https://open.spotify.com/track/...' ),
			array( 'key' => 'account_holder', 'label' => 'Account Holder Name', 'type' => 'text', 'required' => true, 'icon' => 'user', 'half' => true ),
			array( 'key' => 'account_number', 'label' => 'Bank Account Number', 'type' => 'text', 'required' => true, 'icon' => 'banknote', 'half' => true ),
			array( 'key' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true, 'icon' => 'landmark', 'half' => true ),
			array( 'key' => 'ifsc', 'label' => 'IFSC Code', 'type' => 'text', 'required' => true, 'icon' => 'code', 'half' => true ),
			array( 'key' => 'message', 'label' => 'Additional Information', 'type' => 'textarea', 'icon' => 'message-square', 'placeholder' => 'Tell us about your music, works or any questions regarding IPRS registration' ),
			array( 'key' => 'pan_card', 'label' => 'PAN Card', 'type' => 'file', 'required' => true, 'icon' => 'contact', 'accept' => '.jpg,.jpeg,.png,.pdf' ),
			array( 'key' => 'address_proof', 'label' => 'Address Proof', 'type' => 'file', 'required' => true, 'icon' => 'contact', 'accept' => '.jpg,.jpeg,.png,.pdf' ),
			array( 'key' => 'photo', 'label' => 'Passport Size Photo', 'type' => 'file', 'required' => true, 'icon' => 'camera', 'accept' => '.jpg,.jpeg,.png' ),
			array( 'key' => 'song_proof', 'label' => 'Song Proof (Optional)', 'type' => 'file', 'icon' => 'file-audio', 'accept' => '.jpg,.jpeg,.png,.pdf' ),
		),
	);

	$cache[ $slug ] = isset( $defs[ $slug ] ) ? $defs[ $slug ] : array();

	return $cache[ $slug ];
}

/**
 * One field definition.
 *
 * @param string $slug services.slug.
 * @param string $key  Field key.
 * @return array|null
 */
function booking_field( $slug, $key ) {
	foreach ( booking_fields( $slug ) as $field ) {
		if ( $field['key'] === $key ) {
			return $field;
		}
	}

	return null;
}

/**
 * The step a field is answered on.
 *
 * @param array $field Field definition.
 * @return string
 */
function booking_field_step( array $field ) {
	return isset( $field['step'] ) && $field['step'] !== '' ? $field['step'] : 'details';
}

/**
 * The fields belonging to one step, or every field when $step is empty.
 *
 * @param string $slug services.slug.
 * @param string $step Step identifier, '' for the whole registry.
 * @return array
 */
function booking_fields_for_step( $slug, $step = '' ) {
	$fields = array();

	foreach ( booking_fields( $slug ) as $field ) {
		if ( $step === '' || booking_field_step( $field ) === $step ) {
			$fields[] = $field;
		}
	}

	return $fields;
}

/**
 * Whether a service is narrowed down to a category before its details form.
 *
 * @param string $slug services.slug.
 * @return bool
 */
function booking_has_category_step( $slug ) {
	return (bool) booking_fields_for_step( $slug, 'category' );
}

/**
 * The common contact fields, shared by every service.
 *
 * Not part of the registry: contact details are collected once, in their own
 * step, and map to `bookings.customer_*` and the `users` row rather than into
 * `bookings.meta`.
 *
 * @return array
 */
function booking_contact_fields() {
	return array(
		array( 'key' => 'name',     'label' => 'Full Name',  'type' => 'text',  'required' => true, 'icon' => 'user',     'autocomplete' => 'name' ),
		array( 'key' => 'email',    'label' => 'Email',      'type' => 'email', 'required' => true, 'icon' => 'mail', 'autocomplete' => 'email' ),
		array( 'key' => 'phone',    'label' => 'Phone',      'type' => 'tel',   'required' => true, 'icon' => 'phone',    'autocomplete' => 'tel' ),
		array( 'key' => 'whatsapp', 'label' => 'WhatsApp',   'type' => 'tel',   'required' => false, 'icon' => 'message-circle-more', 'autocomplete' => 'tel', 'help' => 'Optional. Only if it differs from your phone number.' ),
	);
}

/**
 * Whether a conditional field should currently be shown.
 *
 * @param array $field  Field definition.
 * @param array $values Current values, keyed by field key.
 * @return bool
 */
function booking_field_visible( array $field, array $values ) {
	if ( empty( $field['depends_on'] ) ) {
		return true;
	}

	$dep     = $field['depends_on'];
	$wanted  = booking_dependency_values( $dep['equals'] );
	$current = isset( $values[ $dep['key'] ] ) ? $values[ $dep['key'] ] : '';

	// A checkbox parent is a set, so any one of its values unlocks the field.
	// Casting the array to a string would compare against the literal "Array".
	if ( is_array( $current ) ) {
		foreach ( $current as $item ) {
			if ( in_array( (string) $item, $wanted, true ) ) {
				return true;
			}
		}

		return false;
	}

	return in_array( (string) $current, $wanted, true );
}

/**
 * The answers that unlock a conditional field, split from its `equals` rule.
 *
 * Kept in step with the matching logic in assets/js/booking-checkout.js, which
 * splits the same value, so the browser and the validator can never disagree
 * about whether a field applies. A single plain value is the common case; `|`
 * is there for a field that more than one answer should unlock.
 *
 * @param string $equals The `equals` rule from a depends_on definition.
 * @return array Accepted answers.
 */
function booking_dependency_values( $equals ) {
	return array_map( 'trim', explode( '|', (string) $equals ) );
}

/**
 * Move one submitted file input into the draft's staging directory.
 *
 * Wraps process_file_upload() / process_multiple_uploads() from
 * `includes/file-upload.php` so the real MIME type is checked with finfo
 * rather than trusting the browser, and so a per-field failure becomes a
 * readable message instead of a silently dropped file. Every accepted file is
 * tagged with the field it came from, because `bookings.files` has to be able
 * to tell an IPRS PAN card from an IPRS address proof.
 *
 * @param array|null $entry   The $_FILES entry for this field, if any.
 * @param array      $field   Field definition.
 * @param array      $allowed Allowed MIME types.
 * @param int        $maxSize Max size per file, in bytes.
 * @param string     $key     Field key.
 * @param array      $errors  Collected error messages keyed by field key, by
 *                           reference, so the renderer can mark the control
 *                           the customer has to fix.
 * @return array Accepted file records.
 */
function booking_collect_uploads( $entry, array $field, array $allowed, $maxSize, $key, array &$errors ) {
	$collected = array();
	$label     = $field['label'];
	$multiple  = ! empty( $field['multiple'] );

	if ( ! is_array( $entry ) || ! isset( $entry['error'] ) ) {
		return $collected;
	}

	$sizeMb = max( 1, (int) round( $maxSize / 1048576 ) );

	/**
	 * Turn an accepted upload into a stored record.
	 *
	 * @param array $stored Output of process_file_upload().
	 * @return array
	 */
	$tag = function ( $stored ) use ( $key, $label ) {
		$stored['field'] = $key;
		$stored['label'] = $label;

		return $stored;
	};

	if ( $multiple ) {
		$count   = is_array( $entry['name'] ) ? count( $entry['name'] ) : 0;
		$skipped = 0;
		$staging = null;

		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! isset( $entry['error'][ $i ] ) || $entry['error'][ $i ] === UPLOAD_ERR_NO_FILE ) {
				continue;
			}

			$single = array(
				'name'     => $entry['name'][ $i ],
				'type'     => $entry['type'][ $i ],
				'tmp_name' => $entry['tmp_name'][ $i ],
				'error'    => $entry['error'][ $i ],
				'size'     => $entry['size'][ $i ],
			);

			// Resolved on first real file, so a form with no uploads never creates
			// a staging directory.

			if ( $staging === null ) {
				$staging = booking_staging_dir();
			}

			$stored = process_file_upload( $single, $staging, $allowed, $maxSize );

			if ( ! $stored ) {
				$skipped++;
				continue;
			}

			$collected[] = $tag( $stored );
		}

		if ( $skipped > 0 ) {
			$errors[ $key ][] = $skipped === 1
				? 'One of the ' . lcfirst( $label ) . ' files was rejected. Use a supported file type, up to ' . $sizeMb . 'MB each.'
				: $skipped . ' of the ' . lcfirst( $label ) . ' files were rejected. Use a supported file type, up to ' . $sizeMb . 'MB each.';
		}

		return $collected;
	}

	if ( $entry['error'] === UPLOAD_ERR_NO_FILE ) {
		return $collected;
	}

	if ( in_array( $entry['error'], array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) ) {
		$errors[ $key ][] = $label . ' is too large. The maximum is ' . $sizeMb . 'MB.';

		return $collected;
	}

	$stored = process_file_upload( $entry, booking_staging_dir(), $allowed, $maxSize );

	if ( ! $stored ) {
		$errors[ $key ][] = $label . ' was rejected. Use a supported file type, up to ' . $sizeMb . 'MB.';

		return $collected;
	}

	$collected[] = $tag( $stored );

	return $collected;
}

/**
 * Validate and sanitise a submitted step's fields.
 *
 * Walks the same definitions the renderer used, so a required field the
 * customer saw is always enforced and a value is only ever stored under a key
 * the registry declared. Returns the clean values, the validation errors keyed
 * by field key, and the accepted upload metadata.
 *
 * @param string $slug  services.slug.
 * @param array  $input Raw $_POST.
 * @param array  $files Raw $_FILES.
 * @param array  $alreadyUploaded Records already staged for this draft, keyed
 *                 by the field they arrived on. A required file field is only
 *                 an error when nothing at all has been attached: a second pass
 *                 over a stored draft has no $_FILES to read, and the files it
 *                 is standing in for are listed here.
 * @param string $step  Step identifier, '' to walk every field (the order
 *                      creation re-check, which has the whole draft in hand).
 * @return array array( $values, $errors, $uploads )
 */
function booking_validate_details( $slug, array $input, array $files, array $alreadyUploaded = array(), $step = '' ) {
	$values   = array();
	$errors   = array();
	$uploads  = array();
	$uploadSv = booking_upload_service( $slug );
	$allowed  = get_allowed_mime_types( $uploadSv );
	$maxSize  = get_max_file_size( $uploadSv );

	foreach ( booking_fields_for_step( $slug, $step ) as $field ) {
		$key   = $field['key'];
		$type  = $field['type'];
		$req   = ! empty( $field['required'] );

		// A conditional field the customer cannot see is not required, and its
		// stale value is dropped rather than stored.

		if ( ! booking_field_visible( $field, $input ) ) {
			continue;
		}

		if ( $type === 'file' || $type === 'files' ) {
			$entry = isset( $files[ $key ] ) ? $files[ $key ] : null;
			$got   = booking_collect_uploads( $entry, $field, $allowed, $maxSize, $key, $errors );

			// A field with no new $_FILES is not a field with no file: one may
			// already be staged on the draft. Only when neither is true is the
			// requirement genuinely unmet.

			$stagedCount = 0;

			foreach ( $alreadyUploaded as $uploaded ) {
				if ( isset( $uploaded['field'] ) && $uploaded['field'] === $key ) {
					$stagedCount++;
				}
			}

			if ( $req && ! $got && $stagedCount === 0 ) {
				$errors[ $key ][] = 'Please upload ' . lcfirst( $field['label'] ) . '.';
			}

			$uploads = array_merge( $uploads, $got );
			continue;
		}

		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';

		// A repeated or bracketed name arrives as an array. That is never valid
		// for a scalar field, and casting it would both emit a notice and store
		// the literal string "Array".
		if ( $type !== 'checkbox' && is_array( $raw ) ) {
			$raw = '';
		}

		if ( $type === 'checkbox' ) {
			$chosen = array();

			// Options that only apply to one answer of another field. The browser
			// hides the rest; this stops a hidden option being posted by hand and
			// stored.

			$groupAllowed = null;

			if ( ! empty( $field['option_groups'] ) && ! empty( $field['group_source'] ) ) {
				$groupSource = $field['group_source'];
				$group       = isset( $input[ $groupSource ] ) && ! is_array( $input[ $groupSource ] )
					? clean_text( $input[ $groupSource ] )
					: '';

				if ( $group !== '' && isset( $field['option_groups'][ $group ] ) ) {
					$groupAllowed = array_map( 'strval', (array) $field['option_groups'][ $group ] );
				}
			}

			foreach ( (array) $raw as $item ) {
				$item = clean_text( $item );

				if ( ! isset( $field['options'] ) || ! array_key_exists( $item, $field['options'] ) ) {
					continue;
				}

				if ( $groupAllowed !== null && ! in_array( $item, $groupAllowed, true ) ) {
					continue;
				}

				$chosen[] = $item;
			}

			$values[ $key ] = $chosen;
			continue;
		}

		if ( $type === 'select' || $type === 'radio' ) {
			$raw = clean_text( $raw );

			if ( $raw === '' ) {
				if ( $req ) {
					$errors[ $key ][] = 'Please select ' . lcfirst( $field['label'] ) . '.';
				}
				continue;
			}

			// A value the registry never offered is discarded, not stored.
			if ( ! isset( $field['options'] ) || ! array_key_exists( $raw, $field['options'] ) ) {
				$errors[ $key ][] = 'Please select a valid option for ' . lcfirst( $field['label'] ) . '.';
				continue;
			}

			$values[ $key ] = $raw;
			continue;
		}

		// Scalar text-like fields.
		$clean = $type === 'email' ? sanitize_email( $raw ) : clean_text( $raw );

		if ( $clean === '' ) {
			if ( $req ) {
				$errors[ $key ][] = 'Please enter ' . lcfirst( $field['label'] ) . '.';
			}
			continue;
		}

		if ( $type === 'email' && ! validate_email( $clean ) ) {
			$errors[ $key ][] = 'Please enter a valid email address.';
			continue;
		}

		if ( $type === 'url' && ! filter_var( $clean, FILTER_VALIDATE_URL ) ) {
			$errors[ $key ][] = 'Please enter a valid ' . lcfirst( $field['label'] ) . '.';
			continue;
		}

		if ( $type === 'date' && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $clean ) ) {
			$errors[ $key ][] = 'Please enter a valid date for ' . lcfirst( $field['label'] ) . '.';
			continue;
		}

		if ( $type === 'tel' ) {
			$digits = preg_replace( '/\D+/', '', $clean );
			if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
				$errors[ $key ][] = 'Please enter a valid phone number for ' . lcfirst( $field['label'] ) . '.';
				continue;
			}
			$clean = $digits;
		}

		$values[ $key ] = $clean;
	}

	return array( $values, $errors, $uploads );
}

/**
 * Validate and sanitise the common contact step.
 *
 * @param array $input Raw $_POST.
 * @return array array( $values, $errors )
 */
function booking_validate_contact( array $input ) {
	$values = array();
	$errors = array();

	foreach ( booking_contact_fields() as $field ) {
		$key = $field['key'];
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';

		if ( is_array( $raw ) ) {
			$raw = '';
		}

		if ( $key === 'email' ) {
			$clean = sanitize_email( $raw );
			if ( $clean === '' ) {
				$errors[ $key ][] = 'Please enter your email address.';
			} elseif ( ! validate_email( $clean ) ) {
				$errors[ $key ][] = 'Please enter a valid email address.';
			} else {
				$values[ $key ] = $clean;
			}
			continue;
		}

		$clean = clean_text( $raw );

		if ( $clean === '' ) {
			if ( ! empty( $field['required'] ) ) {
				$errors[ $key ][] = 'Please enter ' . lcfirst( $field['label'] ) . '.';
			}
			continue;
		}

		if ( $key === 'name' ) {
			if ( strlen( $clean ) < 2 ) {
				$errors[ $key ][] = 'Please enter your full name.';
				continue;
			}
		} else {
			$digits = preg_replace( '/\D+/', '', $clean );
			if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
				$errors[ $key ][] = 'Please enter a valid ' . lcfirst( $field['label'] ) . ' number.';
				continue;
			}
			$clean = $digits;
		}

		$values[ $key ] = $clean;
	}

	return array( $values, $errors );
}

/**
 * Flatten keyed validation errors into a list for a summary banner.
 *
 * The keyed form is what lets the renderer mark the offending control; this is
 * for the "please review the following" block above the form, where the order
 * should follow the fields rather than the order they happened to fail in.
 *
 * @param array $errors Errors keyed by field key.
 * @return array List of messages.
 */
function booking_error_messages( array $errors ) {
	$all = array();

	foreach ( $errors as $messages ) {
		foreach ( (array) $messages as $message ) {
			$all[] = $message;
		}
	}

	return array_values( array_unique( $all ) );
}

// Reuses the service pages' own .form-field / .input-icon / .radio-card /
// .checkbox-card markup, all already styled in assets/scss/components/_form.scss.


/**
 * Escape a value for use in an HTML string, and return it.
 *
 * The shared e() in helpers.php echoes and returns nothing, so it is no use for
 * building markup in a function: concatenating it yields empty attributes.
 *
 * @param mixed $value
 * @return string
 */
function booking_esc( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

/**
 * Render one registry field.
 *
 * This is a pure renderer: it draws whatever definition it is handed. Visibility
 * is passed in as $hidden, because a conditional field has to be present in the
 * markup and hidden, not absent. If it were absent, JavaScript could not reveal
 * it when the customer changes the controlling answer, and the customer would
 * have to guess that an invisible answer unlocks a visible question. The
 * validator still treats a hidden field as not submitted, so the server remains
 * the authority on what is required.
 *
 * @param array $field   Field definition.
 * @param mixed $value   Current value, scalar or array for checkboxes.
 * @param array $uploads Accepted uploads, to list files already attached.
 * @param array $errors  Errors keyed by field key.
 * @param bool  $hidden  Render the control hidden (a conditional field whose
 *                       controlling answer does not currently unlock it).
 * @return string HTML.
 */
function booking_render_field( array $field, $value = null, array $uploads = array(), array $errors = array(), $hidden = false ) {
	$key   = $field['key'];
	$type  = $field['type'];
	$label = $field['label'];
	$icon  = isset( $field['icon'] ) ? $field['icon'] : 'pencil';
	$id    = 'bf-' . $key;

	// A hidden conditional field is not required: the validator skips it, and
	// booking-checkout.js puts `required` back when the field is revealed.
	$req = ! empty( $field['required'] ) && ! $hidden;

	// Half-width controls sit in a col-6, everything else spans the row.
	$col = ! empty( $field['half'] ) ? 'col-6' : 'col-12';

	// d-none is the visibility class the service pages already use.
	$colClass = $hidden ? $col . ' d-none' : $col;

	// Carried on the column so booking-checkout.js can show and hide the field
	// and drop `required` when it is hidden again.
	$depAttrs = '';
	if ( ! empty( $field['depends_on'] ) ) {
		$depAttrs = ' data-booking-depends="' . booking_esc( $field['depends_on']['key'] ) . '"'
			. ' data-booking-equals="' . booking_esc( $field['depends_on']['equals'] ) . '"';
	}

	// The flag below lets booking-checkout.js put `required` back on a field
	// the customer has revealed, so the browser validates it too.
	if ( ! empty( $field['depends_on'] ) && ! empty( $field['required'] ) ) {
		$depAttrs .= ' data-booking-required="1"';
	}

	$star = $req ? ' <span class="required-star">*</span>' : '';

	$messages = isset( $errors[ $key ] ) ? (array) $errors[ $key ] : array();
	$errHtml  = $messages
		? '<small class="field-error">' . booking_esc( implode( ' ', $messages ) ) . '</small>'
		: '';
	$helpHtml = ! empty( $field['help'] )
		? '<small class="field-help">' . booking_esc( $field['help'] ) . '</small>'
		: '';

	// Radio and checkbox groups are a label plus a group of cards. The card is
	// the control, so there is no .input-icon wrapper.
	if ( $type === 'radio' || $type === 'checkbox' ) {
		$isRadio = $type === 'radio';
		$chosen  = $isRadio
			? array( (string) $value )
			: array_map( 'strval', (array) $value );

		$cards = '';
		// Only a checkbox group posts an array. A radio must post a scalar name,
		// otherwise the browser does not group the inputs and the validator
		// receives an array where it expects the single chosen value.
		$groupName = $key . ( $isRadio ? '' : '[]' );

		// `cards` styles the group as full selectable cards; the real input stays
		// where it is inside the label, so click and tab behaviour is unchanged.
		// `option_groups` tags each card with the answer it belongs to, which is
		// what booking-checkout.js filters on.

		$cardClass = ! empty( $field['cards'] )
			? ( $isRadio ? 'radio-card' : 'checkbox-card' ) . ' is-card'
			: ( $isRadio ? 'radio-card' : 'checkbox-card' );

		$optionGroup = array();
		if ( ! empty( $field['option_groups'] ) ) {
			foreach ( $field['option_groups'] as $groupValue => $groupOptions ) {
				foreach ( (array) $groupOptions as $member ) {
					$optionGroup[ (string) $member ] = (string) $groupValue;
				}
			}
		}

		foreach ( $field['options'] as $optionValue => $optionLabel ) {
			$checked = in_array( (string) $optionValue, $chosen, true ) ? ' checked' : '';

			$groupAttr = '';
			if ( isset( $optionGroup[ (string) $optionValue ] ) ) {
				$groupAttr = ' data-booking-option-group="' . booking_esc( $optionGroup[ (string) $optionValue ] ) . '"';
			}

			// `required` is left off individual checkboxes: it would demand every
			// box be ticked rather than at least one. The validator enforces it.

			$cards .= '<label class="' . booking_esc( $cardClass ) . '"' . $groupAttr . '>'
				. '<input type="' . $type . '" name="' . booking_esc( $groupName ) . '" value="' . booking_esc( $optionValue ) . '"' . $checked . '> '
				. booking_esc( $optionLabel )
				. '</label>';
		}

		// The group the options are filtered by, so the script knows which
		// answer to read without having to be told the field names.
		$groupSourceAttr = ! empty( $field['group_source'] )
			? ' data-booking-group-source="' . booking_esc( $field['group_source'] ) . '"'
			: '';

		return '<div class="' . $colClass . '"' . $depAttrs . $groupSourceAttr . '>'
			. '<div class="form-field">'
			. '<label>' . booking_esc( $label ) . $star . '</label>'
			. '<div class="' . ( $isRadio ? 'radio-group' : 'checkbox-group' ) . '">' . $cards . '</div>'
			. $helpHtml . $errHtml
			. '</div></div>';
	}

	$control = '';

	if ( $type === 'select' ) {
		$current = (string) $value;
		$options = '<option value="">Select ...</option>';

		foreach ( $field['options'] as $optionValue => $optionLabel ) {
			$selected = (string) $optionValue === $current ? ' selected' : '';
			$options .= '<option value="' . booking_esc( $optionValue ) . '"' . $selected . '>'
				. booking_esc( $optionLabel ) . '</option>';
		}

		$control = '<div class="input-icon has-chevron">'
			. '<i data-lucide="' . booking_esc( $icon ) . '"></i>'
			. '<select id="' . booking_esc( $id ) . '" name="' . booking_esc( $key ) . '"' . ( $req ? ' required' : '' ) . '>'
			. $options . '</select></div>';
	} elseif ( $type === 'textarea' ) {
		$control = '<div class="input-icon textarea-icon">'
			. '<i data-lucide="' . booking_esc( $icon ) . '"></i>'
			. '<textarea id="' . booking_esc( $id ) . '" name="' . booking_esc( $key ) . '"'
			. ( isset( $field['placeholder'] ) ? ' placeholder="' . booking_esc( $field['placeholder'] ) . '"' : '' )
			. ( $req ? ' required' : '' ) . '>'
			. booking_esc( is_scalar( $value ) ? (string) $value : '' )
			. '</textarea></div>';
	} elseif ( $type === 'file' || $type === 'files' ) {
		$multiple = $type === 'files';

		$control = '<div class="input-icon file-icon">'
			. '<i data-lucide="cloud-upload"></i>'
			. '<input id="' . booking_esc( $id ) . '" type="file" name="' . booking_esc( $key ) . ( $multiple ? '[]' : '' ) . '"'
			. ( ! empty( $field['accept'] ) ? ' accept="' . booking_esc( $field['accept'] ) . '"' : '' )
			. ( $multiple ? ' multiple' : '' )
			. ( $req ? ' required' : '' ) . '></div>';

		// Files accepted earlier are already staged, so they are listed back. The
		// input stays empty, which is why a file field must not be re-validated as
		// if nothing had been attached.

		$attached = array();
		foreach ( $uploads as $upload ) {
			if ( isset( $upload['field'] ) && $upload['field'] === $key && ! empty( $upload['name'] ) ) {
				$attached[] = $upload['name'];
			}
		}

		if ( $attached ) {
			$control .= '<ul class="field-files">';
			foreach ( $attached as $name ) {
				$control .= '<li><i data-lucide="paperclip"></i> ' . booking_esc( $name ) . '</li>';
			}
			$control .= '</ul>';
		}

		// A file field that answers to a radio: booking-checkout.js swaps the label
		// and accepted types, falling back to the registry's own. Presentation
		// only - the server never changes what it accepts.

		if ( ! empty( $field['media_source'] ) ) {
			$mediaAttrs = ' data-booking-media-source="' . booking_esc( $field['media_source'] ) . '"'
				. ' data-booking-media-default-label="' . booking_esc( $label ) . '"';

			if ( ! empty( $field['media_labels'] ) ) {
				$mediaAttrs .= ' data-booking-media-labels="'
					. booking_esc( json_encode( $field['media_labels'], JSON_UNESCAPED_UNICODE ) ) . '"';
			}

			if ( ! empty( $field['media_accept'] ) ) {
				$mediaAttrs .= ' data-booking-media-accept="'
					. booking_esc( json_encode( $field['media_accept'], JSON_UNESCAPED_UNICODE ) ) . '"';
			}

			$depAttrs .= $mediaAttrs;
		}
	} else {
		$inputType = in_array( $type, array( 'text', 'email', 'tel', 'url', 'date' ), true ) ? $type : 'text';

		$control = '<div class="input-icon">'
			. '<i data-lucide="' . booking_esc( $icon ) . '"></i>'
			. '<input id="' . booking_esc( $id ) . '" type="' . $inputType . '" name="' . booking_esc( $key ) . '"'
			. ' value="' . booking_esc( is_scalar( $value ) ? (string) $value : '' ) . '"'
			. ( isset( $field['placeholder'] ) ? ' placeholder="' . booking_esc( $field['placeholder'] ) . '"' : '' )
			. ( $req ? ' required' : '' ) . '></div>';
	}

	return '<div class="' . $colClass . '"' . $depAttrs . '>'
		. '<div class="form-field">'
		. '<label for="' . booking_esc( $id ) . '">' . booking_esc( $label ) . $star . '</label>'
		. $control
		. $helpHtml . $errHtml
		. '</div></div>';
}

/**
 * Render every field a service declares for one step, in order.
 *
 * Conditional fields are rendered and hidden rather than skipped, so
 * booking-checkout.js can reveal them when the customer picks the answer that
 * unlocks them. The validator skips what is hidden, so the two agree about what
 * counts as submitted.
 *
 * A field that reacts to another field's answer (`group_source` / `media_source`)
 * also carries that answer forward as a hidden input when the answer was given on
 * an earlier step, so the script can still narrow its options and the upload's
 * accepted types from a page that no longer holds the radio itself.
 *
 * @param string $slug    services.slug.
 * @param array  $values  Current values keyed by field key.
 * @param array  $uploads Accepted uploads.
 * @param array  $errors  Errors keyed by field key.
 * @param string $step    Step identifier, '' for every field.
 * @return string HTML, a .row of fields.
 */
function booking_render_fields( $slug, array $values = array(), array $uploads = array(), array $errors = array(), $step = '' ) {
	$fields = booking_fields_for_step( $slug, $step );
	$keys   = array();

	foreach ( $fields as $field ) {
		$keys[ $field['key'] ] = true;
	}

	$carried    = array();
	$carriedHtml = '';
	$html       = '';

	foreach ( $fields as $field ) {
		$key = $field['key'];

		foreach ( array( 'group_source', 'media_source' ) as $sourceKey ) {
			$source = ! empty( $field[ $sourceKey ] ) ? (string) $field[ $sourceKey ] : '';

			if ( $source === '' || isset( $keys[ $source ] ) || isset( $carried[ $source ] ) ) {
				continue;
			}

			$carried[ $source ] = true;

			$answer = isset( $values[ $source ] ) ? $values[ $source ] : '';

			if ( ! is_scalar( $answer ) || (string) $answer === '' ) {
				continue;
			}

			$carriedHtml .= '<input type="hidden" name="' . booking_esc( $source ) . '" value="'
				. booking_esc( (string) $answer ) . '">';
		}

		$html .= booking_render_field(
			$field,
			isset( $values[ $key ] ) ? $values[ $key ] : null,
			$uploads,
			$errors,
			! booking_field_visible( $field, $values )
		);
	}

	return '<div class="row">' . $carriedHtml . $html . '</div>';
}

/**
 * Render the common contact fields.
 *
 * @param array $values Current values.
 * @param array $errors Errors keyed by field key.
 * @return string HTML, a .row of fields.
 */
function booking_render_contact_fields( array $values = array(), array $errors = array() ) {
	$html = '';

	foreach ( booking_contact_fields() as $field ) {
		$key   = $field['key'];
		$req   = ! empty( $field['required'] );
		$icon  = isset( $field['icon'] ) ? $field['icon'] : 'pencil';
		$id    = 'bc-' . $key;
		$type  = in_array( $field['type'], array( 'text', 'email', 'tel' ), true ) ? $field['type'] : 'text';

		$messages = isset( $errors[ $key ] ) ? (array) $errors[ $key ] : array();
		$errHtml  = $messages
			? '<small class="field-error">' . booking_esc( implode( ' ', $messages ) ) . '</small>'
			: '';
		$helpHtml = ! empty( $field['help'] )
			? '<small class="field-help">' . booking_esc( $field['help'] ) . '</small>'
			: '';

		$html .= '<div class="col-6">'
			. '<div class="form-field">'
			. '<label for="' . booking_esc( $id ) . '">' . booking_esc( $field['label'] )
			. ( $req ? ' <span class="required-star">*</span>' : '' ) . '</label>'
			. '<div class="input-icon">'
			. '<i data-lucide="' . booking_esc( $icon ) . '"></i>'
			. '<input id="' . booking_esc( $id ) . '" type="' . $type . '" name="' . booking_esc( $key ) . '"'
			. ' value="' . booking_esc( isset( $values[ $key ] ) && is_scalar( $values[ $key ] ) ? (string) $values[ $key ] : '' ) . '"'
			. ( isset( $field['autocomplete'] ) ? ' autocomplete="' . booking_esc( $field['autocomplete'] ) . '"' : '' )
			. ( $req ? ' required' : '' ) . '></div>'
			. $helpHtml . $errHtml
			. '</div></div>';
	}

	return '<div class="row">' . $html . '</div>';
}

/**
 * A summary banner for validation errors.
 *
 * @param array $errors Errors keyed by field key.
 * @return string HTML, or an empty string when there is nothing to report.
 */
function booking_render_error_notice( array $errors ) {
	$messages = booking_error_messages( $errors );

	if ( ! $messages ) {
		return '';
	}

	$html = '<div class="notice error"><strong>Please review the following:</strong><ul>';

	foreach ( $messages as $message ) {
		$html .= '<li>' . booking_esc( $message ) . '</li>';
	}

	return $html . '</ul></div>';
}

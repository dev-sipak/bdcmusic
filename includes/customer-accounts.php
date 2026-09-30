<?php
/**
 * Customer account lookup and creation for the booking flow.
 *
 * A customer who buys without an account gets one created automatically, so the
 * order can be tracked from the dashboard. The same person buying again is
 * matched on email or phone and linked to the existing account rather than
 * getting a duplicate.
 */

if ( ! defined( 'CUSTOMER_ACCOUNTS_LOADED' ) ) {
	define( 'CUSTOMER_ACCOUNTS_LOADED', true );
}

if ( ! function_exists( 'customer_normalise_phone' ) ) {
	/**
	 * Strip the characters people type into phone fields.
	 *
	 * @param string $phone
	 * @return string
	 */
	function customer_normalise_phone( $phone ) {
		return preg_replace( '/[\s\-\(\)]/', '', (string) $phone );
	}
}

if ( ! function_exists( 'customer_find_by_contact' ) ) {
	/**
	 * Find an existing customer by email, then by phone.
	 *
	 * Email is unique and reliable, so it wins. Phone is a secondary identity:
	 * it is not unique, so only the first match is used and only when no email
	 * matched.
	 *
	 * @param PDO    $pdo
	 * @param string $email
	 * @param string $phone
	 * @return array|null The users row, or null when there is no match.
	 */
	function customer_find_by_contact( PDO $pdo, $email, $phone = '' ) {
		$email = strtolower( trim( (string) $email ) );
		$phone = customer_normalise_phone( $phone );

		if ( $email !== '' ) {
			$stmt = $pdo->prepare( 'SELECT * FROM users WHERE email = :email LIMIT 1' );
			$stmt->execute( array( ':email' => $email ) );
			$user = $stmt->fetch();

			if ( $user ) {
				return $user;
			}
		}

		if ( $phone !== '' ) {
			$stmt = $pdo->prepare( 'SELECT * FROM users WHERE mobile = :mobile LIMIT 1' );
			$stmt->execute( array( ':mobile' => $phone ) );
			$user = $stmt->fetch();

			if ( $user ) {
				return $user;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'customer_generate_password' ) ) {
	/**
	 * Generate a temporary password that satisfies the signup policy.
	 *
	 * @return string
	 */
	function customer_generate_password() {
		$upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
		$lower  = 'abcdefghijkmnpqrstuvwxyz';
		$digits = '23456789';
		$all    = $upper . $lower . $digits;

		$password = $upper[ random_int( 0, strlen( $upper ) - 1 ) ]
			. $lower[ random_int( 0, strlen( $lower ) - 1 ) ]
			. $digits[ random_int( 0, strlen( $digits ) - 1 ) ];

		for ( $i = 0; $i < 9; $i++ ) {
			$password .= $all[ random_int( 0, strlen( $all ) - 1 ) ];
		}

		return str_shuffle( $password );
	}
}

if ( ! function_exists( 'customer_ensure_from_contact' ) ) {
	/**
	 * Return the account for a contact, creating one when needed.
	 *
	 * @param PDO    $pdo
	 * @param array  $contact name, email, phone.
	 * @param string $source  users.source value for a new account.
	 * @return array|null Keys: id, created, password, name, email. Null when the
	 *                    contact has no usable email.
	 */
	function customer_ensure_from_contact( PDO $pdo, array $contact, $source = 'auto-booking' ) {
		$name  = clean_text( $contact['name'] ?? '' );
		$email = strtolower( sanitize_email( $contact['email'] ?? '' ) );
		$phone = customer_normalise_phone( $contact['phone'] ?? '' );

		if ( $email === '' || ! validate_email( $email ) ) {
			return null;
		}

		$existing = customer_find_by_contact( $pdo, $email, $phone );

		if ( $existing ) {
			return array(
				'id'       => (string) $existing['id'],
				'created'  => false,
				'password' => '',
				'name'     => (string) $existing['name'],
				'email'    => (string) $existing['email'],
			);
		}

		$password   = customer_generate_password();
		$customerId = 'CUST-' . strtoupper( substr( md5( $email . '|' . microtime( true ) ), 0, 8 ) );

		try {
			$stmt = $pdo->prepare(
				'INSERT INTO users (id, name, email, mobile, password_hash, role, created_at, source)
				 VALUES (:id, :name, :email, :mobile, :password_hash, :role, NOW(), :source)'
			);
			$stmt->execute(
				array(
					':id'            => $customerId,
					':name'          => $name !== '' ? $name : 'Customer',
					':email'         => $email,
					':mobile'        => $phone !== '' ? $phone : null,
					':password_hash' => password_hash( $password, PASSWORD_BCRYPT ),
					':role'          => 'customer',
					':source'        => $source,
				)
			);
		} catch ( PDOException $e ) {
			// Another request may have created the same account between the
			// lookup and the insert; the unique email makes that a duplicate key
			// error, so fall back to what is already there.
			$existing = customer_find_by_contact( $pdo, $email, $phone );

			if ( $existing ) {
				return array(
					'id'       => (string) $existing['id'],
					'created'  => false,
					'password' => '',
					'name'     => (string) $existing['name'],
					'email'    => (string) $existing['email'],
				);
			}

			app_log( 'customer-accounts', 'could not create an account for a booking', $e );

			return null;
		}

		return array(
			'id'       => $customerId,
			'created'  => true,
			'password' => $password,
			'name'     => $name,
			'email'    => $email,
		);
	}
}

if ( ! function_exists( 'booking_link_customer' ) ) {
	/**
	 * Attach a booking to a customer account.
	 *
	 * @param PDO    $pdo
	 * @param string $bookingId
	 * @param string $userId
	 */
	function booking_link_customer( PDO $pdo, $bookingId, $userId ) {
		$stmt = $pdo->prepare(
			'UPDATE bookings SET customer_id = :cid, customer_type = :type WHERE booking_id = :bid'
		);
		$stmt->execute(
			array(
				':cid'  => (string) $userId,
				':type' => 'registered',
				':bid'  => (string) $bookingId,
			)
		);
	}
}

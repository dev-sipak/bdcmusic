<?php
/**
 * Customer and admin email notifications.
 *
 * Every message the site sends is built here and delivered through PHP's mail(),
 * so the project keeps working without an SMTP library. None of these helpers
 * throw: a notification must never take down the request that triggered it, so
 * each returns true when the message left and false otherwise.
 */

if ( ! defined( 'NOTIFICATIONS_LOADED' ) ) {
	define( 'NOTIFICATIONS_LOADED', true );
}

if ( ! function_exists( 'notification_admin_email' ) ) {
	/**
	 * The address admin notifications are sent to.
	 *
	 * @return string
	 */
	function notification_admin_email() {
		if ( defined( 'BOOKING_OWNER_EMAIL' ) && BOOKING_OWNER_EMAIL !== '' ) {
			return BOOKING_OWNER_EMAIL;
		}

		return 'bdcmusic37@gmail.com';
	}
}

if ( ! function_exists( 'notification_site_name' ) ) {
	/**
	 * The site name used in subjects and signatures.
	 *
	 * @return string
	 */
	function notification_site_name() {
		return defined( 'SITE_NAME' ) ? SITE_NAME : 'BDC Music Studio';
	}
}

if ( ! function_exists( 'notification_send' ) ) {
	/**
	 * Send one HTML email.
	 *
	 * @param string $to      Recipient address.
	 * @param string $subject Subject line.
	 * @param string $body    Full HTML body.
	 * @return bool Whether mail() accepted the message.
	 */
	function notification_send( $to, $subject, $body ) {
		$to = sanitize_email( (string) $to );

		if ( $to === '' || ! validate_email( $to ) ) {
			app_log( 'notifications', 'skipped email with an invalid recipient: ' . $subject );

			return false;
		}

		$siteName = notification_site_name();
		$from     = notification_admin_email();

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . $siteName . ' <' . $from . '>' . "\r\n";

		$sent = @mail( $to, $subject, $body, $headers );

		if ( ! $sent ) {
			app_log( 'notifications', 'mail() failed for: ' . $subject );
		}

		return (bool) $sent;
	}
}

if ( ! function_exists( 'notification_body' ) ) {
	/**
	 * Wrap content in the shared HTML shell so every email looks the same.
	 *
	 * @param string $heading
	 * @param string $content Inner HTML, already escaped by the caller.
	 * @return string
	 */
	function notification_body( $heading, $content ) {
		$site = htmlspecialchars( notification_site_name() );

		return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333;line-height:1.6;">'
			. '<h2 style="color:#111;margin:0 0 16px;">' . htmlspecialchars( $heading ) . '</h2>'
			. $content
			. '<p style="color:#999;font-size:12px;margin-top:24px;">This message was sent by ' . $site . '. Please do not reply to this email directly.</p>'
			. '</div>';
	}
}

if ( ! function_exists( 'notification_details_table' ) ) {
	/**
	 * Render label/value rows as a small table.
	 *
	 * @param array $rows label => value.
	 * @return string
	 */
	function notification_details_table( array $rows ) {
		$html = '<table style="border-collapse:collapse;width:100%;margin:16px 0;">';

		foreach ( $rows as $label => $value ) {
			if ( $value === '' || $value === null ) {
				continue;
			}

			$html .= '<tr>'
				. '<td style="padding:8px 12px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;width:40%;">' . htmlspecialchars( (string) $label ) . '</td>'
				. '<td style="padding:8px 12px;border:1px solid #e5e7eb;">' . htmlspecialchars( (string) $value ) . '</td>'
				. '</tr>';
		}

		return $html . '</table>';
	}
}

if ( ! function_exists( 'notify_account_created' ) ) {
	/**
	 * Tell a customer their account was created.
	 *
	 * @param string $name     Customer name.
	 * @param string $email    Sign-in email.
	 * @param string $password Temporary password, when one was generated.
	 * @return bool
	 */
	function notify_account_created( $name, $email, $password = '' ) {
		$content = '<p>Hi ' . htmlspecialchars( $name ) . ',</p>'
			. '<p>Your ' . htmlspecialchars( notification_site_name() ) . ' account is ready. You can sign in to track your orders, manage releases and update your details.</p>';

		if ( $password !== '' ) {
			$content .= '<p>Use the temporary details below to sign in, then change your password from your dashboard.</p>'
				. notification_details_table(
					array(
						'Email'    => $email,
						'Password' => $password,
					)
				);
		}

		$content .= '<p><a href="' . htmlspecialchars( url( 'login.php' ) ) . '" style="display:inline-block;padding:10px 18px;background:#111;color:#fff;text-decoration:none;border-radius:6px;">Sign in</a></p>';

		return notification_send(
			$email,
			'Your ' . notification_site_name() . ' account is ready',
			notification_body( 'Welcome to ' . notification_site_name(), $content )
		);
	}
}

if ( ! function_exists( 'notify_admin_new_account' ) ) {
	/**
	 * Tell the admin a new customer account exists.
	 *
	 * @param string $name
	 * @param string $email
	 * @param string $phone
	 * @param string $source How the account was created.
	 * @return bool
	 */
	function notify_admin_new_account( $name, $email, $phone = '', $source = 'website' ) {
		$content = '<p>A new customer account has been created.</p>'
			. notification_details_table(
				array(
					'Name'   => $name,
					'Email'  => $email,
					'Phone'  => $phone,
					'Source' => $source,
				)
			);

		return notification_send(
			notification_admin_email(),
			'New customer account: ' . $name,
			notification_body( 'New customer account', $content )
		);
	}
}

if ( ! function_exists( 'notify_password_changed' ) ) {
	/**
	 * Confirm to a customer that their password changed.
	 *
	 * @param string $name
	 * @param string $email
	 * @return bool
	 */
	function notify_password_changed( $name, $email ) {
		$content = '<p>Hi ' . htmlspecialchars( $name ) . ',</p>'
			. '<p>Your password was changed and any other signed-in devices have been signed out.</p>'
			. '<p>If you did not make this change, please reset your password and contact us immediately.</p>';

		return notification_send(
			$email,
			'Your password was changed',
			notification_body( 'Password updated', $content )
		);
	}
}

if ( ! function_exists( 'notify_admin_password_changed' ) ) {
	/**
	 * Tell the admin a customer changed their password.
	 *
	 * @param string $name
	 * @param string $email
	 * @return bool
	 */
	function notify_admin_password_changed( $name, $email ) {
		$content = '<p>A customer changed their account password.</p>'
			. notification_details_table(
				array(
					'Name'  => $name,
					'Email' => $email,
					'Time'  => date( 'd M Y, H:i' ),
				)
			);

		return notification_send(
			notification_admin_email(),
			'Password changed: ' . $name,
			notification_body( 'Customer password changed', $content )
		);
	}
}

if ( ! function_exists( 'notify_order_placed' ) ) {
	/**
	 * Confirm an order to the customer.
	 *
	 * @param array $order Booking fields: booking_id, name, email, service_name,
	 *                     plan_name, total.
	 * @return bool
	 */
	function notify_order_placed( array $order ) {
		$content = '<p>Hi ' . htmlspecialchars( $order['name'] ) . ',</p>'
			. '<p>Thank you for your order. Here is what we have on record:</p>'
			. notification_details_table(
				array(
					'Order ID' => $order['booking_id'] ?? '',
					'Service'  => $order['service_name'] ?? '',
					'Package'  => $order['plan_name'] ?? '',
					'Amount'   => $order['total'] !== null ? 'Rs. ' . number_format( (float) $order['total'], 2 ) : '',
					'Status'   => $order['status'] ?? '',
				)
			)
			. '<p>We will email you again when the status of your order changes.</p>';

		return notification_send(
			$order['email'],
			'Order received: ' . $order['booking_id'],
			notification_body( 'Your order is confirmed', $content )
		);
	}
}

if ( ! function_exists( 'notify_admin_new_order' ) ) {
	/**
	 * Tell the admin a new order was placed.
	 *
	 * @param array $order Same keys as notify_order_placed(), plus phone.
	 * @return bool
	 */
	function notify_admin_new_order( array $order ) {
		$content = '<p>A new order has been placed.</p>'
			. notification_details_table(
				array(
					'Order ID' => $order['booking_id'] ?? '',
					'Customer' => $order['name'] ?? '',
					'Email'    => $order['email'] ?? '',
					'Phone'    => $order['phone'] ?? '',
					'Service'  => $order['service_name'] ?? '',
					'Package'  => $order['plan_name'] ?? '',
					'Amount'   => $order['total'] !== null ? 'Rs. ' . number_format( (float) $order['total'], 2 ) : '',
					'Status'   => $order['status'] ?? '',
				)
			);

		return notification_send(
			notification_admin_email(),
			'New order: ' . $order['booking_id'] . ' - ' . $order['service_name'],
			notification_body( 'New order placed', $content )
		);
	}
}

if ( ! function_exists( 'notify_order_status_changed' ) ) {
	/**
	 * Tell a customer their order status changed.
	 *
	 * @param array  $order Booking fields: booking_id, name, email, service_name.
	 * @param string $oldStatus
	 * @param string $newStatus
	 * @return bool
	 */
	function notify_order_status_changed( array $order, $oldStatus, $newStatus ) {
		$content = '<p>Hi ' . htmlspecialchars( $order['name'] ) . ',</p>'
			. '<p>The status of your order has been updated.</p>'
			. notification_details_table(
				array(
					'Order ID'      => $order['booking_id'] ?? '',
					'Service'       => $order['service_name'] ?? '',
					'Previous'      => ucfirst( (string) $oldStatus ),
					'New status'    => ucfirst( (string) $newStatus ),
				)
			)
			. '<p>Sign in to your dashboard to see the full details.</p>';

		return notification_send(
			$order['email'],
			'Order ' . $order['booking_id'] . ' is now ' . ucfirst( (string) $newStatus ),
			notification_body( 'Order status updated', $content )
		);
	}
}

if ( ! function_exists( 'notify_admin_order_status_changed' ) ) {
	/**
	 * Tell the admin an order status changed.
	 *
	 * @param array  $order
	 * @param string $oldStatus
	 * @param string $newStatus
	 * @return bool
	 */
	function notify_admin_order_status_changed( array $order, $oldStatus, $newStatus ) {
		$content = '<p>An order status has been updated.</p>'
			. notification_details_table(
				array(
					'Order ID'   => $order['booking_id'] ?? '',
					'Customer'   => $order['name'] ?? '',
					'Service'    => $order['service_name'] ?? '',
					'Previous'   => ucfirst( (string) $oldStatus ),
					'New status' => ucfirst( (string) $newStatus ),
				)
			);

		return notification_send(
			notification_admin_email(),
			'Order ' . $order['booking_id'] . ' status changed to ' . ucfirst( (string) $newStatus ),
			notification_body( 'Order status updated', $content )
		);
	}
}

if ( ! function_exists( 'notify_admin_new_enquiry' ) ) {
	/**
	 * Tell the admin a new enquiry arrived.
	 *
	 * @param array $enquiry Fields: name, email, phone, message, artist_name.
	 * @return bool
	 */
	function notify_admin_new_enquiry( array $enquiry ) {
		$content = '<p>A new enquiry has been submitted.</p>'
			. notification_details_table(
				array(
					'Name'   => $enquiry['name'] ?? '',
					'Email'  => $enquiry['email'] ?? '',
					'Phone'  => $enquiry['phone'] ?? '',
					'Artist' => $enquiry['artist_name'] ?? '',
				)
			)
			. '<p style="background:#f5f5f5;padding:12px;border-radius:8px;">' . nl2br( htmlspecialchars( (string) ( $enquiry['message'] ?? '' ) ) ) . '</p>';

		return notification_send(
			notification_admin_email(),
			'New enquiry from ' . $enquiry['name'],
			notification_body( 'New enquiry', $content )
		);
	}
}

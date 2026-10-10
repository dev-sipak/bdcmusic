<?php
/**
 * Whether an admin has looked at an order yet.
 *
 * `bookings.admin_read_at` is NULL until an admin opens the order in the admin
 * modal, which is what the badge on the Orders sidebar item counts. Keeping this
 * in one place means the sidebar, the badge poller and the order list cannot
 * disagree about what "unread" means.
 *
 * A new column rather than a separate read-tracking table: the only question
 * asked of it is "has this order been seen", which belongs to the order itself,
 * and a nullable column answers it without a second table to keep in step.
 */

/**
 * Orders no admin has opened yet.
 *
 * @param PDO $pdo
 * @return int
 */
function admin_unread_orders_count( PDO $pdo ) {
	return (int) $pdo->query(
		'SELECT COUNT(*) FROM bookings WHERE admin_read_at IS NULL'
	)->fetchColumn();
}

/**
 * Record that an admin has opened an order.
 *
 * Only the first viewing is recorded, so the timestamp answers "when was this
 * first noticed" instead of moving every time the order is reopened. The
 * WHERE admin_read_at IS NULL guard is what stops that, and it also keeps the
 * update from touching the row at all once it is set.
 *
 * @param PDO    $pdo
 * @param string $bookingId
 * @return void
 */
function admin_mark_order_read( PDO $pdo, $bookingId ) {
	$stmt = $pdo->prepare(
		'UPDATE bookings SET admin_read_at = NOW()
		 WHERE booking_id = :id AND admin_read_at IS NULL'
	);
	$stmt->execute( array( ':id' => (string) $bookingId ) );
}

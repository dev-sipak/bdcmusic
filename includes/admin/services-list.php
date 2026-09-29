<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header( 'Content-Type: application/json' );

// Role and password-fingerprint are both enforced in one place, so a
// session left over from before a password change cannot call this endpoint.
$pdo = require_api_role( 'admin' );

try {
    $services = $pdo->query(
        'SELECT s.id, s.slug, s.name, s.booking_mode, s.price_note, s.is_active,
                ( SELECT COUNT(*) FROM service_plans p
                   WHERE p.service_id = s.id AND p.is_active = 1 ) AS plan_count
         FROM services s
         ORDER BY s.name'
    )->fetchAll();

    // The package filter needs every group that exists, not only the ones on
    // the page currently loaded, so it comes back with the services.
    $groups = $pdo->query(
        'SELECT service_id, group_key, MIN(group_label) AS group_label, COUNT(*) AS plan_count
         FROM service_plans
         WHERE is_active = 1
         GROUP BY service_id, group_key
         ORDER BY MIN(sort_order), group_key'
    )->fetchAll();

    echo json_encode( [
        'success'  => true,
        'services' => $services,
        'groups'   => $groups,
    ] );
} catch ( Throwable $e ) {
    app_log( 'services-list', 'request failed', $e );
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'The services could not be loaded. Please try again.' ] );
}

<?php
require_once __DIR__ . '/../../includes/config.php';

$pageTitle       = isset( $pageTitle ) ? $pageTitle : 'Admin Dashboard - BDC Music Studio';
$metaDescription = isset( $metaDescription ) ? $metaDescription : 'Admin management panel.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars( $pageTitle ); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars( $metaDescription ); ?>">
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>dist/main.css">
    <link rel="icon" href="<?php echo $assetPath; ?>images/favicon.png" type="image/png">
</head>
<body>

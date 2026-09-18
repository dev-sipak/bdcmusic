<?php
$pageTitle = 'Service Provider Dashboard';
$metaDescription = 'Manage portfolio, availability, assigned orders, earnings, and client communication from your BDC provider dashboard.';
include_once '../header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <span>Service Provider Dashboard</span>
        </div>

        <div class="section-hero">
            <span class="eyebrow">PROVIDER PORTAL</span>
            <h1>Service Provider Dashboard</h1>
            <p>Manage your profile, portfolio, work requests, payout information, and completed projects in one professional workspace.</p>
        </div>

        <div class="section-block">
            <h2>Dashboard Modules</h2>
            <ul class="check-list">
                <li>Provider profile and portfolio</li>
                <li>Availability and skill management</li>
                <li>Assigned orders and delivery tracking</li>
                <li>Earnings and payout reports</li>
            </ul>
        </div>
    </div>
</section>
<?php include_once '../footer.php'; ?>

<?php
$pageTitle = 'Customer Dashboard';
$metaDescription = 'Track orders, manage profile details, download invoices, and review purchased services from your BDC customer dashboard.';
include_once '../header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo $siteUrl; ?>">Home</a>
            <span>/</span>
            <span>Customer Dashboard</span>
        </div>

        <div class="section-hero">
            <span class="eyebrow">CUSTOMER PORTAL</span>
            <h1>Customer Dashboard</h1>
            <p>Manage your profile, view service orders, track progress, and access your downloads from one place.</p>
        </div>

        <div class="section-block">
            <h2>Dashboard Modules</h2>
            <ul class="check-list">
                <li>Profile management</li>
                <li>Order tracking</li>
                <li>Invoice and payment history</li>
                <li>File uploads and project notes</li>
            </ul>
        </div>
    </div>
</section>
<?php include_once '../footer.php'; ?>

<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => 'Services', 'description' => 'Explore the design services offered by ' . ($business['business_name'] ?? 'our studio') . '.', 'canonicalPath' => 'services']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main class="services-page">
        <div class="page-heading"><p class="eyebrow">What we do</p><h1>Services</h1><p>Explore the ways we can help bring your next idea to life.</p></div>
        <?php if ($services === []): ?>
            <div class="service-empty"><p>Our services are being prepared.</p><?php if (isset($publicPages['contact'])): ?><a href="/contact">Get in touch &nearr;</a><?php endif ?></div>
        <?php else: ?>
            <div class="service-list">
                <?php foreach ($services as $service): ?>
                    <a class="service-row" href="/services/<?= esc($service['slug'], 'attr') ?>"><span><?= esc($service['name']) ?></span><small><?= esc($service['summary']) ?></small><span aria-hidden="true">&nearr;</span></a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

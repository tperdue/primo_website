<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Explore the design services offered by <?= esc($business['business_name'] ?? 'our studio', 'attr') ?>.">
    <title>Services | <?= esc($business['business_name'] ?? 'Design studio') ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business]) ?>
    <main class="services-page">
        <div class="page-heading"><p class="eyebrow">What we do</p><h1>Services</h1><p>Explore the ways we can help bring your next idea to life.</p></div>
        <?php if ($services === []): ?>
            <div class="service-empty"><p>Our services are being prepared.</p><?php if (! empty($business['contact_email'])): ?><a href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch &nearr;</a><?php endif ?></div>
        <?php else: ?>
            <div class="service-list">
                <?php foreach ($services as $service): ?>
                    <a class="service-row" href="/services/<?= esc($service['slug'], 'attr') ?>"><span><?= esc($service['name']) ?></span><small><?= esc($service['summary']) ?></small><span aria-hidden="true">&nearr;</span></a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business]) ?>
</body>
</html>

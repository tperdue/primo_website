<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($service['summary'], 'attr') ?>">
    <title><?= esc($service['name']) ?> | <?= esc($business['business_name'] ?? 'Design studio') ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business]) ?>
    <main class="service-detail">
        <a class="back-link" href="/services">&larr; All services</a>
        <div class="service-detail-grid">
            <div><p class="eyebrow">Design service</p><h1><?= esc($service['name']) ?></h1><p class="service-intro"><?= esc($service['summary']) ?></p></div>
            <div class="service-detail-body">
                <div class="service-description"><?= nl2br(esc($service['description'])) ?></div>
                <?php if ($service['show_price'] && $service['starting_price'] !== null): ?><p class="service-price">Starting at <?= esc($service['currency_code']) ?> <?= esc(number_format((float) $service['starting_price'], 2)) ?></p><?php endif ?>
                <?php if (! empty($business['contact_email'])): ?><a class="button button-primary" href="mailto:<?= esc($business['contact_email'], 'attr') ?>?subject=<?= esc(rawurlencode('About ' . $service['name']), 'attr') ?>">Discuss this service <span aria-hidden="true">&nearr;</span></a><?php endif ?>
            </div>
        </div>
    </main>
    <?= view('partials/public_footer', ['business' => $business]) ?>
</body>
</html>

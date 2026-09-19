<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => $service['name'], 'description' => $service['summary'], 'canonicalPath' => 'services/' . $service['slug']]) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main class="service-detail">
        <a class="back-link" href="/services">&larr; All services</a>
        <div class="service-detail-grid">
            <div><p class="eyebrow">Design service</p><h1><?= esc($service['name']) ?></h1><p class="service-intro"><?= esc($service['summary']) ?></p></div>
            <div class="service-detail-body">
                <div class="service-description"><?= nl2br(esc($service['description'])) ?></div>
                <?php if ($service['show_price'] && $service['starting_price'] !== null): ?><p class="service-price">Starting at <?= esc($service['currency_code']) ?> <?= esc(number_format((float) $service['starting_price'], 2)) ?></p><?php endif ?>
                <?php if (isset($publicPages['contact'])): ?><a class="button button-primary" href="/contact">Discuss this service <span aria-hidden="true">&nearr;</span></a><?php endif ?>
            </div>
        </div>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

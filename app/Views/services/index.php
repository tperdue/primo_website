<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => 'Services', 'description' => 'Explore the design services offered by ' . ($business['business_name'] ?? 'our studio') . '.', 'canonicalPath' => 'services']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages, 'quoteCount' => $quoteCount]) ?>
    <main class="services-page">
        <div class="page-heading"><p class="eyebrow">What we do</p><h1>Services</h1><p>Explore the ways we can help bring your next idea to life.</p></div>
        <?php if (session('quoteError')): ?><div class="public-notice public-notice-error" role="alert"><?= esc(session('quoteError')) ?></div><?php endif ?>
        <?php if (session('quoteMessage')): ?><div class="public-notice" role="status"><?= esc(session('quoteMessage')) ?></div><?php endif ?>
        <?php if ($services === []): ?>
            <div class="service-empty"><p>Our services are being prepared.</p><?php if (isset($publicPages['contact'])): ?><a href="/contact">Get in touch &nearr;</a><?php endif ?></div>
        <?php else: ?>
            <div class="service-list">
                <?php foreach ($services as $service): ?>
                    <div class="service-catalog-row"><a class="service-row" href="/services/<?= esc($service['slug'], 'attr') ?>"><span><?= esc($service['name']) ?></span><small><?= esc($service['summary']) ?></small><span aria-hidden="true">&nearr;</span></a><?php if (in_array((int) $service['id'], $quoteServiceIds, true)): ?><a class="service-quote-action is-selected" href="/quote">In quote <span aria-hidden="true">&check;</span></a><?php else: ?><form action="/quote/services/<?= (int) $service['id'] ?>" method="post"><?= csrf_field() ?><button class="service-quote-action" type="submit"><span aria-hidden="true">+</span> Add to quote</button></form><?php endif ?></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

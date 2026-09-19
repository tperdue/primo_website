<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => $service['name'], 'description' => $service['summary'], 'canonicalPath' => 'services/' . $service['slug'], 'openGraphImage' => $service['image_path']]) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main class="service-detail">
        <a class="back-link" href="/services">&larr; All services</a>
        <?php if ($service['image_path'] !== null): ?><figure class="service-detail-image"><img src="<?= esc(base_url($service['image_path']), 'attr') ?>" alt="<?= esc($service['image_alt'], 'attr') ?>"></figure><?php endif ?>
        <div class="service-detail-grid">
            <div><p class="eyebrow">Design service</p><h1><?= esc($service['name']) ?></h1><p class="service-intro"><?= esc($service['summary']) ?></p></div>
            <div class="service-detail-body">
                <div class="service-description"><?= nl2br(esc($service['description'])) ?></div>
                <?php if ($service['show_price'] && $service['starting_price'] !== null): ?><p class="service-price">Starting at <?= esc($service['currency_code']) ?> <?= esc(number_format((float) $service['starting_price'], 2)) ?></p><?php endif ?>
                <?php if (isset($publicPages['contact'])): ?><a class="button button-primary" href="/contact">Discuss this service <span aria-hidden="true">&nearr;</span></a><?php endif ?>
            </div>
        </div>
        <?php if ($relatedProjects !== []): ?><section class="service-related-work" aria-labelledby="service-related-work-title"><div class="featured-work-heading"><div><p class="eyebrow">Related work</p><h2 id="service-related-work-title">See the service in practice.</h2></div><a href="/work">All work &nearr;</a></div><div class="work-grid"><?php foreach ($relatedProjects as $project): ?><a class="work-item" href="/work/<?= esc($project['slug'], 'attr') ?>"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>" loading="lazy"><span class="work-item-meta"><strong><?= esc($project['title']) ?></strong><span><?= esc($project['summary']) ?></span></span></a><?php endforeach ?></div></section><?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => 'Work', 'description' => 'Selected design projects from ' . ($business['business_name'] ?? 'our studio') . '.', 'canonicalPath' => 'work']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main class="work-page">
        <div class="work-page-heading"><p class="eyebrow">Portfolio</p><h1>Selected work</h1><p>Projects shaped by clear ideas and purposeful design.</p></div>
        <?php if ($projects === []): ?>
            <div class="service-empty"><p>Our work is being prepared for the site.</p><?php if (isset($publicPages['contact'])): ?><a href="/contact">Get in touch &nearr;</a><?php endif ?></div>
        <?php else: ?>
            <div class="work-grid">
                <?php foreach ($projects as $project): ?>
                    <a class="work-item" href="/work/<?= esc($project['slug'], 'attr') ?>"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>" loading="lazy"><span class="work-item-meta"><strong><?= esc($project['title']) ?></strong><span><?= esc($project['summary']) ?></span></span></a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Selected design projects from <?= esc($business['business_name'] ?? 'our studio', 'attr') ?>.">
    <title>Work | <?= esc($business['business_name'] ?? 'Design studio') ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business]) ?>
    <main class="work-page">
        <div class="work-page-heading"><p class="eyebrow">Portfolio</p><h1>Selected work</h1><p>Projects shaped by clear ideas and purposeful design.</p></div>
        <?php if ($projects === []): ?>
            <div class="service-empty"><p>Our work is being prepared for the site.</p><?php if (! empty($business['contact_email'])): ?><a href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch &nearr;</a><?php endif ?></div>
        <?php else: ?>
            <div class="work-grid">
                <?php foreach ($projects as $project): ?>
                    <a class="work-item" href="/work/<?= esc($project['slug'], 'attr') ?>"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>" loading="lazy"><span class="work-item-meta"><strong><?= esc($project['title']) ?></strong><span><?= esc($project['summary']) ?></span></span></a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business]) ?>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($project['summary'], 'attr') ?>">
    <title><?= esc($project['title']) ?> | <?= esc($business['business_name'] ?? 'Design studio') ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business]) ?>
    <main class="work-detail">
        <a class="back-link" href="/work">&larr; All work</a>
        <div class="work-detail-heading"><p class="eyebrow">Portfolio project</p><h1><?= esc($project['title']) ?></h1><p><?= esc($project['summary']) ?></p></div>
        <figure class="work-detail-image"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>"><figcaption><?= esc($project['image_alt']) ?></figcaption></figure>
        <div class="work-detail-story"><div class="work-detail-facts"><span>Project</span><strong><?= esc($project['title']) ?></strong><?php if ($project['client_name'] !== null): ?><span>Client</span><strong><?= esc($project['client_name']) ?></strong><?php endif ?><?php if ($project['project_date'] !== null): ?><span>Date</span><strong><?= esc($project['project_date']) ?></strong><?php endif ?></div><div class="work-detail-narrative"><section><h2>The challenge</h2><p><?= nl2br(esc($project['challenge'])) ?></p></section><section><h2>The solution</h2><p><?= nl2br(esc($project['solution'])) ?></p></section></div></div>
    </main>
    <?= view('partials/public_footer', ['business' => $business]) ?>
</body>
</html>

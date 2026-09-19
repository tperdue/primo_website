<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => $project['title'], 'description' => $project['summary'], 'canonicalPath' => 'work/' . $project['slug'], 'openGraphType' => 'article', 'openGraphImage' => $project['image_path']]) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main class="work-detail">
        <a class="back-link" href="/work">&larr; All work</a>
        <div class="work-detail-heading"><p class="eyebrow">Portfolio project</p><h1><?= esc($project['title']) ?></h1><p><?= esc($project['summary']) ?></p></div>
        <figure class="work-detail-image"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>"><figcaption><?= esc($project['image_alt']) ?></figcaption></figure>
        <div class="work-detail-story"><div class="work-detail-facts"><span>Project</span><strong><?= esc($project['title']) ?></strong><?php if ($project['client_name'] !== null): ?><span>Client</span><strong><?= esc($project['client_name']) ?></strong><?php endif ?><?php if ($project['project_date'] !== null): ?><span>Date</span><strong><?= esc($project['project_date']) ?></strong><?php endif ?><?php if ($relatedServices !== []): ?><span>Services</span><div class="work-detail-service-links"><?php foreach ($relatedServices as $service): ?><a href="/services/<?= esc($service['slug'], 'attr') ?>"><?= esc($service['name']) ?></a><?php endforeach ?></div><?php endif ?></div><div class="work-detail-narrative"><section><h2>The challenge</h2><p><?= nl2br(esc($project['challenge'])) ?></p></section><section><h2>The solution</h2><p><?= nl2br(esc($project['solution'])) ?></p></section></div></div>
        <?php if ($gallery !== []): ?><section class="work-detail-gallery" aria-labelledby="project-gallery-title"><div><p class="eyebrow">Project gallery</p><h2 id="project-gallery-title">A closer look.</h2></div><div class="work-gallery-grid"><?php foreach ($gallery as $image): ?><figure><img src="<?= esc(base_url($image['path']), 'attr') ?>" alt="<?= esc($image['alt_text'], 'attr') ?>" loading="lazy"><figcaption><?= esc($image['alt_text']) ?></figcaption></figure><?php endforeach ?></div></section><?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

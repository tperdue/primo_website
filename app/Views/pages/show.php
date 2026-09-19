<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => $page['meta_title'], 'description' => $page['meta_description'], 'canonicalPath' => $page['slug']]) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages, 'quoteCount' => $quoteCount]) ?>
    <main class="content-page">
        <header class="content-page-heading"><p class="eyebrow"><?= esc($page['eyebrow'] ?: 'Studio') ?></p><h1><?= esc($page['title']) ?></h1><p><?= esc($page['summary']) ?></p></header>
        <article class="content-page-body"><?= nl2br(esc($page['body'])) ?></article>
        <?php if ($page['slug'] === 'about' && isset($publicPages['contact'])): ?><a class="button button-primary" href="/contact">Start a conversation <span aria-hidden="true">&nearr;</span></a><?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

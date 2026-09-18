<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($business['description'] ?? '', 'attr') ?>">
    <title><?= esc($business['business_name'] ?? 'Design studio') ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body>
    <header class="site-header">
        <div class="site-header-inner">
            <a class="wordmark" href="/" aria-label="<?= esc(($business['business_name'] ?? 'Design studio') . ' home', 'attr') ?>"><?= esc($business['business_name'] ?? 'Design studio') ?></a>
            <nav class="site-nav" aria-label="Main navigation">
                <a href="#concepts">Concepts</a>
                <a href="#studio">Studio</a>
            </nav>
            <?php if (! empty($business['contact_email'])): ?>
                <a class="nav-action" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch <span aria-hidden="true">&nearr;</span></a>
            <?php endif ?>
            <details class="mobile-menu">
                <summary title="Menu"><span class="visually-hidden">Menu</span><span class="menu-icon" aria-hidden="true"><span></span><span></span><span></span></span></summary>
                <nav aria-label="Mobile navigation">
                    <a href="#concepts">Concepts</a>
                    <a href="#studio">Studio</a>
                    <?php if (! empty($business['contact_email'])): ?>
                        <a class="mobile-menu-action" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch <span aria-hidden="true">&nearr;</span></a>
                    <?php endif ?>
                </nav>
            </details>
        </div>
    </header>
    <main>
        <section class="public-hero" aria-labelledby="hero-title">
            <img class="public-hero-image" src="<?= esc(base_url('assets/images/hero-concepts.png'), 'attr') ?>" alt="Original graphic design concepts across print, packaging, and digital media">
            <div class="public-hero-inner">
                <p class="hero-kicker">Independent graphic design studio</p>
                <h1 id="hero-title"><?= esc($business['business_name'] ?? 'Design studio') ?></h1>
                <p class="hero-tagline"><?= esc($business['tagline'] ?? '') ?></p>
                <div class="hero-actions">
                    <?php if (! empty($business['contact_email'])): ?>
                        <a class="button button-primary" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Start a conversation <span aria-hidden="true">&nearr;</span></a>
                    <?php endif ?>
                    <a class="button button-outline" href="#concepts">Explore concepts <span aria-hidden="true">&searr;</span></a>
                </div>
            </div>
        </section>
        <section class="concept-section" id="concepts" aria-labelledby="concept-title">
            <div class="section-heading">
                <p class="eyebrow">Creative direction</p>
                <h2 id="concept-title">Design, with purpose.</h2>
                <p><?= esc($business['description'] ?? '') ?></p>
            </div>
            <figure class="concept-image">
                <img src="<?= esc(base_url('assets/images/concept-study.png'), 'attr') ?>" alt="Illustrative packaging, poster, and editorial design concepts" loading="lazy">
                <figcaption>Illustrative concept studies, not client work.</figcaption>
            </figure>
        </section>
        <section class="studio-section" id="studio" aria-labelledby="studio-title">
            <div class="studio-section-inner">
                <p class="eyebrow">The studio</p>
                <h2 id="studio-title">Good work starts with a conversation.</h2>
                <?php if (! empty($business['contact_email'])): ?>
                    <a class="button button-dark" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Tell us what you are building <span aria-hidden="true">&nearr;</span></a>
                <?php endif ?>
            </div>
        </section>
    </main>
    <footer class="site-footer"><span><?= esc($business['business_name'] ?? 'Design studio') ?></span><a href="#hero-title">Back to top &uarr;</a></footer>
    <script src="<?= esc(base_url('assets/js/nav.js'), 'attr') ?>" defer></script>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => null, 'description' => $business['description'] ?? '', 'canonicalPath' => '']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages]) ?>
    <main>
        <section class="public-hero" aria-labelledby="hero-title">
            <img class="public-hero-image" src="<?= esc(base_url('assets/images/hero-concepts.png'), 'attr') ?>" alt="Original graphic design concepts across print, packaging, and digital media">
            <div class="public-hero-inner">
                <p class="hero-kicker">Independent graphic design studio</p>
                <h1 id="hero-title"><?= esc($business['business_name'] ?? 'Design studio') ?></h1>
                <p class="hero-tagline"><?= esc($business['tagline'] ?? '') ?></p>
                <div class="hero-actions">
                    <?php if (isset($publicPages['contact'])): ?><a class="button button-primary" href="/contact">Start a conversation <span aria-hidden="true">&nearr;</span></a><?php endif ?>
                    <a class="button button-outline" href="#concepts">Explore concepts <span aria-hidden="true">&searr;</span></a>
                </div>
            </div>
        </section>
        <?php if ($services !== []): ?>
        <section class="home-services" aria-labelledby="home-services-title">
            <div class="home-services-inner">
                <div class="home-services-heading"><div><p class="eyebrow">What we do</p><h2 id="home-services-title">Services shaped around your goals.</h2></div><a href="/services">All services &nearr;</a></div>
                <div class="service-list">
                    <?php foreach ($services as $service): ?>
                        <a class="service-row" href="/services/<?= esc($service['slug'], 'attr') ?>"><span><?= esc($service['name']) ?></span><small><?= esc($service['summary']) ?></small><span aria-hidden="true">&nearr;</span></a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
        <?php endif ?>
        <?php if ($featuredProjects !== []): ?>
        <section class="featured-work" aria-labelledby="featured-work-title">
            <div class="featured-work-inner"><div class="featured-work-heading"><div><p class="eyebrow">Selected work</p><h2 id="featured-work-title">Work worth a closer look.</h2></div><a href="/work">See all work &nearr;</a></div>
                <div class="work-grid">
                    <?php foreach ($featuredProjects as $project): ?>
                    <a class="work-item" href="/work/<?= esc($project['slug'], 'attr') ?>"><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="<?= esc($project['image_alt'], 'attr') ?>" loading="lazy"><span class="work-item-meta"><strong><?= esc($project['title']) ?></strong><span><?= esc($project['summary']) ?></span></span></a>
                    <?php endforeach ?>
                </div>
            </div>
        </section>
        <?php endif ?>
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
                <?php if (isset($publicPages['contact'])): ?><a class="button button-dark" href="/contact">Tell us what you are building <span aria-hidden="true">&nearr;</span></a><?php endif ?>
            </div>
        </section>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

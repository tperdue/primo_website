<header class="site-header">
    <div class="site-header-inner">
        <a class="wordmark" href="/" aria-label="<?= esc(($business['business_name'] ?? 'Design studio') . ' home', 'attr') ?>"><?= esc($business['business_name'] ?? 'Design studio') ?></a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="/services">Services</a>
            <a href="/work">Work</a>
            <a href="/#concepts">Concepts</a>
            <a href="/#studio">Studio</a>
        </nav>
        <?php if (! empty($business['contact_email'])): ?>
            <a class="nav-action" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch <span aria-hidden="true">&nearr;</span></a>
        <?php endif ?>
        <details class="mobile-menu">
            <summary title="Menu"><span class="visually-hidden">Menu</span><span class="menu-icon" aria-hidden="true"><span></span><span></span><span></span></span></summary>
            <nav aria-label="Mobile navigation">
                <a href="/services">Services</a>
                <a href="/work">Work</a>
                <a href="/#concepts">Concepts</a>
                <a href="/#studio">Studio</a>
                <?php if (! empty($business['contact_email'])): ?>
                    <a class="mobile-menu-action" href="mailto:<?= esc($business['contact_email'], 'attr') ?>">Get in touch <span aria-hidden="true">&nearr;</span></a>
                <?php endif ?>
            </nav>
        </details>
    </div>
</header>

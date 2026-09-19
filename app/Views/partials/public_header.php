<header class="site-header">
    <div class="site-header-inner">
        <a class="wordmark" href="/" aria-label="<?= esc(($business['business_name'] ?? 'Design studio') . ' home', 'attr') ?>"><?= esc($business['business_name'] ?? 'Design studio') ?></a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="/services">Services</a>
            <a href="/work">Work</a>
            <?php if (isset($publicPages['about'])): ?><a href="/about">About</a><?php endif ?>
        </nav>
        <?php if (isset($publicPages['contact'])): ?><a class="nav-action" href="/contact">Get in touch <span aria-hidden="true">&nearr;</span></a><?php endif ?>
        <details class="mobile-menu">
            <summary title="Menu"><span class="visually-hidden">Menu</span><span class="menu-icon" aria-hidden="true"><span></span><span></span><span></span></span></summary>
            <nav aria-label="Mobile navigation">
                <a href="/services">Services</a>
                <a href="/work">Work</a>
                <?php if (isset($publicPages['about'])): ?><a href="/about">About</a><?php endif ?>
                <?php if (isset($publicPages['contact'])): ?><a class="mobile-menu-action" href="/contact">Get in touch <span aria-hidden="true">&nearr;</span></a><?php endif ?>
            </nav>
        </details>
    </div>
</header>

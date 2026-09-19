<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle) ?> | Admin</title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body class="admin-page">
<div class="admin-shell">
    <aside class="admin-sidebar" aria-label="Workspace">
        <a class="admin-brand" href="/admin/" aria-label="Workspace overview"><span class="admin-brand-mark" aria-hidden="true">P</span><span><strong>Primo</strong><small>Studio workspace</small></span></a>
        <div class="admin-business" title="<?= esc($businessName, 'attr') ?>"><span class="admin-business-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($businessName, 0, 1))) ?></span><span><strong><?= esc($businessName) ?></strong><small>Business workspace</small></span></div>
        <nav class="admin-side-nav" aria-label="Admin sections">
            <span class="admin-nav-heading">WORKSPACE</span>
            <a href="/admin/" <?= $activeSection === 'overview' ? 'aria-current="page"' : '' ?>>Overview</a>
            <span class="admin-nav-heading">WEBSITE</span>
            <a href="/admin/services" <?= $activeSection === 'services' ? 'aria-current="page"' : '' ?>>Services</a>
            <a href="/admin/portfolio" <?= $activeSection === 'portfolio' ? 'aria-current="page"' : '' ?>>Portfolio</a>
            <span class="admin-nav-heading">CONFIGURATION</span>
            <a href="/admin/settings" <?= $activeSection === 'settings' ? 'aria-current="page"' : '' ?>>Business settings</a>
        </nav>
        <div class="admin-sidebar-footer"><span class="admin-sidebar-dot" aria-hidden="true"></span>Owner workspace</div>
    </aside>
    <div class="admin-content">
        <header class="admin-topbar"><div class="admin-topbar-location"><span>Workspace</span><span aria-hidden="true">/</span><strong><?= esc($pageTitle) ?></strong></div><div class="admin-topbar-actions"><a href="/" target="_blank" rel="noopener">View website <span aria-hidden="true">&nearr;</span></a><form action="<?= esc(url_to('logout'), 'attr') ?>" method="post"><?= csrf_field() ?><button type="submit" class="text-button">Sign out</button></form></div></header>

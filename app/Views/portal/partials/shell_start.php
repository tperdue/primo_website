<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= esc($pageTitle) ?> | <?= esc($businessName) ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">
</head>
<body class="portal-page">
<header class="portal-header">
    <a class="portal-brand" href="/portal"><?= esc($businessName) ?><span>Client portal</span></a>
    <nav aria-label="Client portal"><a href="/portal" <?= $activeSection === 'overview' ? 'aria-current="page"' : '' ?>>Overview</a><a href="/portal/profile" <?= $activeSection === 'profile' ? 'aria-current="page"' : '' ?>>Profile</a></nav>
    <form action="<?= esc(url_to('logout'), 'attr') ?>" method="post"><?= csrf_field() ?><button class="text-button" type="submit">Sign out</button></form>
</header>
<main class="portal-main">

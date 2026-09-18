<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Business settings | Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="admin-page">
    <header class="admin-header">
        <a class="wordmark" href="/">Primo <span class="admin-label">Admin</span></a>
        <nav aria-label="Admin"><a href="/" target="_blank" rel="noopener">View site</a><form action="<?= url_to('logout') ?>" method="post"><?= csrf_field() ?><button type="submit" class="text-button">Sign out</button></form></nav>
    </header>
    <main class="admin-main">
        <div class="admin-heading"><p class="eyebrow">Workspace</p><h1>Business settings</h1><p>These details appear on the public home page.</p></div>
        <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
        <?php if (session('errors')): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
        <form class="settings-form" action="/admin/settings" method="post">
            <?= csrf_field() ?>
            <?php $errors = session('errors') ?? []; ?>
            <div class="form-row"><label for="business_name">Business name</label><input id="business_name" name="business_name" maxlength="120" required value="<?= esc(old('business_name', $business['business_name'] ?? ''), 'attr') ?>"><?php if (isset($errors['business_name'])): ?><p class="field-error"><?= esc($errors['business_name']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="tagline">Tagline</label><input id="tagline" name="tagline" maxlength="180" required value="<?= esc(old('tagline', $business['tagline'] ?? ''), 'attr') ?>"><?php if (isset($errors['tagline'])): ?><p class="field-error"><?= esc($errors['tagline']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000" rows="6" required><?= esc(old('description', $business['description'] ?? '')) ?></textarea><?php if (isset($errors['description'])): ?><p class="field-error"><?= esc($errors['description']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="contact_email">Contact email</label><input id="contact_email" name="contact_email" type="email" maxlength="254" autocomplete="email" value="<?= esc(old('contact_email', $business['contact_email'] ?? ''), 'attr') ?>"><p class="field-help">Shown as a contact link when provided.</p><?php if (isset($errors['contact_email'])): ?><p class="field-error"><?= esc($errors['contact_email']) ?></p><?php endif ?></div>
            <div class="form-actions"><button class="button button-primary" type="submit">Save changes</button></div>
        </form>
    </main>
</body>
</html>

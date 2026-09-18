<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Primo Admin</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="admin-page">
    <header class="admin-header"><a class="wordmark" href="/">Primo <span class="admin-label">Admin</span></a></header>
    <main class="login-main">
        <div class="admin-heading"><p class="eyebrow">Workspace</p><h1>Sign in</h1><p>Manage your studio details.</p></div>
        <?php if (session('error')): ?><div class="notice notice-error" role="alert"><?= esc(session('error')) ?></div><?php endif ?>
        <?php if (session('errors')): ?><div class="notice notice-error" role="alert">Please enter a valid email and password.</div><?php endif ?>
        <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
        <form class="settings-form" action="<?= url_to('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-row"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" required value="<?= esc(old('email'), 'attr') ?>"></div>
            <div class="form-row"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
            <button class="button button-primary" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>

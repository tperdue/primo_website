<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><title>Set up client portal | <?= esc($businessName) ?></title><link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>"></head>
<body class="portal-page portal-auth-page">
<header class="portal-auth-header"><a href="/"><?= esc($businessName) ?></a><span>Client portal</span></header>
<main class="portal-auth-main">
    <div class="portal-auth-intro"><p class="eyebrow">PRIVATE WORKSPACE</p><h1>Set up your account</h1><?php if ($customer): ?><p>Create a password for <strong><?= esc($customer['email']) ?></strong>.</p><?php else: ?><p>This invitation cannot be used.</p><?php endif ?></div>
    <?php if (session('error')): ?><div class="notice notice-error" role="alert"><?= esc(session('error')) ?></div><?php endif ?>
    <?php if (session('errors')): ?><div class="notice notice-error" role="alert"><?php foreach (session('errors') as $error): ?><div><?= esc($error) ?></div><?php endforeach ?></div><?php endif ?>
    <?php if ($isValid): ?><form class="portal-auth-form" method="post" action="/portal/activate/<?= esc($token, 'attr') ?>"><?= csrf_field() ?><div class="form-row"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required><small>Use at least 12 characters and avoid personal details.</small></div><div class="form-row"><label for="password_confirm">Confirm password</label><input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required></div><button class="button button-primary" type="submit">Create account</button></form><?php else: ?><div class="portal-auth-form"><p>The link may have expired, already been used, or been replaced. Ask the studio for a new invitation.</p><a class="button button-secondary" href="/">Return to website</a></div><?php endif ?>
</main>
</body>
</html>

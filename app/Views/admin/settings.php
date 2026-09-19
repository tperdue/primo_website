<?= view('admin/partials/shell_start', ['pageTitle' => 'Business settings', 'activeSection' => 'settings', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <div class="admin-page-heading"><div><p class="admin-kicker">CONFIGURATION</p><h1>Business settings</h1><p>These details appear on your public website.</p></div></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <?php if (session('errors')): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
    <form class="admin-form" action="/admin/settings" method="post">
        <?= csrf_field() ?>
        <?php $errors = session('errors') ?? []; ?>
        <section class="admin-form-section" aria-labelledby="identity-heading"><div class="admin-form-section-heading"><h2 id="identity-heading">Business identity</h2><p>How your studio is presented to visitors.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="business_name">Business name</label><input id="business_name" name="business_name" maxlength="120" required value="<?= esc(old('business_name', $business['business_name'] ?? ''), 'attr') ?>"><?php if (isset($errors['business_name'])): ?><p class="field-error"><?= esc($errors['business_name']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="tagline">Tagline</label><input id="tagline" name="tagline" maxlength="180" required value="<?= esc(old('tagline', $business['tagline'] ?? ''), 'attr') ?>"><?php if (isset($errors['tagline'])): ?><p class="field-error"><?= esc($errors['tagline']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000" rows="5" required><?= esc(old('description', $business['description'] ?? '')) ?></textarea><?php if (isset($errors['description'])): ?><p class="field-error"><?= esc($errors['description']) ?></p><?php endif ?></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="contact-heading"><div class="admin-form-section-heading"><h2 id="contact-heading">Contact</h2><p>Where prospective clients can reach you and where form alerts are delivered.</p></div><div class="admin-form-fields"><div class="form-row"><label for="contact_email">Public contact email</label><input id="contact_email" name="contact_email" type="email" maxlength="254" autocomplete="email" value="<?= esc(old('contact_email', $business['contact_email'] ?? ''), 'attr') ?>"><p class="field-help">Shown publicly on the contact page when provided.</p><?php if (isset($errors['contact_email'])): ?><p class="field-error"><?= esc($errors['contact_email']) ?></p><?php endif ?></div><div class="form-row"><label for="notification_email">Notification email</label><input id="notification_email" name="notification_email" type="email" maxlength="254" value="<?= esc(old('notification_email', $business['notification_email'] ?? ''), 'attr') ?>"><p class="field-help">Receives contact-form alerts. Falls back to the public email when blank.</p><?php if (isset($errors['notification_email'])): ?><p class="field-error"><?= esc($errors['notification_email']) ?></p><?php endif ?></div></div></section>
        <div class="admin-form-actions"><button class="admin-primary-action" type="submit">Save changes</button></div>
    </form>
</main>
<?= view('admin/partials/shell_end') ?>

<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => $page['meta_title'], 'description' => $page['meta_description'], 'canonicalPath' => 'contact']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages, 'quoteCount' => $quoteCount]) ?>
    <main class="contact-page">
        <div class="contact-intro"><p class="eyebrow"><?= esc($page['eyebrow'] ?: 'Contact') ?></p><h1><?= esc($page['title']) ?></h1><p class="contact-summary"><?= esc($page['summary']) ?></p><div class="contact-copy"><?= nl2br(esc($page['body'])) ?></div><?php if (! empty($business['contact_email'])): ?><a class="contact-email" href="mailto:<?= esc($business['contact_email'], 'attr') ?>"><?= esc($business['contact_email']) ?></a><?php endif ?></div>
        <div class="contact-form-wrap">
            <?php $errors = session('errors') ?? []; ?>
            <?php if (session('contact_success')): ?><div class="contact-success" role="status"><strong>Message received.</strong><span>Thanks for reaching out. The studio will follow up soon.</span></div>
            <?php elseif (! empty($rateLimited)): ?><div class="notice notice-error" role="alert">Too many messages were submitted from this connection. Please wait 15 minutes and try again.</div>
            <?php else: ?>
                <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
                <form class="contact-form" action="/contact" method="post">
                    <?= csrf_field() ?>
                    <div class="form-row"><label for="name">Name</label><input id="name" name="name" maxlength="120" autocomplete="name" required value="<?= esc(old('name', ''), 'attr') ?>"><?php if (isset($errors['name'])): ?><p class="field-error"><?= esc($errors['name']) ?></p><?php endif ?></div>
                    <div class="contact-form-grid"><div class="form-row"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="254" autocomplete="email" required value="<?= esc(old('email', ''), 'attr') ?>"><?php if (isset($errors['email'])): ?><p class="field-error"><?= esc($errors['email']) ?></p><?php endif ?></div><div class="form-row"><label for="phone">Phone <span>Optional</span></label><input id="phone" name="phone" type="tel" maxlength="40" autocomplete="tel" value="<?= esc(old('phone', ''), 'attr') ?>"><?php if (isset($errors['phone'])): ?><p class="field-error"><?= esc($errors['phone']) ?></p><?php endif ?></div></div>
                    <div class="form-row"><label for="subject">What can we help with?</label><input id="subject" name="subject" maxlength="160" required value="<?= esc(old('subject', ''), 'attr') ?>"><?php if (isset($errors['subject'])): ?><p class="field-error"><?= esc($errors['subject']) ?></p><?php endif ?></div>
                    <div class="form-row"><label for="message">Project details</label><textarea id="message" name="message" minlength="20" maxlength="5000" rows="8" required><?= esc(old('message', '')) ?></textarea><p class="field-help">Include goals, timing, and any helpful context.</p><?php if (isset($errors['message'])): ?><p class="field-error"><?= esc($errors['message']) ?></p><?php endif ?></div>
                    <button class="button button-primary" type="submit">Send message <span aria-hidden="true">&nearr;</span></button>
                </form>
            <?php endif ?>
        </div>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

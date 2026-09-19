<?= view('admin/partials/shell_start', ['pageTitle' => 'Contact submission', 'activeSection' => 'contacts', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <a class="admin-back-link" href="/admin/contacts">&larr; Contact inbox</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">CONTACT REQUEST</p><h1><?= esc($submission['subject']) ?></h1><p>Received <?= esc($submission['created_at']) ?></p></div></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-contact-detail"><dl><dt>From</dt><dd><?= esc($submission['name']) ?></dd><dt>Email</dt><dd><a href="mailto:<?= esc($submission['email'], 'attr') ?>"><?= esc($submission['email']) ?></a></dd><dt>Phone</dt><dd><?= $submission['phone'] ? esc($submission['phone']) : 'Not provided' ?></dd><dt>Notification</dt><dd><?= esc(str_replace('_', ' ', ucfirst($submission['notification_status']))) ?></dd></dl><section><h2>Message</h2><p><?= nl2br(esc($submission['message'])) ?></p></section></div>
    <form class="admin-contact-status" action="/admin/contacts/<?= (int) $submission['id'] ?>" method="post"><?= csrf_field() ?><div class="form-row"><label for="status">Inbox status</label><select id="status" name="status"><?php foreach (['new' => 'New', 'read' => 'Read', 'closed' => 'Closed'] as $value => $label): ?><option value="<?= $value ?>" <?= $submission['status'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></div><button class="admin-primary-action" type="submit">Update status</button></form>
</main>
<?= view('admin/partials/shell_end') ?>

<?= view('admin/partials/shell_start', ['pageTitle' => 'Contact inbox', 'activeSection' => 'contacts', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">INBOX</p><h1>Contact submissions</h1><p>Review general inquiries sent through the public website.</p></div></div>
    <div class="admin-list-toolbar"><span><?= count($submissions) ?> <?= count($submissions) === 1 ? 'message' : 'messages' ?></span><a href="/contact" target="_blank" rel="noopener">View contact form &nearr;</a></div>
    <?php if ($submissions === []): ?><div class="admin-empty"><h2>No messages yet</h2><p>New contact inquiries will appear here.</p></div><?php else: ?>
    <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">From</th><th scope="col">Subject</th><th scope="col">Status</th><th scope="col">Received</th><th scope="col">Action</th></tr></thead><tbody>
        <?php foreach ($submissions as $submission): ?><tr><td><strong><?= esc($submission['name']) ?></strong><small><?= esc($submission['email']) ?></small></td><td data-label="Subject"><?= esc($submission['subject']) ?></td><td data-label="Status"><span class="admin-status admin-status-<?= esc($submission['status'], 'attr') ?>"><?= esc(ucfirst($submission['status'])) ?></span></td><td data-label="Received"><?= esc(substr($submission['created_at'] ?? '', 0, 16)) ?></td><td data-label="Action"><a href="/admin/contacts/<?= (int) $submission['id'] ?>">Open</a></td></tr><?php endforeach ?>
    </tbody></table></div><?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

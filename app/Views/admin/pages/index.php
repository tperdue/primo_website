<?= view('admin/partials/shell_start', ['pageTitle' => 'Pages', 'activeSection' => 'pages', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE</p><h1>Pages</h1><p>Manage the studio story, contact introduction, and legal content.</p></div></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-list-toolbar"><span><?= count($pages) ?> managed pages</span><a href="/about" target="_blank" rel="noopener">View public pages &nearr;</a></div>
    <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Page</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Updated</th><th scope="col">Action</th></tr></thead><tbody>
        <?php foreach ($pages as $page): ?><tr><td><strong><?= esc($page['title']) ?></strong><small><?= esc($page['summary']) ?></small></td><td data-label="Path">/<?= esc($page['slug']) ?></td><td data-label="Status"><span class="admin-status admin-status-<?= esc($page['status'], 'attr') ?>"><?= esc(ucfirst($page['status'])) ?></span></td><td data-label="Updated"><?= esc(substr($page['updated_at'] ?? '', 0, 10)) ?></td><td data-label="Action"><a href="/admin/pages/<?= (int) $page['id'] ?>/edit">Edit</a></td></tr><?php endforeach ?>
    </tbody></table></div>
</main>
<?= view('admin/partials/shell_end') ?>

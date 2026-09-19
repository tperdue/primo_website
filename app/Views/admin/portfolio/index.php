<?= view('admin/partials/shell_start', ['pageTitle' => 'Portfolio', 'activeSection' => 'portfolio', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE</p><h1>Portfolio</h1><p>Publish the projects that show how your studio works.</p></div><a class="admin-primary-action" href="/admin/portfolio/new">Add project</a></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-list-toolbar"><span><?= count($projects) ?> <?= count($projects) === 1 ? 'project' : 'projects' ?></span><a href="/work" target="_blank" rel="noopener">View public work &nearr;</a></div>
    <?php if ($projects === []): ?>
        <div class="admin-empty"><h2>No portfolio projects yet</h2><p>Start with a project you are ready to share.</p><a href="/admin/portfolio/new">Add your first project &nearr;</a></div>
    <?php else: ?>
        <div class="admin-service-table-wrap"><table class="admin-service-table admin-portfolio-table"><thead><tr><th scope="col">Project</th><th scope="col">Status</th><th scope="col">Featured</th><th scope="col">Updated</th><th scope="col">Action</th></tr></thead><tbody>
            <?php foreach ($projects as $project): ?>
                <tr><td><div class="admin-project-name"><?php if ($project['image_path'] !== null): ?><img src="<?= esc(base_url($project['image_path']), 'attr') ?>" alt="" loading="lazy"><?php else: ?><span class="admin-project-no-image" aria-hidden="true"></span><?php endif ?><span><strong><?= esc($project['title']) ?></strong><small><?= esc($project['summary']) ?></small></span></div></td><td data-label="Status"><span class="admin-status admin-status-<?= esc($project['status'], 'attr') ?>"><?= esc(ucfirst($project['status'])) ?></span></td><td data-label="Featured"><?= $project['is_featured'] ? 'Yes' : 'No' ?></td><td data-label="Updated"><?= esc(substr($project['updated_at'] ?? '', 0, 10)) ?></td><td data-label="Action"><a href="/admin/portfolio/<?= (int) $project['id'] ?>/edit">Edit</a></td></tr>
            <?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

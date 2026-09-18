<?= view('admin/partials/shell_start', ['pageTitle' => 'Overview', 'activeSection' => 'overview', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">OVERVIEW</p><h1>Workspace overview</h1><p>Keep your public catalog current as the rest of the workflow takes shape.</p></div><a class="admin-primary-action" href="/admin/services/new">Add service</a></div>
    <section class="admin-metrics" aria-label="Service summary"><div><span>Total services</span><strong><?= (int) $totalServices ?></strong></div><div><span>Published</span><strong><?= (int) $publishedServices ?></strong></div><div><span>Drafts</span><strong><?= (int) $draftServices ?></strong></div></section>
    <section class="admin-section" aria-labelledby="recent-services-title"><div class="admin-section-heading"><h2 id="recent-services-title">Recent services</h2><a href="/admin/services">Manage services &nearr;</a></div>
        <?php if ($recentServices === []): ?><div class="admin-empty"><h3>No services yet</h3><p>Start with the services you want visitors to browse.</p><a href="/admin/services/new">Add your first service &nearr;</a></div><?php else: ?>
            <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Service</th><th scope="col">Status</th><th scope="col">Updated</th><th scope="col">Action</th></tr></thead><tbody><?php foreach ($recentServices as $service): ?><tr><td><strong><?= esc($service['name']) ?></strong></td><td data-label="Status"><span class="admin-status admin-status-<?= esc($service['status'], 'attr') ?>"><?= esc(ucfirst($service['status'])) ?></span></td><td data-label="Updated"><?= esc(substr($service['updated_at'] ?? '', 0, 10)) ?></td><td data-label="Action"><a href="/admin/services/<?= (int) $service['id'] ?>/edit">Edit</a></td></tr><?php endforeach ?></tbody></table></div>
        <?php endif ?>
    </section>
</main>
<?= view('admin/partials/shell_end') ?>

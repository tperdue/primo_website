<?= view('admin/partials/shell_start', ['pageTitle' => 'Services', 'activeSection' => 'services', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE</p><h1>Services</h1><p>Manage the services visitors can browse and ask about.</p></div><a class="admin-primary-action" href="/admin/services/new">Add service</a></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-list-toolbar"><span><?= count($services) ?> <?= count($services) === 1 ? 'service' : 'services' ?></span><a href="/services" target="_blank" rel="noopener">View public catalog &nearr;</a></div>
    <?php if ($services === []): ?>
        <div class="admin-empty"><h2>No services yet</h2><p>Add a service to begin building your public catalog.</p><a href="/admin/services/new">Create a service &nearr;</a></div>
    <?php else: ?>
        <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Service</th><th scope="col">Status</th><th scope="col">Public price</th><th scope="col">Order</th><th scope="col">Action</th></tr></thead><tbody>
            <?php foreach ($services as $service): ?>
                <tr><td><strong><?= esc($service['name']) ?></strong><small><?= esc($service['summary']) ?></small></td><td data-label="Status"><span class="admin-status admin-status-<?= esc($service['status'], 'attr') ?>"><?= esc(ucfirst($service['status'])) ?></span></td><td data-label="Public price"><?= $service['show_price'] && $service['starting_price'] !== null ? esc($service['currency_code']) . ' ' . esc(number_format((float) $service['starting_price'], 2)) : '&mdash;' ?></td><td data-label="Order"><?= (int) $service['sort_order'] ?></td><td data-label="Action"><a href="/admin/services/<?= (int) $service['id'] ?>/edit">Edit</a></td></tr>
            <?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

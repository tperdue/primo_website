<?= view('admin/partials/shell_start', ['pageTitle' => 'Projects', 'activeSection' => 'projects', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">DELIVERY</p><h1>Projects</h1><p>Track accepted work from deposit through completion.</p></div></div>
    <div class="admin-list-toolbar"><span><?= count($projects) ?> <?= count($projects) === 1 ? 'project' : 'projects' ?></span><a href="/admin/quotes">Review accepted quotes &nearr;</a></div>
    <?php if ($projects === []): ?>
        <div class="admin-empty"><h2>No projects yet</h2><p>Open an accepted quote and convert it when the work is ready to schedule.</p></div>
    <?php else: ?>
        <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Project</th><th scope="col">Customer</th><th scope="col">Status</th><th scope="col">Schedule</th><th scope="col">Quote</th><th scope="col">Action</th></tr></thead><tbody>
            <?php foreach ($projects as $project): ?><tr><td><strong><?= esc($project['name']) ?></strong><small><?= esc($project['project_number']) ?></small></td><td data-label="Customer"><?= esc($project['customer_business_name'] ?: $project['customer_name']) ?></td><td data-label="Status"><span class="admin-status admin-status-<?= esc(str_replace('_', '-', $project['status']), 'attr') ?>"><?= esc($statuses[$project['status']] ?? ucfirst($project['status'])) ?></span></td><td data-label="Schedule"><?= $project['due_date'] ? 'Due ' . esc($project['due_date']) : 'Not scheduled' ?></td><td data-label="Quote"><?= esc($project['quote_number']) ?></td><td data-label="Action"><a href="/admin/projects/<?= (int) $project['id'] ?>/edit">Open</a></td></tr><?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

<?= view('admin/partials/shell_start', ['pageTitle' => 'Quote questions', 'activeSection' => 'questions', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading">
        <div><p class="admin-kicker">QUOTE SETUP</p><h1>Question groups</h1><p>Build reusable intake questions before opening the public quote flow.</p></div>
        <a class="admin-primary-action" href="/admin/question-groups/new">Add group</a>
    </div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-list-toolbar"><span><?= count($groups) ?> <?= count($groups) === 1 ? 'group' : 'groups' ?></span><span>Unassigned groups apply to every quote request.</span></div>
    <?php if ($groups === []): ?>
        <div class="admin-empty"><h2>No question groups yet</h2><p>Start with reusable information you need for every project.</p><a href="/admin/question-groups/new">Create a question group &nearr;</a></div>
    <?php else: ?>
        <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Group</th><th scope="col">Status</th><th scope="col">Questions</th><th scope="col">Applies to</th><th scope="col">Order</th><th scope="col">Action</th></tr></thead><tbody>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td><strong><?= esc($group['name']) ?></strong><small><?= esc($group['description'] ?? 'No description') ?></small></td>
                    <td data-label="Status"><span class="admin-status admin-status-<?= $group['is_active'] ? 'published' : 'archived' ?>"><?= $group['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td data-label="Questions"><?= (int) $group['active_question_count'] ?> active / <?= (int) $group['question_count'] ?> total</td>
                    <td data-label="Applies to"><?= $group['service_count'] ? (int) $group['service_count'] . ' selected ' . ((int) $group['service_count'] === 1 ? 'service' : 'services') : 'Every request' ?></td>
                    <td data-label="Order"><?= (int) $group['sort_order'] ?></td>
                    <td data-label="Action"><a href="/admin/question-groups/<?= (int) $group['id'] ?>/edit">Configure</a></td>
                </tr>
            <?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

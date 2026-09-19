<?= view('admin/partials/shell_start', ['pageTitle' => $group === null ? 'Add question group' : 'Configure group', 'activeSection' => 'questions', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <a class="admin-back-link" href="/admin/question-groups">&larr; Quote questions</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">QUOTE SETUP</p><h1><?= $group === null ? 'Add question group' : esc($group['name']) ?></h1><p>Groups without service assignments appear once on every quote request.</p></div><?php if ($group !== null): ?><a class="admin-primary-action" href="/admin/question-groups/<?= (int) $group['id'] ?>/questions/new">Add question</a><?php endif ?></div>
    <?php $errors = session('errors') ?? []; ?>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
    <form class="admin-form" action="<?= $group === null ? '/admin/question-groups' : '/admin/question-groups/' . (int) $group['id'] ?>" method="post">
        <?= csrf_field() ?>
        <section class="admin-form-section" aria-labelledby="group-details-heading"><div class="admin-form-section-heading"><h2 id="group-details-heading">Group details</h2><p>Name and organize this part of the intake flow.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="name">Group name</label><input id="name" name="name" maxlength="120" required value="<?= esc(old('name', $group['name'] ?? ''), 'attr') ?>"><?php if (isset($errors['name'])): ?><p class="field-error"><?= esc($errors['name']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="description">Introduction</label><textarea id="description" name="description" maxlength="500" rows="3"><?= esc(old('description', $group['description'] ?? '')) ?></textarea><p class="field-help">Shown with the group in the future quote builder.</p><?php if (isset($errors['description'])): ?><p class="field-error"><?= esc($errors['description']) ?></p><?php endif ?></div>
            <div class="form-grid"><div class="form-row"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="9999" step="1" required value="<?= esc(old('sort_order', $group['sort_order'] ?? 0), 'attr') ?>"><?php if (isset($errors['sort_order'])): ?><p class="field-error"><?= esc($errors['sort_order']) ?></p><?php endif ?></div><div class="form-row admin-toggle-row"><label class="checkbox-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" <?= old('is_active', $group['is_active'] ?? 1) ? 'checked' : '' ?>> Active in quote requests</label></div></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="group-services-heading"><div class="admin-form-section-heading"><h2 id="group-services-heading">Service assignment</h2><p>Leave every service clear for a general group, or select the services that need these questions.</p></div><div class="admin-form-fields">
            <?php $oldServiceIds = old('service_ids', $selectedServiceIds); $oldServiceIds = is_array($oldServiceIds) ? array_map('intval', $oldServiceIds) : []; ?>
            <?php if ($services === []): ?><p class="field-help">No services are available yet. This group will be general.</p><?php else: ?><div class="admin-checkbox-list"><?php foreach ($services as $service): ?><label class="checkbox-label"><input type="checkbox" name="service_ids[]" value="<?= (int) $service['id'] ?>" <?= in_array((int) $service['id'], $oldServiceIds, true) ? 'checked' : '' ?>> <span><?= esc($service['name']) ?></span><small><?= esc(ucfirst($service['status'])) ?></small></label><?php endforeach ?></div><?php endif ?>
            <?php if (isset($errors['service_ids'])): ?><p class="field-error"><?= esc($errors['service_ids']) ?></p><?php endif ?>
        </div></section>
        <div class="admin-form-actions"><a href="/admin/question-groups">Cancel</a><button class="admin-primary-action" type="submit">Save group</button></div>
    </form>
    <?php if ($group !== null): ?>
        <section class="admin-question-section" aria-labelledby="group-questions-heading">
            <div class="admin-section-heading"><div><h2 id="group-questions-heading">Questions</h2><p>Inactive questions stay available for historical quote records.</p></div><a href="/admin/question-groups/<?= (int) $group['id'] ?>/questions/new">Add question &nearr;</a></div>
            <?php if ($questions === []): ?><div class="admin-empty"><h3>No questions in this group</h3><p>Add the first field visitors should complete.</p><a href="/admin/question-groups/<?= (int) $group['id'] ?>/questions/new">Add a question &nearr;</a></div><?php else: ?>
                <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Question</th><th scope="col">Type</th><th scope="col">Status</th><th scope="col">Order</th><th scope="col">Action</th></tr></thead><tbody><?php foreach ($questions as $question): ?><tr><td><strong><?= esc($question['label']) ?></strong><small><?= $question['is_required'] ? 'Required' : 'Optional' ?><?= $question['option_count'] ? ' &middot; ' . (int) $question['option_count'] . ' choices' : '' ?></small></td><td data-label="Type"><?= esc($fieldTypes[$question['field_type']] ?? $question['field_type']) ?></td><td data-label="Status"><span class="admin-status admin-status-<?= $question['is_active'] ? 'published' : 'archived' ?>"><?= $question['is_active'] ? 'Active' : 'Inactive' ?></span></td><td data-label="Order"><?= (int) $question['sort_order'] ?></td><td data-label="Action"><a href="/admin/question-groups/<?= (int) $group['id'] ?>/questions/<?= (int) $question['id'] ?>/edit">Edit</a></td></tr><?php endforeach ?></tbody></table></div>
            <?php endif ?>
        </section>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

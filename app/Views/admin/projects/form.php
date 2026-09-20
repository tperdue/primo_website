<?php $errors = session('errors') ?? []; ?>
<?= view('admin/partials/shell_start', ['pageTitle' => 'Edit project', 'activeSection' => 'projects', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <a class="admin-back-link" href="/admin/projects">&larr; Projects</a>
    <div class="admin-page-heading"><div><p class="admin-kicker"><?= esc($project['project_number']) ?></p><h1><?= esc($project['name']) ?></h1><p><a href="/admin/customers/<?= (int) $customer['id'] ?>"><?= esc($customer['business_name'] ?: $customer['name']) ?></a> &middot; <a href="/admin/quotes/<?= (int) $quote['id'] ?>/edit"><?= esc($quote['quote_number']) ?></a></p></div><span class="admin-status admin-status-<?= esc(str_replace('_', '-', $project['status']), 'attr') ?>"><?= esc($statuses[$project['status']] ?? ucfirst($project['status'])) ?></span></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the highlighted project fields.</div><?php endif ?>
    <form class="admin-form" action="/admin/projects/<?= (int) $project['id'] ?>" method="post">
        <?= csrf_field() ?>
        <section class="admin-form-section" aria-labelledby="project-details-heading"><div class="admin-form-section-heading"><h2 id="project-details-heading">Project details</h2><p>The operational record created from the accepted quote.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="project-name">Project name</label><input id="project-name" name="name" maxlength="180" required value="<?= esc(old('name', $project['name']), 'attr') ?>"><?php if (isset($errors['name'])): ?><p class="field-error"><?= esc($errors['name']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="project-status">Status</label><select id="project-status" name="status"><?php foreach ($statuses as $value => $label): ?><option value="<?= esc($value, 'attr') ?>" <?= old('status', $project['status']) === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach ?></select></div>
            <div class="form-grid"><div class="form-row"><label for="start-date">Start date</label><input id="start-date" name="start_date" type="date" value="<?= esc(old('start_date', $project['start_date'] ?? ''), 'attr') ?>"></div><div class="form-row"><label for="due-date">Due date</label><input id="due-date" name="due_date" type="date" value="<?= esc(old('due_date', $project['due_date'] ?? ''), 'attr') ?>"><?php if (isset($errors['due_date'])): ?><p class="field-error"><?= esc($errors['due_date']) ?></p><?php endif ?></div></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="project-notes-heading"><div class="admin-form-section-heading"><h2 id="project-notes-heading">Working notes</h2><p>Keep internal context separate from the update intended for the customer.</p></div><div class="admin-form-fields"><div class="form-row"><label for="project-notes">Private notes</label><textarea id="project-notes" name="notes" maxlength="10000" rows="7"><?= esc(old('notes', $project['notes'] ?? '')) ?></textarea></div><div class="form-row"><label for="customer-update">Customer-visible update</label><textarea id="customer-update" name="customer_update" maxlength="5000" rows="6"><?= esc(old('customer_update', $project['customer_update'] ?? '')) ?></textarea><p class="field-help">Stored now for the later customer portal.</p></div></div></section>
        <div class="admin-form-actions"><button class="admin-primary-action" type="submit">Save project</button></div>
    </form>
</main>
<?= view('admin/partials/shell_end') ?>

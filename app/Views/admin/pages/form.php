<?= view('admin/partials/shell_start', ['pageTitle' => 'Edit page', 'activeSection' => 'pages', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <a class="admin-back-link" href="/admin/pages">&larr; Pages</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE CONTENT</p><h1><?= esc($page['title']) ?></h1><p>Public path: /<?= esc($page['slug']) ?></p></div></div>
    <?php $errors = session('errors') ?? []; ?>
    <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
    <form class="admin-form" action="/admin/pages/<?= (int) $page['id'] ?>" method="post">
        <?= csrf_field() ?>
        <section class="admin-form-section" aria-labelledby="page-copy-heading"><div class="admin-form-section-heading"><h2 id="page-copy-heading">Page content</h2><p>Plain text is formatted safely for the public site.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="title">Title</label><input id="title" name="title" maxlength="160" required value="<?= esc(old('title', $page['title']), 'attr') ?>"><?php if (isset($errors['title'])): ?><p class="field-error"><?= esc($errors['title']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="eyebrow">Section label</label><input id="eyebrow" name="eyebrow" maxlength="80" value="<?= esc(old('eyebrow', $page['eyebrow']), 'attr') ?>"><?php if (isset($errors['eyebrow'])): ?><p class="field-error"><?= esc($errors['eyebrow']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="summary">Introductory summary</label><textarea id="summary" name="summary" maxlength="320" rows="3" required><?= esc(old('summary', $page['summary'])) ?></textarea><?php if (isset($errors['summary'])): ?><p class="field-error"><?= esc($errors['summary']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="body">Body</label><textarea id="body" name="body" maxlength="30000" rows="12" required><?= esc(old('body', $page['body'])) ?></textarea><p class="field-help">Use blank lines to separate paragraphs.</p><?php if (isset($errors['body'])): ?><p class="field-error"><?= esc($errors['body']) ?></p><?php endif ?></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="page-search-heading"><div class="admin-form-section-heading"><h2 id="page-search-heading">Search and publishing</h2><p>Control search presentation and public visibility.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="meta_title">Search title</label><input id="meta_title" name="meta_title" maxlength="160" required value="<?= esc(old('meta_title', $page['meta_title']), 'attr') ?>"><?php if (isset($errors['meta_title'])): ?><p class="field-error"><?= esc($errors['meta_title']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="meta_description">Search description</label><textarea id="meta_description" name="meta_description" maxlength="320" rows="3" required><?= esc(old('meta_description', $page['meta_description'])) ?></textarea><?php if (isset($errors['meta_description'])): ?><p class="field-error"><?= esc($errors['meta_description']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?= old('status', $page['status']) === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= old('status', $page['status']) === 'published' ? 'selected' : '' ?>>Published</option></select><p class="field-help">Legal pages begin as drafts so placeholder language is never presented as approved policy.</p><?php if (isset($errors['status'])): ?><p class="field-error"><?= esc($errors['status']) ?></p><?php endif ?></div>
        </div></section>
        <div class="admin-form-actions"><a href="/admin/pages">Cancel</a><button class="admin-primary-action" type="submit">Save page</button></div>
    </form>
</main>
<?= view('admin/partials/shell_end') ?>

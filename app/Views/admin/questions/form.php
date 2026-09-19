<?= view('admin/partials/shell_start', ['pageTitle' => $question === null ? 'Add question' : 'Edit question', 'activeSection' => 'questions', 'businessName' => $businessName]) ?>
<main class="admin-main">
    <a class="admin-back-link" href="/admin/question-groups/<?= (int) $group['id'] ?>/edit">&larr; <?= esc($group['name']) ?></a>
    <div class="admin-page-heading"><div><p class="admin-kicker">QUOTE QUESTION</p><h1><?= $question === null ? 'Add question' : 'Edit question' ?></h1><p>Configure how this field will appear in the quote builder.</p></div></div>
    <?php $errors = session('errors') ?? []; ?>
    <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the highlighted fields.</div><?php endif ?>
    <form class="admin-form" action="<?= $question === null ? '/admin/question-groups/' . (int) $group['id'] . '/questions' : '/admin/question-groups/' . (int) $group['id'] . '/questions/' . (int) $question['id'] ?>" method="post">
        <?= csrf_field() ?>
        <section class="admin-form-section" aria-labelledby="question-content-heading"><div class="admin-form-section-heading"><h2 id="question-content-heading">Question</h2><p>Use direct language a prospective client can answer quickly.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="label">Question label</label><input id="label" name="label" maxlength="180" required value="<?= esc(old('label', $question['label'] ?? ''), 'attr') ?>"><?php if (isset($errors['label'])): ?><p class="field-error"><?= esc($errors['label']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="field_type">Answer type</label><select id="field_type" name="field_type" required><?php foreach ($fieldTypes as $value => $label): ?><option value="<?= esc($value, 'attr') ?>" <?= old('field_type', $question['field_type'] ?? 'text') === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach ?></select><p class="field-help">File questions are configured here; private upload handling arrives with the quote builder.</p><?php if (isset($errors['field_type'])): ?><p class="field-error"><?= esc($errors['field_type']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="help_text">Help text</label><textarea id="help_text" name="help_text" maxlength="500" rows="3"><?= esc(old('help_text', $question['help_text'] ?? '')) ?></textarea><?php if (isset($errors['help_text'])): ?><p class="field-error"><?= esc($errors['help_text']) ?></p><?php endif ?></div>
            <div class="form-row"><label for="placeholder">Placeholder</label><input id="placeholder" name="placeholder" maxlength="180" value="<?= esc(old('placeholder', $question['placeholder'] ?? ''), 'attr') ?>"><?php if (isset($errors['placeholder'])): ?><p class="field-error"><?= esc($errors['placeholder']) ?></p><?php endif ?></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="question-options-heading"><div class="admin-form-section-heading"><h2 id="question-options-heading">Choices</h2><p>Used only by dropdown, radio, and checkbox fields.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="options">Options</label><textarea id="options" name="options" maxlength="9050" rows="8"><?= esc(old('options', $optionsText)) ?></textarea><p class="field-help">Enter 2-50 distinct choices, one per line.</p><?php if (isset($errors['options'])): ?><p class="field-error"><?= esc($errors['options']) ?></p><?php endif ?></div>
        </div></section>
        <section class="admin-form-section" aria-labelledby="question-behavior-heading"><div class="admin-form-section-heading"><h2 id="question-behavior-heading">Behavior</h2><p>Control validation, visibility, and order.</p></div><div class="admin-form-fields">
            <div class="form-row"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="9999" step="1" required value="<?= esc(old('sort_order', $question['sort_order'] ?? 0), 'attr') ?>"><?php if (isset($errors['sort_order'])): ?><p class="field-error"><?= esc($errors['sort_order']) ?></p><?php endif ?></div>
            <div class="admin-checkbox-list"><label class="checkbox-label"><input type="hidden" name="is_required" value="0"><input type="checkbox" name="is_required" value="1" <?= old('is_required', $question['is_required'] ?? 0) ? 'checked' : '' ?>> Required answer</label><label class="checkbox-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" <?= old('is_active', $question['is_active'] ?? 1) ? 'checked' : '' ?>> Active in quote requests</label></div>
        </div></section>
        <div class="admin-form-actions"><a href="/admin/question-groups/<?= (int) $group['id'] ?>/edit">Cancel</a><button class="admin-primary-action" type="submit">Save question</button></div>
    </form>
</main>
<?= view('admin/partials/shell_end') ?>

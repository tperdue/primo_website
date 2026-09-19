<?= view('admin/partials/shell_start', ['pageTitle' => 'Media library', 'activeSection' => 'media', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">WEBSITE</p><h1>Media library</h1><p>Upload public images once, then reuse them across services and portfolio work.</p></div></div>
    <?php $errors = session('errors') ?? []; ?>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <?php if ($errors !== []): ?><div class="notice notice-error" role="alert">Please review the upload fields.</div><?php endif ?>
    <form class="admin-media-upload" action="/admin/media" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-row"><label for="image">Image</label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required><p class="field-help">JPEG, PNG, or WebP; up to 5 MB and 8000 pixels per side.</p><?php if (isset($errors['image'])): ?><p class="field-error"><?= esc($errors['image']) ?></p><?php endif ?></div>
        <div class="form-row"><label for="alt_text">Image description</label><input id="alt_text" name="alt_text" maxlength="255" required value="<?= esc(old('alt_text', ''), 'attr') ?>"><p class="field-help">Describe the image for visitors using screen readers.</p><?php if (isset($errors['alt_text'])): ?><p class="field-error"><?= esc($errors['alt_text']) ?></p><?php endif ?></div>
        <button class="admin-primary-action" type="submit">Upload image</button>
    </form>
    <div class="admin-list-toolbar"><span><?= count($assets) ?> <?= count($assets) === 1 ? 'image' : 'images' ?></span></div>
    <?php if ($assets === []): ?><div class="admin-empty"><h2>No media yet</h2><p>Upload an image to begin the reusable library.</p></div><?php else: ?><div class="admin-media-grid"><?php foreach ($assets as $asset): ?><a class="admin-media-item" href="/admin/media/<?= (int) $asset['id'] ?>/edit"><img src="<?= esc(base_url($asset['path']), 'attr') ?>" alt=""><span><strong><?= esc($asset['alt_text']) ?></strong><small><?= esc(strtoupper(str_replace('image/', '', $asset['mime_type']))) ?> · <?= esc(number_format($asset['byte_size'] / 1024, 0)) ?> KB · <?= (int) $asset['usage']['total'] ?> uses</small></span></a><?php endforeach ?></div><?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

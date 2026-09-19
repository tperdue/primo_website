<?php
$siteName = $business['business_name'] ?? 'Design studio';
$fullTitle = empty($pageTitle) ? $siteName : $pageTitle . ' | ' . $siteName;
$canonicalUrl = base_url(ltrim($canonicalPath ?? '', '/'));
$description = trim((string) ($description ?? ''));
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= esc($description, 'attr') ?>">
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= esc($canonicalUrl, 'attr') ?>">
<meta property="og:type" content="<?= esc($openGraphType ?? 'website', 'attr') ?>">
<meta property="og:site_name" content="<?= esc($siteName, 'attr') ?>">
<meta property="og:title" content="<?= esc($fullTitle, 'attr') ?>">
<meta property="og:description" content="<?= esc($description, 'attr') ?>">
<meta property="og:url" content="<?= esc($canonicalUrl, 'attr') ?>">
<?php if (! empty($openGraphImage)): ?><meta property="og:image" content="<?= esc(base_url($openGraphImage), 'attr') ?>"><?php endif ?>
<title><?= esc($fullTitle) ?></title>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/app.css'), 'attr') ?>">

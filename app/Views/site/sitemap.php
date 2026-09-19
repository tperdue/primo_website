<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>    <url>
        <loc><?= esc($url['location']) ?></loc>
<?php if ($url['updated_at'] !== null): ?>        <lastmod><?= esc(substr($url['updated_at'], 0, 10)) ?></lastmod>
<?php endif ?>    </url>
<?php endforeach ?></urlset>

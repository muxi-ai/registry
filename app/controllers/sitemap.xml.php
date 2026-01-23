<?php
header('Content-Type: text/xml');

$baseUrl = 'https://registry.muxi.org';
$today = date('Y-m-d');

$users = tiny::db()->query("SELECT registry_username, last_seen_at FROM users ORDER BY registry_username")->fetchAll();

$formations = tiny::db()->query("
    SELECT f.name, f.published_at, f.last_synced_at, u.registry_username
    FROM formations f
    JOIN users u ON f.user_id = u.id
    WHERE f.deleted_at IS NULL AND f.is_public = 1
    ORDER BY u.registry_username, f.name
")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= $baseUrl ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/browse</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $baseUrl ?>/search</loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php foreach ($users as $user): ?>
    <url>
        <loc><?= $baseUrl ?>/@<?= htmlspecialchars($user['registry_username']) ?></loc>
        <lastmod><?= $user['last_seen_at'] ? date('Y-m-d', strtotime($user['last_seen_at'])) : $today ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>
<?php foreach ($formations as $f): ?>
    <url>
        <loc><?= $baseUrl ?>/@<?= htmlspecialchars($f['registry_username']) ?>/<?= htmlspecialchars($f['name']) ?></loc>
        <lastmod><?= $f['last_synced_at'] ? date('Y-m-d', strtotime($f['last_synced_at'])) : ($f['published_at'] ? date('Y-m-d', strtotime($f['published_at'])) : $today) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>
</urlset>


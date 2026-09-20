<?= view('admin/partials/shell_start', ['pageTitle' => 'Customer profile', 'activeSection' => 'customers', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <a class="admin-back-link" href="/admin/customers">&larr; Customers</a>
    <div class="admin-page-heading"><div><p class="admin-kicker">CUSTOMER PROFILE</p><h1><?= esc($customer['business_name'] ?: $customer['name']) ?></h1><p><?= esc($customer['name']) ?> &middot; Customer since <?= esc(substr((string) $customer['created_at'], 0, 10)) ?></p></div><a class="admin-primary-action" href="/admin/customers/<?= (int) $customer['id'] ?>/edit">Edit profile</a></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-customer-summary"><div><span>Quote requests</span><strong><?= count($requests) ?></strong></div><div><span>Quotes</span><strong><?= count($quotes) ?></strong></div><div><span>Accepted quotes</span><strong><?= (int) $acceptedCount ?></strong></div><div><span>Projects</span><strong><?= count($projects) ?></strong></div></div>
    <div class="admin-customer-layout">
        <aside class="admin-customer-profile">
            <section><h2>Contact</h2><dl><dt>Email</dt><dd><a href="mailto:<?= esc($customer['email'], 'attr') ?>"><?= esc($customer['email']) ?></a></dd><dt>Phone</dt><dd><?= $customer['phone'] ? esc($customer['phone']) : '&mdash;' ?></dd></dl></section>
            <section><h2>Address</h2><?php $hasAddress = array_filter([$customer['address_line_1'], $customer['address_line_2'], $customer['city'], $customer['region'], $customer['postal_code'], $customer['country']]); ?><?php if ($hasAddress): ?><address><?= $customer['address_line_1'] ? esc($customer['address_line_1']) . '<br>' : '' ?><?= $customer['address_line_2'] ? esc($customer['address_line_2']) . '<br>' : '' ?><?= esc(implode(', ', array_filter([$customer['city'], $customer['region'], $customer['postal_code']]))) ?><?= $customer['country'] ? '<br>' . esc($customer['country']) : '' ?></address><?php else: ?><p class="admin-muted">No address recorded.</p><?php endif ?></section>
            <section><h2>Internal notes</h2><p><?= $customer['notes'] ? nl2br(esc($customer['notes'])) : '<span class="admin-muted">No internal notes.</span>' ?></p></section>
        </aside>
        <section class="admin-customer-history" aria-labelledby="relationship-history-heading"><div class="admin-section-heading"><div><h2 id="relationship-history-heading">Relationship history</h2><p>Requests, quotes, and projects are ordered by when they entered the workspace.</p></div></div>
            <?php if ($timeline === []): ?><div class="admin-empty"><h3>No relationship activity</h3><p>Linked quote requests, quotes, and projects will appear here.</p></div><?php else: ?><div class="admin-timeline"><?php foreach ($timeline as $entry): ?><article><div class="admin-timeline-marker" aria-hidden="true"></div><div class="admin-timeline-content"><div><span><?= esc(strtoupper($entry['type'])) ?> &middot; <?= esc(substr($entry['date'], 0, 16)) ?></span><span class="admin-status admin-status-<?= esc(str_replace('_', '-', $entry['status']), 'attr') ?>"><?= esc($entry['statusLabel']) ?></span></div><h3><a href="<?= esc($entry['url'], 'attr') ?>"><?= esc($entry['title']) ?></a></h3><p><?= esc($entry['summary']) ?></p></div></article><?php endforeach ?></div><?php endif ?>
        </section>
    </div>
</main>
<?= view('admin/partials/shell_end') ?>

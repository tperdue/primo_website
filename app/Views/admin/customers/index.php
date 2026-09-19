<?= view('admin/partials/shell_start', ['pageTitle' => 'Customers', 'activeSection' => 'customers', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">RELATIONSHIPS</p><h1>Customers</h1><p>Keep contact details, quote activity, and relationship history in one place.</p></div><a class="admin-primary-action" href="/admin/customers/new">Add customer</a></div>
    <?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
    <div class="admin-customer-toolbar">
        <span><?= count($customers) ?> <?= count($customers) === 1 ? 'customer' : 'customers' ?><?= $query !== '' ? ' found' : '' ?></span>
        <form action="/admin/customers" method="get" role="search"><label class="sr-only" for="customer-search">Search customers</label><input id="customer-search" name="q" type="search" maxlength="120" placeholder="Search name, business, email, or phone" value="<?= esc($query, 'attr') ?>"><button type="submit">Search</button><?php if ($query !== ''): ?><a href="/admin/customers">Clear</a><?php endif ?></form>
    </div>
    <?php if ($customers === []): ?>
        <div class="admin-empty"><h2><?= $query === '' ? 'No customers yet' : 'No matching customers' ?></h2><p><?= $query === '' ? 'Customer profiles appear here when you add one or convert a qualified request.' : 'Try a broader name, email, business, or phone search.' ?></p><?php if ($query === ''): ?><a href="/admin/customers/new">Create a customer &nearr;</a><?php endif ?></div>
    <?php else: ?>
        <div class="admin-service-table-wrap"><table class="admin-service-table admin-customer-table"><thead><tr><th scope="col">Customer</th><th scope="col">Contact</th><th scope="col">Requests</th><th scope="col">Quotes</th><th scope="col">Last activity</th><th scope="col">Action</th></tr></thead><tbody>
        <?php foreach ($customers as $customer): ?><tr><td><strong><?= esc($customer['business_name'] ?: $customer['name']) ?></strong><small><?= esc($customer['business_name'] ? $customer['name'] : 'Individual customer') ?></small></td><td data-label="Contact"><a href="mailto:<?= esc($customer['email'], 'attr') ?>"><?= esc($customer['email']) ?></a><?php if ($customer['phone']): ?><small><?= esc($customer['phone']) ?></small><?php endif ?></td><td data-label="Requests"><?= (int) $customer['request_count'] ?></td><td data-label="Quotes"><?= (int) $customer['quote_count'] ?></td><td data-label="Last activity"><?= $customer['last_activity'] !== '' ? esc(substr($customer['last_activity'], 0, 10)) : '&mdash;' ?></td><td data-label="Action"><a href="/admin/customers/<?= (int) $customer['id'] ?>">Open</a></td></tr><?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</main>
<?= view('admin/partials/shell_end') ?>

<?= view('admin/partials/shell_start', ['pageTitle' => 'Overview', 'activeSection' => 'overview', 'businessName' => $businessName]) ?>
<main class="admin-main admin-main-wide">
    <div class="admin-page-heading"><div><p class="admin-kicker">ATTENTION</p><h1>Workspace overview</h1><p>Start with quote, proposal, and inbox work that needs a human decision.</p></div><a class="admin-primary-action" href="/admin/quote-requests">Open quote inbox</a></div>

    <section class="admin-metrics admin-attention-metrics" aria-label="Attention summary">
        <div><span>Open quote requests</span><strong><?= (int) $attentionCounts['openRequests'] ?></strong></div>
        <div><span>Quotes to send</span><strong><?= (int) $attentionCounts['quotesToSend'] ?></strong></div>
        <div><span>Awaiting response</span><strong><?= (int) $attentionCounts['awaitingResponse'] ?></strong></div>
        <div><span>New contacts</span><strong><?= (int) $attentionCounts['newContacts'] ?></strong></div>
    </section>

    <section class="admin-section admin-attention-board" aria-labelledby="attention-board-title">
        <div class="admin-section-heading"><div><h2 id="attention-board-title">Attention queue</h2><p>Prioritized from active requests, deliverable quotes, and unread inquiries.</p></div><a href="/admin/quotes">Manage quotes &nearr;</a></div>
        <?php if ($attentionItems === []): ?>
            <div class="admin-empty"><h2>All clear</h2><p>No quote requests, proposals, or contact messages need attention right now.</p></div>
        <?php else: ?>
            <div class="admin-attention-list">
                <?php foreach ($attentionItems as $item): ?>
                    <a class="admin-attention-item admin-attention-<?= esc($item['priority'], 'attr') ?>" href="<?= esc($item['href'], 'attr') ?>">
                        <span class="admin-attention-priority"><?= esc($item['label']) ?></span>
                        <span><strong><?= esc($item['title']) ?></strong><small><?= esc($item['detail']) ?></small></span>
                        <span class="admin-attention-meta"><?= esc($item['meta']) ?></span>
                        <span class="admin-attention-action"><?= esc($item['action']) ?></span>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </section>

    <?php if ($recentWins !== []): ?>
        <section class="admin-section admin-dashboard-band" aria-labelledby="recent-wins-title">
            <div class="admin-section-heading"><h2 id="recent-wins-title">Recent wins</h2><a href="/admin/quotes">View quotes &nearr;</a></div>
            <div class="admin-service-table-wrap"><table class="admin-service-table"><thead><tr><th scope="col">Quote</th><th scope="col">Customer</th><th scope="col">Total</th><th scope="col">Updated</th><th scope="col">Action</th></tr></thead><tbody>
                <?php foreach ($recentWins as $quote): ?><tr><td><strong><?= esc($quote['quote_number']) ?></strong><small>Accepted</small></td><td data-label="Customer"><?= esc($quote['customer_business_name'] ?: $quote['customer_name']) ?></td><td data-label="Total"><strong><?= esc($quote['currency_code']) ?> <?= esc(number_format((float) $quote['total'], 2)) ?></strong></td><td data-label="Updated"><?= esc(substr($quote['updated_at'] ?? '', 0, 10)) ?></td><td data-label="Action"><a href="/admin/quotes/<?= (int) $quote['id'] ?>/edit">Open</a></td></tr><?php endforeach ?>
            </tbody></table></div>
        </section>
    <?php endif ?>

    <section class="admin-section admin-dashboard-band" aria-labelledby="recent-activity-title">
        <div class="admin-section-heading"><h2 id="recent-activity-title">Recent activity</h2><a href="/admin/contacts">Open contact inbox &nearr;</a></div>
        <div class="admin-dashboard-columns">
            <div class="admin-dashboard-column"><h3>Quote requests</h3><?php if ($recentActivity['requests'] === []): ?><p>No requests yet.</p><?php else: ?><ul><?php foreach ($recentActivity['requests'] as $request): ?><li><a href="/admin/quote-requests/<?= (int) $request['id'] ?>"><strong><?= esc($request['reference_number']) ?></strong><span><?= esc($request['company'] ?: $request['name']) ?></span></a></li><?php endforeach ?></ul><?php endif ?></div>
            <div class="admin-dashboard-column"><h3>Contact messages</h3><?php if ($recentActivity['contacts'] === []): ?><p>No contact messages yet.</p><?php else: ?><ul><?php foreach ($recentActivity['contacts'] as $contact): ?><li><a href="/admin/contacts/<?= (int) $contact['id'] ?>"><strong><?= esc($contact['subject']) ?></strong><span><?= esc($contact['name']) ?></span></a></li><?php endforeach ?></ul><?php endif ?></div>
        </div>
    </section>

    <section class="admin-section admin-dashboard-band" aria-labelledby="catalog-health-title">
        <div class="admin-section-heading"><h2 id="catalog-health-title">Catalog health</h2><a href="/admin/services">Manage services &nearr;</a></div>
        <div class="admin-portfolio-summary"><span><?= (int) $catalogHealth['publishedServices'] ?> published <?= (int) $catalogHealth['publishedServices'] === 1 ? 'service' : 'services' ?></span><span><?= (int) $catalogHealth['draftServices'] ?> service <?= (int) $catalogHealth['draftServices'] === 1 ? 'draft' : 'drafts' ?></span><span><?= (int) $catalogHealth['portfolioPublished'] ?> published work <?= (int) $catalogHealth['portfolioPublished'] === 1 ? 'item' : 'items' ?></span><span><?= (int) $catalogHealth['acceptedQuotes'] ?> accepted <?= (int) $catalogHealth['acceptedQuotes'] === 1 ? 'quote' : 'quotes' ?></span><a href="/admin/portfolio">Manage portfolio &nearr;</a></div>
    </section>
</main>
<?= view('admin/partials/shell_end') ?>

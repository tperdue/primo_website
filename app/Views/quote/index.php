<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => 'Request a quote', 'description' => 'Build a tailored design quote request.', 'canonicalPath' => 'quote']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages, 'quoteCount' => $quoteCount]) ?>
    <main class="quote-page">
        <div class="quote-heading"><p class="eyebrow">Project inquiry</p><h1>Request a quote</h1><p>Select the services you are considering and share enough context for a thoughtful response.</p></div>
        <?php if (session('quoteMessage')): ?><div class="public-notice" role="status"><?= esc(session('quoteMessage')) ?></div><?php endif ?>
        <?php if ($services === []): ?>
            <section class="quote-empty"><h2>Your quote is empty</h2><p>Browse the service catalog and add the work you would like to discuss.</p><a class="button button-primary" href="/services">Explore services <span aria-hidden="true">&nearr;</span></a></section>
        <?php else: ?>
            <form class="quote-builder" action="/quote/save" method="post">
                <?= csrf_field() ?>
                <div class="quote-layout">
                    <aside class="quote-summary" aria-labelledby="quote-summary-title"><div class="quote-summary-heading"><span>YOUR QUOTE</span><strong id="quote-summary-title"><?= count($services) ?> <?= count($services) === 1 ? 'service' : 'services' ?></strong></div><div class="quote-service-list">
                        <?php foreach ($services as $service): ?><article class="quote-service-item"><?php if ($service['image_path']): ?><img src="<?= esc(base_url($service['image_path']), 'attr') ?>" alt=""><?php endif ?><div><h2><?= esc($service['name']) ?></h2><p><?= esc($service['summary']) ?></p><?php if ($service['show_price'] && $service['starting_price'] !== null): ?><small>Starting at <?= esc($service['currency_code']) ?> <?= esc(number_format((float) $service['starting_price'], 2)) ?></small><?php endif ?></div><button type="submit" formaction="/quote/remove/<?= (int) $service['id'] ?>" aria-label="Remove <?= esc($service['name'], 'attr') ?> from quote">Remove</button></article><?php endforeach ?>
                    </div><button class="quote-browse-action" type="submit" name="next" value="services">+ Save and add another service</button></aside>
                    <div class="quote-questions">
                        <?php if ($questionGroups === []): ?><section class="quote-no-questions"><h2>Project details</h2><p>No intake questions are configured for these services yet. Your selected services will remain saved.</p></section><?php else: ?>
                            <?php foreach ($questionGroups as $groupIndex => $group): ?><section class="quote-question-group" aria-labelledby="quote-group-<?= (int) $group['id'] ?>"><div class="quote-group-heading"><span><?= str_pad((string) ($groupIndex + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h2 id="quote-group-<?= (int) $group['id'] ?>"><?= esc($group['name']) ?></h2><?php if ($group['description']): ?><p><?= esc($group['description']) ?></p><?php endif ?></div></div><div class="quote-group-fields"><?php foreach ($group['questions'] as $question): ?><?= view('quote/_question', ['question' => $question, 'answers' => $answers]) ?><?php endforeach ?></div></section><?php endforeach ?>
                        <?php endif ?>
                    </div>
                </div>
                <div class="quote-save-bar"><p>Saving progress does not send a quote request.</p><button class="button button-primary" type="submit" name="next" value="quote">Save progress</button></div>
            </form>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

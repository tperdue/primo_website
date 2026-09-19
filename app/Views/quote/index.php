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
        <?php if (session('quoteError')): ?><div class="public-notice public-notice-error" role="alert"><?= esc(session('quoteError')) ?></div><?php endif ?>
        <?php if (! empty($rateLimited)): ?><div class="public-notice public-notice-error" role="alert">Too many requests were submitted from this connection. Please wait 15 minutes and try again.</div><?php endif ?>
        <?php if ($services === []): ?>
            <section class="quote-empty"><h2>Your quote is empty</h2><p>Browse the service catalog and add the work you would like to discuss.</p><a class="button button-primary" href="/services">Explore services <span aria-hidden="true">&nearr;</span></a></section>
        <?php else: ?>
            <form class="quote-builder" action="/quote/save" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="quote-layout">
                    <aside class="quote-summary" aria-labelledby="quote-summary-title"><div class="quote-summary-heading"><span>YOUR QUOTE</span><strong id="quote-summary-title"><?= count($services) ?> <?= count($services) === 1 ? 'service' : 'services' ?></strong></div><div class="quote-service-list">
                        <?php foreach ($services as $service): ?><article class="quote-service-item"><?php if ($service['image_path']): ?><img src="<?= esc(base_url($service['image_path']), 'attr') ?>" alt=""><?php endif ?><div><h2><?= esc($service['name']) ?></h2><p><?= esc($service['summary']) ?></p><?php if ($service['show_price'] && $service['starting_price'] !== null): ?><small>Starting at <?= esc($service['currency_code']) ?> <?= esc(number_format((float) $service['starting_price'], 2)) ?></small><?php endif ?></div><button type="submit" formaction="/quote/remove/<?= (int) $service['id'] ?>" aria-label="Remove <?= esc($service['name'], 'attr') ?> from quote">Remove</button></article><?php endforeach ?>
                    </div><button class="quote-browse-action" type="submit" name="next" value="services">+ Save and add another service</button></aside>
                    <div class="quote-questions">
                        <?php if ($questionGroups === []): ?><section class="quote-no-questions"><h2>Project details</h2><p>No intake questions are configured for these services yet. Your selected services will remain saved.</p></section><?php else: ?>
                            <?php foreach ($questionGroups as $groupIndex => $group): ?><section class="quote-question-group" aria-labelledby="quote-group-<?= (int) $group['id'] ?>"><div class="quote-group-heading"><span><?= str_pad((string) ($groupIndex + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h2 id="quote-group-<?= (int) $group['id'] ?>"><?= esc($group['name']) ?></h2><?php if ($group['description']): ?><p><?= esc($group['description']) ?></p><?php endif ?></div></div><div class="quote-group-fields"><?php foreach ($group['questions'] as $question): ?><?= view('quote/_question', ['question' => $question, 'answers' => $answers]) ?><?php endforeach ?></div></section><?php endforeach ?>
                        <?php endif ?>
                        <section class="quote-question-group quote-contact-section" aria-labelledby="quote-contact-title"><div class="quote-group-heading"><span><?= str_pad((string) (count($questionGroups) + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h2 id="quote-contact-title">Your project and contact details</h2><p>Tell us where to send the follow-up and give us the bigger picture.</p></div></div><div class="quote-group-fields">
                            <div class="quote-question"><label for="quote-name">Name <span>(required)</span></label><input id="quote-name" name="name" maxlength="120" value="<?= esc(old('name'), 'attr') ?>" autocomplete="name"><?php if (session('errors.name')): ?><p class="field-error" role="alert"><?= esc(session('errors.name')) ?></p><?php endif ?></div>
                            <div class="quote-question"><label for="quote-company">Company</label><input id="quote-company" name="company" maxlength="160" value="<?= esc(old('company'), 'attr') ?>" autocomplete="organization"><?php if (session('errors.company')): ?><p class="field-error" role="alert"><?= esc(session('errors.company')) ?></p><?php endif ?></div>
                            <div class="quote-question"><label for="quote-email">Email <span>(required)</span></label><input id="quote-email" type="email" name="email" maxlength="254" value="<?= esc(old('email'), 'attr') ?>" autocomplete="email"><?php if (session('errors.email')): ?><p class="field-error" role="alert"><?= esc(session('errors.email')) ?></p><?php endif ?></div>
                            <div class="quote-question"><label for="quote-phone">Phone</label><input id="quote-phone" type="tel" name="phone" maxlength="40" value="<?= esc(old('phone'), 'attr') ?>" autocomplete="tel"><?php if (session('errors.phone')): ?><p class="field-error" role="alert"><?= esc(session('errors.phone')) ?></p><?php endif ?></div>
                            <div class="quote-question"><label for="preferred-contact">Preferred contact <span>(required)</span></label><select id="preferred-contact" name="preferred_contact"><?php foreach (['email' => 'Email', 'phone' => 'Phone', 'either' => 'Either'] as $value => $label): ?><option value="<?= $value ?>" <?= old('preferred_contact', 'email') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></div>
                            <div class="quote-question"><label for="completion-date">Desired completion date</label><input id="completion-date" type="date" name="desired_completion_date" value="<?= esc(old('desired_completion_date'), 'attr') ?>"><?php if (session('errors.desired_completion_date')): ?><p class="field-error" role="alert"><?= esc(session('errors.desired_completion_date')) ?></p><?php endif ?></div>
                            <div class="quote-question quote-question-wide"><label for="project-summary">Project summary <span>(required)</span></label><textarea id="project-summary" name="project_summary" rows="6" maxlength="5000" placeholder="What are you creating, who is it for, and what should the finished work accomplish?"><?= esc(old('project_summary')) ?></textarea><?php if (session('errors.project_summary')): ?><p class="field-error" role="alert"><?= esc(session('errors.project_summary')) ?></p><?php endif ?></div>
                            <div class="quote-question"><label for="budget-range">Budget range</label><input id="budget-range" name="budget_range" maxlength="120" value="<?= esc(old('budget_range'), 'attr') ?>" placeholder="For example, $1,500-$3,000"></div>
                            <div class="quote-question quote-question-wide"><label for="additional-notes">Anything else we should know?</label><textarea id="additional-notes" name="additional_notes" rows="4" maxlength="5000"><?= esc(old('additional_notes')) ?></textarea></div>
                            <label class="quote-consent"><input type="checkbox" name="consent" value="1" <?= old('consent') === '1' ? 'checked' : '' ?>><span>I understand this submits a project inquiry, not a final price or acceptance of the project.</span></label><?php if (session('errors.consent')): ?><p class="field-error quote-consent-error" role="alert"><?= esc(session('errors.consent')) ?></p><?php endif ?>
                        </div></section>
                    </div>
                </div>
                <div class="quote-save-bar"><p>Save your progress, or send the completed request for review.</p><div><button class="button button-secondary" type="submit" name="next" value="quote" formaction="/quote/save">Save progress</button><button class="button button-primary" type="submit" formaction="/quote/submit">Submit request</button></div></div>
            </form>
        <?php endif ?>
    </main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

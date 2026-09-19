<!doctype html>
<html lang="en">
<head>
    <?= view('partials/public_meta', ['business' => $business, 'pageTitle' => 'Quote request received', 'description' => 'Your project inquiry has been received.', 'canonicalPath' => 'quote']) ?>
</head>
<body id="top">
    <?= view('partials/public_header', ['business' => $business, 'publicPages' => $publicPages, 'quoteCount' => $quoteCount]) ?>
    <main class="quote-received"><p class="eyebrow">Request received</p><h1>Thank you for the introduction.</h1><p>We have your project details and will review them before following up. This confirmation is not a final quote or acceptance of the project.</p><dl><dt>Reference</dt><dd><?= esc($reference) ?></dd></dl><div><a class="button button-primary" href="/work">View selected work</a><a class="button button-secondary" href="/">Return home</a></div></main>
    <?= view('partials/public_footer', ['business' => $business, 'publicPages' => $publicPages]) ?>
</body>
</html>

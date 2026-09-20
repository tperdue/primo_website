</main>
<footer class="portal-footer"><span><?= esc($businessName) ?></span><?php if ($contactEmail): ?><a href="mailto:<?= esc($contactEmail, 'attr') ?>"><?= esc($contactEmail) ?></a><?php endif ?></footer>
</body>
</html>

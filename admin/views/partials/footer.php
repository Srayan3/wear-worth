        </div>
    </main>
</div>

<script src="<?= admin_asset('assets/js/admin.js') ?>" defer></script>
<?php if (isset($extraScripts)) foreach ($extraScripts as $s): ?>
<script src="<?= admin_asset($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>

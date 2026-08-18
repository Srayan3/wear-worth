<div class="container" style="padding:60px 0 100px; max-width:760px;">
    <span class="eyebrow"><?= e(setting('store_name', 'Atelier')) ?></span>
    <h1 style="font-size:clamp(30px,4vw,46px); margin:10px 0 30px;"><?= e(setting('about_heading', 'Our Story')) ?></h1>
    <div style="font-size:16px; line-height:1.8; color:var(--ink-soft);">
        <?= nl2br(e(setting('about_body', ''))) ?>
    </div>
</div>

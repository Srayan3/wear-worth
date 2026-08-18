<div class="auth-page">
    <h1>Sign In</h1>
    <p class="lead">Track past orders and check out faster next time. Guest checkout is always available too.</p>
    <form method="post" action="<?= url('account/login') ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="input" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="input" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
    <p class="auth-switch">New here? <a href="<?= url('account/register') ?>">Create an account</a> — or <a href="<?= url('shop') ?>">continue as guest</a>.</p>
</div>

<div class="auth-page">
    <h1>Create Account</h1>
    <p class="lead">Optional — you can always check out as a guest instead.</p>
    <form method="post" action="<?= url('account/register') ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="input" required autofocus>
        </div>
        <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="input" required>
        </div>
        <div class="field">
            <label for="email">Email <span style="text-transform:none; font-weight:400;">(optional)</span></label>
            <input type="email" id="email" name="email" class="input">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="input" minlength="6" required>
            <p class="hint">At least 6 characters.</p>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="<?= url('account/login') ?>">Sign in</a></p>
</div>

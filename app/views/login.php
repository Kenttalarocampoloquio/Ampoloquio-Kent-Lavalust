<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>

    <div class="login-wrap">
        <div class="login-card">
            <h1>Welcome back</h1>
            <p class="sub">Sign in to manage your products</p>

            <?php if (!empty($data['error'])): ?>
                <div class="error-banner"><?= html_escape($data['error']) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= site_url('login') ?>">
                <div class="field">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button class="btn btn-primary" type="submit" style="width:100%">Log In</button>
            </form>
        </div>
    </div>

</body>

</html>
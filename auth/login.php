<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/status-message.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-animation"></div>
    <div class="form-container">
        <form class="login-form" action="login_process.php" method="POST">
            <h1>Welcome Back!</h1>
            <p class="subtitle">Log in to access your HealthFirst account.</p>
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="status-message error"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="status-message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <div class="input-group">
                <input type="email" name="email" required>
                <label>Email Address</label>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" required minlength="8">
                <label>Password</label>
            </div>

            <button type="submit">Login</button>
            <p class="bottom-text">Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </div>
</body>
</html>
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="../assets/css/status-message.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-animation"></div>
    <div class="form-container">
        <form class="register-form" action="register_process.php" method="POST">
            <h1>Create Your Account</h1>
            <p class="subtitle">Join HealthFirst today. It's free and only takes a minute.</p>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="error" style="color:red; font-weight:bold; margin-bottom: 15px; text-align:center;">
                    <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <input type="text" name="name" required>
                <label>Full Name</label>
            </div>

            <div class="input-group">
                <input type="email" name="email" required>
                <label>Email Address</label>
            </div>

            <div class="input-group">
                <input type="tel" name="phone" required>
                <label>Phone Number</label>
            </div>

            <div class="input-group">
                <select name="role" required>
                    <option value="" disabled selected></option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Admin</option>
                </select>
                <label>Select Role</label>
            </div>

            <div class="input-group">
                <input type="password" name="password" required minlength="8" title="Password must be at least 8 characters long.">
                <label>Password</label>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" required minlength="8">
                <label>Confirm Password</label>
            </div>

            <button type="submit">Register</button>
            <p class="bottom-text">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>
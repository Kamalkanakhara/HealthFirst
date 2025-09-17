<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - HealthFirst</title>
    <link rel="stylesheet" href="assets/css/contact-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-animation"></div>
    <div class="contact-container">
        <div class="contact-box">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <p class="subtitle">We are here to help. Fill out the form or use our contact details.</p>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>123 HealthFirst Avenue, Wellness City, 12345</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <p>contact@healthfirst.com</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <p>+1 (234) 567-890</p>
                </div>
            </div>
            <div class="contact-form">
                <form action="contact_process.php" method="POST">
                    <h2>Send us a Message</h2>

                    <!-- Display Success/Error Messages -->
                    <?php if (isset($_SESSION['contact_success'])): ?>
                        <div class="status-message success"><?php echo $_SESSION['contact_success']; unset($_SESSION['contact_success']); ?></div>
                    <?php elseif (isset($_SESSION['contact_error'])): ?>
                        <div class="status-message error"><?php echo $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?></div>
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
                        <textarea name="message" rows="5" required></textarea>
                        <label>Your Message</label>
                    </div>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

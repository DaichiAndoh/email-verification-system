<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email Address</title>
</head>
<body>
    <p>Dear [User's Name],</p>
    <p>Thank you for signing up with <strong>[Your Company Name]</strong>.</p>
    <p>To complete your registration and verify your email address, please click the link below within the next hour:</p>
    <p><a href="<?php echo htmlspecialchars($signedURL); ?>" style="color: #1a73e8;">Verify your email address</a></p>
    <p>This link will expire in 1 hour for your security.</p>
    <p>If you did not create an account, you can safely ignore this email.</p>
    <p>Thank you,<br>The [Your Company Name] Team</p>
</body>
</html>

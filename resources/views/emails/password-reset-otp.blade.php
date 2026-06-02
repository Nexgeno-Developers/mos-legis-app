<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <h2 style="color: #dc3545; margin-top: 0;">Password Reset Request</h2>
        
        <p>Hello,</p>
        
        <p>You have requested to reset your password. Please use the following OTP to reset your password:</p>
        
        <div style="background-color: #fff; border: 2px dashed #dc3545; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px;">
            <h1 style="color: #dc3545; font-size: 32px; letter-spacing: 5px; margin: 0;">{{ $otp }}</h1>
        </div>
        
        <p>This OTP will expire in 10 minutes.</p>
        
        <p><strong>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</strong></p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>


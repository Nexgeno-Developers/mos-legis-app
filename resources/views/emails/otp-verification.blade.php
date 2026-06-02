<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 10px;">
        <h2 style="color: #007bff; margin-top: 0;">Email Verification</h2>
        
        <p>Hello,</p>
        
        <p>Thank you for registering with us. Please use the following OTP to verify your email address:</p>
        
        <div style="background-color: #fff; border: 2px dashed #007bff; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px;">
            <h1 style="color: #007bff; font-size: 32px; letter-spacing: 5px; margin: 0;">{{ $otp }}</h1>
        </div>
        
        <p>This OTP will expire in 10 minutes.</p>
        
        <p>If you didn't request this OTP, please ignore this email.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>


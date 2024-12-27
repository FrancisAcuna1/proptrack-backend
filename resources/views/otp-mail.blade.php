<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .otp-code {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            color: #333;
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Change Verification</h2>
        
        <p>You have requested to change your password. Please use the following One-Time Password (OTP) to complete the process:</p>
        
        <div class="otp-code">
        <strong>{{ $otp }}</strong>
        </div>
        
        <p>This OTP is valid for 10 minutes. Do not share this code with anyone.</p>
        
        <p>If you did not request this change, please ignore this email or contact our support team.</p>
        
        <div class="footer">
        <p>© {{ date('Y') }} PropTrack: Integrated Property Management and Tenant Communication System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
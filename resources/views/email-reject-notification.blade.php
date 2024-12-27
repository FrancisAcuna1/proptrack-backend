<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request Rejected</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        
        .header {
            background: #673ab7;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: -2rem;
        }
        
        .container {
            max-width: 650px;
            margin: 2rem auto;
            padding: 3rem;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 4rem;
            position: relative;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .accent-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1a1a1a, #333);
            border-radius: 16px 16px 0 0;
        }
        
        .container .logo {
            display: block;
            width: 140px;
            height: auto;
            margin: 0 auto 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        h1 {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 700;
            letter-spacing: -0.5px;
            position: relative;
            padding-bottom: 1rem;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1a1a1a, #333);
            border-radius: 2px;
        }
        
        .content {
            color: #2c2c2c;
            line-height: 1.8;
            font-size: 16px;
            padding: 0 1.5rem;
        }

        .content p {
            margin-bottom: 1.5rem;
        }

        .greeting {
            font-size: 20px;
            color: #1a1a1a;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .contact-info {
            background: linear-gradient(145deg, #f8f8f8, #ffffff);
            padding: 2rem;
            border-radius: 12px;
            margin-top: 3rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .contact-info p {
            margin: 0.75rem 0;
            color: #444;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-info a {
            color: #1a1a1a;
            text-decoration: none;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding-bottom: 2px;
            transition: border-color 0.3s ease;
        }

        .footer {
            text-align: center;
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0,0,0,0.08);
            color: #666;
            font-size: 14px;
        }

        .company-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
            margin-bottom: 0.1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            margin-top:2rem;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.06), transparent);
            margin: 2.5rem 0;
        }

        @media (max-width: 640px) {
            .container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .content {
                padding: 0;
            }
            
            h1 {
                font-size: 24px;
            }

            .contact-info {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header"></div>
    
    <div class="container">
        <!-- <div class="accent-border"></div> -->
        <img src="https://i.pinimg.com/564x/65/8e/07/658e0702ed408e5f62ec06d754eaa087.jpg" alt="PropTrack Logo" class="logo">
        
        <h1>Maintenance Request Update</h1>
        <div class="status-badge">Request Status: Not Approved</div>
        <div class="divider1"></div>
        <div class="content">
            <p class="greeting">Dear <?php echo $mailData['firstname'] . ' ' . $mailData['lastname']; ?>,</p>
            
            <p style="color:black">We have carefully reviewed your maintenance request and regret to inform you that it has not been approved at this time. We understand this may impact your plans, and we want to ensure you have all the information you need regarding this decision.</p>
            
            <p style="color:black">Our team is available to discuss any questions you may have about this outcome and to explore alternative solutions that might better address your needs.</p>
            
            <div class="divider"></div>
            
            <!-- <div class="contact-info">
                <p style="display:flex; align-items: center;">📧 Email Support: <a href="mailto:FrancisAcuna@yourcompany.com">support@yourcompany.com</a></p>
                <p style="display:flex; align-items: center;">🌐 Support Portal: <a href="#">support.proptrack.com</a></p>
                <p style="display:flex; align-items: center;">⏰ Response Time: Within 24 hours</p>
            </div> -->
        </div>
        
        <div class="footer">
            <p class="company-name">PropTrack</p>
            <p style="color: #1a1a1a; font-size:16px">Integrated Property Management and Tenant Communication System</p>
            <p style="margin-top: 1rem; color: #1a1a1a; opacity: 0.7;">© <?php echo date('Y'); ?> All rights reserved</p>
        </div>
    </div>
</body>
</html>
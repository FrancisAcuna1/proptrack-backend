<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request Approved</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(24, 182, 32);
            --secondary-color: #81C784;
            --accent-color:#E8F5E9;
            --text-primary: #2c3e50;
            --text-secondary: #546e7a;
        }

        body {
            background-color: #ede7f6;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        .header {
            background: #673ab7;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: -2rem;
        }
        .email-container {
            max-width: 700px;
            margin: 3rem auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .top-banner {
            height: 8px;
            background-color: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        .content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 2rem 0;
        }
        .approval-icon-wrapper {
            width: 110px;
            height: 110px;
            /* background: var(--accent-color); */
            border-radius: 50%;
            margin: 2rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
            /* animation: pulse 2s infinite; */
        }

        .approval-icon {
            font-size: 48px;
            color: var(--primary-color);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .header-title {
            color: #263238;
            font-weight: 600;
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .status-badge {
            background: #E8F5E9;
            color: #009688;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .message-body {
            background: #f5f5f5;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem;
        }

        .info-alert {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            margin: 1.5rem 0;
        }

        .steps-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 2rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            padding: 0.8rem;
            background: #f5f5f5;
            border-radius: 8px;
        }

        /* .step-item:hover {
            transform: translateX(5px);
        } */

        .step-icon {
            width: 45px;
            height: 45px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .step-icon i {
            color: var(--primary-color);
        }

        .footer {
            background: #f5f5f5;
            padding: 2rem;
            text-align: center;
            color: var(--text-secondary);
            border-top: 1px solid #eef2f7;
        }

        .contact-support {
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #eef2f7;
        }
        .calendar-image{
            width: 24px; /* Set the desired width */
            height: 24px;
        }
        .company-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
            margin-bottom: 0.1rem;
        }

    </style>
</head>
<body>
    <div class="header"></div>
    
    <div class="container">
        <div class="email-container">
            <!-- Top Gradient Banner -->
            <div class="top-banner"></div>

            <!-- Header Section -->
            <div class="text-center pt-4" style="align-items: center; justify-content: center; text-align: center;">
                <div class="approval-icon-wrapper"  style="aling-items:center">
                    <img src="https://cdn-icons-png.flaticon.com/128/10703/10703030.png" alt="Approved" class="approved-image">
                </div>
                <h1 class="header-title">Maintenance Request Approved</h1>
                <div class="status-badge">
                    <img src="https://cdn-icons-png.flaticon.com/128/17509/17509118.png" alt="Check Icon" width="24" height="24" style="margin-right: 10px">
                    <span style="font-size:18px">Request Approved</span>
                </div>
            </div>

            <!-- Message Body -->
            <div class="message-body" >
                <div style="margin-bottom:50px;">
                    <p class="h5 mb-4" style="font-size:18px; color: black; font-weight:bold;">Dear <?php echo $mailData['firstname'] . ' ' . $mailData['lastname']; ?>,</p>
                    
                    <p class="lead mb-4" style="font-size:17px; color: black;">
                        We're pleased to inform you that your maintenance request has been approved. Our team will proceed with scheduling the necessary work.
                    </p>

                </div>
                <!-- Info Alert -->
                <div class="info-alert">
                    <div style="display: flex; align-items: center; justify-content:center;">
                        <img src="https://cdn-icons-png.flaticon.com/128/16852/16852458.png"  style="margin-right: 8px" alt="info" width="24" height="24" />

                        <p class="mb-0" style="font-size:15px; color:black">Our maintenance team will contact you shortly to arrange a convenient time for the work to be carried out.</p>
                    </div>
                </div>

                <!-- Steps Section -->
                <div class="steps-container">
                    <div style="display:flex; align-items: center; margin-bottom: 10px;" >
                        <img src="https://cdn-icons-png.flaticon.com/128/1423/1423474.png" alt="Calendar Check Icon" width="30" height="30" style="margin-right: 10px;">
                        <span style="font-size:15px; color: black; font-weight: bold">
                        Next Steps
                        </span>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">
                            <img src="https://cdn-icons-png.flaticon.com/128/9485/9485913.png" alt="Calendar Check Icon" width="28" height="28">

                        </div>
                        <span  style="font-size:16px; color:black;">Await scheduling confirmation</span>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">
                            <!-- <i class="fas fa-phone-alt"></i> -->
                            <img src="https://cdn-icons-png.flaticon.com/128/10969/10969201.png" alt="Telephone Icon" width="28" height="28">
                        </div>
                        <span  style="font-size:16px; color:black;">Be available for contact</span>
                    </div>
                    <div class="step-item">
                        <div class="step-icon">
                            <!-- <i class="fas fa-key"></i> -->
                            <img src="https://cdn-icons-png.flaticon.com/128/17982/17982263.png" alt="Key Icon" width="28" height="28">

                        </div>
                        <span style="font-size:16px; color:black;">Ensure access to the property</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="company-name">PropTrack</p>
                <p style="color: #1a1a1a; font-size:16px">Integrated Property Management and Tenant Communication System</p>
                <p class="mt-4 mb-0">© <?php echo date('Y'); ?> All rights reserved.</p>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
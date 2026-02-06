<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Taxi Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 50px;
        }
        
        .login-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .login-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .login-card i {
            font-size: 3em;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .login-card h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }
        
        .login-card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚕 Online Taxi Management System</h1>
            <p>Fast, Reliable, and Convenient Transportation</p>
        </div>
        
        <div class="content">
            <h2 style="text-align: center; color: #333; margin-bottom: 10px;">Welcome! Please Select Your Login</h2>
            
            <div class="login-options">
                <div class="login-card">
                    <div class="icon">👤</div>
                    <h3>Passenger</h3>
                    <p>Book a taxi and manage your rides</p>
                    <a href="passenger/login.php" class="btn">Login</a>
                    <div class="register-link">
                        <a href="passenger/register.php">Register as Passenger</a>
                    </div>
                </div>
                
                <div class="login-card">
                    <div class="icon">🚗</div>
                    <h3>Driver</h3>
                    <p>Manage your assigned rides</p>
                    <a href="driver/login.php" class="btn">Login</a>
                    <div class="register-link">
                        <a href="driver/register.php">Register as Driver</a>
                    </div>
                </div>
                
                <div class="login-card">
                    <div class="icon">⚙️</div>
                    <h3>Admin</h3>
                    <p>Manage the entire system</p>
                    <a href="admin/login.php" class="btn">Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

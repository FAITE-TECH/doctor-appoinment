<?php
// Main Navigation and Testing Hub
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointment System - Testing Hub</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; text-align: center; margin-bottom: 30px; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .section h2 { color: #374151; margin-top: 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; margin: 5px; transition: background 0.3s; }
        .btn:hover { background: #2563eb; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .btn-warning { background: #f59e0b; }
        .btn-warning:hover { background: #d97706; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .status.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status.info { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏥 Doctor Appointment System</h1>
        <p style="text-align: center; color: #6b7280; margin-bottom: 30px;">Testing and Development Hub</p>
        
        <div class="section">
            <h2>🔧 System Tests</h2>
            <p>Run these tests to verify your system is working correctly:</p>
            <a href="test_connection.php" class="btn">Database Connection Test</a>
            <a href="test_auth.php" class="btn btn-success">Authentication Test</a>
        </div>
        
        <div class="section">
            <h2>🌐 Frontend Application</h2>
            <p>Access the main application:</p>
            <a href="Frontend/pages/index.html" class="btn btn-success">Go to Application</a>
            <a href="Frontend/pages/signin.html" class="btn">Sign In</a>
            <a href="Frontend/pages/signup.html" class="btn">Sign Up</a>
        </div>
        
        <div class="section">
            <h2>⚙️ Admin Panel</h2>
            <p>Access the admin panel (requires admin login):</p>
            <a href="Frontend/pages/admin/index.html" class="btn btn-warning">Admin Panel</a>
            <a href="Frontend/pages/admin/login.html" class="btn btn-warning">Admin Login</a>
        </div>
        
        <div class="section">
            <h2>📊 Database Status</h2>
            <div class="status info">
                <strong>Current Setup:</strong> Your database has 4 roles (admin, doctor, patient, staff) but only admin and doctor users are currently populated.
            </div>
            <div class="status success">
                <strong>Next Steps:</strong> Run the Authentication Test to create a test patient user, then try signing in with those credentials.
            </div>
        </div>
        
        <div class="section">
            <h2>🔍 Quick Troubleshooting</h2>
            <ul>
                <li><strong>Database Connection Issues:</strong> Make sure XAMPP MySQL is running</li>
                <li><strong>Sign In Problems:</strong> Run the Authentication Test first to create test users</li>
                <li><strong>Role Issues:</strong> The system uses role-based access with 4 different user types</li>
            </ul>
        </div>
    </div>
</body>
</html>






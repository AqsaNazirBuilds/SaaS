<!DOCTYPE html>
<html>
<head>
    <title>Login - SaaS Project</title>
    <link rel="stylesheet" href="css/aqsa_core_style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 100px; text-align: center;">
        <h2 style="border:none;">Welcome Back</h2>
        <p>Login to manage your business</p>
        
        <form action="core/auth.php" method="POST" style="margin-top: 20px;">
            <input type="email" name="email" placeholder="Email Address" style="width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ddd;" required>
            <input type="password" name="password" placeholder="Password" style="width:100%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ddd;" required>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login Now</button>
        </form>
        
        <p style="margin-top:15px; font-size:14px;">Don't have an account? <a href="modules/tenant/register.php" style="color:var(--accent-orange);">Register Here</a></p>
    </div>
</body>
</html>
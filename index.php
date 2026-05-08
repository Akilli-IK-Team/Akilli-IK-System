<?php
session_start();
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'candidate') {
        header("Location: anasayfa.php");
        exit();
    } else {
        header("Location: anasayfa.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Smart HR Management | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="glass-container">
        <h1>Welcome to IKSystem</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="tab-container">
            <div class="tab active" onclick="switchTab('candidate')">Candidate Login</div>
            <div class="tab" onclick="switchTab('employer')">Employer Login</div>
        </div>

        <form action="islem.php" method="POST" id="loginForm">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="user_type" id="userType" value="candidate">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="example@email.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="link-text">
            Don't have an account? <a href="kayit.php">Register Now</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if (type === 'candidate') {
                document.querySelectorAll('.tab')[0].classList.add('active');
            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
            }
            document.getElementById('userType').value = type;
        }
    </script>
</body>
</html>
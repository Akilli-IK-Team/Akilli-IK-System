<?php
session_start();
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'aday') {
        header("Location: panel.php");
        exit();
    } else {
        header("Location: isveren_panel.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Akıllı İK Yönetimi | Giriş</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="glass-container">
        <h1>IKSystem'e Hoşgeldiniz</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="tab-container">
            <div class="tab active" onclick="switchTab('aday')">Aday Girişi</div>
            <div class="tab" onclick="switchTab('isveren')">İşveren Girişi</div>
        </div>

        <form action="islem.php" method="POST" id="loginForm">
            <input type="hidden" name="islem" value="giris">
            <input type="hidden" name="user_type" id="userType" value="aday">

            <div class="form-group">
                <label for="email">E-posta Adresi</label>
                <input type="email" id="email" name="email" required placeholder="ornek@email.com">
            </div>

            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Giriş Yap</button>
        </form>

        <div class="link-text">
            Hesabınız yok mu? <a href="kayit.php">Hemen Kayıt Olun</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if (type === 'aday') {
                document.querySelectorAll('.tab')[0].classList.add('active');
            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
            }
            document.getElementById('userType').value = type;
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Kayıt Ol</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="glass-container">
        <h1>Yeni Hesap Oluştur</h1>

        <div class="tab-container">
            <div class="tab active" onclick="switchTab('aday')">Aday Kaydı</div>
            <div class="tab" onclick="switchTab('isveren')">İşveren Kaydı</div>
        </div>

        <form action="islem.php" method="POST" id="registerForm">
            <input type="hidden" name="islem" value="kayit">
            <input type="hidden" name="user_type" id="userType" value="aday">

            <!-- Aday Fields -->
            <div id="adayFields">
                <div class="form-group">
                    <label for="ad">Ad</label>
                    <input type="text" id="ad" name="ad" placeholder="Adınız">
                </div>
                <div class="form-group">
                    <label for="soyad">Soyad</label>
                    <input type="text" id="soyad" name="soyad" placeholder="Soyadınız">
                </div>
                <div class="form-group">
                    <label for="dogum_tarihi">Doğum Tarihi</label>
                    <input type="date" id="dogum_tarihi" name="dogum_tarihi">
                </div>
                <div class="form-group">
                    <label for="meslek">Meslek</label>
                    <input type="text" id="meslek" name="meslek" placeholder="Örn: Yazılım Mühendisi">
                </div>
            </div>

            <!-- İşveren Fields -->
            <div id="isverenFields" style="display: none;">
                <div class="form-group">
                    <label for="sirket_adi">Şirket Adı</label>
                    <input type="text" id="sirket_adi" name="sirket_adi" placeholder="Şirketinizin Adı">
                </div>
                <div class="form-group">
                    <label for="sektor">Sektör</label>
                    <input type="text" id="sektor" name="sektor" placeholder="Örn: Bilişim, Sağlık">
                </div>
            </div>

            <!-- Ortak Fields -->
            <div class="form-group">
                <label for="email">E-posta Adresi</label>
                <input type="email" id="email" name="email" required placeholder="ornek@email.com">
            </div>
            <div class="form-group">
                <label for="telefon">Telefon Numarası</label>
                <input type="text" id="telefon" name="telefon" placeholder="05XXXXXXXXX">
            </div>
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Kayıt Ol</button>
        </form>

        <div class="link-text">
            Zaten hesabınız var mı? <a href="index.php">Giriş Yapın</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if (type === 'aday') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('adayFields').style.display = 'block';
                document.getElementById('isverenFields').style.display = 'none';
                
                // toggle required properties appropriately to allow submit
                document.getElementById('ad').required = true;
                document.getElementById('soyad').required = true;
                document.getElementById('sirket_adi').required = false;

            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('adayFields').style.display = 'none';
                document.getElementById('isverenFields').style.display = 'block';

                document.getElementById('ad').required = false;
                document.getElementById('soyad').required = false;
                document.getElementById('sirket_adi').required = true;
            }
            document.getElementById('userType').value = type;
        }

        // Initialize required fields
        switchTab('aday');
    </script>
</body>
</html>

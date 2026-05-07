<?php
require_once 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'aday') {
    header("Location: index.php");
    exit();
}

$aday_id = $_SESSION['user_id'];

// Aday bilgilerini çek
$stmt = $conn->prepare("SELECT * FROM aday WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$aday = $stmt->get_result()->fetch_assoc();

// Eğitimleri çek
$stmt = $conn->prepare("SELECT * FROM egitim WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$egitimler = $stmt->get_result();

// Deneyimleri çek
$stmt = $conn->prepare("SELECT * FROM deneyim WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$deneyimler = $stmt->get_result();

// Yetenekleri çek
$stmt = $conn->prepare("SELECT * FROM yetenek WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$yetenekler = $stmt->get_result();

// İlanları çek
$ilanlar = $conn->query("SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id ORDER BY is_ilani.ilan_id DESC");

// Başvuruları çek
$stmt = $conn->prepare("SELECT basvuru.*, is_ilani.pozisyon, isveren.sirket_adi FROM basvuru JOIN is_ilani ON basvuru.ilan_id = is_ilani.ilan_id JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id WHERE basvuru.aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$basvurular = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aday Paneli - IKSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem</div>
        <div class="nav-links">
            <span style="color: #fff; margin-right: 20px;">Merhaba, <?= htmlspecialchars($aday['ad'] . ' ' . $aday['soyad']) ?></span>
            <a href="islem.php?islem=cikis">Çıkış Yap</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Profil ve Formlar -->
        <div>
            <div class="card">
                <h3>Profil Bilgileri</h3>
                <p><strong>Meslek:</strong> <?= htmlspecialchars($aday['meslek']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($aday['email']) ?></p>
                <p><strong>Telefon:</strong> <?= htmlspecialchars($aday['telefon']) ?></p>
            </div>

            <div class="card">
                <h3>Eğitim Ekle</h3>
                <form action="islem.php" method="POST">
                    <input type="hidden" name="islem" value="egitim_ekle">
                    <div class="form-group"><input type="text" name="okul" placeholder="Okul Adı" required></div>
                    <div class="form-group"><input type="text" name="bolum" placeholder="Bölüm" required></div>
                    <div class="form-group"><input type="text" name="derece" placeholder="Derece (Lisans vs.)" required></div>
                    <div class="form-group"><input type="number" name="mezuniyet_yili" placeholder="Mezuniyet Yılı" required></div>
                    <button type="submit" class="btn">Ekle</button>
                </form>
            </div>

            <div class="card">
                <h3>Deneyim Ekle</h3>
                <form action="islem.php" method="POST">
                    <input type="hidden" name="islem" value="deneyim_ekle">
                    <div class="form-group"><input type="text" name="sirket" placeholder="Şirket" required></div>
                    <div class="form-group"><input type="text" name="pozisyon" placeholder="Pozisyon" required></div>
                    <div class="form-group">
                        <label>Başlangıç</label>
                        <input type="date" name="baslangic_tarihi" required>
                    </div>
                    <div class="form-group">
                        <label>Bitiş (Devam ediyorsa boş bırakın)</label>
                        <input type="date" name="bitis_tarihi">
                    </div>
                    <button type="submit" class="btn">Ekle</button>
                </form>
            </div>

            <div class="card">
                <h3>Yetenek Ekle</h3>
                <form action="islem.php" method="POST">
                    <input type="hidden" name="islem" value="yetenek_ekle">
                    <div class="form-group"><input type="text" name="yetenek_adi" placeholder="Yetenek Adı (Örn: PHP)" required></div>
                    <div class="form-group"><input type="text" name="kategori" placeholder="Kategori (Örn: Yazılım)"></div>
                    <div class="form-group">
                        <select name="seviye" required>
                            <option value="Başlangıç">Başlangıç</option>
                            <option value="Orta" selected>Orta</option>
                            <option value="İleri">İleri</option>
                            <option value="Uzman">Uzman</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Ekle</button>
                </form>
            </div>
        </div>

        <!-- İlanlar ve Başvurular -->
        <div>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Başvurularım</h3>
                <?php if($basvurular->num_rows > 0): ?>
                    <?php while($b = $basvurular->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4><?= htmlspecialchars($b['pozisyon']) ?></h4>
                            <p><?= htmlspecialchars($b['sirket_adi']) ?></p>
                            <span class="badge">Durum: <?= htmlspecialchars($b['durum']) ?></span>
                            <span class="badge pink">AI Skor: %<?= $b['ai_eslesme_skoru'] ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #aaa;">Henüz başvuru yapmadınız.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Açık İş İlanları</h3>
                <?php while($ilan = $ilanlar->fetch_assoc()): ?>
                    <div class="list-item">
                        <h4><?= htmlspecialchars($ilan['pozisyon']) ?></h4>
                        <p><strong>Şirket:</strong> <?= htmlspecialchars($ilan['sirket_adi']) ?></p>
                        <p><strong>Açıklama:</strong> <?= htmlspecialchars($ilan['aciklama']) ?></p>
                        <p><strong>Konum:</strong> <?= htmlspecialchars($ilan['konum']) ?></p>
                        <p><strong>Maaş:</strong> <?= htmlspecialchars($ilan['maas_araligi']) ?></p>
                        <span class="badge">Son Başvuru: <?= htmlspecialchars($ilan['son_basvuru']) ?></span>
                        
                        <form action="islem.php" method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="islem" value="basvuru_yap">
                            <input type="hidden" name="ilan_id" value="<?= $ilan['ilan_id'] ?>">
                            <button type="submit" class="btn" style="padding: 8px;">Hemen Başvur</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'isveren') {
    header("Location: index.php");
    exit();
}

$isveren_id = $_SESSION['user_id'];

// İşverenin ilanlarını çek
$stmt = $conn->prepare("SELECT * FROM is_ilani WHERE isveren_id = ? ORDER BY ilan_id DESC");
$stmt->bind_param("i", $isveren_id);
$stmt->execute();
$ilanlar = $stmt->get_result();

// Gelen başvuruları çek
$stmt = $conn->prepare("
    SELECT basvuru.*, is_ilani.pozisyon, aday.ad, aday.soyad, aday.email, aday.meslek 
    FROM basvuru 
    JOIN is_ilani ON basvuru.ilan_id = is_ilani.ilan_id 
    JOIN aday ON basvuru.aday_id = aday.aday_id 
    WHERE is_ilani.isveren_id = ? 
    ORDER BY basvuru.ai_eslesme_skoru DESC
");
$stmt->bind_param("i", $isveren_id);
$stmt->execute();
$basvurular = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşveren Paneli - IKSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem (İşveren)</div>
        <div class="nav-links">
            <span style="color: #fff; margin-right: 20px;">Hoşgeldiniz, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="islem.php?islem=cikis">Çıkış Yap</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Formlar -->
        <div>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Yeni İlan Ver</h3>
                <form action="islem.php" method="POST">
                    <input type="hidden" name="islem" value="ilan_ekle">
                    <div class="form-group"><input type="text" name="pozisyon" placeholder="Pozisyon Adı" required></div>
                    <div class="form-group"><textarea name="aciklama" placeholder="İlan Açıklaması" rows="4"></textarea></div>
                    <div class="form-group"><input type="text" name="maas_araligi" placeholder="Maaş Aralığı (Örn: 20K - 30K)"></div>
                    <div class="form-group"><input type="text" name="konum" placeholder="Konum (Örn: İstanbul / Uzaktan)"></div>
                    <div class="form-group">
                        <label>Son Başvuru Tarihi</label>
                        <input type="date" name="son_basvuru" required>
                    </div>
                    <button type="submit" class="btn">İlanı Yayınla</button>
                </form>
            </div>

            <div class="card">
                <h3>Mevcut İlanlarım</h3>
                <?php if($ilanlar->num_rows > 0): ?>
                    <?php while($ilan = $ilanlar->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4><?= htmlspecialchars($ilan['pozisyon']) ?></h4>
                            <p><strong>Açıklama:</strong> <?= htmlspecialchars($ilan['aciklama']) ?></p>
                            <span class="badge">Son Başvuru: <?= htmlspecialchars($ilan['son_basvuru']) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #aaa;">Henüz ilan vermediniz.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Başvurular -->
        <div>
            <div class="card">
                <h3>Gelen Başvurular (AI Skoruna Göre)</h3>
                <?php if($basvurular->num_rows > 0): ?>
                    <?php while($b = $basvurular->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4 style="color: var(--neon-pink);"><?= htmlspecialchars($b['ad'] . ' ' . $b['soyad']) ?> <span class="badge pink" style="float:right;">AI Skor: %<?= $b['ai_eslesme_skoru'] ?></span></h4>
                            <p><strong>Başvurduğu Pozisyon:</strong> <?= htmlspecialchars($b['pozisyon']) ?></p>
                            <p><strong>Adayın Mesleği:</strong> <?= htmlspecialchars($b['meslek']) ?></p>
                            <p><strong>İletişim:</strong> <?= htmlspecialchars($b['email']) ?></p>
                            <span class="badge">Durum: <?= htmlspecialchars($b['durum']) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #aaa;">Henüz başvuru bulunmuyor.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

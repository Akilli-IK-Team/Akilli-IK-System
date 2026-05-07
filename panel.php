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
            <a href="anasayfa.php" style="color: var(--neon-pink); font-weight: bold;">Anasayfaya Dön</a>
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
                <hr style="border-color: var(--glass-border); margin: 15px 0;">
                
                <h4 style="margin-bottom: 10px; color: var(--neon-pink);">Yeteneklerim</h4>
                <div style="margin-bottom: 15px;">
                    <?php if($yetenekler->num_rows > 0): ?>
                        <?php while($y = $yetenekler->fetch_assoc()): ?>
                            <div style="display: inline-block; margin-bottom: 5px;">
                                <span class="badge pink" style="margin-right: 5px;"><?= htmlspecialchars($y['yetenek_adi']) ?> (<?= htmlspecialchars($y['seviye']) ?>)</span>
                                <form action="islem.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="islem" value="yetenek_sil">
                                    <input type="hidden" name="yetenek_id" value="<?= $y['yetenek_id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 0.8rem;">[x]</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">Henüz yetenek eklenmedi.</p>
                    <?php endif; ?>
                </div>

                <h4 style="margin-bottom: 10px; color: var(--neon-purple);">Eğitim Bilgilerim</h4>
                <div style="margin-bottom: 15px;">
                    <?php if($egitimler->num_rows > 0): ?>
                        <?php while($e = $egitimler->fetch_assoc()): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px; margin-bottom: 5px;">
                                <p style="font-size: 0.9rem; margin:0;">🎓 <strong><?= htmlspecialchars($e['okul']) ?></strong> - <?= htmlspecialchars($e['bolum']) ?> (<?= htmlspecialchars($e['mezuniyet_yili']) ?>)</p>
                                <form action="islem.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="islem" value="egitim_sil">
                                    <input type="hidden" name="egitim_id" value="<?= $e['egitim_id'] ?>">
                                    <button type="submit" style="background: rgba(255,0,0,0.2); border: none; color: #ff6b6b; cursor: pointer; font-size: 0.7rem; padding: 2px 5px; border-radius: 3px;">Sil</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">Henüz eğitim eklenmedi.</p>
                    <?php endif; ?>
                </div>

                <h4 style="margin-bottom: 10px; color: var(--neon-purple);">Deneyimlerim</h4>
                <div>
                    <?php if($deneyimler->num_rows > 0): ?>
                        <?php while($d = $deneyimler->fetch_assoc()): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px; margin-bottom: 5px;">
                                <p style="font-size: 0.9rem; margin:0;">💼 <strong><?= htmlspecialchars($d['sirket']) ?></strong> - <?= htmlspecialchars($d['pozisyon']) ?></p>
                                <form action="islem.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="islem" value="deneyim_sil">
                                    <input type="hidden" name="deneyim_id" value="<?= $d['deneyim_id'] ?>">
                                    <button type="submit" style="background: rgba(255,0,0,0.2); border: none; color: #ff6b6b; cursor: pointer; font-size: 0.7rem; padding: 2px 5px; border-radius: 3px;">Sil</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">Henüz deneyim eklenmedi.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h3>Profil Güncelle</h3>
                <form action="islem.php" method="POST">
                    <input type="hidden" name="islem" value="profil_guncelle">
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="text" name="telefon" value="<?= htmlspecialchars($aday['telefon']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Meslek</label>
                        <input type="text" name="meslek" value="<?= htmlspecialchars($aday['meslek']) ?>" required>
                    </div>
                    <button type="submit" class="btn">Güncelle</button>
                </form>
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
                            <form action="islem.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="islem" value="basvuru_sil">
                                <input type="hidden" name="basvuru_id" value="<?= $b['basvuru_id'] ?>">
                                <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: rgba(255,0,0,0.2); color: #ff6b6b; border: 1px solid rgba(255,0,0,0.5);">İptal Et</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #aaa;">Henüz başvuru yapmadınız.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

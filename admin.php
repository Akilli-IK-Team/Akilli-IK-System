<?php
require_once 'db.php';

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if ($is_logged_in) {
    // Adayları Çek
    $aday_sorgu = $conn->query("SELECT * FROM aday ORDER BY aday_id DESC");

    // İşverenleri Çek
    $isveren_sorgu = $conn->query("SELECT * FROM isveren ORDER BY isveren_id DESC");

    // İlanları Çek
    $ilan_sorgu = $conn->query("SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id ORDER BY is_ilani.ilan_id DESC");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Yöneticisi - IKSystem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            overflow: hidden;
            font-size: 0.9rem;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }
        th {
            background: rgba(138, 43, 226, 0.2);
            color: var(--neon-purple);
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .action-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 5px;
        }
        .btn-delete {
            background: rgba(255,0,0,0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255,0,0,0.5);
        }
        .btn-reset {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.5);
        }
        .admin-login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem (Admin Paneli)</div>
        <div class="nav-links">
            <a href="index.php">Ana Sayfaya Dön</a>
            <?php if($is_logged_in): ?>
                <a href="admin_islem.php?islem=cikis" style="color: #ff6b6b; margin-left: 15px;">Güvenli Çıkış</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-container" style="grid-template-columns: 1fr;">
        
        <?php if(isset($_SESSION['admin_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></div>
        <?php endif; ?>

        <?php if(!$is_logged_in): ?>
            <!-- Admin Giriş Formu -->
            <div class="card admin-login-card">
                <h3 style="text-align: center; color: var(--neon-pink);">Yönetici Girişi</h3>
                <form action="admin_islem.php" method="POST">
                    <input type="hidden" name="islem" value="admin_giris">
                    <div class="form-group">
                        <label>Yönetici Şifresi</label>
                        <input type="password" name="sifre" required placeholder="Şifrenizi girin">
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Giriş Yap</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Paneli İçeriği -->
            <div class="card">
                <h3 style="color: var(--neon-pink);">Kayıtlı Adaylar Yönetimi</h3>
                <?php if($aday_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Meslek</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($aday = $aday_sorgu->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $aday['aday_id'] ?></td>
                                    <td><?= htmlspecialchars($aday['ad'] . ' ' . $aday['soyad']) ?></td>
                                    <td><?= htmlspecialchars($aday['email']) ?></td>
                                    <td><?= htmlspecialchars($aday['telefon']) ?></td>
                                    <td><?= htmlspecialchars($aday['meslek']) ?></td>
                                    <td style="display: flex;">
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Şifreyi 123456 yapmak istediğinize emin misiniz?');">
                                            <input type="hidden" name="islem" value="sifre_sifirla">
                                            <input type="hidden" name="hedef_tip" value="aday">
                                            <input type="hidden" name="hedef_id" value="<?= $aday['aday_id'] ?>">
                                            <button type="submit" class="action-btn btn-reset" title="Şifreyi Sıfırla">Sıfırla</button>
                                        </form>
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Bu adayı ve tüm verilerini silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                                            <input type="hidden" name="islem" value="aday_sil">
                                            <input type="hidden" name="aday_id" value="<?= $aday['aday_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Sistemde henüz kayıtlı aday bulunmuyor.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="color: var(--neon-purple);">Kayıtlı İşverenler Yönetimi</h3>
                <?php if($isveren_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Şirket Adı</th>
                                <th>Sektör</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($isveren = $isveren_sorgu->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $isveren['isveren_id'] ?></td>
                                    <td><?= htmlspecialchars($isveren['sirket_adi']) ?></td>
                                    <td><?= htmlspecialchars($isveren['sektor']) ?></td>
                                    <td><?= htmlspecialchars($isveren['email']) ?></td>
                                    <td><?= htmlspecialchars($isveren['telefon']) ?></td>
                                    <td style="display: flex;">
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Şifreyi 123456 yapmak istediğinize emin misiniz?');">
                                            <input type="hidden" name="islem" value="sifre_sifirla">
                                            <input type="hidden" name="hedef_tip" value="isveren">
                                            <input type="hidden" name="hedef_id" value="<?= $isveren['isveren_id'] ?>">
                                            <button type="submit" class="action-btn btn-reset" title="Şifreyi Sıfırla">Sıfırla</button>
                                        </form>
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Bu işvereni ve tüm ilanlarını silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="islem" value="isveren_sil">
                                            <input type="hidden" name="isveren_id" value="<?= $isveren['isveren_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Sistemde henüz kayıtlı işveren bulunmuyor.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="color: #4CAF50;">Sistemdeki Tüm İş İlanları</h3>
                <?php if($ilan_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Şirket</th>
                                <th>Pozisyon</th>
                                <th>Konum</th>
                                <th>Son Başvuru</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ilan = $ilan_sorgu->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $ilan['ilan_id'] ?></td>
                                    <td><?= htmlspecialchars($ilan['sirket_adi']) ?></td>
                                    <td><?= htmlspecialchars($ilan['pozisyon']) ?></td>
                                    <td><?= htmlspecialchars($ilan['konum']) ?></td>
                                    <td><?= htmlspecialchars($ilan['son_basvuru']) ?></td>
                                    <td>
                                        <form action="admin_islem.php" method="POST" style="margin:0;" onsubmit="return confirm('Bu ilanı silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="islem" value="ilan_sil">
                                            <input type="hidden" name="ilan_id" value="<?= $ilan['ilan_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Sistemde henüz yayınlanmış ilan bulunmuyor.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

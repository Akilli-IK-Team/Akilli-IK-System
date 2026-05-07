<?php
require_once 'db.php';

if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

$arama = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id";

if ($arama !== '') {
    $arama_param = "%" . $arama . "%";
    $stmt = $conn->prepare($sql . " WHERE is_ilani.pozisyon LIKE ? OR is_ilani.istenen_yetenekler LIKE ? OR isveren.sirket_adi LIKE ? OR is_ilani.konum LIKE ? ORDER BY is_ilani.ilan_id DESC");
    $stmt->bind_param("ssss", $arama_param, $arama_param, $arama_param, $arama_param);
    $stmt->execute();
    $ilanlar = $stmt->get_result();
} else {
    $ilanlar = $conn->query($sql . " ORDER BY is_ilani.ilan_id DESC");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Anasayfa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .search-bar input {
            flex: 1;
        }
        .search-bar button {
            width: auto;
            padding: 12px 24px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem</div>
        <div class="nav-links">
            <span style="color: #fff; margin-right: 20px;">Hoşgeldiniz, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            
            <?php if($_SESSION['user_type'] == 'aday'): ?>
                <a href="panel.php" style="color: var(--neon-pink); font-weight: bold;">Profilime Git</a>
            <?php else: ?>
                <a href="isveren_panel.php" style="color: var(--neon-purple); font-weight: bold;">İşveren Paneli</a>
            <?php endif; ?>
            
            <a href="islem.php?islem=cikis">Çıkış Yap</a>
        </div>
    </div>

    <div class="dashboard-container" style="grid-template-columns: 1fr; max-width: 900px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">İş İlanlarını Keşfedin</h2>
            
            <form action="anasayfa.php" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Pozisyon, yetenek, şirket veya konum ara..." value="<?= htmlspecialchars($arama) ?>">
                <button type="submit" class="btn">Ara</button>
            </form>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if($ilanlar->num_rows > 0): ?>
                <?php while($ilan = $ilanlar->fetch_assoc()): ?>
                    <div class="list-item" style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.2rem; color: #fff;"><?= htmlspecialchars($ilan['pozisyon']) ?></h4>
                            <p style="color: var(--neon-pink); font-weight: bold; margin-bottom: 10px;"><?= htmlspecialchars($ilan['sirket_adi']) ?></p>
                            <p><strong>Açıklama:</strong> <?= htmlspecialchars($ilan['aciklama']) ?></p>
                            <p><strong>Konum:</strong> <?= htmlspecialchars($ilan['konum']) ?></p>
                            <p><strong>Aranan Yetenekler:</strong> <?= htmlspecialchars($ilan['istenen_yetenekler']) ?></p>
                            <p><strong>Maaş:</strong> <?= htmlspecialchars($ilan['maas_araligi']) ?></p>
                            <span class="badge">Son Başvuru: <?= htmlspecialchars($ilan['son_basvuru']) ?></span>
                        </div>
                        
                        <?php if($_SESSION['user_type'] == 'aday'): ?>
                            <div style="margin-left: 20px; text-align: right;">
                                <form action="islem.php" method="POST">
                                    <input type="hidden" name="islem" value="basvuru_yap">
                                    <input type="hidden" name="ilan_id" value="<?= $ilan['ilan_id'] ?>">
                                    <input type="hidden" name="from" value="anasayfa">
                                    <button type="submit" class="btn" style="padding: 10px 20px;">Hemen Başvur</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #aaa;">Aradığınız kriterlere uygun ilan bulunamadı.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
require_once 'db.php';

// Adayları Çek
$aday_sorgu = $conn->query("SELECT * FROM aday ORDER BY aday_id DESC");

// İşverenleri Çek
$isveren_sorgu = $conn->query("SELECT * FROM isveren ORDER BY isveren_id DESC");
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
        }
        th, td {
            padding: 12px 15px;
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
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem (Admin)</div>
        <div class="nav-links">
            <a href="index.php">Ana Sayfaya Dön</a>
        </div>
    </div>

    <div class="dashboard-container" style="grid-template-columns: 1fr;">
        <div class="card">
            <h3 style="color: var(--neon-pink);">Kayıtlı Adaylar</h3>
            <?php if($aday_sorgu->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad Soyad</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Meslek</th>
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
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Sistemde henüz kayıtlı aday bulunmuyor.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="color: var(--neon-purple);">Kayıtlı İşverenler</h3>
            <?php if($isveren_sorgu->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Şirket Adı</th>
                            <th>Sektör</th>
                            <th>Email</th>
                            <th>Telefon</th>
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
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Sistemde henüz kayıtlı işveren bulunmuyor.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

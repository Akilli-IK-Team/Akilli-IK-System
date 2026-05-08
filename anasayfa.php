<?php
// Veritabanı bağlantı dosyasını projeye dahil ediyoruz.
require_once 'db.php';

// Güvenlik kontrolü: Eğer kullanıcı giriş yapmamışsa (oturumda 'user_type' yoksa), yetkisiz erişimi engellemek için index.php'ye (giriş sayfasına) yönlendirilir.
if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcının arama formundan gönderdiği 'q' parametresi (sorgu kelimesi) varsa al, yoksa boş string ata.
$search = isset($_GET['q']) ? $_GET['q'] : '';

// İlanları çekmek için temel SQL sorgusu. İş ilanı ve İşveren tablolarını birleştirir (JOIN), böylece şirketin adını da ekranda gösterebiliriz.
$sql = "SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id";

if ($search !== '') {
    // Eğer bir arama yapılmışsa, SQL Injection (Güvenlik açığı) saldırılarını engellemek için prepare (hazırlanmış) sorgu kullanıyoruz.
    // % işaretleri LIKE operatöründe "içinde geçen" kelimeleri bulmak içindir.
    $search_param = "%" . $search . "%";
    
    // Pozisyon, istenen yetenek, şirket adı veya konum bilgilerinin içinde aranan kelimenin olup olmadığı kontrol edilir. Sonuçlar ilan ID'sine göre tersten sıralanır (en yeni ilk).
    $stmt = $conn->prepare($sql . " WHERE is_ilani.pozisyon LIKE ? OR is_ilani.istenen_yetenekler LIKE ? OR isveren.sirket_adi LIKE ? OR is_ilani.konum LIKE ? ORDER BY is_ilani.ilan_id DESC");
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $jobs = $stmt->get_result();
} else {
    // Arama yapılmamışsa, tüm ilanları kronolojik sıraya göre listeler (en son eklenen ilan en üstte görünür).
    $jobs = $conn->query($sql . " ORDER BY is_ilani.ilan_id DESC");
}

// Arayüzde ilanları ayırmak için iki farklı boş dizi oluşturuyoruz.
$featured_jobs = []; // Öne çıkan ilanlar
$regular_jobs = [];  // Diğer (normal) ilanlar

// Veritabanından dönen satır (ilan) sayısı sıfırdan büyükse işlem yap
if ($jobs->num_rows > 0) {
    $count = 0;
    // Gelen sonuçları döngü ile tek tek okuyoruz
    while($job = $jobs->fetch_assoc()) {
        // Arama yapılmamışsa ve sıradaki ilan ilk 3 ilandan biriyse, bunu "Öne Çıkanlar" kısmına ekle
        if ($search === '' && $count < 3) {
            $featured_jobs[] = $job;
        } else {
            // İlk 3'ten sonrakiler veya arama yapılmışsa tüm sonuçlar normal listeye eklenir
            $regular_jobs[] = $job;
        }
        $count++;
    }
}

/**
 * Bu fonksiyon, bir iş ilanının HTML kartını (tasarımını) tek bir merkezden oluşturmak için kullanılır.
 * Böylece aynı kodu sayfada tekrar tekrar yazmaktan kurtuluruz (Kod sadeleştirme).
 *
 * @param array $job Veritabanından gelen ilan verisi
 * @return string Üretilen HTML kodu
 */
function renderJobCard($job) {
    // Global session (oturum) değişkenine erişebilmek için tanımlıyoruz
    global $_SESSION;
    
    // Çıktı tamponlamasını (Output Buffering) başlatarak HTML kodunu belleğe alıyoruz
    ob_start();
    ?>
    <div class="list-item">
        <div>
            <h4 style="font-size: 1.2rem; color: #333;"><?= htmlspecialchars($job['pozisyon']) ?></h4>
            <p style="color: var(--neon-pink); font-weight: bold; margin-bottom: 10px;"><?= htmlspecialchars($job['sirket_adi']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($job['aciklama']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($job['konum']) ?></p>
            <p><strong>Required Skills:</strong> <?= htmlspecialchars($job['istenen_yetenekler']) ?></p>
            <p><strong>Salary:</strong> <?= htmlspecialchars($job['maas_araligi']) ?></p>
            <span class="badge">Deadline: <?= htmlspecialchars($job['son_basvuru']) ?></span>
        </div>
        
        <?php 
        // Sadece giriş yapan kullanıcı "Aday" (Candidate) tipindeyse Başvur butonunu gösteririz
        if($_SESSION['user_type'] == 'candidate'): 
        ?>
            <div style="margin-top: 15px;">
                <form action="islem.php" method="POST">
                    <input type="hidden" name="action" value="apply_job">
                    <input type="hidden" name="job_id" value="<?= $job['ilan_id'] ?>">
                    <input type="hidden" name="from" value="anasayfa">
                    <button type="submit" class="btn">Apply Now</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
    // Belleğe alınan HTML kodunu geri döndürüyoruz
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKSystem - Homepage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem</div>
        <div class="nav-links">
            <span style="color: #555; margin-right: 20px;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            
            <?php if($_SESSION['user_type'] == 'candidate'): ?>
                <a href="panel.php" style="color: var(--neon-pink); font-weight: bold;">My Profile</a>
            <?php else: ?>
                <a href="isveren_panel.php" style="color: var(--neon-purple); font-weight: bold;">Employer Panel</a>
            <?php endif; ?>
            
            <a href="islem.php?action=logout">Logout</a>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1>Find Your Dream Job with Smart Matching</h1>
        <p>Discover opportunities that perfectly align with your skills and career goals through our intelligent HR platform.</p>
    </div>

    <!-- Search Section -->
    <div class="hero-search-wrapper">
        <form action="anasayfa.php" method="GET" class="search-bar" accept-charset="UTF-8">
            <input type="text" name="q" placeholder="Search by position, skill, company or location..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>

    <div class="dashboard-container">
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if(empty($search) && count($featured_jobs) > 0): ?>
            <!-- Featured Jobs -->
            <h2 class="section-title">✨ Featured Listings</h2>
            <div class="featured-jobs-container">
                <?php foreach($featured_jobs as $job): ?>
                    <div class="featured-job-box">
                        <?= htmlspecialchars($job['pozisyon']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- All Job Listings -->
        <h2 class="section-title"><?= empty($search) ? '🌍 All Job Listings' : '🔍 Search Results' ?></h2>
        
        <?php if(count($regular_jobs) > 0 || (count($featured_jobs) > 0 && !empty($search))): ?>
            <div class="job-list-container">
                <?php foreach($regular_jobs as $job): ?>
                    <?= renderJobCard($job) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #aaa; padding: 40px 0; font-size: 1.1rem;">No job listings found matching your criteria.</p>
        <?php endif; ?>
        
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content" style="max-width: 600px; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto;">
            <h3 style="font-size: 2.2rem; color: var(--neon-purple); margin-bottom: 30px;">Communication</h3>
            <p style="font-size: 1.3rem; color: #636e72; margin-bottom: 15px;">info@iksystem.com</p>
            <p style="font-size: 1.3rem; color: #636e72; margin-bottom: 15px;">+90 555 123 4567</p>
            <p style="font-size: 1.3rem; color: #636e72; margin-bottom: 40px;">Istanbul, Turkey</p>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> IKSystem. All rights reserved.
        </div>
    </footer>

</body>
</html>

<?php
// api_jobs.php
// İş ilanlarını veritabanından çekip JSON formatında frontend'e gönderir (AJAX için)

header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

// Arama sorgusu var mı kontrol et
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    if ($search) {
        // Arama yapıldıysa başlığa veya açıklamaya göre filtrele (Prepared Statement kullanımı)
        $stmt = $pdo->prepare("
            SELECT j.job_id, j.title, j.description, e.company_name 
            FROM Jobs j
            JOIN Employers e ON j.employer_id = e.employer_id
            WHERE j.title LIKE :search OR j.description LIKE :search
            ORDER BY j.created_at DESC
        ");
        $stmt->execute(['search' => '%' . $search . '%']);
    } else {
        // Arama yoksa tüm işleri getir
        $stmt = $pdo->query("
            SELECT j.job_id, j.title, j.description, e.company_name 
            FROM Jobs j
            JOIN Employers e ON j.employer_id = e.employer_id
            ORDER BY j.created_at DESC
        ");
    }

    $jobs = $stmt->fetchAll();

    // Her iş için gereken yetenekleri de çekiyoruz
    foreach ($jobs as &$job) {
        $skill_stmt = $pdo->prepare("
            SELECT s.skill_name 
            FROM Job_Skills js
            JOIN Skills s ON js.skill_id = s.skill_id
            WHERE js.job_id = :job_id
        ");
        $skill_stmt->execute(['job_id' => $job['job_id']]);
        $job['skills'] = $skill_stmt->fetchAll(PDO::FETCH_COLUMN); // Sadece skill_name array'i döner
        
        // Örnek eşleşme skoru (Gerçek sistemde giriş yapan adayın yeteneklerine göre hesaplanır)
        // Şimdilik görsel amaçlı rastgele veya sabit bir skor veriyoruz.
        $job['match_score'] = rand(70, 100); 
    }

    // JSON olarak çıktıyı bas
    echo json_encode($jobs);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı hatası oluştu.']);
}
?>

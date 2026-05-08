<?php
// Veritabanı bağlantı dosyasını sisteme dahil ediyoruz.
require_once 'db.php';

/**
 * GENEL İŞLEM YÖNLENDİRİCİSİ (ROUTER)
 * Bu dosya, sitedeki tüm form gönderimlerini (POST) ve bazı link tıklamalarını (GET) karşılar.
 * Hangi işlemin yapılacağını formlardaki gizli (hidden) 'action' inputu belirler.
 */

// Sadece POST istekleri kabul edilir ve 'action' değeri doluysa işleme başlanır.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ---------------------------------------------------------
    // 1. KULLANICI KAYDI (REGISTER) İŞLEMİ
    // ---------------------------------------------------------
    if ($action == 'register') {
        $user_type = $_POST['user_type']; // Kayıt olan kişi 'aday' mı yoksa 'isveren' mi?
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        // Şifreyi veritabanına kaydetmeden önce güvenli bir şekilde şifreliyoruz (Hash).
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if ($user_type == 'candidate') {
            // Aday kayıt formundan gelen ekstra bilgileri alıyoruz
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $birth_date = $_POST['birth_date'];
            $profession = $_POST['profession'];

            // Aday tablosuna veri eklemek için SQL sorgusunu hazırlıyoruz.
            $stmt = $conn->prepare("INSERT INTO aday (ad, soyad, email, telefon, dogum_tarihi, meslek, sifre) VALUES (?, ?, ?, ?, ?, ?, ?)");
            // Soru işaretleri yerine değişkenleri bağlıyoruz (Güvenlik için). 's' harfleri verinin tipinin 'string' (metin) olduğunu belirtir.
            $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $birth_date, $profession, $password);
        } else {
            // İşveren kayıt formundan gelen ekstra bilgileri alıyoruz
            $company_name = $_POST['company_name'];
            $industry = $_POST['industry'];

            // İşveren tablosuna veri eklemek için SQL sorgusunu hazırlıyoruz.
            $stmt = $conn->prepare("INSERT INTO isveren (sirket_adi, sektor, email, telefon, sifre) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $company_name, $industry, $email, $phone, $password);
        }

        try {
            // Sorguyu çalıştırıyoruz. Başarılı olursa kullanıcıyı ana sayfaya yönlendiriyoruz.
            if ($stmt->execute()) {
                $_SESSION['success'] = "Kayıt işlemi başarıyla tamamlandı. Artık giriş yapabilirsiniz.";
                header("Location: index.php");
                exit();
            }
        } catch (Exception $e) {
            // Email zaten kayıtlıysa veya başka bir hata çıkarsa kullanıcıya hata mesajı gösteriyoruz.
            $_SESSION['error'] = "Kayıt sırasında bir hata oluştu veya bu email adresi zaten kullanılıyor.";
            header("Location: kayit.php");
            exit();
        }

    // ---------------------------------------------------------
    // 2. KULLANICI GİRİŞİ (LOGIN) İŞLEMİ
    // ---------------------------------------------------------
    } elseif ($action == 'login') {
        $user_type = $_POST['user_type'];
        $email = $_POST['email'];
        $password_input = $_POST['password'];

        if ($user_type == 'candidate') {
            // Adaylar tablosunda bu emaile sahip bir kullanıcı var mı kontrol ediyoruz.
            $stmt = $conn->prepare("SELECT aday_id, sifre, ad, soyad FROM aday WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Kullanıcı bulunduysa
            if ($row = $result->fetch_assoc()) {
                // Girilen şifre ile veritabanındaki şifrelenmiş (hashli) şifreyi karşılaştırıyoruz
                if (password_verify($password_input, $row['sifre'])) {
                    // Şifre doğruysa oturum (session) bilgilerini oluşturuyoruz
                    $_SESSION['user_type'] = 'candidate';
                    $_SESSION['user_id'] = $row['aday_id'];
                    $_SESSION['user_name'] = $row['ad'] . ' ' . $row['soyad'];
                    header("Location: anasayfa.php"); // Giriş başarılı, ana sayfaya git
                    exit();
                }
            }
        } else {
            // İşverenler tablosunda bu emaile sahip bir kullanıcı var mı kontrol ediyoruz.
            $stmt = $conn->prepare("SELECT isveren_id, sifre, sirket_adi FROM isveren WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password_input, $row['sifre'])) {
                    $_SESSION['user_type'] = 'employer';
                    $_SESSION['user_id'] = $row['isveren_id'];
                    $_SESSION['user_name'] = $row['sirket_adi'];
                    header("Location: anasayfa.php");
                    exit();
                }
            }
        }

        // Eğer hiçbir IF bloğunda exit() çalışmadıysa, e-posta veya şifre yanlıştır.
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: index.php");
        exit();

    // ---------------------------------------------------------
    // 3. İŞ İLANI EKLEME (İşverenler İçin)
    // ---------------------------------------------------------
    } elseif ($action == 'add_job' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'employer') {
        // Formdan gelen verileri alıyoruz
        $isveren_id = $_SESSION['user_id']; // Hangi işverenin eklediğini bilmek için oturumdaki ID'yi kullanıyoruz
        $position = $_POST['position'];
        $description = $_POST['description'];
        $salary_range = $_POST['salary_range'];
        $location = $_POST['location'];
        $required_skills = $_POST['required_skills'];
        $deadline = $_POST['deadline'];

        // is_ilani tablosuna yeni ilanı kaydediyoruz.
        $stmt = $conn->prepare("INSERT INTO is_ilani (isveren_id, pozisyon, aciklama, maas_araligi, konum, istenen_yetenekler, son_basvuru) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // 'issssss' -> i (integer: isveren_id) ve s (string: diğer metinler)
        $stmt->bind_param("issssss", $isveren_id, $position, $description, $salary_range, $location, $required_skills, $deadline);

        if ($stmt->execute()) {
            $_SESSION['success'] = "İş ilanı başarıyla eklendi.";
        } else {
            $_SESSION['error'] = "İş ilanı eklenirken bir hata oluştu.";
        }
        header("Location: isveren_panel.php");
        exit();

    // ---------------------------------------------------------
    // 4. ADAY PROFİL BİLGİLERİ GÜNCELLEMELERİ (Adaylar İçin)
    // ---------------------------------------------------------
    
    // 4.a EĞİTİM EKLEME
    } elseif ($action == 'add_education' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $school = $_POST['school'];
        $department = $_POST['department'];
        $degree = $_POST['degree'];
        $graduation_year = $_POST['graduation_year'];

        $stmt = $conn->prepare("INSERT INTO egitim (aday_id, okul, bolum, derece, mezuniyet_yili) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $aday_id, $school, $department, $degree, $graduation_year);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    // 4.b DENEYİM EKLEME
    } elseif ($action == 'add_experience' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $company = $_POST['company'];
        $position = $_POST['position'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ? $_POST['end_date'] : null;

        $stmt = $conn->prepare("INSERT INTO deneyim (aday_id, sirket, pozisyon, baslangic_tarihi, bitis_tarihi) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $aday_id, $company, $position, $start_date, $end_date);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    // 4.c YETENEK EKLEME
    } elseif ($action == 'add_skill' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $skill_name = $_POST['skill_name'];
        $category = $_POST['category'];
        $level = $_POST['level'];

        $stmt = $conn->prepare("INSERT INTO yetenek (aday_id, yetenek_adi, kategori, seviye) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $aday_id, $skill_name, $category, $level);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    // ---------------------------------------------------------
    // 5. İŞ BAŞVURUSU VE AKILLI EŞLEŞTİRME (AI MATCHING)
    // ---------------------------------------------------------
    } elseif ($action == 'apply_job' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $job_id = $_POST['job_id'];

        // AŞAMA 1: İlanın gerektirdiği yetenekleri veritabanından çekiyoruz.
        $stmt = $conn->prepare("SELECT istenen_yetenekler FROM is_ilani WHERE ilan_id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $job_result = $stmt->get_result()->fetch_assoc();
        $required_raw = $job_result['istenen_yetenekler'] ?? '';
        
        // Gelen yetenekleri virgülle ayırıp, küçük harfe çevirip, boşluklarını temizliyoruz. (Örn: "PHP, MySQL" -> ["php", "mysql"])
        $required_array = array_map('trim', explode(',', strtolower($required_raw)));
        $required_array = array_filter($required_array);

        // AŞAMA 2: Adayın kendi eklediği yetenekleri çekiyoruz.
        $stmt = $conn->prepare("SELECT yetenek_adi FROM yetenek WHERE aday_id = ?");
        $stmt->bind_param("i", $aday_id);
        $stmt->execute();
        $skills_result = $stmt->get_result();
        $candidate_skills = [];
        while($y = $skills_result->fetch_assoc()) {
            $candidate_skills[] = strtolower(trim($y['yetenek_adi']));
        }

        // AŞAMA 3: Karşılaştırma Algoritması (Eşleşme Skoru Hesaplama)
        $score = 0;
        if (count($required_array) > 0) {
            $matched_count = 0;
            // İstenen her bir yetenek için, adayın yeteneklerini kontrol et
            foreach ($required_array as $req) {
                foreach ($candidate_skills as $c_skill) {
                    // Kısmi eşleşme kontrolü (Örn: "java", "javascript" içinde geçiyor mu?)
                    if (strpos($c_skill, $req) !== false || strpos($req, $c_skill) !== false) {
                        $matched_count++;
                        break; // Yetenek eşleştiyse diğer adayın yeteneklerine bakmayı bırak, sıradaki istenen yeteneğe geç.
                    }
                }
            }
            // Eşleşme yüzdesini hesaplıyoruz: (Eşleşen Sayı / Toplam İstenen Sayı) * 100
            $score = round(($matched_count / count($required_array)) * 100);
        } else {
            // Eğer işveren özel bir yetenek belirtmemişse standart olarak %50 puan ver.
            $score = 50; 
        }

        // AŞAMA 4: Başvuruyu veritabanına kaydet
        $stmt = $conn->prepare("INSERT INTO basvuru (aday_id, ilan_id, ai_eslesme_skoru) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $aday_id, $job_id, $score);
        
        try {
            if ($stmt->execute()) {
                $_SESSION['success'] = "Başvurunuz başarıyla alındı. Algoritma Eşleşme Skorunuz: %" . $score;
            }
        } catch (Exception $e) {
            // Aynı ilana ikinci kez başvurmayı engelleme (Veritabanı unique index kuralı sayesinde)
            $_SESSION['error'] = "Bu ilana zaten başvuru yaptınız!";
        }
        
        // Kullanıcının başvuruyu nereden (ana sayfa mı yoksa aday paneli mi) yaptığına göre geri döndür
        $from = isset($_POST['from']) ? $_POST['from'] : 'panel';
        if ($from == 'anasayfa') {
            header("Location: anasayfa.php");
        } else {
            header("Location: panel.php");
        }
        exit();

    // ---------------------------------------------------------
    // 6. PROFİL BİLGİLERİNİ GÜNCELLEME
    // ---------------------------------------------------------
    } elseif ($action == 'update_profile' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $phone = $_POST['phone'];
        $profession = $_POST['profession'];

        $stmt = $conn->prepare("UPDATE aday SET telefon = ?, meslek = ? WHERE aday_id = ?");
        $stmt->bind_param("ssi", $phone, $profession, $aday_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profil başarıyla güncellendi.";
        } else {
            $_SESSION['error'] = "Profil güncellenirken bir hata oluştu.";
        }
        header("Location: panel.php");
        exit();

    // ---------------------------------------------------------
    // 7. SİLME İŞLEMLERİ (Eğitim, Deneyim, Yetenek, Başvuru, İlan)
    // ---------------------------------------------------------
    } elseif ($action == 'delete_education' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $edu_id = $_POST['education_id'];

        $stmt = $conn->prepare("DELETE FROM egitim WHERE egitim_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $edu_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    } elseif ($action == 'delete_experience' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $exp_id = $_POST['experience_id'];

        $stmt = $conn->prepare("DELETE FROM deneyim WHERE deneyim_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $exp_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    } elseif ($action == 'delete_skill' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $skill_id = $_POST['skill_id'];

        $stmt = $conn->prepare("DELETE FROM yetenek WHERE yetenek_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $skill_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();

    } elseif ($action == 'delete_application' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'candidate') {
        $aday_id = $_SESSION['user_id'];
        $app_id = $_POST['application_id'];

        // Sadece adayın kendi başvurularını silebilmesi için 'aday_id = ?' koşulunu da ekliyoruz.
        $stmt = $conn->prepare("DELETE FROM basvuru WHERE basvuru_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $app_id, $aday_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Başvuru başarıyla iptal edildi.";
        } else {
            $_SESSION['error'] = "Başvuru silinirken bir hata oluştu.";
        }
        
        header("Location: panel.php");
        exit();

    } elseif ($action == 'delete_job' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'employer') {
        $isveren_id = $_SESSION['user_id'];
        $job_id = $_POST['job_id'];

        // Güvenlik: İşveren sadece kendi açtığı ilanları silebilir ('isveren_id = ?')
        $stmt = $conn->prepare("DELETE FROM is_ilani WHERE ilan_id = ? AND isveren_id = ?");
        $stmt->bind_param("ii", $job_id, $isveren_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "İş ilanı başarıyla silindi.";
        } else {
            $_SESSION['error'] = "İş ilanı silinirken bir hata oluştu.";
        }
        
        header("Location: isveren_panel.php");
        exit();
    }
}

// ---------------------------------------------------------
// 8. ÇIKIŞ YAPMA (LOGOUT) İŞLEMİ (GET İsteği)
// ---------------------------------------------------------
// Kullanıcı çıkış linkine tıkladığında (örnek: islem.php?action=logout) burası çalışır.
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Oturumdaki tüm verileri (isim, id, rol) temizler.
    session_destroy(); 
    header("Location: index.php");
    exit();
}

// Hiçbir şart sağlanmazsa (Örn: bu dosyaya doğrudan tarayıcıdan girilirse) güvenli bir şekilde ana sayfaya yönlendir.
header("Location: index.php");
exit();
?>

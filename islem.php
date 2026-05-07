<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem'])) {
    $islem = $_POST['islem'];

    if ($islem == 'kayit') {
        $user_type = $_POST['user_type'];
        $email = $_POST['email'];
        $telefon = $_POST['telefon'];
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

        if ($user_type == 'aday') {
            $ad = $_POST['ad'];
            $soyad = $_POST['soyad'];
            $dogum_tarihi = $_POST['dogum_tarihi'];
            $meslek = $_POST['meslek'];

            $stmt = $conn->prepare("INSERT INTO aday (ad, soyad, email, telefon, dogum_tarihi, meslek, sifre) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $ad, $soyad, $email, $telefon, $dogum_tarihi, $meslek, $sifre);
        } else {
            $sirket_adi = $_POST['sirket_adi'];
            $sektor = $_POST['sektor'];

            $stmt = $conn->prepare("INSERT INTO isveren (sirket_adi, sektor, email, telefon, sifre) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $sirket_adi, $sektor, $email, $telefon, $sifre);
        }

        try {
            if ($stmt->execute()) {
                $_SESSION['success'] = "Kayıt başarıyla tamamlandı. Giriş yapabilirsiniz.";
                header("Location: index.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Kayıt sırasında bir hata oluştu veya bu email zaten kullanımda.";
            header("Location: kayit.php");
            exit();
        }

    } elseif ($islem == 'giris') {
        $user_type = $_POST['user_type'];
        $email = $_POST['email'];
        $sifre_girilen = $_POST['sifre'];

        if ($user_type == 'aday') {
            $stmt = $conn->prepare("SELECT aday_id, sifre, ad, soyad FROM aday WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($sifre_girilen, $row['sifre'])) {
                    $_SESSION['user_type'] = 'aday';
                    $_SESSION['user_id'] = $row['aday_id'];
                    $_SESSION['user_name'] = $row['ad'] . ' ' . $row['soyad'];
                    $_SESSION['user_name'] = $row['ad'] . ' ' . $row['soyad'];
                    header("Location: anasayfa.php");
                    exit();
                }
            }
        } else {
            $stmt = $conn->prepare("SELECT isveren_id, sifre, sirket_adi FROM isveren WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($sifre_girilen, $row['sifre'])) {
                    $_SESSION['user_type'] = 'isveren';
                    $_SESSION['user_id'] = $row['isveren_id'];
                    $_SESSION['user_name'] = $row['sirket_adi'];
                    $_SESSION['user_name'] = $row['sirket_adi'];
                    header("Location: anasayfa.php");
                    exit();
                }
            }
        }

        $_SESSION['error'] = "E-posta veya şifre hatalı!";
        header("Location: index.php");
        exit();

    } elseif ($islem == 'ilan_ekle' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'isveren') {
        $isveren_id = $_SESSION['user_id'];
        $pozisyon = $_POST['pozisyon'];
        $aciklama = $_POST['aciklama'];
        $maas_araligi = $_POST['maas_araligi'];
        $konum = $_POST['konum'];
        $istenen_yetenekler = $_POST['istenen_yetenekler'];
        $son_basvuru = $_POST['son_basvuru'];

        $stmt = $conn->prepare("INSERT INTO is_ilani (isveren_id, pozisyon, aciklama, maas_araligi, konum, istenen_yetenekler, son_basvuru) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $isveren_id, $pozisyon, $aciklama, $maas_araligi, $konum, $istenen_yetenekler, $son_basvuru);

        if ($stmt->execute()) {
            $_SESSION['success'] = "İlan başarıyla eklendi.";
        } else {
            $_SESSION['error'] = "İlan eklenirken bir hata oluştu.";
        }
        header("Location: isveren_panel.php");
        exit();

    } elseif ($islem == 'egitim_ekle' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $okul = $_POST['okul'];
        $bolum = $_POST['bolum'];
        $derece = $_POST['derece'];
        $mezuniyet_yili = $_POST['mezuniyet_yili'];

        $stmt = $conn->prepare("INSERT INTO egitim (aday_id, okul, bolum, derece, mezuniyet_yili) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $aday_id, $okul, $bolum, $derece, $mezuniyet_yili);
        
        $stmt->execute();
        header("Location: panel.php");
        exit();

    } elseif ($islem == 'deneyim_ekle' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $sirket = $_POST['sirket'];
        $pozisyon = $_POST['pozisyon'];
        $baslangic_tarihi = $_POST['baslangic_tarihi'];
        $bitis_tarihi = $_POST['bitis_tarihi'] ? $_POST['bitis_tarihi'] : null;

        $stmt = $conn->prepare("INSERT INTO deneyim (aday_id, sirket, pozisyon, baslangic_tarihi, bitis_tarihi) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $aday_id, $sirket, $pozisyon, $baslangic_tarihi, $bitis_tarihi);
        
        $stmt->execute();
        header("Location: panel.php");
        exit();

    } elseif ($islem == 'yetenek_ekle' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $yetenek_adi = $_POST['yetenek_adi'];
        $kategori = $_POST['kategori'];
        $seviye = $_POST['seviye'];

        $stmt = $conn->prepare("INSERT INTO yetenek (aday_id, yetenek_adi, kategori, seviye) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $aday_id, $yetenek_adi, $kategori, $seviye);
        
        $stmt->execute();
        header("Location: panel.php");
        exit();

    } elseif ($islem == 'basvuru_yap' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $ilan_id = $_POST['ilan_id'];

        // Gerçek AI Eşleşme Skoru Hesaplaması
        // 1. İlanın istenen yeteneklerini çek
        $stmt = $conn->prepare("SELECT istenen_yetenekler FROM is_ilani WHERE ilan_id = ?");
        $stmt->bind_param("i", $ilan_id);
        $stmt->execute();
        $ilan_result = $stmt->get_result()->fetch_assoc();
        $istenenler_raw = $ilan_result['istenen_yetenekler'] ?? '';
        
        // Virgülle ayrılmış yetenekleri diziye çevir, küçük harfe çevirip boşlukları temizle
        $istenenler_array = array_map('trim', explode(',', strtolower($istenenler_raw)));
        $istenenler_array = array_filter($istenenler_array); // Boşlukları çıkar

        // 2. Adayın yeteneklerini çek
        $stmt = $conn->prepare("SELECT yetenek_adi FROM yetenek WHERE aday_id = ?");
        $stmt->bind_param("i", $aday_id);
        $stmt->execute();
        $yetenekler_result = $stmt->get_result();
        $aday_yetenekler = [];
        while($y = $yetenekler_result->fetch_assoc()) {
            $aday_yetenekler[] = strtolower(trim($y['yetenek_adi']));
        }

        // 3. Karşılaştırma yap
        $skor = 0;
        if (count($istenenler_array) > 0) {
            $eslesen_sayisi = 0;
            foreach ($istenenler_array as $istenen) {
                // Adayın yetenekleri içinde bu kelime geçiyor mu?
                foreach ($aday_yetenekler as $aday_yetenegi) {
                    if (strpos($aday_yetenegi, $istenen) !== false || strpos($istenen, $aday_yetenegi) !== false) {
                        $eslesen_sayisi++;
                        break;
                    }
                }
            }
            $skor = round(($eslesen_sayisi / count($istenenler_array)) * 100);
        } else {
            // İlanda yetenek belirtilmemişse genel bir puan verilebilir veya adayın mesleğine vs. bakılabilir.
            // Şimdilik 50 veriyoruz.
            $skor = 50; 
        }

        $stmt = $conn->prepare("INSERT INTO basvuru (aday_id, ilan_id, ai_eslesme_skoru) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $aday_id, $ilan_id, $skor);
        
        try {
            if ($stmt->execute()) {
                $_SESSION['success'] = "Başvurunuz başarıyla yapıldı. Eşleşme Skorunuz: %" . $skor;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Bu ilana zaten başvuru yaptınız!";
        }
        
        $from = isset($_POST['from']) ? $_POST['from'] : 'panel';
        if ($from == 'anasayfa') {
            header("Location: anasayfa.php");
        } else {
            header("Location: panel.php");
        }
        exit();
    } elseif ($islem == 'profil_guncelle' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $telefon = $_POST['telefon'];
        $meslek = $_POST['meslek'];

        $stmt = $conn->prepare("UPDATE aday SET telefon = ?, meslek = ? WHERE aday_id = ?");
        $stmt->bind_param("ssi", $telefon, $meslek, $aday_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profiliniz başarıyla güncellendi.";
        } else {
            $_SESSION['error'] = "Profil güncellenirken bir hata oluştu.";
        }
        header("Location: panel.php");
        exit();
    } elseif ($islem == 'egitim_sil' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $egitim_id = $_POST['egitim_id'];

        $stmt = $conn->prepare("DELETE FROM egitim WHERE egitim_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $egitim_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();
    } elseif ($islem == 'deneyim_sil' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $deneyim_id = $_POST['deneyim_id'];

        $stmt = $conn->prepare("DELETE FROM deneyim WHERE deneyim_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $deneyim_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();
    } elseif ($islem == 'yetenek_sil' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $yetenek_id = $_POST['yetenek_id'];

        $stmt = $conn->prepare("DELETE FROM yetenek WHERE yetenek_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $yetenek_id, $aday_id);
        $stmt->execute();
        
        header("Location: panel.php");
        exit();
    } elseif ($islem == 'basvuru_sil' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'aday') {
        $aday_id = $_SESSION['user_id'];
        $basvuru_id = $_POST['basvuru_id'];

        $stmt = $conn->prepare("DELETE FROM basvuru WHERE basvuru_id = ? AND aday_id = ?");
        $stmt->bind_param("ii", $basvuru_id, $aday_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Başvurunuz başarıyla iptal edildi.";
        } else {
            $_SESSION['error'] = "Başvuru silinirken bir hata oluştu.";
        }
        
        header("Location: panel.php");
        exit();
    } elseif ($islem == 'ilan_sil' && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'isveren') {
        $isveren_id = $_SESSION['user_id'];
        $ilan_id = $_POST['ilan_id'];

        $stmt = $conn->prepare("DELETE FROM is_ilani WHERE ilan_id = ? AND isveren_id = ?");
        $stmt->bind_param("ii", $ilan_id, $isveren_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "İlan başarıyla silindi.";
        } else {
            $_SESSION['error'] = "İlan silinirken bir hata oluştu.";
        }
        
        header("Location: isveren_panel.php");
        exit();
    }
}

// GET istekleri (Çıkış Yap)
if (isset($_GET['islem']) && $_GET['islem'] == 'cikis') {
    session_destroy();
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
?>

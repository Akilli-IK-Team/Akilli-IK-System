<?php
require_once 'db.php';

// Sabit Yönetici Şifresi
$ADMIN_PASSWORD = 'Patron123!';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem'])) {
    $islem = $_POST['islem'];

    if ($islem == 'admin_giris') {
        $sifre = $_POST['sifre'];
        if ($sifre === $ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php");
        } else {
            $_SESSION['admin_error'] = "Hatalı yönetici şifresi!";
            header("Location: admin.php");
        }
        exit();
    }

    // Buradan sonraki işlemler için admin girişi şart
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: admin.php");
        exit();
    }

    if ($islem == 'aday_sil') {
        $aday_id = $_POST['aday_id'];
        $stmt = $conn->prepare("DELETE FROM aday WHERE aday_id = ?");
        $stmt->bind_param("i", $aday_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Aday ve adaya ait tüm veriler başarıyla silindi.";
        } else {
            $_SESSION['admin_error'] = "Aday silinirken hata oluştu.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($islem == 'isveren_sil') {
        $isveren_id = $_POST['isveren_id'];
        $stmt = $conn->prepare("DELETE FROM isveren WHERE isveren_id = ?");
        $stmt->bind_param("i", $isveren_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "İşveren ve işverene ait tüm ilanlar başarıyla silindi.";
        } else {
            $_SESSION['admin_error'] = "İşveren silinirken hata oluştu.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($islem == 'ilan_sil') {
        $ilan_id = $_POST['ilan_id'];
        $stmt = $conn->prepare("DELETE FROM is_ilani WHERE ilan_id = ?");
        $stmt->bind_param("i", $ilan_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "İlan başarıyla silindi.";
        } else {
            $_SESSION['admin_error'] = "İlan silinirken hata oluştu.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($islem == 'sifre_sifirla') {
        $hedef_id = $_POST['hedef_id'];
        $hedef_tip = $_POST['hedef_tip']; // 'aday' veya 'isveren'
        
        $yeni_sifre = '123456';
        $hashed_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);

        if ($hedef_tip == 'aday') {
            $stmt = $conn->prepare("UPDATE aday SET sifre = ? WHERE aday_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE isveren SET sifre = ? WHERE isveren_id = ?");
        }

        $stmt->bind_param("si", $hashed_sifre, $hedef_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Kullanıcının şifresi başarıyla '123456' olarak sıfırlandı.";
        } else {
            $_SESSION['admin_error'] = "Şifre sıfırlanırken bir hata oluştu.";
        }
        header("Location: admin.php");
        exit();
    }
}

// Çıkış
if (isset($_GET['islem']) && $_GET['islem'] == 'cikis') {
    unset($_SESSION['admin_logged_in']);
    header("Location: admin.php");
    exit();
}

header("Location: admin.php");
exit();
?>

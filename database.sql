CREATE DATABASE IF NOT EXISTS akilli_ik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE akilli_ik;

CREATE TABLE IF NOT EXISTS aday (
    aday_id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    soyad VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefon VARCHAR(20),
    dogum_tarihi DATE,
    meslek VARCHAR(100),
    sifre VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS isveren (
    isveren_id INT AUTO_INCREMENT PRIMARY KEY,
    sirket_adi VARCHAR(150) NOT NULL,
    sektor VARCHAR(100),
    email VARCHAR(150) NOT NULL UNIQUE,
    telefon VARCHAR(20),
    sifre VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS is_ilani (
    ilan_id INT AUTO_INCREMENT PRIMARY KEY,
    isveren_id INT NOT NULL,
    pozisyon VARCHAR(150) NOT NULL,
    aciklama TEXT,
    maas_araligi VARCHAR(100),
    konum VARCHAR(150),
    son_basvuru DATE,
    FOREIGN KEY (isveren_id) REFERENCES isveren(isveren_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS egitim (
    egitim_id INT AUTO_INCREMENT PRIMARY KEY,
    aday_id INT NOT NULL,
    okul VARCHAR(150) NOT NULL,
    bolum VARCHAR(150),
    derece VARCHAR(50),
    mezuniyet_yili INT,
    FOREIGN KEY (aday_id) REFERENCES aday(aday_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS deneyim (
    deneyim_id INT AUTO_INCREMENT PRIMARY KEY,
    aday_id INT NOT NULL,
    sirket VARCHAR(150) NOT NULL,
    pozisyon VARCHAR(150) NOT NULL,
    baslangic_tarihi DATE,
    bitis_tarihi DATE,
    FOREIGN KEY (aday_id) REFERENCES aday(aday_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS yetenek (
    yetenek_id INT AUTO_INCREMENT PRIMARY KEY,
    aday_id INT NOT NULL,
    yetenek_adi VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    seviye ENUM('Başlangıç', 'Orta', 'İleri', 'Uzman') DEFAULT 'Orta',
    FOREIGN KEY (aday_id) REFERENCES aday(aday_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS basvuru (
    basvuru_id INT AUTO_INCREMENT PRIMARY KEY,
    aday_id INT NOT NULL,
    ilan_id INT NOT NULL,
    durum ENUM('Bekliyor', 'İnceleniyor', 'Kabul Edildi', 'Reddedildi') DEFAULT 'Bekliyor',
    ai_eslesme_skoru INT DEFAULT 0,
    basvuru_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aday_id) REFERENCES aday(aday_id) ON DELETE CASCADE,
    FOREIGN KEY (ilan_id) REFERENCES is_ilani(ilan_id) ON DELETE CASCADE,
    UNIQUE KEY (aday_id, ilan_id)
);

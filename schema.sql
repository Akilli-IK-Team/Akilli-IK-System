-- SmartHR Veritabanı Şeması (MySQL)

CREATE DATABASE IF NOT EXISTS smarthr_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smarthr_db;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('candidate', 'employer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Candidates (
    candidate_id INT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    experience_years INT DEFAULT 0,
    FOREIGN KEY (candidate_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Employers (
    employer_id INT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (employer_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

CREATE TABLE Skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE Candidate_Skills (
    candidate_id INT,
    skill_id INT,
    PRIMARY KEY (candidate_id, skill_id),
    FOREIGN KEY (candidate_id) REFERENCES Candidates(candidate_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES Skills(skill_id) ON DELETE CASCADE
);

CREATE TABLE Jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    required_experience_years INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES Employers(employer_id) ON DELETE CASCADE
);

CREATE TABLE Job_Skills (
    job_id INT,
    skill_id INT,
    PRIMARY KEY (job_id, skill_id),
    FOREIGN KEY (job_id) REFERENCES Jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES Skills(skill_id) ON DELETE CASCADE
);

CREATE TABLE Applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    candidate_id INT,
    match_score DECIMAL(5,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'Pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(job_id, candidate_id),
    FOREIGN KEY (job_id) REFERENCES Jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES Candidates(candidate_id) ON DELETE CASCADE
);

-- Trigger: Aday başvurduğunda ilan gereksinimleri ile aday yeteneklerini kıyaslayıp otomatik % puan hesaplar
DELIMITER //

CREATE TRIGGER trg_calculate_match_score
BEFORE INSERT ON Applications
FOR EACH ROW
BEGIN
    DECLARE req_skills_count INT;
    DECLARE matched_skills_count INT;
    DECLARE calculated_score DECIMAL(5,2);

    -- İlan için gereken toplam yetenek sayısını bul
    SELECT COUNT(*) INTO req_skills_count
    FROM Job_Skills
    WHERE job_id = NEW.job_id;

    -- İlan için spesifik bir yetenek gerekmiyorsa %100 eşleşme ver
    IF req_skills_count = 0 THEN
        SET NEW.match_score = 100.00;
    ELSE
        -- Adayın yetenekleri ile ilanın yeteneklerinin kesişimini (eşleşen sayısını) bul
        SELECT COUNT(*) INTO matched_skills_count
        FROM Job_Skills js
        JOIN Candidate_Skills cs ON js.skill_id = cs.skill_id
        WHERE js.job_id = NEW.job_id AND cs.candidate_id = NEW.candidate_id;

        -- Yüzdelik skor hesabı
        SET calculated_score = (matched_skills_count / req_skills_count) * 100;
        SET NEW.match_score = calculated_score;
    END IF;
END //

DELIMITER ;

-- Örnek Veri Eklemeleri (Mock Data)
INSERT INTO Users (email, password_hash, user_type) VALUES ('employer@test.com', 'hashedpassword', 'employer');
INSERT INTO Users (email, password_hash, user_type) VALUES ('candidate@test.com', 'hashedpassword', 'candidate');

INSERT INTO Employers (employer_id, company_name, description) VALUES (1, 'TechCorp A.Ş.', 'Yazılım Çözümleri');
INSERT INTO Candidates (candidate_id, first_name, last_name, experience_years) VALUES (2, 'Ali', 'Yılmaz', 3);

INSERT INTO Skills (skill_name) VALUES ('PHP'), ('MySQL'), ('JavaScript'), ('HTML'), ('CSS');

INSERT INTO Jobs (employer_id, title, description, required_experience_years) VALUES (1, 'Full Stack PHP Developer', 'PHP ve MySQL bilen...', 2);
INSERT INTO Job_Skills (job_id, skill_id) VALUES (1, 1), (1, 2), (1, 3); -- İlan için PHP, MySQL ve JS lazım

INSERT INTO Candidate_Skills (candidate_id, skill_id) VALUES (2, 1), (2, 3); -- Aday PHP ve JS biliyor

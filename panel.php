<?php
require_once 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'candidate') {
    header("Location: index.php");
    exit();
}

$aday_id = $_SESSION['user_id'];

// Fetch candidate information
$stmt = $conn->prepare("SELECT * FROM aday WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$aday = $stmt->get_result()->fetch_assoc();

// Fetch education
$stmt = $conn->prepare("SELECT * FROM egitim WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$egitimler = $stmt->get_result();

// Fetch experiences
$stmt = $conn->prepare("SELECT * FROM deneyim WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$deneyimler = $stmt->get_result();

// Fetch skills
$stmt = $conn->prepare("SELECT * FROM yetenek WHERE aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$yetenekler = $stmt->get_result();

// Fetch job listings
$ilanlar = $conn->query("SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id ORDER BY is_ilani.ilan_id DESC");

// Fetch applications
$stmt = $conn->prepare("SELECT basvuru.*, is_ilani.pozisyon, isveren.sirket_adi FROM basvuru JOIN is_ilani ON basvuru.ilan_id = is_ilani.ilan_id JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id WHERE basvuru.aday_id = ?");
$stmt->bind_param("i", $aday_id);
$stmt->execute();
$basvurular = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Panel - IKSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem</div>
        <div class="nav-links">
            <span style="color: #555; margin-right: 20px;">Hello, <?= htmlspecialchars($aday['ad'] . ' ' . $aday['soyad']) ?></span>
            <a href="anasayfa.php" style="color: var(--neon-pink); font-weight: bold;">Back to Home</a>
            <a href="islem.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Profile and Forms -->
        <div>
            <div class="card">
                <h3>Profile Information</h3>
                <p><strong>Profession:</strong> <?= htmlspecialchars($aday['meslek']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($aday['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($aday['telefon']) ?></p>
                <hr style="border-color: var(--glass-border); margin: 15px 0;">
                
                <h4 style="margin-bottom: 10px; color: var(--neon-pink);">My Skills</h4>
                <div style="margin-bottom: 15px;">
                    <?php if($yetenekler->num_rows > 0): ?>
                        <?php while($y = $yetenekler->fetch_assoc()): ?>
                            <div style="display: inline-block; margin-bottom: 5px;">
                                <?php 
                                $seviye_cevirisi = [
                                    'Başlangıç' => 'Beginner',
                                    'Orta' => 'Intermediate',
                                    'İleri' => 'Advanced',
                                    'Uzman' => 'Expert'
                                ];
                                $gosterilecek_seviye = isset($seviye_cevirisi[$y['seviye']]) ? $seviye_cevirisi[$y['seviye']] : $y['seviye'];
                                ?>
                                <span class="badge pink" style="margin-right: 5px;">
                                    <?= htmlspecialchars($y['yetenek_adi']) ?> (<?= htmlspecialchars($gosterilecek_seviye) ?>)
                                </span>
                                <form action="islem.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_skill">
                                    <input type="hidden" name="skill_id" value="<?= $y['yetenek_id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 0.8rem;">[x]</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">No skills added yet.</p>
                    <?php endif; ?>
                </div>

                <h4 style="margin-bottom: 10px; color: var(--neon-purple);">My Education</h4>
                <div style="margin-bottom: 15px;">
                    <?php if($egitimler->num_rows > 0): ?>
                        <?php while($e = $egitimler->fetch_assoc()): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 5px; margin-bottom: 5px;">
                                <p style="font-size: 0.9rem; margin:0;">🎓 <strong><?= htmlspecialchars($e['okul']) ?></strong> - <?= htmlspecialchars($e['bolum']) ?> (<?= htmlspecialchars($e['mezuniyet_yili']) ?>)</p>
                                <form action="islem.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_education">
                                    <input type="hidden" name="education_id" value="<?= $e['egitim_id'] ?>">
                                    <button type="submit" style="background: rgba(255,0,0,0.1); border: none; color: #ff6b6b; cursor: pointer; font-size: 0.7rem; padding: 2px 5px; border-radius: 3px;">Delete</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">No education records added yet.</p>
                    <?php endif; ?>
                </div>

                <h4 style="margin-bottom: 10px; color: var(--neon-purple);">My Experiences</h4>
                <div>
                    <?php if($deneyimler->num_rows > 0): ?>
                        <?php while($d = $deneyimler->fetch_assoc()): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 5px; margin-bottom: 5px;">
                                <p style="font-size: 0.9rem; margin:0;">💼 <strong><?= htmlspecialchars($d['sirket']) ?></strong> - <?= htmlspecialchars($d['pozisyon']) ?></p>
                                <form action="islem.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_experience">
                                    <input type="hidden" name="experience_id" value="<?= $d['deneyim_id'] ?>">
                                    <button type="submit" style="background: rgba(255,0,0,0.1); border: none; color: #ff6b6b; cursor: pointer; font-size: 0.7rem; padding: 2px 5px; border-radius: 3px;">Delete</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-size: 0.8rem; color: #aaa;">No experiences added yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h3>Update Profile</h3>
                <form action="islem.php" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($aday['telefon']) ?>" pattern="[0-9]{11}" minlength="11" maxlength="11" title="Please enter your 11-digit phone number (digits only)" required>
                    </div>
                    <div class="form-group">
                        <label>Profession</label>
                        <input type="text" name="profession" value="<?= htmlspecialchars($aday['meslek']) ?>" required>
                    </div>
                    <button type="submit" class="btn">Update</button>
                </form>
            </div>

            <div class="card">
                <h3>Add Education</h3>
                <form action="islem.php" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add_education">
                    <div class="form-group"><input type="text" name="school" placeholder="School Name" required></div>
                    <div class="form-group"><input type="text" name="department" placeholder="Department" required></div>
                    <div class="form-group"><input type="text" name="degree" placeholder="Degree (Bachelor's, etc.)" required></div>
                    <div class="form-group"><input type="number" name="graduation_year" placeholder="Graduation Year" required></div>
                    <button type="submit" class="btn">Add</button>
                </form>
            </div>

            <div class="card">
                <h3>Add Experience</h3>
                <form action="islem.php" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add_experience">
                    <div class="form-group"><input type="text" name="company" placeholder="Company" required></div>
                    <div class="form-group"><input type="text" name="position" placeholder="Position" required></div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date (Leave blank if current)</label>
                        <input type="date" name="end_date">
                    </div>
                    <button type="submit" class="btn">Add</button>
                </form>
            </div>

            <div class="card">
                <h3>Add Skill</h3>
                <form action="islem.php" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add_skill">
                    <div class="form-group"><input type="text" name="skill_name" placeholder="Skill Name (e.g., PHP)" required></div>
                    <div class="form-group"><input type="text" name="category" placeholder="Category (e.g., Software)"></div>
                    <div class="form-group">
                        <select name="level" required>
                            <option value="Başlangıç">Beginner</option>
                            <option value="Orta" selected>Intermediate</option>
                            <option value="İleri">Advanced</option>
                            <option value="Uzman">Expert</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Add</button>
                </form>
            </div>
        </div>

        <!-- Applications -->
        <div>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>My Applications</h3>
                <?php if($basvurular->num_rows > 0): ?>
                    <div class="job-list-container">
                    <?php while($b = $basvurular->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4><?= htmlspecialchars($b['pozisyon']) ?></h4>
                            <p><?= htmlspecialchars($b['sirket_adi']) ?></p>
                            <span class="badge">Status: <?= htmlspecialchars($b['durum']) ?></span>
                            <span class="badge pink">AI Score: %<?= $b['ai_eslesme_skoru'] ?></span>
                            <form action="islem.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="delete_application">
                                <input type="hidden" name="application_id" value="<?= $b['basvuru_id'] ?>">
                                <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: rgba(255,0,0,0.1); color: #ff6b6b; border: 1px solid rgba(255,0,0,0.3);">Cancel</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #aaa;">You haven't made any applications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
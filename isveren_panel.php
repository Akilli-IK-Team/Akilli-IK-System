<?php
require_once 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

// Fetch employer's job listings
$stmt = $conn->prepare("SELECT * FROM is_ilani WHERE isveren_id = ? ORDER BY ilan_id DESC");
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$ilanlar = $stmt->get_result();

// Fetch incoming applications
$stmt = $conn->prepare("
    SELECT basvuru.*, is_ilani.pozisyon, aday.ad, aday.soyad, aday.email, aday.meslek 
    FROM basvuru 
    JOIN is_ilani ON basvuru.ilan_id = is_ilani.ilan_id 
    JOIN aday ON basvuru.aday_id = aday.aday_id 
    WHERE is_ilani.isveren_id = ? 
    ORDER BY basvuru.ai_eslesme_skoru DESC
");
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$basvurular = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Panel - IKSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem (Employer)</div>
        <div class="nav-links">
            <span style="color: #555; margin-right: 20px;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="anasayfa.php" style="color: var(--neon-purple); font-weight: bold;">Back to Home</a>
            <a href="islem.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Forms -->
        <div>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Post a New Job</h3>
                <form action="islem.php" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add_job">
                    <div class="form-group"><input type="text" name="position" placeholder="Position Title" required></div>
                    <div class="form-group"><textarea name="description" placeholder="Job Description" rows="4"></textarea></div>
                    <div class="form-group"><input type="text" name="salary_range" placeholder="Salary Range (e.g., 20K - 30K)"></div>
                    <div class="form-group"><input type="text" name="location" placeholder="Location (e.g., London / Remote)"></div>
                    <div class="form-group"><input type="text" name="required_skills" placeholder="Required Skills (Comma separated, e.g., PHP, MySQL, CSS)"></div>
                    <div class="form-group">
                        <label>Application Deadline</label>
                        <input type="date" name="deadline" required>
                    </div>
                    <button type="submit" class="btn">Post Job</button>
                </form>
            </div>

            <div class="card">
                <h3>My Current Listings</h3>
                <?php if($ilanlar->num_rows > 0): ?>
                    <div class="job-list-container">
                    <?php while($ilan = $ilanlar->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4><?= htmlspecialchars($ilan['pozisyon']) ?></h4>
                            <p><strong>Description:</strong> <?= htmlspecialchars($ilan['aciklama']) ?></p>
                            <p><strong>Required Skills:</strong> <?= htmlspecialchars($ilan['istenen_yetenekler']) ?></p>
                            <span class="badge">Deadline: <?= htmlspecialchars($ilan['son_basvuru']) ?></span>
                            <form action="islem.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="delete_job">
                                <input type="hidden" name="job_id" value="<?= $ilan['ilan_id'] ?>">
                                <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: rgba(255,0,0,0.1); color: #ff6b6b; border: 1px solid rgba(255,0,0,0.3);">Delete Job</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #aaa;">You haven't posted any jobs yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Applications -->
        <div>
            <div class="card">
                <h3>Applications Received (By AI Score)</h3>
                <?php if($basvurular->num_rows > 0): ?>
                    <div class="job-list-container">
                    <?php while($b = $basvurular->fetch_assoc()): ?>
                        <div class="list-item">
                            <h4 style="color: var(--neon-pink);"><?= htmlspecialchars($b['ad'] . ' ' . $b['soyad']) ?> <span class="badge pink" style="float:right;">AI Score: %<?= $b['ai_eslesme_skoru'] ?></span></h4>
                            <p><strong>Applied Position:</strong> <?= htmlspecialchars($b['pozisyon']) ?></p>
                            <p><strong>Candidate's Profession:</strong> <?= htmlspecialchars($b['meslek']) ?></p>
                            <p><strong>Contact:</strong> <?= htmlspecialchars($b['email']) ?></p>
                            <span class="badge">Status: <?= htmlspecialchars($b['durum']) ?></span>
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #aaa;">No applications received yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once 'db.php';

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if ($is_logged_in) {
    // Fetch Candidates
    $aday_sorgu = $conn->query("SELECT * FROM aday ORDER BY aday_id DESC");

    // Fetch Employers
    $isveren_sorgu = $conn->query("SELECT * FROM isveren ORDER BY isveren_id DESC");

    // Fetch Job Advertisements
    $ilan_sorgu = $conn->query("SELECT is_ilani.*, isveren.sirket_adi FROM is_ilani JOIN isveren ON is_ilani.isveren_id = isveren.isveren_id ORDER BY is_ilani.ilan_id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Administrator - IKSystem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            overflow: hidden;
            font-size: 0.9rem;
        }
        th, td {
            padding: 10px;
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
        .action-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 5px;
        }
        .btn-delete {
            background: rgba(255,0,0,0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255,0,0,0.5);
        }
        .btn-reset {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.5);
        }
        .admin-login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">IKSystem (Admin Panel)</div>
        <div class="nav-links">
            <a href="index.php">Back to Home</a>
            <?php if($is_logged_in): ?>
                <a href="admin_islem.php?action=logout" style="color: #ff6b6b; margin-left: 15px;">Secure Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-container" style="grid-template-columns: 1fr;">
        
        <?php if(isset($_SESSION['admin_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></div>
        <?php endif; ?>

        <?php if(!$is_logged_in): ?>
            <!-- Admin Login Form -->
            <div class="card admin-login-card">
                <h3 style="text-align: center; color: var(--neon-pink);">Administrator Login</h3>
                <form action="admin_islem.php" method="POST">
                    <input type="hidden" name="action" value="admin_login">
                    <div class="form-group">
                        <label>Admin Password</label>
                        <input type="password" name="password" required placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Login</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Panel Content -->
            <div class="card">
                <h3 style="color: var(--neon-pink);">Registered Candidates Management</h3>
                <?php if($aday_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Profession</th>
                                <th>Actions</th>
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
                                    <td style="display: flex;">
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Are you sure you want to reset the password to 123456?');">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="target_type" value="candidate">
                                            <input type="hidden" name="target_id" value="<?= $aday['aday_id'] ?>">
                                            <button type="submit" class="action-btn btn-reset" title="Reset Password">Reset</button>
                                        </form>
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this candidate and all their data? This action cannot be undone!');">
                                            <input type="hidden" name="action" value="delete_candidate">
                                            <input type="hidden" name="candidate_id" value="<?= $aday['aday_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No registered candidates found in the system.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="color: var(--neon-purple);">Registered Employers Management</h3>
                <?php if($isveren_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Industry</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
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
                                    <td style="display: flex;">
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Are you sure you want to reset the password to 123456?');">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="target_type" value="employer">
                                            <input type="hidden" name="target_id" value="<?= $isveren['isveren_id'] ?>">
                                            <button type="submit" class="action-btn btn-reset" title="Reset Password">Reset</button>
                                        </form>
                                        <form action="admin_islem.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this employer and all their job listings?');">
                                            <input type="hidden" name="action" value="delete_employer">
                                            <input type="hidden" name="employer_id" value="<?= $isveren['isveren_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No registered employers found in the system.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="color: #4CAF50;">All Job Advertisements in System</h3>
                <?php if($ilan_sorgu->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ilan = $ilan_sorgu->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $ilan['ilan_id'] ?></td>
                                    <td><?= htmlspecialchars($ilan['sirket_adi']) ?></td>
                                    <td><?= htmlspecialchars($ilan['pozisyon']) ?></td>
                                    <td><?= htmlspecialchars($ilan['konum']) ?></td>
                                    <td><?= htmlspecialchars($ilan['son_basvuru']) ?></td>
                                    <td>
                                        <form action="admin_islem.php" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this job listing?');">
                                            <input type="hidden" name="action" value="delete_job">
                                            <input type="hidden" name="job_id" value="<?= $ilan['ilan_id'] ?>">
                                            <button type="submit" class="action-btn btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No published job advertisements found in the system.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
require_once 'db.php';

// Static Admin Password
$ADMIN_PASSWORD = 'Patron123!';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'admin_login') {
        $password = $_POST['password'];
        if ($password === $ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php");
        } else {
            $_SESSION['admin_error'] = "Incorrect admin password!";
            header("Location: admin.php");
        }
        exit();
    }

    // Admin login is required for the following operations
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: admin.php");
        exit();
    }

    if ($action == 'delete_candidate') {
        $candidate_id = $_POST['candidate_id'];
        $stmt = $conn->prepare("DELETE FROM aday WHERE aday_id = ?");
        $stmt->bind_param("i", $candidate_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Candidate and all associated data have been successfully deleted.";
        } else {
            $_SESSION['admin_error'] = "An error occurred while deleting the candidate.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($action == 'delete_employer') {
        $employer_id = $_POST['employer_id'];
        $stmt = $conn->prepare("DELETE FROM isveren WHERE isveren_id = ?");
        $stmt->bind_param("i", $employer_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Employer and all associated jobs have been successfully deleted.";
        } else {
            $_SESSION['admin_error'] = "An error occurred while deleting the employer.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($action == 'delete_job') {
        $job_id = $_POST['job_id'];
        $stmt = $conn->prepare("DELETE FROM is_ilani WHERE ilan_id = ?");
        $stmt->bind_param("i", $job_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Job advertisement has been successfully deleted.";
        } else {
            $_SESSION['admin_error'] = "An error occurred while deleting the job advertisement.";
        }
        header("Location: admin.php");
        exit();

    } elseif ($action == 'reset_password') {
        $target_id = $_POST['target_id'];
        $target_type = $_POST['target_type']; // 'candidate' or 'employer'
        
        $new_password = '123456';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        if ($target_type == 'candidate') {
            $stmt = $conn->prepare("UPDATE aday SET sifre = ? WHERE aday_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE isveren SET sifre = ? WHERE isveren_id = ?");
        }

        $stmt->bind_param("si", $hashed_password, $target_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "User password has been successfully reset to '123456'.";
        } else {
            $_SESSION['admin_error'] = "An error occurred while resetting the password.";
        }
        header("Location: admin.php");
        exit();
    }
}

// Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    header("Location: admin.php");
    exit();
}

header("Location: admin.php");
exit();
?>
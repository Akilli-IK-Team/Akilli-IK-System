<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $user_type = $_POST['user_type'] ?? 'candidate';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header("Location: login.php?error=Please fill in all fields.");
            exit;
        }

        try {
            // Check if email is already in use
            $checkStmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->rowCount() > 0) {
                header("Location: login.php?error=This email address is already in use.");
                exit;
            }

            // Hash the password (using bcrypt for security)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction because we will write to 2 tables (Users and Profiles)
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Users (email, password_hash, user_type) VALUES (?, ?, ?)");
            $stmt->execute([$email, $password_hash, $user_type]);
            $user_id = $pdo->lastInsertId();

            if ($user_type === 'candidate') {
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $stmt_cand = $pdo->prepare("INSERT INTO Candidates (candidate_id, first_name, last_name) VALUES (?, ?, ?)");
                $stmt_cand->execute([$user_id, $first_name, $last_name]);
            } else {
                $company_name = trim($_POST['company_name'] ?? '');
                $stmt_emp = $pdo->prepare("INSERT INTO Employers (employer_id, company_name) VALUES (?, ?)");
                $stmt_emp->execute([$user_id, $company_name]);
            }

            $pdo->commit();
            header("Location: login.php?success=1");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            header("Location: login.php?error=A database error occurred.");
            exit;
        }

    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header("Location: login.php?error=Email and password are required.");
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['email'] = $user['email'];
                header("Location: index.php");
                exit;
            } else {
                // Login failed
                header("Location: login.php?error=Incorrect email or password.");
                exit;
            }
        } catch (PDOException $e) {
            header("Location: login.php?error=A database error occurred.");
            exit;
        }
    }
}
?>

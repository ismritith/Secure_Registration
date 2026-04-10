<?php
session_start();
header('Content-Type: application/json');

require 'db_connection.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    // Brute force protection
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as failed_attempts 
        FROM login_history 
        WHERE ip_address = :ip 
        AND status = 'failed' 
        AND login_time > NOW() - INTERVAL 15 MINUTE
    ");
    $stmt->execute([':ip' => $ip]);
    if ($stmt->fetch()['failed_attempts'] >= 5) {
        echo json_encode(['success' => false, 'message' => 'Too many attempts. Try later.']);
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT id, first_name, password FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {

        $stmt = $pdo->prepare("
            INSERT INTO login_history (user_id, ip_address, user_agent, status) 
            VALUES (:user_id, :ip, :ua, 'failed')
        ");
        $stmt->execute([
            ':user_id' => $user['id'] ?? null,
            ':ip' => $ip,
            ':ua' => $userAgent
        ]);

        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Generate OTP
    $otp = random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $otpExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $pdo->prepare("
        UPDATE users 
        SET otp_hash = :otp, otp_expiry = :expiry 
        WHERE id = :id
    ");
    $stmt->execute([
        ':otp' => $otpHash,
        ':expiry' => $otpExpiry,
        ':id' => $user['id']
    ]);

    // Send Email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'smritiththapa@gmail.com';
        $mail->Password   = 'sxyygrizfhnbjwmm'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'SecureRegister');
        $mail->addAddress($email, $user['first_name']);
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body = "Your OTP is <b>$otp</b>. Valid for 5 minutes.";

        $mail->send();

   } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Mailer Error: ' . $mail->ErrorInfo
        ]);
        exit;
    }

    $_SESSION['2fa_user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'];

    echo json_encode([
        'success' => true,
        'otp_required' => true,
        'message' => 'OTP sent'
    ]);
    exit;
}
exit;
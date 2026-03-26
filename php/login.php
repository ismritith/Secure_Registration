<?php
// login.php
session_start();
require 'db_connection.php';

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $errors = [];

    // Optional: Enable reCAPTCHA verification if you have valid keys
    /*
    if (empty($recaptchaResponse)) {
        $errors[] = "Captcha verification failed";
    } else {
        $secretKey = 'YOUR_SECRET_KEY';
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $ch = curl_init($verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseKeys = json_decode($response, true);
        if (empty($responseKeys['success'])) {
            $errors[] = "Captcha verification failed";
        }
    }
    */

    // Basic validation
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';

    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }

    // Check failed attempts (last 15 min)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as failed_attempts 
        FROM login_history 
        WHERE ip_address = :ip 
        AND status = 'failed' 
        AND login_time > NOW() - INTERVAL 15 MINUTE
    ");
    $stmt->execute([':ip' => $ip]);
    $res = $stmt->fetch();

    if ($res['failed_attempts'] >= 5) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Too many failed attempts. Try again later.'
        ]);
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Log SUCCESS login
        $stmt = $pdo->prepare("
            INSERT INTO login_history (user_id, ip_address, user_agent, status) 
            VALUES (:user_id, :ip, :ua, 'success')
        ");
        $stmt->execute([
            ':user_id' => $user['id'],
            ':ip' => $ip,
            ':ua' => $userAgent
        ]);

        // Start secure session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['first_name'];

        // Return success JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Login successful'
        ]);
        exit;

    } else {
        // Log FAILED login
        $stmt = $pdo->prepare("
            INSERT INTO login_history (user_id, ip_address, user_agent, status) 
            VALUES (:user_id, :ip, :ua, 'failed')
        ");
        $stmt->execute([
            ':user_id' => $user['id'] ?? null,
            ':ip' => $ip,
            ':ua' => $userAgent
        ]);

        // Return failure JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

} else {
    // Redirect if not POST
    header('Location: ../front/login.html');
    exit;
}
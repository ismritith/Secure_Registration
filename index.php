<?php
session_start();
require 'php/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: front/login.html");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Username';
$userEmail = $_SESSION['user_email'] ?? '';

// Get last successful login
$stmt = $pdo->prepare("
    SELECT ip_address, user_agent, login_time 
    FROM login_history 
    WHERE user_id = :user_id AND status = 'success'
    ORDER BY login_time DESC
    LIMIT 1
");
$stmt->execute([':user_id' => $userId]);
$lastLogin = $stmt->fetch();

// Get failed attempts in last 24 hours
$stmt = $pdo->prepare("
    SELECT COUNT(*) as failed_count 
    FROM login_history 
    WHERE user_id = :user_id AND status = 'failed' 
    AND login_time > NOW() - INTERVAL 1 DAY
");
$stmt->execute([':user_id' => $userId]);
$failedCount = $stmt->fetchColumn();

// Get last 20 login history
$stmt = $pdo->prepare("
    SELECT ip_address, user_agent, status, login_time 
    FROM login_history 
    WHERE user_id = :user_id
    ORDER BY login_time DESC
    LIMIT 20
");
$stmt->execute([':user_id' => $userId]);
$loginHistory = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Login History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar { height: 100vh; transition: all 0.3s; }
        .collapsed { width: 60px; }
        .collapsed .nav-link span { display: none; }
        .collapsed .nav-link i { margin-right: 0; }
        .profile-icon { width: 50px; height: 50px; border-radius: 50%; background-color: #ccc; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; }
        .success { color: green; font-weight: bold; }
        .failed { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="bg-dark text-white sidebar col-md-2 col-sm-3" id="sidebar">
            <div class="p-3 text-center">
                <div class="profile-icon"><i class="fas fa-user"></i></div>
                <p class="mb-0"><?php echo htmlspecialchars($userName); ?></p>
                <small><?php echo htmlspecialchars($userEmail); ?></small>
            </div>
            <ul class="flex-column nav">
                <li class="nav-item"><a class="text-white nav-link" href="dashboard.php"><i class="me-2 fas fa-home"></i><span>Home</span></a></li>
                <li class="nav-item"><a class="text-white nav-link" href="#"><i class="me-2 fas fa-chart-bar"></i><span>Analytics</span></a></li>
                <li class="nav-item"><a class="text-white nav-link" href="#"><i class="me-2 fas fa-cog"></i><span>Settings</span></a></li>
                <li class="nav-item"><a class="text-white nav-link" href="php/logout.php"><i class="me-2 fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="col-md-10 col-sm-9" id="main">
            <button class="btn btn-dark d-md-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="p-4">
                <h2>Welcome, <?php echo htmlspecialchars($userName); ?>!</h2>

                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Last Successful Login</h5>
                                <?php if($lastLogin): ?>
                                    <p><strong>IP:</strong> <?php echo htmlspecialchars($lastLogin['ip_address']); ?></p>
                                    <p><strong>Device:</strong> <?php echo htmlspecialchars($lastLogin['user_agent']); ?></p>
                                    <p><strong>Time:</strong> <?php echo htmlspecialchars($lastLogin['login_time']); ?></p>
                                <?php else: ?>
                                    <p>No login recorded yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>Failed Attempts (24h)</h5>
                                <p><?php echo $failedCount; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4">Login History (Last 20)</h4>
                <table class="table table-bordered table-striped mt-2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>IP Address</th>
                            <th>Device / Browser</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($loginHistory as $index => $log): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_agent']); ?></td>
                            <td class="<?php echo $log['status'] === 'success' ? 'success' : 'failed'; ?>">
                                <?php echo ucfirst($log['status']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($loginHistory)): ?>
                        <tr><td colspan="5" class="text-center">No login history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
}
</script>
</body>
</html>
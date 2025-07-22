<?php
session_start();
require_once 'config.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($_POST['password'], $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
        } else {
            $login_error = "Username atau password salah!";
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Admin - SUS Survey</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                .login-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
                h2 { text-align: center; margin-bottom: 30px; color: #2c3e50; }
                .form-group { margin-bottom: 20px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; }
                .btn { width: 100%; background: #3498db; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
                .btn:hover { background: #2980b9; }
                .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
                .back-link { text-align: center; margin-top: 20px; }
                .back-link a { color: #666; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="login-card">
                <h2>Login Admin</h2>
                <?php if (isset($login_error)): ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
                <div class="back-link">
                    <a href="index.php">← Kembali ke Survei</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle export
if (isset($_GET['export'])) {
    $stmt = $pdo->query("SELECT * FROM survey_responses ORDER BY created_at DESC");
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sus_survey_export_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header CSV
    fputcsv($output, [
        'ID', 'Nama', 'Email', 'Q1', 'Q2', 'Q3', 'Q4', 'Q5', 
        'Q6', 'Q7', 'Q8', 'Q9', 'Q10', 'Skor Total', 'Interpretasi', 'Tanggal'
    ]);
    
    // Data
    foreach ($responses as $response) {
        fputcsv($output, [
            $response['id'],
            $response['respondent_name'],
            $response['respondent_email'],
            $response['q1'], $response['q2'], $response['q3'], $response['q4'], $response['q5'],
            $response['q6'], $response['q7'], $response['q8'], $response['q9'], $response['q10'],
            $response['total_score'],
            $response['interpretation'],
            $response['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_responses FROM survey_responses");
$total_responses = $stmt->fetch()['total_responses'];

$stmt = $pdo->query("SELECT AVG(total_score) as avg_score FROM survey_responses");
$avg_score = round($stmt->fetch()['avg_score'], 2);

$stmt = $pdo->query("SELECT interpretation, COUNT(*) as count FROM survey_responses GROUP BY interpretation");
$interpretations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent responses
$stmt = $pdo->query("SELECT * FROM survey_responses ORDER BY created_at DESC LIMIT 10");
$recent_responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - SUS Survey</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; }
        .stat-label { font-size: 0.9em; opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { background: #3498db; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .interpretation { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .interpretation.sangat-baik { background: #d4edda; color: #155724; }
        .interpretation.baik { background: #fff3cd; color: #856404; }
        .interpretation.perlu-perbaikan { background: #f8d7da; color: #721c24; }
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 10px; }
            table { font-size: 14px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Panel Admin - SUS Survey</h1>
            <div>
                <a href="?export=1" class="btn btn-success">Export CSV</a>
                <a href="index.php" class="btn">Lihat Survei</a>
                <a href="?logout=1" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_responses; ?></div>
                <div class="stat-label">Total Responden</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $avg_score ?: '0'; ?></div>
                <div class="stat-label">Rata-rata Skor</div>
            </div>
            <?php foreach ($interpretations as $interp): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $interp['count']; ?></div>
                <div class="stat-label"><?php echo $interp['interpretation']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="card">
            <h2>Respons Terbaru</h2>
            <?php if (empty($recent_responses)): ?>
                <p>Belum ada respons survei.</p>
            <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Skor</th>
                            <th>Interpretasi</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_responses as $response): ?>
                        <tr>
                            <td><?php echo $response['id']; ?></td>
                            <td><?php echo htmlspecialchars($response['respondent_name'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($response['respondent_email'] ?: '-'); ?></td>
                            <td><strong><?php echo $response['total_score']; ?></strong></td>
                            <td>
                                <span class="interpretation <?php echo strtolower(str_replace(' ', '-', $response['interpretation'])); ?>">
                                    <?php echo $response['interpretation']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($response['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
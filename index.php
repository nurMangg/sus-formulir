<?php
require_once 'config.php';

$message = '';
$messageType = '';

if ($_POST) {
    try {
        // Validasi input
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        // Validasi semua pertanyaan dijawab
        for ($i = 1; $i <= 10; $i++) {
            if (!isset($_POST["q$i"]) || $_POST["q$i"] == '') {
                throw new Exception("Mohon jawab semua pertanyaan!");
            }
        }
        
        // Hitung skor SUS
        $score = calculateSUSScore($_POST);
        $interpretation = interpretScore($score);
        
        // Simpan ke database
        $sql = "INSERT INTO survey_responses (respondent_name, respondent_email, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, total_score, interpretation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, $email,
            $_POST['q1'], $_POST['q2'], $_POST['q3'], $_POST['q4'], $_POST['q5'],
            $_POST['q6'], $_POST['q7'], $_POST['q8'], $_POST['q9'], $_POST['q10'],
            $score, $interpretation
        ]);
        
        $message = "Terima kasih! Skor SUS Anda: $score ($interpretation)";
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$questions = [
    1 => "Saya ingin menggunakan sistem ini secara rutin.",
    2 => "Saya merasa sistem ini terlalu rumit untuk digunakan.",
    3 => "Sistem ini mudah digunakan.",
    4 => "Saya membutuhkan bantuan teknis untuk menggunakan sistem ini.",
    5 => "Fitur-fitur dalam sistem ini terintegrasi dengan baik.",
    6 => "Sistem ini seringkali tidak konsisten.",
    7 => "Kebanyakan orang akan mudah belajar menggunakan sistem ini.",
    8 => "Sistem ini terasa membingungkan untuk digunakan.",
    9 => "Saya merasa sangat percaya diri menggunakan sistem ini.",
    10 => "Saya perlu belajar banyak sebelum bisa menggunakan sistem ini."
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survei System Usability Scale (SUS)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .question { margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3498db; }
        .question h3 { margin-bottom: 15px; color: #2c3e50; }
        .scale { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .scale-item { text-align: center; margin: 5px; }
        .scale-item input[type="radio"] { margin-bottom: 5px; }
        .scale-labels { display: flex; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #666; }
        .btn { background: #3498db; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #2980b9; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .admin-link { text-align: center; margin-top: 20px; }
        .admin-link a { color: #666; text-decoration: none; font-size: 14px; }
        .admin-link a:hover { color: #333; }
        @media (max-width: 600px) {
            .scale { flex-direction: column; }
            .scale-item { margin: 10px 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Survei System Usability Scale (SUS)</h1>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nama (Opsional):</label>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama Anda">
                </div>
                
                <div class="form-group">
                    <label for="email">Email (Opsional):</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda">
                </div>
                
                <?php foreach ($questions as $num => $question): ?>
                <div class="question">
                    <h3><?php echo $num; ?>. <?php echo htmlspecialchars($question); ?></h3>
                    <div class="scale">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="scale-item">
                            <input type="radio" id="q<?php echo $num; ?>_<?php echo $i; ?>" name="q<?php echo $num; ?>" value="<?php echo $i; ?>" required>
                            <label for="q<?php echo $num; ?>_<?php echo $i; ?>"><?php echo $i; ?></label>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <div class="scale-labels">
                        <span>Sangat Tidak Setuju</span>
                        <span>Sangat Setuju</span>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn">Kirim Survei</button>
            </form>
        </div>
        
        <div class="admin-link">
            <a href="admin.php">Panel Admin</a>
        </div>
    </div>
</body>
</html>
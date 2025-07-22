<?php
// Konfigurasi database
$host = 'localhost';
$dbname = 'sus_survey';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk menghitung skor SUS
function calculateSUSScore($responses) {
    $totalScore = 0;
    
    for ($i = 1; $i <= 10; $i++) {
        if ($i % 2 == 1) {
            // Pertanyaan ganjil: skor = jawaban - 1
            $totalScore += ($responses["q$i"] - 1);
        } else {
            // Pertanyaan genap: skor = 5 - jawaban
            $totalScore += (5 - $responses["q$i"]);
        }
    }
    
    // Kalikan dengan 2.5
    return $totalScore * 2.5;
}

// Fungsi untuk interpretasi skor
function interpretScore($score) {
    if ($score >= 80) {
        return "Sangat Baik";
    } elseif ($score >= 68) {
        return "Baik";
    } else {
        return "Perlu Perbaikan";
    }
}
?>
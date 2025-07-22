#Usability Testing Aplikasi Mobile Tagihan PDAM Berbasis IoT

##Tujuan Pengujian
1. Mengukur kemudahan dan kenyamanan penggunaan aplikasi (usability) dari persepsi pengguna.
2. Menilai efektivitas, efisiensi, dan kepuasan pengguna dalam menjalankan fungsi utama aplikasi.
3. Mengidentifikasi hambatan penggunaan untuk perbaikan.

##Cara Perhitungan Skor SUS
Untuk nomor pernyataan ganjil: skor = jawaban - 1
Untuk nomor pernyataan genap: skor = 5 - jawaban

Jumlahkan skor semua pernyataan, kalikan hasilnya dengan 2.5
Skor akhir antara 0–100, dengan interpretasi umum:
80 = sangat baik
68 – 80 = baik
< 68 = perlu perbaikan

Database :
```
-- Buat database dan tabel untuk survei SUS
CREATE DATABASE sus_survey;
USE sus_survey;

-- Tabel untuk menyimpan respons survei
CREATE TABLE survey_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    respondent_name VARCHAR(255),
    respondent_email VARCHAR(255),
    q1 INT NOT NULL,
    q2 INT NOT NULL,
    q3 INT NOT NULL,
    q4 INT NOT NULL,
    q5 INT NOT NULL,
    q6 INT NOT NULL,
    q7 INT NOT NULL,
    q8 INT NOT NULL,
    q9 INT NOT NULL,
    q10 INT NOT NULL,
    total_score DECIMAL(5,2),
    interpretation VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk admin (opsional, untuk autentikasi sederhana)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default (password: admin123)
INSERT INTO admin_users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

```

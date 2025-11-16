-- Database: gizi_db
-- Sistem Diagnosa Status Gizi Anak

CREATE DATABASE IF NOT EXISTS gizi_db;
USE gizi_db;

-- Tabel Admin
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Data Anak
CREATE TABLE data_anak (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_anak VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tanggal_lahir DATE NOT NULL,
    umur_bulan INT NOT NULL,
    berat_badan DECIMAL(5,2) NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    lingkar_lengan DECIMAL(5,2),
    nama_orangtua VARCHAR(100),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Hasil Diagnosa
CREATE TABLE hasil_diagnosa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_anak_id INT NOT NULL,
    metode_diagnosa VARCHAR(50) NOT NULL,
    status_gizi VARCHAR(50) NOT NULL,
    kategori_bb_u VARCHAR(50),
    kategori_tb_u VARCHAR(50),
    kategori_bb_tb VARCHAR(50),
    z_score_bb_u DECIMAL(5,2),
    z_score_tb_u DECIMAL(5,2),
    z_score_bb_tb DECIMAL(5,2),
    rekomendasi TEXT,
    confidence_score DECIMAL(5,2),
    tanggal_diagnosa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (data_anak_id) REFERENCES data_anak(id) ON DELETE CASCADE
);

-- Tabel Dataset Training
CREATE TABLE dataset_training (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    umur_bulan INT NOT NULL,
    berat_badan DECIMAL(5,2) NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    lingkar_lengan DECIMAL(5,2),
    z_score_bb_u DECIMAL(5,2),
    z_score_tb_u DECIMAL(5,2),
    z_score_bb_tb DECIMAL(5,2),
    status_gizi VARCHAR(50) NOT NULL,
    sumber_data VARCHAR(50) DEFAULT 'WHO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Standar WHO
CREATE TABLE standar_who (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    umur_bulan INT NOT NULL,
    indikator VARCHAR(20) NOT NULL,
    median DECIMAL(5,2) NOT NULL,
    sd DECIMAL(5,2) NOT NULL,
    minus_3sd DECIMAL(5,2),
    minus_2sd DECIMAL(5,2),
    minus_1sd DECIMAL(5,2),
    plus_1sd DECIMAL(5,2),
    plus_2sd DECIMAL(5,2),
    plus_3sd DECIMAL(5,2)
);

-- Tabel Log Aktivitas
CREATE TABLE log_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    aktivitas VARCHAR(255) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password, nama_lengkap, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@gizi.com');

-- Index untuk performa
CREATE INDEX idx_jk_umur ON standar_who(jenis_kelamin, umur_bulan);
CREATE INDEX idx_data_anak_tgl ON data_anak(tanggal_lahir);
CREATE INDEX idx_diagnosa_tgl ON hasil_diagnosa(tanggal_diagnosa);
CREATE INDEX idx_dataset_jk ON dataset_training(jenis_kelamin);
-- Database: gizi_db
CREATE DATABASE IF NOT EXISTS gizi_db;
USE gizi_db;

-- Tabel users (admin/petugas)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    role ENUM('admin', 'petugas') DEFAULT 'petugas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Tabel data anak
CREATE TABLE anak (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tanggal_lahir DATE NOT NULL,
    umur_bulan INT,
    nama_ortu VARCHAR(100),
    alamat TEXT,
    no_telp VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel hasil diagnosa
CREATE TABLE diagnosa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anak_id INT,
    berat_badan DECIMAL(5,2) NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    lingkar_lengan DECIMAL(5,2),
    umur_bulan INT NOT NULL,
    status_gizi VARCHAR(50) NOT NULL,
    kategori_bb_u VARCHAR(30),
    kategori_tb_u VARCHAR(30),
    kategori_bb_tb VARCHAR(30),
    confidence_score DECIMAL(5,2),
    rekomendasi TEXT,
    petugas_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel dataset uploads
CREATE TABLE dataset_uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255),
    total_rows INT,
    uploaded_by INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Insert default admin
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gizi.com', 'Administrator', 'admin');
-- Password: password (harus diganti setelah login pertama)

-- Sample data anak
INSERT INTO anak (nama, jenis_kelamin, tanggal_lahir, umur_bulan, nama_ortu, alamat, no_telp) VALUES
('Budi Santoso', 'L', '2022-01-15', 34, 'Ibu Siti', 'Jl. Merdeka No. 10, Jakarta', '081234567890'),
('Ani Wijaya', 'P', '2021-06-20', 40, 'Ibu Dewi', 'Jl. Sudirman No. 25, Bandung', '082345678901');

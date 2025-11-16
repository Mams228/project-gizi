# Quick Start Guide
## Sistem Diagnosa Status Gizi Anak

Panduan cepat untuk menjalankan sistem dalam **15 menit**.

---

## ⚡ Setup Cepat (Quick Setup)

### 1️⃣ Requirements Check

```bash
# Cek PHP
php -v
# Harus: PHP 7.4 atau lebih tinggi

# Cek MySQL
mysql --version
# Harus: MySQL 5.7 atau lebih tinggi

# Cek Python
python --version
# Harus: Python 3.7 atau lebih tinggi
```

### 2️⃣ Extract Project

```bash
# Extract ke folder web server
# Windows XAMPP: C:/xampp/htdocs/project_gizi/
# Linux: /var/www/html/project_gizi/
# Mac: /Applications/XAMPP/htdocs/project_gizi/
```

### 3️⃣ Setup Database (2 menit)

```bash
# 1. Buka MySQL
mysql -u root -p

# 2. Buat database
CREATE DATABASE gizi_db;
exit;

# 3. Import SQL
mysql -u root -p gizi_db < database/gizi_db.sql
```

✅ Database siap!

### 4️⃣ Konfigurasi (1 menit)

Edit `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Password MySQL Anda
define('DB_NAME', 'gizi_db');
define('BASE_URL', 'http://localhost/project_gizi/');
```

✅ Konfigurasi selesai!

### 5️⃣ Install Python Libraries (2 menit)

```bash
cd model/
pip install -r requirements.txt
```

✅ Dependencies terinstall!

### 6️⃣ Generate Dataset (3 menit)

```bash
# Masih di folder model/
python generate_dataset.py
```

Output:
```
Generating 5000 samples...
Dataset generated successfully!
Total samples: 5000

Distribusi Status Gizi:
Gizi Baik      2500
Gizi Kurang     750
Stunting        750
Gizi Buruk      500
Gizi Lebih      500

Dataset saved to: dataset_gizi_anak.csv
```

✅ Dataset siap!

### 7️⃣ Train Machine Learning Model (5 menit)

```bash
# Masih di folder model/
python train_model.py
```

Output:
```
==================================================
Training Model...
==================================================

Training set: 4000 samples
Testing set: 1000 samples

Training Random Forest...
✓ Model trained successfully!
Test Accuracy: 87.50%

Cross-Validation (5-fold):
CV Mean: 86.20% (+/- 2.40%)

✓ Model saved: model_gizi_rf.pkl
✓ Label encoder saved: label_encoder_gender.pkl
✓ Metadata saved: model_metadata.json
✓ Evaluation plot saved: model_evaluation.png

==================================================
Training Complete!
==================================================
```

✅ Model siap digunakan!

### 8️⃣ Test Sistem (2 menit)

```bash
# Start web server (jika belum)
# XAMPP: Start Apache & MySQL dari control panel

# Buka browser
http://localhost/project_gizi/
```

**Test Input:**
- Nama: Budi
- Jenis Kelamin: Laki-laki
- Tanggal Lahir: 2022-01-01
- Berat: 12.5 kg
- Tinggi: 87 cm
- Lingkar Lengan: 15 cm

Klik **"Diagnosa Status Gizi"**

**Expected Result:**
- Status Gizi: Gizi Baik
- Z-Score BB/U: ~0.23
- Z-Score TB/U: ~-0.12
- Z-Score BB/TB: ~0.15
- Confidence: ~85%

✅ Sistem berfungsi!

---

## 🎯 Test Cases Lengkap

### Test Case 1: Gizi Baik
```
Input:
- Nama: Ani
- Jenis Kelamin: Perempuan
- Umur: 36 bulan
- Berat: 14.0 kg
- Tinggi: 95 cm

Expected: Status Gizi Baik
```

### Test Case 2: Gizi Kurang
```
Input:
- Nama: Citra
- Jenis Kelamin: Perempuan
- Umur: 36 bulan
- Berat: 10.5 kg
- Tinggi: 88 cm

Expected: Gizi Kurang
```

### Test Case 3: Stunting
```
Input:
- Nama: Doni
- Jenis Kelamin: Laki-laki
- Umur: 48 bulan
- Berat: 14.0 kg
- Tinggi: 92 cm

Expected: Stunting
```

### Test Case 4: Gizi Buruk
```
Input:
- Nama: Eko
- Jenis Kelamin: Laki-laki
- Umur: 24 bulan
- Berat: 8.5 kg
- Tinggi: 80 cm

Expected: Gizi Buruk
```

### Test Case 5: Gizi Lebih
```
Input:
- Nama: Fira
- Jenis Kelamin: Perempuan
- Umur: 24 bulan
- Berat: 16.0 kg
- Tinggi: 90 cm

Expected: Gizi Lebih
```

---

## 🔐 Login Admin

**URL**: `http://localhost/project_gizi/admin/`

**Kredensial Default:**
```
Username: admin
Password: admin123
```

**Menu Admin:**
- 📊 Dashboard - Statistik & grafik
- 📋 Manage Data - CRUD data anak
- 📄 View Results - Lihat hasil diagnosa
- 📤 Export - Export data ke CSV

---

## 🐛 Troubleshooting

### Error: "Connection failed"

**Problem**: Tidak bisa connect ke database

**Solution**:
```bash
# 1. Cek MySQL sudah running
# XAMPP: Start MySQL dari control panel

# 2. Cek kredensial di config.php
define('DB_USER', 'root');
define('DB_PASS', '');  // Sesuaikan dengan password Anda

# 3. Test koneksi
php -r "new mysqli('localhost', 'root', '', 'gizi_db');"
```

### Error: "No module named sklearn"

**Problem**: Library Python belum terinstall

**Solution**:
```bash
cd model/
pip install -r requirements.txt

# Atau install manual
pip install numpy pandas scikit-learn matplotlib seaborn
```

### Error: "model_gizi_rf.pkl not found"

**Problem**: Model belum di-train

**Solution**:
```bash
cd model/
python generate_dataset.py
python train_model.py
```

### Error: "Cannot execute Python script"

**Problem**: Python tidak ditemukan atau PATH belum diset

**Solution**:
```bash
# Windows: Tambahkan Python ke PATH
# Atau edit config.php
define('PYTHON_PATH', 'C:/Python39/python.exe');

# Linux/Mac
define('PYTHON_PATH', '/usr/bin/python3');
```

### Error: "Dataset kosong"

**Problem**: Generate dataset gagal

**Solution**:
```bash
cd model/

# Regenerate dengan verbose
python -u generate_dataset.py

# Cek file
ls -lh dataset_gizi_anak.csv
```

### Prediksi Tidak Akurat

**Problem**: Model akurasi rendah

**Solution**:
```bash
# 1. Generate dataset lebih besar
python generate_dataset.py  # Edit n_samples=10000

# 2. Retrain model
python train_model.py

# 3. Cek akurasi
# Harus > 85%
```

---

## 📊 Verify Installation

### Checklist ✅

```bash
# Database
✅ Database 'gizi_db' created
✅ Tables created (7 tables)
✅ Default admin inserted

# Files
✅ config.php configured
✅ model/dataset_gizi_anak.csv exists
✅ model/model_gizi_rf.pkl exists
✅ model/label_encoder_gender.pkl exists
✅ model/model_metadata.json exists

# Web Access
✅ http://localhost/project_gizi/ accessible
✅ http://localhost/project_gizi/admin/ accessible
✅ Can login as admin

# Functionality
✅ Can submit diagnosis form
✅ Can see results
✅ ML prediction working
✅ Z-scores calculated correctly
✅ Recommendations generated
```

### Quick Test Script

```bash
# test.sh
#!/bin/bash

echo "Testing Sistem Diagnosa Gizi..."

# 1. Test Database
mysql -u root -p -e "USE gizi_db; SELECT COUNT(*) FROM admin;"

# 2. Test Dataset
test -f model/dataset_gizi_anak.csv && echo "✓ Dataset exists"

# 3. Test Model
test -f model/model_gizi_rf.pkl && echo "✓ Model exists"

# 4. Test Prediction
cd model
python predict_gizi.py '{"jenis_kelamin":"L","umur_bulan":24,"berat_badan":12.5,"tinggi_badan":87,"lingkar_lengan":15}'

echo "All tests completed!"
```

---

## 🚀 Next Steps

### 1. Customize Dataset
```bash
# Edit generate_dataset.py
# Ubah n_samples, distribusi, dll
python generate_dataset.py
python train_model.py
```

### 2. Improve Model
```bash
# Coba algoritma lain:
# - XGBoost
# - LightGBM
# - Neural Networks
# - Ensemble methods
```

### 3. Add Features
```bash
# Tambahkan:
# - Export PDF
# - Email notifications
# - SMS alerts
# - Growth monitoring charts
# - Historical tracking
```

### 4. Deploy to Production
```bash
# Security:
# - Change default admin password
# - Enable HTTPS
# - Setup firewall
# - Regular backups
# - Monitor logs
```

---

## 📚 Dokumentasi Lengkap

- 📖 **README.md** - Overview sistem
- 📊 **PANDUAN_DATASET.md** - Panduan dataset WHO
- ⚡ **QUICK_START.md** - Guide ini
- 🔧 **API_DOCUMENTATION.md** - API docs (if needed)

---

## 💡 Tips

1. **Backup Database** secara rutin
2. **Monitor akurasi** model dengan data real
3. **Update dataset** dengan data aktual dari lapangan
4. **Retrain model** setiap 3-6 bulan
5. **Validasi hasil** dengan ahli gizi

---

## 🆘 Butuh Bantuan?

1. Baca dokumentasi lengkap
2. Check error logs di `logs/`
3. Cek issue di GitHub (if available)
4. Contact support

---

## ✨ Selamat!

Sistem Anda sudah siap digunakan! 🎉

**Total Setup Time**: ~15 menit
**Status**: ✅ Ready for use

Happy diagnosing! 🏥👶
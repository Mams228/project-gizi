# 📊 PANDUAN LENGKAP: Dataset & Training Model

## Daftar Isi
1. [Generate Dataset WHO](#generate-dataset-who)
2. [Training Model](#training-model)
3. [Testing Model](#testing-model)
4. [Upload Dataset via Web](#upload-via-web)
5. [Format Dataset](#format-dataset)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Generate Dataset WHO

Generator ini menghasilkan data antropometri anak yang realistis berdasarkan **WHO Child Growth Standards**.

### Cara 1: Via Command Line (Recommended)

```bash
cd model
python generate_dataset.py
```

**Output Interaktif:**
```
GENERATOR DATA ANTROPOMETRI ANAK
Berbasis Standar WHO Child Growth Standards

📊 PILIHAN DATASET:
1. Dataset Balanced (untuk ML Training) - 1000 data
2. Dataset Balanced Large - 5000 data
3. Dataset Realistis (distribusi Indonesia) - 1000 data
4. Dataset Custom

Pilih (1-4):
```

### Cara 2: Via Web Admin Panel

1. Login ke admin panel
2. Menu: **Upload Dataset**
3. Pilih: **Generate Dataset WHO**
4. Pilih jumlah data dan distribusi
5. Klik: **Generate Dataset**

### Dataset Generated

File yang dihasilkan: `data_gizi_antropometri_XXXX.csv`

**Struktur Data:**
```csv
ID,Umur_Bulan,Umur_Tahun,Jenis_Kelamin,Berat_Badan_kg,Tinggi_Badan_cm,Z_Score_BB_U,Z_Score_TB_U,Z_Score_BB_TB,Status_Gizi
A0001,24,2.0,L,12.5,85.2,-0.25,0.15,-0.45,Normal
A0002,18,1.5,P,9.8,78.5,-1.85,-1.25,-1.95,Gizi Kurang
...
```

### Distribusi Dataset

#### Balanced (untuk ML Training)
- **Normal**: 35%
- **Gizi Kurang**: 25%
- **Gizi Buruk**: 15%
- **Gizi Lebih**: 15%
- **Obesitas**: 10%

#### Realistis (Indonesia)
- **Normal**: 60%
- **Gizi Kurang**: 20%
- **Gizi Buruk**: 8%
- **Gizi Lebih**: 8%
- **Obesitas**: 4%

---

## 🤖 Training Model

### Persiapan

1. **Install Dependencies**
```bash
pip install pandas numpy scikit-learn joblib
```

2. **Generate atau Siapkan Dataset**
```bash
cd model
python generate_dataset.py
# Pilih option 2: Dataset Balanced Large - 5000 data
```

### Training via Command Line

```bash
cd model
python train_model.py
```

**Proses Training:**

```
TRAINING MODEL MACHINE LEARNING
Sistem Diagnosa Gizi Anak

📂 Data Source:
1. Generate new dataset (balanced 5000 data)
2. Use existing CSV file

Pilih (1-2): 1

⏳ Generating dataset...
✅ Dataset generated and saved to training_data.csv

📊 Dataset Info:
   Total records: 5000
   Features: ['ID', 'Umur_Bulan', 'Jenis_Kelamin', ...]

📈 Status Gizi Distribution:
   Normal         : 1750 (35.00%)
   Gizi Kurang    : 1250 (25.00%)
   Gizi Buruk     :  750 (15.00%)
   Gizi Lebih     :  750 (15.00%)
   Obesitas       :  500 (10.00%)

🔧 Preparing features...
✅ Features prepared: (5000, 10)
   Feature names: ['Umur_Bulan', 'Berat_Badan_kg', 'Tinggi_Badan_cm', ...]

📊 Data Split:
   Training set: 4000 samples
   Test set: 1000 samples

🤖 MODEL TRAINING
======================================================================

Testing RANDOM_FOREST
======================================================================
🔧 Training random_forest model...
📊 Cross-validation scores: [0.965, 0.968, 0.962, ...]
📊 Mean CV score: 0.9650 (+/- 0.0025)

📈 MODEL EVALUATION
======================================================================
✅ Accuracy: 0.9680 (96.80%)

📊 Classification Report:
              precision    recall  f1-score   support

 Gizi Baik       0.98      0.99      0.98       350
Gizi Buruk       0.96      0.95      0.96       150
...

📊 Feature Importance:
           Feature  Importance
  Z_Score_BB_TB        0.2850
  Z_Score_BB_U         0.2420
  Z_Score_TB_U         0.2180
              BMI         0.1350
  Berat_Badan_kg         0.0650
...

======================================================================
🏆 BEST MODEL: RANDOM_FOREST
🎯 Accuracy: 0.9680 (96.80%)
======================================================================

💾 Save this model? (y/n): y

💾 Model saved to:
   - best_model.joblib
   - scaler.joblib
   - label_encoder.joblib

✅ Training completed successfully!
🎯 Model ready for prediction!
```

### File Output Training

Setelah training, akan dihasilkan:
```
model/
├── best_model.joblib          # Model terlatih
├── scaler.joblib              # Scaler untuk normalisasi
├── label_encoder.joblib       # Encoder untuk label
├── feature_names.txt          # Nama features
└── training_data.csv          # Dataset training
```

---

## 🧪 Testing Model

### Test via Command Line

```bash
cd model
python predict_gizi.py L 24 12.5 85
```

**Parameter:**
1. Jenis Kelamin: `L` (Laki-laki) atau `P` (Perempuan)
2. Umur (bulan): `0-60`
3. Berat Badan (kg): contoh `12.5`
4. Tinggi Badan (cm): contoh `85`

**Output:**
```json
{
  "status_gizi": "Normal",
  "kategori_bb_u": "Normal",
  "kategori_tb_u": "Normal",
  "kategori_bb_tb": "Normal",
  "z_score_bb_u": -0.25,
  "z_score_tb_u": 0.15,
  "z_score_bb_tb": -0.45,
  "confidence": 92.5,
  "rekomendasi": "✅ Selamat! Status gizi anak dalam kondisi baik...",
  "who_median_bb": 12.8,
  "who_median_tb": 86.0
}
```

### Test via Web Interface

1. Buka: `http://localhost/project_gizi`
2. Isi form diagnosa
3. Klik: **Diagnosa Sekarang**
4. Lihat hasil

---

## 📤 Upload Dataset via Web

### Langkah-langkah:

1. **Login Admin**
   - URL: `http://localhost/project_gizi/admin/login.php`
   - Username: `admin`
   - Password: `password`

2. **Menu Upload Dataset**
   - Klik: **Upload Dataset**

3. **Drag & Drop CSV**
   - Drag file CSV ke area upload
   - Atau klik untuk browse file

4. **Upload**
   - Klik: **Upload Dataset**
   - Dataset akan tersimpan dan tercatat

5. **Train Model**
   - Pilih dataset dari tabel
   - Klik: **Train Model**
   - Tunggu proses selesai

---

## 📋 Format Dataset

### Kolom Wajib

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `Umur_Bulan` | Integer | 0-60 bulan |
| `Jenis_Kelamin` | String | L atau P |
| `Berat_Badan_kg` | Float | Berat badan dalam kg |
| `Tinggi_Badan_cm` | Float | Tinggi badan dalam cm |
| `Status_Gizi` | String | Normal, Gizi Kurang, Gizi Buruk, Gizi Lebih, Obesitas |

### Kolom Opsional (Recommended)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `Z_Score_BB_U` | Float | Z-score BB/U |
| `Z_Score_TB_U` | Float | Z-score TB/U |
| `Z_Score_BB_TB` | Float | Z-score BB/TB |
| `ID` | String | ID unik anak |
| `Umur_Tahun` | Float | Umur dalam tahun |

### Contoh CSV

```csv
ID,Umur_Bulan,Jenis_Kelamin,Berat_Badan_kg,Tinggi_Badan_cm,Status_Gizi,Z_Score_BB_U,Z_Score_TB_U,Z_Score_BB_TB
A001,12,L,9.5,75.5,Normal,0.15,0.25,0.05
A002,24,P,11.2,85.0,Normal,-0.45,0.10,-0.55
A003,18,L,8.5,78.0,Gizi Kurang,-2.15,-1.85,-2.25
A004,36,P,14.8,95.5,Normal,0.35,0.45,0.15
A005,30,L,11.5,90.0,Gizi Kurang,-1.95,-1.55,-2.05
```

### Validasi Data

**Cek validasi sebelum upload:**
```python
import pandas as pd

df = pd.read_csv('your_dataset.csv')

# Cek kolom wajib
required = ['Umur_Bulan', 'Jenis_Kelamin', 'Berat_Badan_kg', 'Tinggi_Badan_cm', 'Status_Gizi']
missing = [col for col in required if col not in df.columns]
if missing:
    print(f"❌ Kolom tidak lengkap: {missing}")
else:
    print("✅ Format CSV valid")

# Cek nilai
print(f"Umur range: {df['Umur_Bulan'].min()}-{df['Umur_Bulan'].max()}")
print(f"BB range: {df['Berat_Badan_kg'].min()}-{df['Berat_Badan_kg'].max()}")
print(f"TB range: {df['Tinggi_Badan_cm'].min()}-{df['Tinggi_Badan_cm'].max()}")
print(f"\nStatus Gizi:\n{df['Status_Gizi'].value_counts()}")
```

---

## 🐛 Troubleshooting

### Error: "ModuleNotFoundError"

**Problem:**
```
ModuleNotFoundError: No module named 'pandas'
```

**Solution:**
```bash
pip install pandas numpy scikit-learn joblib
```

### Error: "FileNotFoundError"

**Problem:**
```
FileNotFoundError: [Errno 2] No such file or directory: 'best_model.joblib'
```

**Solution:**
Train model terlebih dahulu:
```bash
cd model
python train_model.py
```

### Error: "Permission denied"

**Problem:**
```
PermissionError: [Errno 13] Permission denied: 'uploads/dataset.csv'
```

**Solution (Linux/Mac):**
```bash
chmod 755 uploads/
chmod 755 model/
```

**Solution (Windows):**
- Jalankan command prompt sebagai Administrator
- Atau ubah permission folder secara manual

### Model Accuracy Rendah

**Penyebab:**
- Dataset terlalu kecil
- Data tidak balance
- Data tidak realistis

**Solution:**
1. Generate dataset lebih besar (5000+)
2. Gunakan distribusi balanced
3. Cek kualitas data:
```python
df = pd.read_csv('dataset.csv')
print(df.isnull().sum())  # Cek missing values
print(df.describe())       # Cek statistik
```

### Prediction Tidak Akurat

**Cek:**
1. Apakah model sudah di-train?
2. Apakah input dalam range normal?
3. Apakah file model ada?

```bash
ls -la model/best_model.joblib
```

### Python Command Not Found

**Windows:**
Gunakan `python` atau `py`:
```cmd
py generate_dataset.py
```

**Linux/Mac:**
```bash
python3 generate_dataset.py
```

Atau update config.php:
```php
define('PYTHON_EXECUTABLE', 'python3');
```

---

## 📊 Best Practices

### 1. Dataset Size
- **Minimum**: 1000 data
- **Recommended**: 5000 data
- **Production**: 10,000+ data

### 2. Data Quality
- Gunakan WHO standards
- Balance distribusi status gizi
- Validasi data sebelum training

### 3. Model Training
- Gunakan cross-validation
- Test dengan data terpisah
- Save multiple models dan pilih terbaik

### 4. Testing
- Test dengan berbagai kasus
- Bandingkan dengan standar WHO
- Validasi dengan ahli gizi

### 5. Update Model
- Re-train setiap 6 bulan
- Tambah data baru
- Monitor accuracy

---

## 🎯 Quick Start

**1. Generate Dataset:**
```bash
cd model
python generate_dataset.py
# Pilih: 2 (5000 data balanced)
```

**2. Train Model:**
```bash
python train_model.py
# Pilih: 1 (generate new dataset)
# Tunggu sampai selesai
# Ketik: y (save model)
```

**3. Test:**
```bash
python predict_gizi.py L 24 12.5 85
```

**4. Use in Web:**
- Buka: http://localhost/project_gizi
- Test form diagnosa
- ✅ Done!

---

## 📞 Support

Jika ada pertanyaan:
- 📧 Email: info@sistemgizi.com
- 📖 Documentation: README.md
- 🐛 Issues: GitHub Issues

**Happy Training! 🚀**

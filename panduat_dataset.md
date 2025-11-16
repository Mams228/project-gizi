# Panduan Dataset Standar WHO untuk Status Gizi Anak

## 📊 Tentang Dataset

Dataset yang digunakan dalam sistem ini berbasis pada **WHO Child Growth Standards** yang merupakan standar internasional untuk menilai pertumbuhan dan status gizi anak.

## 🌍 Standar WHO

### Indikator Antropometri

WHO menggunakan 3 indikator utama untuk anak 0-60 bulan:

1. **Weight-for-age (BB/U)**
   - Berat badan berdasarkan umur
   - Indikator underweight/overweight

2. **Length/Height-for-age (TB/U)**
   - Tinggi badan berdasarkan umur
   - Indikator stunting

3. **Weight-for-length/height (BB/TB)**
   - Berat badan berdasarkan tinggi
   - Indikator wasting/obesity

### Rentang Umur

- **0-24 bulan**: Length-based (berbaring)
- **24-60 bulan**: Height-based (berdiri)

## 📈 Z-Score Classification

### Interpretasi Z-Score

Z-Score menunjukkan berapa **standar deviasi** nilai anak dari median populasi referensi WHO.

#### Weight-for-Age (BB/U)
```
Z-Score        Status
< -3 SD       Severely underweight (Gizi Buruk)
-3 to <-2 SD  Underweight (Gizi Kurang)
-2 to +1 SD   Normal (Gizi Baik)
+1 to +2 SD   Possible risk of overweight
> +2 SD       Overweight (Gizi Lebih)
```

#### Height-for-Age (TB/U)
```
Z-Score        Status
< -3 SD       Severely stunted (Sangat Pendek)
-3 to <-2 SD  Stunted (Pendek/Stunting)
-2 to +3 SD   Normal
> +3 SD       Tall
```

#### Weight-for-Height (BB/TB)
```
Z-Score        Status
< -3 SD       Severely wasted (Sangat Kurus)
-3 to <-2 SD  Wasted (Kurus)
-2 to +1 SD   Normal
+1 to +2 SD   Possible risk of overweight (Gemuk)
+2 to +3 SD   Overweight
> +3 SD       Obese (Obesitas)
```

## 🔢 Cara Menghitung Z-Score

### Formula

```
Z-Score = (Observed Value - Median Reference) / SD Reference
```

### Contoh Perhitungan

**Anak Laki-laki, 24 bulan:**
- Berat badan aktual: 10.5 kg
- Median WHO (24 bulan, L): 12.2 kg
- SD WHO: 1.3 kg

```
Z-Score BB/U = (10.5 - 12.2) / 1.3 = -1.31
Interpretasi: Normal (dalam rentang -2 to +1)
```

## 📊 Struktur Dataset

### Format CSV

```csv
jenis_kelamin,umur_bulan,berat_badan,tinggi_badan,lingkar_lengan,z_score_bb_u,z_score_tb_u,z_score_bb_tb,status_gizi
L,24,12.5,87.0,15.2,0.23,-0.12,0.15,Gizi Baik
P,36,11.2,88.5,13.8,-1.85,-1.92,-1.45,Gizi Kurang
L,12,8.5,73.0,12.5,-2.15,-0.89,-2.35,Gizi Kurang
```

### Field Descriptions

| Field | Type | Range | Description |
|-------|------|-------|-------------|
| jenis_kelamin | String | L/P | Laki-laki/Perempuan |
| umur_bulan | Integer | 0-60 | Umur dalam bulan |
| berat_badan | Float | 2-30 | Berat badan (kg) |
| tinggi_badan | Float | 45-120 | Tinggi/panjang badan (cm) |
| lingkar_lengan | Float | 10-25 | MUAC - Mid-upper arm circumference (cm) |
| z_score_bb_u | Float | -5 to +5 | Z-score berat per umur |
| z_score_tb_u | Float | -5 to +5 | Z-score tinggi per umur |
| z_score_bb_tb | Float | -5 to +5 | Z-score berat per tinggi |
| status_gizi | String | - | Gizi Baik/Kurang/Buruk/Stunting/Lebih |

## 🎯 Distribusi Dataset

### Balanced Dataset

Untuk training yang optimal, gunakan distribusi seimbang:

```
Gizi Baik      : 50% (2500 samples)
Gizi Kurang    : 15% (750 samples)
Gizi Buruk     : 10% (500 samples)
Stunting       : 15% (750 samples)
Gizi Lebih     : 10% (500 samples)
-----------------------------------
Total          : 5000 samples
```

### Mengapa Balanced?

- Mencegah bias model terhadap kelas mayoritas
- Meningkatkan kemampuan deteksi kasus minoritas (Gizi Buruk)
- Performa lebih baik pada semua kelas

## 🔧 Generate Dataset

### Option 1: Menggunakan Script Python

```bash
cd model/
python generate_dataset.py
```

Output:
- `dataset_gizi_anak.csv` (5000 samples)

### Option 2: Custom Generation

Edit `generate_dataset.py`:

```python
# Ubah jumlah samples
df = generate_dataset(n_samples=10000)

# Ubah distribusi
target_distribution = {
    'Gizi Baik': 0.60,    # 60%
    'Gizi Kurang': 0.15,  # 15%
    'Gizi Buruk': 0.10,   # 10%
    'Stunting': 0.10,     # 10%
    'Gizi Lebih': 0.05    # 5%
}
```

## 📥 Menggunakan Data WHO Official

### Download Tabel WHO

1. Kunjungi: https://www.who.int/tools/child-growth-standards/standards
2. Download tables:
   - Weight-for-age (0-60 months)
   - Length/height-for-age (0-60 months)
   - Weight-for-length/height

### Format WHO Tables

WHO menyediakan tabel dengan format:

```
Month  L      M       S      SD    -3SD   -2SD   -1SD   Median  +1SD   +2SD   +3SD
0      1.0    3.3464  0.14602  ...
1      1.0    4.4709  0.13395  ...
...
```

Dimana:
- **L**: Box-Cox transformation
- **M**: Median
- **S**: Coefficient of variation
- **SD**: Standard deviation

### Import ke Database

```sql
-- Insert ke tabel standar_who
INSERT INTO standar_who (jenis_kelamin, umur_bulan, indikator, median, sd, minus_3sd, minus_2sd, plus_2sd, plus_3sd)
VALUES ('L', 0, 'BB/U', 3.3, 0.45, 2.1, 2.5, 4.4, 4.8);
```

## 🧪 Validasi Dataset

### Checklist Quality

✅ **Completeness**
- Tidak ada missing values
- Semua field terisi

✅ **Consistency**
- Z-scores konsisten dengan BB, TB, umur
- Status gizi sesuai dengan z-scores

✅ **Distribution**
- Distribusi umur merata (0-60 bulan)
- Distribusi gender seimbang (50:50)
- Distribusi status gizi sesuai target

✅ **Range**
- Berat: 2-30 kg (realistis)
- Tinggi: 45-120 cm (realistis)
- Z-scores: -5 to +5 (dalam batas normal)

### Script Validasi

```python
import pandas as pd

df = pd.read_csv('dataset_gizi_anak.csv')

# Check completeness
print("Missing values:")
print(df.isnull().sum())

# Check distribution
print("\nStatus Gizi Distribution:")
print(df['status_gizi'].value_counts())

print("\nGender Distribution:")
print(df['jenis_kelamin'].value_counts())

print("\nAge Distribution:")
print(df['umur_bulan'].describe())

# Check ranges
print("\nValue Ranges:")
print(f"Berat: {df['berat_badan'].min():.2f} - {df['berat_badan'].max():.2f} kg")
print(f"Tinggi: {df['tinggi_badan'].min():.2f} - {df['tinggi_badan'].max():.2f} cm")
print(f"Z-Score BB/U: {df['z_score_bb_u'].min():.2f} - {df['z_score_bb_u'].max():.2f}")
```

## 📊 Visualisasi Dataset

### Plot Distributions

```python
import matplotlib.pyplot as plt
import seaborn as sns

# Status Gizi
plt.figure(figsize=(10,6))
df['status_gizi'].value_counts().plot(kind='bar')
plt.title('Distribusi Status Gizi')
plt.xlabel('Status Gizi')
plt.ylabel('Jumlah')
plt.xticks(rotation=45)
plt.tight_layout()
plt.savefig('dist_status_gizi.png')

# Z-Scores Distribution
fig, axes = plt.subplots(1, 3, figsize=(15,5))
df['z_score_bb_u'].hist(bins=30, ax=axes[0])
axes[0].set_title('Z-Score BB/U')
df['z_score_tb_u'].hist(bins=30, ax=axes[1])
axes[1].set_title('Z-Score TB/U')
df['z_score_bb_tb'].hist(bins=30, ax=axes[2])
axes[2].set_title('Z-Score BB/TB')
plt.tight_layout()
plt.savefig('dist_zscores.png')
```

## 🔄 Update Dataset

### Menambah Data Real

1. **Collect data real** dari Puskesmas/Posyandu
2. **Format** sesuai dengan struktur CSV
3. **Hitung Z-scores** menggunakan standar WHO
4. **Append** ke dataset existing
5. **Retrain** model

```python
# Append new data
new_data = pd.read_csv('data_real.csv')
existing_data = pd.read_csv('dataset_gizi_anak.csv')
combined = pd.concat([existing_data, new_data], ignore_index=True)
combined.to_csv('dataset_updated.csv', index=False)

# Retrain
python train_model.py
```

## 📚 Referensi

1. **WHO Child Growth Standards**
   - https://www.who.int/tools/child-growth-standards

2. **WHO Anthro Software**
   - https://www.who.int/tools/child-growth-standards/software

3. **Growth Reference Data**
   - https://www.who.int/tools/growth-reference-data-for-5to19-years

4. **Training Course on Child Growth Assessment**
   - https://www.who.int/publications/training-course-on-child-growth-assessment

## ❓ FAQ

**Q: Berapa ukuran dataset minimal untuk training?**
A: Minimal 1000 samples, optimal 5000+ dengan distribusi seimbang.

**Q: Apakah dataset harus balance?**
A: Ya, untuk performa optimal terutama pada kasus minoritas (Gizi Buruk).

**Q: Bagaimana menangani imbalanced data?**
A: Gunakan SMOTE, class weights, atau stratified sampling.

**Q: Apakah bisa gunakan data Indonesia?**
A: Ya, tapi tetap gunakan standar WHO sebagai referensi Z-score.

**Q: Bagaimana update standar WHO yang baru?**
A: Download tabel terbaru, update database, regenerate dataset, retrain model.

---

**Note**: Dataset ini adalah simulasi edukatif. Untuk aplikasi klinis, gunakan data real dengan supervisi ahli gizi/dokter.
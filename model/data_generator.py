"""
GENERATOR DATA ANTROPOMETRI ANAK
Berbasis Standar WHO Child Growth Standards

Tool ini menghasilkan dataset sintetis antropometri anak yang realistis
berdasarkan kurva pertumbuhan WHO untuk anak Indonesia

Output: CSV file siap pakai untuk Machine Learning
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

class GiziDataGenerator:
    """
    Generator data antropometri anak berbasis standar WHO
    """
    
    def __init__(self, seed=42):
        np.random.seed(seed)
        random.seed(seed)
        
        # Standar WHO untuk anak Indonesia
        # Format: {umur_bulan: {'L': (bb_median, tb_median), 'P': (bb_median, tb_median)}}
        self.who_standards = self._init_who_standards()
        
    def _init_who_standards(self):
        """
        Inisialisasi standar WHO (simplified)
        Data ini disederhanakan dari WHO Child Growth Standards
        """
        standards = {}
        
        # Data untuk setiap bulan (0-60 bulan)
        for month in range(0, 61):
            if month == 0:  # Lahir
                standards[month] = {
                    'L': {'bb': 3.3, 'tb': 49.9, 'bb_sd': 0.4, 'tb_sd': 1.9},
                    'P': {'bb': 3.2, 'tb': 49.1, 'bb_sd': 0.4, 'tb_sd': 1.9}
                }
            elif month <= 12:  # 0-12 bulan
                standards[month] = {
                    'L': {
                        'bb': 3.3 + (month * 0.6),
                        'tb': 49.9 + (month * 2.8),
                        'bb_sd': 0.5 + (month * 0.05),
                        'tb_sd': 2.0 + (month * 0.1)
                    },
                    'P': {
                        'bb': 3.2 + (month * 0.55),
                        'tb': 49.1 + (month * 2.6),
                        'bb_sd': 0.5 + (month * 0.05),
                        'tb_sd': 2.0 + (month * 0.1)
                    }
                }
            elif month <= 24:  # 13-24 bulan
                base_month = month - 12
                standards[month] = {
                    'L': {
                        'bb': 10.5 + (base_month * 0.25),
                        'tb': 77.0 + (base_month * 1.2),
                        'bb_sd': 1.2,
                        'tb_sd': 2.8
                    },
                    'P': {
                        'bb': 9.8 + (base_month * 0.23),
                        'tb': 75.0 + (base_month * 1.1),
                        'bb_sd': 1.2,
                        'tb_sd': 2.8
                    }
                }
            elif month <= 36:  # 25-36 bulan
                base_month = month - 24
                standards[month] = {
                    'L': {
                        'bb': 13.5 + (base_month * 0.22),
                        'tb': 91.0 + (base_month * 1.0),
                        'bb_sd': 1.4,
                        'tb_sd': 3.0
                    },
                    'P': {
                        'bb': 12.8 + (base_month * 0.20),
                        'tb': 89.0 + (base_month * 0.95),
                        'bb_sd': 1.4,
                        'tb_sd': 3.0
                    }
                }
            else:  # 37-60 bulan
                base_month = month - 36
                standards[month] = {
                    'L': {
                        'bb': 16.0 + (base_month * 0.18),
                        'tb': 103.0 + (base_month * 0.85),
                        'bb_sd': 1.6,
                        'tb_sd': 3.2
                    },
                    'P': {
                        'bb': 15.2 + (base_month * 0.17),
                        'tb': 101.0 + (base_month * 0.80),
                        'bb_sd': 1.6,
                        'tb_sd': 3.2
                    }
                }
        
        return standards
    
    def calculate_z_score(self, value, median, sd):
        """Hitung Z-score"""
        return (value - median) / sd
    
    def determine_status_gizi(self, z_bb_u, z_tb_u, z_bb_tb):
        """
        Tentukan status gizi berdasarkan Z-score
        Menggunakan standar WHO
        """
        # Prioritas: BB/TB > TB/U > BB/U
        
        # Wasting (BB/TB)
        if z_bb_tb < -3:
            return 'Gizi Buruk'  # Severely wasted
        elif z_bb_tb < -2:
            return 'Gizi Kurang'  # Wasted
        
        # Stunting (TB/U)
        if z_tb_u < -3:
            return 'Gizi Buruk'  # Severely stunted
        elif z_tb_u < -2:
            return 'Gizi Kurang'  # Stunted
        
        # Underweight (BB/U)
        if z_bb_u < -3:
            return 'Gizi Buruk'  # Severely underweight
        elif z_bb_u < -2:
            return 'Gizi Kurang'  # Underweight
        
        # Overweight/Obesity (BB/TB)
        if z_bb_tb > 3:
            return 'Obesitas'
        elif z_bb_tb > 2:
            return 'Gizi Lebih'
        
        return 'Normal'
    
    def generate_child_data(self, umur_bulan, jenis_kelamin, status_target=None):
        """
        Generate data satu anak
        
        Parameters:
        - umur_bulan: int (0-60)
        - jenis_kelamin: str ('L' atau 'P')
        - status_target: str (optional) - paksa status gizi tertentu
        """
        std = self.who_standards[umur_bulan][jenis_kelamin]
        
        if status_target is None:
            # Generate random dengan distribusi normal
            z_bb = np.random.normal(0, 1)
            z_tb = np.random.normal(0, 1)
        else:
            # Generate sesuai status yang diinginkan
            if status_target == 'Gizi Buruk':
                z_bb = np.random.uniform(-4, -2.5)
                z_tb = np.random.uniform(-4, -2.5)
            elif status_target == 'Gizi Kurang':
                z_bb = np.random.uniform(-2.5, -1.5)
                z_tb = np.random.uniform(-2.5, -1.5)
            elif status_target == 'Normal':
                z_bb = np.random.uniform(-1.5, 1.5)
                z_tb = np.random.uniform(-1.5, 1.5)
            elif status_target == 'Gizi Lebih':
                z_bb = np.random.uniform(2, 2.5)
                z_tb = np.random.uniform(-1, 1)
            elif status_target == 'Obesitas':
                z_bb = np.random.uniform(2.5, 4)
                z_tb = np.random.uniform(-1, 1)
        
        # Hitung berat dan tinggi badan
        berat_badan = std['bb'] + (z_bb * std['bb_sd'])
        tinggi_badan = std['tb'] + (z_tb * std['tb_sd'])
        
        # Pastikan nilai positif
        berat_badan = max(2.0, berat_badan)
        tinggi_badan = max(45.0, tinggi_badan)
        
        # Hitung Z-score aktual
        z_bb_u = self.calculate_z_score(berat_badan, std['bb'], std['bb_sd'])
        z_tb_u = self.calculate_z_score(tinggi_badan, std['tb'], std['tb_sd'])
        
        # Z-score BB/TB (simplified)
        expected_bb = std['bb'] * (tinggi_badan / std['tb'])
        z_bb_tb = self.calculate_z_score(berat_badan, expected_bb, std['bb_sd'])
        
        # Tentukan status gizi
        status_gizi = self.determine_status_gizi(z_bb_u, z_tb_u, z_bb_tb)
        
        return {
            'Berat_Badan_kg': round(berat_badan, 2),
            'Tinggi_Badan_cm': round(tinggi_badan, 2),
            'Z_Score_BB_U': round(z_bb_u, 2),
            'Z_Score_TB_U': round(z_tb_u, 2),
            'Z_Score_BB_TB': round(z_bb_tb, 2),
            'Status_Gizi': status_gizi
        }
    
    def generate_dataset(self, n_samples=1000, balanced=True, region=None):
        """
        Generate dataset lengkap
        
        Parameters:
        - n_samples: int - jumlah data
        - balanced: bool - seimbangkan distribusi status gizi
        - region: str - nama region (opsional)
        """
        data = []
        
        # Tentukan distribusi status gizi
        if balanced:
            # Distribusi seimbang untuk training ML
            status_distribution = {
                'Normal': 0.35,
                'Gizi Kurang': 0.25,
                'Gizi Buruk': 0.15,
                'Gizi Lebih': 0.15,
                'Obesitas': 0.10
            }
        else:
            # Distribusi realistis Indonesia (berdasarkan SSGI)
            status_distribution = {
                'Normal': 0.60,
                'Gizi Kurang': 0.20,
                'Gizi Buruk': 0.08,
                'Gizi Lebih': 0.08,
                'Obesitas': 0.04
            }
        
        # Hitung jumlah per kategori
        samples_per_status = {
            status: int(n_samples * pct) 
            for status, pct in status_distribution.items()
        }
        
        # Generate data per status
        id_counter = 1
        for status, count in samples_per_status.items():
            for _ in range(count):
                # Random umur dan jenis kelamin
                umur_bulan = np.random.randint(0, 61)
                jenis_kelamin = random.choice(['L', 'P'])
                
                # Generate data anak
                child_data = self.generate_child_data(
                    umur_bulan, 
                    jenis_kelamin,
                    status_target=status
                )
                
                # Compile data
                record = {
                    'ID': f'A{id_counter:04d}',
                    'Umur_Bulan': umur_bulan,
                    'Umur_Tahun': round(umur_bulan / 12, 1),
                    'Jenis_Kelamin': jenis_kelamin,
                    **child_data
                }
                
                if region:
                    record['Region'] = region
                
                data.append(record)
                id_counter += 1
        
        # Shuffle data
        df = pd.DataFrame(data)
        df = df.sample(frac=1, random_state=42).reset_index(drop=True)
        
        return df

# ============================================================================
# MAIN PROGRAM
# ============================================================================

if __name__ == "__main__":
    print("="*70)
    print("GENERATOR DATA ANTROPOMETRI ANAK")
    print("Berbasis Standar WHO Child Growth Standards")
    print("="*70)
    
    # Inisialisasi generator
    generator = GiziDataGenerator(seed=42)
    
    # Menu pilihan
    print("\n📊 PILIHAN DATASET:")
    print("1. Dataset Balanced (untuk ML Training) - 1000 data")
    print("2. Dataset Balanced Large - 5000 data")
    print("3. Dataset Realistis (distribusi Indonesia) - 1000 data")
    print("4. Dataset Custom")
    
    choice = input("\nPilih (1-4): ").strip()
    
    if choice == '1':
        print("\n⏳ Generating dataset balanced 1000 data...")
        df = generator.generate_dataset(n_samples=1000, balanced=True)
        filename = 'data_gizi_antropometri_1000.csv'
        
    elif choice == '2':
        print("\n⏳ Generating dataset balanced 5000 data...")
        df = generator.generate_dataset(n_samples=5000, balanced=True)
        filename = 'data_gizi_antropometri_5000.csv'
        
    elif choice == '3':
        print("\n⏳ Generating dataset realistis...")
        df = generator.generate_dataset(n_samples=1000, balanced=False)
        filename = 'data_gizi_antropometri_realistis.csv'
        
    elif choice == '4':
        n = int(input("Jumlah data: "))
        bal = input("Balanced? (y/n): ").lower() == 'y'
        print(f"\n⏳ Generating dataset custom {n} data...")
        df = generator.generate_dataset(n_samples=n, balanced=bal)
        filename = f'data_gizi_antropometri_custom_{n}.csv'
        
    else:
        print("❌ Pilihan tidak valid. Menggunakan default...")
        df = generator.generate_dataset(n_samples=1000, balanced=True)
        filename = 'data_gizi_antropometri_1000.csv'
    
    # Simpan ke CSV
    df.to_csv(filename, index=False)
    
    # Tampilkan info
    print(f"\n✅ Dataset berhasil dibuat!")
    print(f"📁 File: {filename}")
    print(f"📊 Jumlah data: {len(df)}")
    
    print(f"\n📈 Distribusi Status Gizi:")
    status_counts = df['Status_Gizi'].value_counts()
    for status, count in status_counts.items():
        pct = (count / len(df)) * 100
        print(f"   {status:<15}: {count:>4} ({pct:>5.2f}%)")
    
    print(f"\n👥 Distribusi Jenis Kelamin:")
    gender_counts = df['Jenis_Kelamin'].value_counts()
    for gender, count in gender_counts.items():
        jk = "Laki-laki" if gender == 'L' else "Perempuan"
        print(f"   {jk:<15}: {count:>4} ({count/len(df)*100:>5.2f}%)")
    
    print(f"\n📋 Preview 10 data pertama:")
    print(df.head(10).to_string(index=False))
    
    print(f"\n📊 Statistik Deskriptif:")
    print(df[['Umur_Bulan', 'Berat_Badan_kg', 'Tinggi_Badan_cm']].describe())
    
    print(f"\n🎯 Dataset siap digunakan untuk Machine Learning!")
    print(f"   Gunakan file: {filename}")
    print(f"\n" + "="*70)

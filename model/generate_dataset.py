#!/usr/bin/env python3
"""
Generate Dataset Status Gizi Anak berdasarkan Standar WHO
Dataset ini menggunakan Growth Standards WHO untuk anak 0-60 bulan
"""

import numpy as np
import pandas as pd
from datetime import datetime
import random

# Standar WHO Z-Score untuk klasifikasi
# BB/U (Berat Badan per Umur)
def klasifikasi_bb_u(z_score):
    if z_score < -3: return 'Gizi Buruk'
    if z_score < -2: return 'Gizi Kurang'
    if z_score <= 1: return 'Gizi Baik'
    if z_score <= 2: return 'Berisiko Gizi Lebih'
    return 'Gizi Lebih'

# TB/U (Tinggi Badan per Umur)
def klasifikasi_tb_u(z_score):
    if z_score < -3: return 'Sangat Pendek'
    if z_score < -2: return 'Pendek'
    if z_score <= 3: return 'Normal'
    return 'Tinggi'

# BB/TB (Berat Badan per Tinggi Badan)
def klasifikasi_bb_tb(z_score):
    if z_score < -3: return 'Sangat Kurus'
    if z_score < -2: return 'Kurus'
    if z_score <= 1: return 'Normal'
    if z_score <= 2: return 'Gemuk'
    return 'Obesitas'

# Tentukan status gizi keseluruhan
def tentukan_status_gizi(z_bb_u, z_tb_u, z_bb_tb):
    kat_bb_u = klasifikasi_bb_u(z_bb_u)
    kat_tb_u = klasifikasi_tb_u(z_tb_u)
    kat_bb_tb = klasifikasi_bb_tb(z_bb_tb)
    
    # Prioritas: Gizi Buruk > Gizi Kurang > Stunting > Wasting
    if kat_bb_u == 'Gizi Buruk' or kat_bb_tb == 'Sangat Kurus':
        return 'Gizi Buruk'
    elif kat_bb_u == 'Gizi Kurang' or kat_bb_tb == 'Kurus':
        return 'Gizi Kurang'
    elif kat_tb_u in ['Sangat Pendek', 'Pendek']:
        return 'Stunting'
    elif kat_bb_u == 'Gizi Lebih' or kat_bb_tb in ['Gemuk', 'Obesitas']:
        return 'Gizi Lebih'
    else:
        return 'Gizi Baik'

# Standar WHO median dan SD (simplified version)
# Untuk dataset lengkap, gunakan tabel WHO resmi
def get_who_standard(jenis_kelamin, umur_bulan, indikator):
    """
    Mendapatkan median dan SD dari standar WHO
    Data ini adalah aproksimasi, untuk produksi gunakan tabel WHO lengkap
    """
    
    if indikator == 'BB/U':  # Berat badan (kg)
        if jenis_kelamin == 'L':  # Laki-laki
            median = 3.3 + (umur_bulan * 0.15)
            sd = 0.4 + (umur_bulan * 0.01)
        else:  # Perempuan
            median = 3.2 + (umur_bulan * 0.14)
            sd = 0.4 + (umur_bulan * 0.01)
    
    elif indikator == 'TB/U':  # Tinggi badan (cm)
        if jenis_kelamin == 'L':
            median = 49.9 + (umur_bulan * 1.1)
            sd = 1.9 + (umur_bulan * 0.02)
        else:
            median = 49.1 + (umur_bulan * 1.0)
            sd = 1.9 + (umur_bulan * 0.02)
    
    elif indikator == 'BB/TB':  # Berat per tinggi
        # Simplified - seharusnya berdasarkan tinggi badan
        median = 15 + (umur_bulan * 0.05)
        sd = 1.2
    
    return median, sd

# Generate dataset
def generate_dataset(n_samples=5000):
    """
    Generate dataset training dengan distribusi yang seimbang
    """
    print(f"Generating {n_samples} samples...")
    
    data = []
    
    # Distribusi target yang seimbang
    target_distribution = {
        'Gizi Baik': 0.50,      # 50%
        'Gizi Kurang': 0.15,    # 15%
        'Gizi Buruk': 0.10,     # 10%
        'Stunting': 0.15,       # 15%
        'Gizi Lebih': 0.10      # 10%
    }
    
    for status_gizi, proportion in target_distribution.items():
        n_target = int(n_samples * proportion)
        
        for _ in range(n_target):
            # Generate atribut dasar
            jenis_kelamin = random.choice(['L', 'P'])
            umur_bulan = random.randint(0, 60)
            
            # Get WHO standard
            median_bb, sd_bb = get_who_standard(jenis_kelamin, umur_bulan, 'BB/U')
            median_tb, sd_tb = get_who_standard(jenis_kelamin, umur_bulan, 'TB/U')
            median_bb_tb, sd_bb_tb = get_who_standard(jenis_kelamin, umur_bulan, 'BB/TB')
            
            # Generate z-scores berdasarkan target status gizi
            if status_gizi == 'Gizi Baik':
                z_bb_u = np.random.normal(0, 0.8)
                z_tb_u = np.random.normal(0, 0.8)
                z_bb_tb = np.random.normal(0, 0.8)
            
            elif status_gizi == 'Gizi Kurang':
                z_bb_u = np.random.uniform(-3, -2)
                z_tb_u = np.random.normal(0, 1)
                z_bb_tb = np.random.uniform(-2.5, -1.5)
            
            elif status_gizi == 'Gizi Buruk':
                z_bb_u = np.random.uniform(-4, -3)
                z_tb_u = np.random.normal(-1, 1)
                z_bb_tb = np.random.uniform(-4, -3)
            
            elif status_gizi == 'Stunting':
                z_bb_u = np.random.normal(-1, 0.8)
                z_tb_u = np.random.uniform(-3.5, -2)
                z_bb_tb = np.random.normal(0, 0.8)
            
            elif status_gizi == 'Gizi Lebih':
                z_bb_u = np.random.uniform(2, 3.5)
                z_tb_u = np.random.normal(0, 1)
                z_bb_tb = np.random.uniform(2, 3.5)
            
            # Hitung nilai aktual dari z-score
            berat_badan = round(median_bb + (z_bb_u * sd_bb), 2)
            tinggi_badan = round(median_tb + (z_tb_u * sd_tb), 2)
            
            # Batasi nilai yang realistis
            berat_badan = max(2.0, min(30.0, berat_badan))
            tinggi_badan = max(45.0, min(120.0, tinggi_badan))
            
            # Lingkar lengan (MUAC) - korelasi dengan BB dan umur
            lingkar_lengan = round(10 + (berat_badan * 0.5) + (umur_bulan * 0.05) + np.random.normal(0, 0.5), 2)
            lingkar_lengan = max(10.0, min(25.0, lingkar_lengan))
            
            # Verifikasi status gizi
            status_final = tentukan_status_gizi(z_bb_u, z_tb_u, z_bb_tb)
            
            data.append({
                'jenis_kelamin': jenis_kelamin,
                'umur_bulan': umur_bulan,
                'berat_badan': berat_badan,
                'tinggi_badan': tinggi_badan,
                'lingkar_lengan': lingkar_lengan,
                'z_score_bb_u': round(z_bb_u, 2),
                'z_score_tb_u': round(z_tb_u, 2),
                'z_score_bb_tb': round(z_bb_tb, 2),
                'status_gizi': status_final
            })
    
    # Create DataFrame
    df = pd.DataFrame(data)
    
    # Shuffle
    df = df.sample(frac=1).reset_index(drop=True)
    
    print(f"\nDataset generated successfully!")
    print(f"Total samples: {len(df)}")
    print("\nDistribusi Status Gizi:")
    print(df['status_gizi'].value_counts())
    
    return df

if __name__ == "__main__":
    # Generate dataset
    df = generate_dataset(5000)
    
    # Save to CSV
    output_file = 'dataset_gizi_anak.csv'
    df.to_csv(output_file, index=False)
    print(f"\nDataset saved to: {output_file}")
    
    # Statistik
    print("\n=== Dataset Statistics ===")
    print(df.describe())
    
    print("\n=== Sample Data ===")
    print(df.head(10))
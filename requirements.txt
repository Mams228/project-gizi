#!/usr/bin/env python3
"""
Script prediksi status gizi menggunakan trained model
Dapat dipanggil dari PHP atau command line
"""

import sys
import json
import joblib
import numpy as np
import pandas as pd

def load_model():
    """Load trained model dan encoders"""
    try:
        model = joblib.load('model_gizi_rf.pkl')
        le_gender = joblib.load('label_encoder_gender.pkl')
        
        with open('model_metadata.json', 'r') as f:
            metadata = json.load(f)
        
        return model, le_gender, metadata
    except Exception as e:
        print(json.dumps({'error': f'Failed to load model: {str(e)}'}))
        sys.exit(1)

def calculate_z_score(nilai, median, sd):
    """Hitung z-score"""
    return (nilai - median) / sd

def get_who_standard(jenis_kelamin, umur_bulan, indikator):
    """
    Mendapatkan median dan SD dari standar WHO
    Ini adalah aproksimasi sederhana, untuk produksi gunakan tabel WHO lengkap
    """
    if indikator == 'BB/U':  # Berat badan (kg)
        if jenis_kelamin == 'L':
            median = 3.3 + (umur_bulan * 0.15)
            sd = 0.4 + (umur_bulan * 0.01)
        else:
            median = 3.2 + (umur_bulan * 0.14)
            sd = 0.4 + (umur_bulan * 0.01)
    
    elif indikator == 'TB/U':  # Tinggi badan (cm)
        if jenis_kelamin == 'L':
            median = 49.9 + (umur_bulan * 1.1)
            sd = 1.9 + (umur_bulan * 0.02)
        else:
            median = 49.1 + (umur_bulan * 1.0)
            sd = 1.9 + (umur_bulan * 0.02)
    
    elif indikator == 'BB/TB':
        median = 15 + (umur_bulan * 0.05)
        sd = 1.2
    
    return median, sd

def preprocess_input(data, le_gender):
    """Preprocess input data"""
    # Encode jenis kelamin
    jk_encoded = le_gender.transform([data['jenis_kelamin']])[0]
    
    # Calculate z-scores
    median_bb, sd_bb = get_who_standard(data['jenis_kelamin'], data['umur_bulan'], 'BB/U')
    median_tb, sd_tb = get_who_standard(data['jenis_kelamin'], data['umur_bulan'], 'TB/U')
    median_bb_tb, sd_bb_tb = get_who_standard(data['jenis_kelamin'], data['umur_bulan'], 'BB/TB')
    
    z_bb_u = calculate_z_score(data['berat_badan'], median_bb, sd_bb)
    z_tb_u = calculate_z_score(data['tinggi_badan'], median_tb, sd_tb)
    z_bb_tb = calculate_z_score(data['berat_badan'], median_bb_tb, sd_bb_tb)
    
    # Prepare features
    features = np.array([[
        jk_encoded,
        data['umur_bulan'],
        data['berat_badan'],
        data['tinggi_badan'],
        data.get('lingkar_lengan', 13.0),  # default jika tidak ada
        z_bb_u,
        z_tb_u,
        z_bb_tb
    ]])
    
    return features, {
        'z_score_bb_u': round(float(z_bb_u), 2),
        'z_score_tb_u': round(float(z_tb_u), 2),
        'z_score_bb_tb': round(float(z_bb_tb), 2)
    }

def predict(data):
    """Main prediction function"""
    # Load model
    model, le_gender, metadata = load_model()
    
    # Preprocess
    features, z_scores = preprocess_input(data, le_gender)
    
    # Predict
    prediction = model.predict(features)[0]
    probabilities = model.predict_proba(features)[0]
    
    # Get confidence
    max_prob = max(probabilities)
    confidence = round(float(max_prob * 100), 2)
    
    # Class probabilities
    class_probs = {}
    for i, cls in enumerate(model.classes_):
        class_probs[cls] = round(float(probabilities[i] * 100), 2)
    
    # Result
    result = {
        'status_gizi': prediction,
        'confidence': confidence,
        'z_scores': z_scores,
        'probabilities': class_probs,
        'model_version': metadata.get('train_date', 'unknown')
    }
    
    return result

def main():
    """Main function untuk CLI dan PHP integration"""
    if len(sys.argv) < 2:
        print(json.dumps({
            'error': 'No input data provided',
            'usage': 'python predict_gizi.py \'{"jenis_kelamin":"L","umur_bulan":24,"berat_badan":12.5,"tinggi_badan":85,"lingkar_lengan":15}\''
        }))
        sys.exit(1)
    
    try:
        # Parse input
        input_json = sys.argv[1]
        data = json.loads(input_json)
        
        # Validate required fields
        required = ['jenis_kelamin', 'umur_bulan', 'berat_badan', 'tinggi_badan']
        for field in required:
            if field not in data:
                raise ValueError(f"Missing required field: {field}")
        
        # Predict
        result = predict(data)
        
        # Output as JSON
        print(json.dumps(result))
        
    except json.JSONDecodeError as e:
        print(json.dumps({'error': f'Invalid JSON: {str(e)}'}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
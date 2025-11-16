#!/usr/bin/env python3
"""
Training Machine Learning Model untuk Klasifikasi Status Gizi Anak
Menggunakan Random Forest Classifier dengan validasi lengkap
"""

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib
import json
import matplotlib.pyplot as plt
import seaborn as sns

def load_and_preprocess_data(filepath='dataset_gizi_anak.csv'):
    """Load dan preprocessing dataset"""
    print("Loading dataset...")
    df = pd.read_csv(filepath)
    
    print(f"Dataset loaded: {len(df)} samples")
    print(f"\nColumns: {df.columns.tolist()}")
    
    # Encode jenis kelamin
    le_gender = LabelEncoder()
    df['jenis_kelamin_encoded'] = le_gender.fit_transform(df['jenis_kelamin'])
    
    # Features dan Target
    feature_columns = ['jenis_kelamin_encoded', 'umur_bulan', 'berat_badan', 
                      'tinggi_badan', 'lingkar_lengan', 
                      'z_score_bb_u', 'z_score_tb_u', 'z_score_bb_tb']
    
    X = df[feature_columns]
    y = df['status_gizi']
    
    print(f"\nFeatures shape: {X.shape}")
    print(f"Target classes: {y.unique()}")
    print(f"\nClass distribution:\n{y.value_counts()}")
    
    return X, y, le_gender, feature_columns

def train_model(X, y):
    """Training model dengan Random Forest"""
    print("\n" + "="*50)
    print("Training Model...")
    print("="*50)
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    print(f"\nTraining set: {len(X_train)} samples")
    print(f"Testing set: {len(X_test)} samples")
    
    # Initialize Random Forest
    rf_model = RandomForestClassifier(
        n_estimators=200,
        max_depth=15,
        min_samples_split=5,
        min_samples_leaf=2,
        random_state=42,
        n_jobs=-1
    )
    
    # Train
    print("\nTraining Random Forest...")
    rf_model.fit(X_train, y_train)
    
    # Predictions
    y_pred = rf_model.predict(X_test)
    
    # Evaluate
    accuracy = accuracy_score(y_test, y_pred)
    print(f"\n✓ Model trained successfully!")
    print(f"Test Accuracy: {accuracy*100:.2f}%")
    
    return rf_model, X_test, y_test, y_pred

def evaluate_model(model, X, y, y_test, y_pred):
    """Evaluasi model secara komprehensif"""
    print("\n" + "="*50)
    print("Model Evaluation")
    print("="*50)
    
    # Cross-validation
    print("\nCross-Validation (5-fold):")
    cv_scores = cross_val_score(model, X, y, cv=5)
    print(f"CV Scores: {cv_scores}")
    print(f"CV Mean: {cv_scores.mean()*100:.2f}% (+/- {cv_scores.std()*2*100:.2f}%)")
    
    # Classification Report
    print("\n" + "="*50)
    print("Classification Report:")
    print("="*50)
    print(classification_report(y_test, y_pred))
    
    # Confusion Matrix
    cm = confusion_matrix(y_test, y_pred)
    print("\nConfusion Matrix:")
    print(cm)
    
    # Feature Importance
    feature_names = ['Jenis Kelamin', 'Umur (bulan)', 'Berat (kg)', 
                    'Tinggi (cm)', 'Lingkar Lengan (cm)',
                    'Z-Score BB/U', 'Z-Score TB/U', 'Z-Score BB/TB']
    
    feature_importance = pd.DataFrame({
        'feature': feature_names,
        'importance': model.feature_importances_
    }).sort_values('importance', ascending=False)
    
    print("\n" + "="*50)
    print("Feature Importance:")
    print("="*50)
    print(feature_importance.to_string(index=False))
    
    return cv_scores, cm, feature_importance

def plot_results(cm, feature_importance, classes):
    """Visualisasi hasil"""
    fig, axes = plt.subplots(1, 2, figsize=(15, 5))
    
    # Confusion Matrix
    sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', 
                xticklabels=classes, yticklabels=classes, ax=axes[0])
    axes[0].set_title('Confusion Matrix')
    axes[0].set_ylabel('True Label')
    axes[0].set_xlabel('Predicted Label')
    
    # Feature Importance
    axes[1].barh(feature_importance['feature'], feature_importance['importance'])
    axes[1].set_xlabel('Importance')
    axes[1].set_title('Feature Importance')
    axes[1].invert_yaxis()
    
    plt.tight_layout()
    plt.savefig('model_evaluation.png', dpi=300, bbox_inches='tight')
    print("\n✓ Evaluation plot saved: model_evaluation.png")

def save_model(model, le_gender, feature_columns, metadata):
    """Save model dan metadata"""
    print("\n" + "="*50)
    print("Saving Model...")
    print("="*50)
    
    # Save model
    joblib.dump(model, 'model_gizi_rf.pkl')
    print("✓ Model saved: model_gizi_rf.pkl")
    
    # Save label encoder
    joblib.dump(le_gender, 'label_encoder_gender.pkl')
    print("✓ Label encoder saved: label_encoder_gender.pkl")
    
    # Save metadata
    metadata_full = {
        'model_type': 'RandomForestClassifier',
        'feature_columns': feature_columns,
        'classes': metadata['classes'],
        'accuracy': metadata['accuracy'],
        'cv_mean': metadata['cv_mean'],
        'cv_std': metadata['cv_std'],
        'n_samples': metadata['n_samples'],
        'train_date': pd.Timestamp.now().strftime('%Y-%m-%d %H:%M:%S')
    }
    
    with open('model_metadata.json', 'w') as f:
        json.dump(metadata_full, f, indent=4)
    print("✓ Metadata saved: model_metadata.json")

def main():
    """Main training pipeline"""
    print("="*50)
    print("SISTEM DIAGNOSA STATUS GIZI ANAK")
    print("Machine Learning Model Training")
    print("="*50)
    
    # 1. Load data
    X, y, le_gender, feature_columns = load_and_preprocess_data()
    
    # 2. Train model
    model, X_test, y_test, y_pred = train_model(X, y)
    
    # 3. Evaluate
    cv_scores, cm, feature_importance = evaluate_model(model, X, y, y_test, y_pred)
    
    # 4. Plot results
    classes = sorted(y.unique())
    plot_results(cm, feature_importance, classes)
    
    # 5. Save model
    metadata = {
        'classes': classes,
        'accuracy': float(accuracy_score(y_test, y_pred)),
        'cv_mean': float(cv_scores.mean()),
        'cv_std': float(cv_scores.std()),
        'n_samples': len(X)
    }
    save_model(model, le_gender, feature_columns, metadata)
    
    print("\n" + "="*50)
    print("Training Complete!")
    print("="*50)
    print(f"\n✓ Final Test Accuracy: {metadata['accuracy']*100:.2f}%")
    print(f"✓ Cross-Validation: {metadata['cv_mean']*100:.2f}% (+/- {metadata['cv_std']*2*100:.2f}%)")
    print("\nModel files created:")
    print("  - model_gizi_rf.pkl")
    print("  - label_encoder_gender.pkl")
    print("  - model_metadata.json")
    print("  - model_evaluation.png")

if __name__ == "__main__":
    main()
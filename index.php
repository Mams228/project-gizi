<?php
require_once 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo APP_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Form Diagnosa Status Gizi Anak</h4>
                    </div>
                    <div class="card-body">
                        <form action="result.php" method="POST" id="formDiagnosa">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Anak *</label>
                                    <input type="text" name="nama_anak" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin *</label>
                                    <select name="jenis_kelamin" class="form-select" required>
                                        <option value="">Pilih...</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir *</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Umur (Bulan) *</label>
                                    <input type="number" name="umur_bulan" class="form-control" required min="0" max="60" readonly>
                                    <small class="text-muted">Otomatis terisi dari tanggal lahir</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Berat Badan (kg) *</label>
                                    <input type="number" name="berat_badan" class="form-control" step="0.1" required min="2" max="30">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tinggi Badan (cm) *</label>
                                    <input type="number" name="tinggi_badan" class="form-control" step="0.1" required min="45" max="120">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lingkar Lengan (cm)</label>
                                    <input type="number" name="lingkar_lengan" class="form-control" step="0.1" min="10" max="25">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Orang Tua</label>
                                    <input type="text" name="nama_orangtua" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="alamat" class="form-control">
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Catatan:</strong> Sistem akan melakukan diagnosa menggunakan Machine Learning berdasarkan standar WHO (World Health Organization).
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-clipboard-check"></i> Diagnosa Status Gizi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5>Informasi Penting:</h5>
                        <ul>
                            <li>Pastikan data yang dimasukkan akurat</li>
                            <li>Untuk anak usia 0-60 bulan (0-5 tahun)</li>
                            <li>Hasil diagnosa sebagai referensi awal</li>
                            <li>Konsultasikan dengan tenaga kesehatan untuk tindakan lanjut</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2024 <?php echo APP_NAME; ?> - Versi <?php echo APP_VERSION; ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Auto hitung umur dari tanggal lahir
        document.querySelector('input[name="tanggal_lahir"]').addEventListener('change', function() {
            const lahir = new Date(this.value);
            const sekarang = new Date();
            const bulan = (sekarang.getFullYear() - lahir.getFullYear()) * 12 + 
                         (sekarang.getMonth() - lahir.getMonth());
            document.querySelector('input[name="umur_bulan"]').value = bulan;
        });

        // Validasi form
        document.getElementById('formDiagnosa').addEventListener('submit', function(e) {
            const umur = parseInt(document.querySelector('input[name="umur_bulan"]').value);
            if (umur < 0 || umur > 60) {
                e.preventDefault();
                alert('Umur harus antara 0-60 bulan (0-5 tahun)');
            }
        });
    </script>
</body>
</html>
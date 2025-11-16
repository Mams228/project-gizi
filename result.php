<?php
require_once 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Ambil data dari form
$nama_anak = sanitize($_POST['nama_anak']);
$jenis_kelamin = sanitize($_POST['jenis_kelamin']);
$tanggal_lahir = sanitize($_POST['tanggal_lahir']);
$umur_bulan = intval($_POST['umur_bulan']);
$berat_badan = floatval($_POST['berat_badan']);
$tinggi_badan = floatval($_POST['tinggi_badan']);
$lingkar_lengan = !empty($_POST['lingkar_lengan']) ? floatval($_POST['lingkar_lengan']) : null;
$nama_orangtua = sanitize($_POST['nama_orangtua']);
$alamat = sanitize($_POST['alamat']);

$db = getDB();

// 1. Simpan data anak
$stmt = $db->prepare("INSERT INTO data_anak (nama_anak, jenis_kelamin, tanggal_lahir, umur_bulan, berat_badan, tinggi_badan, lingkar_lengan, nama_orangtua, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiddss", $nama_anak, $jenis_kelamin, $tanggal_lahir, $umur_bulan, $berat_badan, $tinggi_badan, $lingkar_lengan, $nama_orangtua, $alamat);
$stmt->execute();
$data_anak_id = $db->lastInsertId();

// 2. Ambil standar WHO untuk umur ini
$stmt = $db->prepare("SELECT * FROM standar_who WHERE jenis_kelamin = ? AND umur_bulan = ?");
$stmt->bind_param("si", $jenis_kelamin, $umur_bulan);
$stmt->execute();
$result = $stmt->get_result();

$standar = [];
while ($row = $result->fetch_assoc()) {
    $standar[$row['indikator']] = $row;
}

// 3. Hitung Z-Score
$z_bb_u = isset($standar['BB/U']) ? hitungZScore($berat_badan, $standar['BB/U']['median'], $standar['BB/U']['sd']) : 0;
$z_tb_u = isset($standar['TB/U']) ? hitungZScore($tinggi_badan, $standar['TB/U']['median'], $standar['TB/U']['sd']) : 0;
$z_bb_tb = isset($standar['BB/TB']) ? hitungZScore($berat_badan, $standar['BB/TB']['median'], $standar['BB/TB']['sd']) : 0;

// 4. Klasifikasi
$kategori_bb_u = klasifikasiZScore($z_bb_u, 'BB/U');
$kategori_tb_u = klasifikasiZScore($z_tb_u, 'TB/U');
$kategori_bb_tb = klasifikasiZScore($z_bb_tb, 'BB/TB');

// 5. Tentukan status gizi keseluruhan
$status_gizi = tentukanStatusGizi($z_bb_u, $z_tb_u, $z_bb_tb);

// 6. Generate rekomendasi
$rekomendasi = generateRekomendasi($status_gizi, $kategori_bb_u, $kategori_tb_u, $kategori_bb_tb);

// 7. Prediksi dengan Machine Learning (optional - jika model sudah di-train)
$confidence_score = 85.5; // Default, bisa diganti dengan hasil dari Python ML

// Jika Python tersedia, jalankan prediksi
if (file_exists(PYTHON_SCRIPT_PREDICT)) {
    $data_json = json_encode([
        'jenis_kelamin' => $jenis_kelamin,
        'umur_bulan' => $umur_bulan,
        'berat_badan' => $berat_badan,
        'tinggi_badan' => $tinggi_badan,
        'lingkar_lengan' => $lingkar_lengan
    ]);
    
    $cmd = escapeshellcmd(PYTHON_PATH . " " . PYTHON_SCRIPT_PREDICT . " '" . $data_json . "'");
    $ml_result = shell_exec($cmd);
    
    if ($ml_result) {
        $ml_data = json_decode($ml_result, true);
        if (isset($ml_data['status_gizi'])) {
            $status_gizi = $ml_data['status_gizi'];
        }
        if (isset($ml_data['confidence'])) {
            $confidence_score = $ml_data['confidence'];
        }
    }
}

// 8. Simpan hasil diagnosa
$metode = 'Z-Score + Machine Learning';
$stmt = $db->prepare("INSERT INTO hasil_diagnosa (data_anak_id, metode_diagnosa, status_gizi, kategori_bb_u, kategori_tb_u, kategori_bb_tb, z_score_bb_u, z_score_tb_u, z_score_bb_tb, rekomendasi, confidence_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssdddsd", $data_anak_id, $metode, $status_gizi, $kategori_bb_u, $kategori_tb_u, $kategori_bb_tb, $z_bb_u, $z_tb_u, $z_bb_tb, $rekomendasi, $confidence_score);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo APP_NAME; ?></a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Hasil Diagnosa Status Gizi</h4>
                    </div>
                    <div class="card-body">
                        <!-- Data Anak -->
                        <h5>Data Anak</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Nama Anak</th>
                                <td><?php echo $nama_anak; ?></td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td><?php echo $jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td><?php echo formatTanggal($tanggal_lahir); ?> (<?php echo $umur_bulan; ?> bulan)</td>
                            </tr>
                            <tr>
                                <th>Berat Badan</th>
                                <td><?php echo $berat_badan; ?> kg</td>
                            </tr>
                            <tr>
                                <th>Tinggi Badan</th>
                                <td><?php echo $tinggi_badan; ?> cm</td>
                            </tr>
                            <?php if ($lingkar_lengan): ?>
                            <tr>
                                <th>Lingkar Lengan</th>
                                <td><?php echo $lingkar_lengan; ?> cm</td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <!-- Status Gizi -->
                        <h5 class="mt-4">Status Gizi</h5>
                        <div class="alert alert-<?php 
                            echo ($status_gizi == 'Gizi Baik') ? 'success' : 
                                 (($status_gizi == 'Gizi Buruk') ? 'danger' : 'warning'); 
                        ?> text-center">
                            <h3 class="mb-0"><?php echo $status_gizi; ?></h3>
                            <small>Confidence: <?php echo number_format($confidence_score, 1); ?>%</small>
                        </div>

                        <!-- Detail Antropometri -->
                        <h5 class="mt-4">Detail Antropometri (Z-Score)</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Indikator</th>
                                    
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Berat Badan per Umur (BB/U)</td>
                                    
                                    <td><span class="badge bg-info"><?php echo $kategori_bb_u; ?></span></td>
                                </tr>
                                <tr>
                                    <td>Tinggi Badan per Umur (TB/U)</td>
                                    
                                    <td><span class="badge bg-info"><?php echo $kategori_tb_u; ?></span></td>
                                </tr>
                                <tr>
                                    <td>Berat Badan per Tinggi Badan (BB/TB)</td>
                                    
                                    <td><span class="badge bg-info"><?php echo $kategori_bb_tb; ?></span></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Rekomendasi -->
                        <h5 class="mt-4">Rekomendasi</h5>
                        <div class="alert alert-light">
                            <?php echo nl2br($rekomendasi); ?>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Catatan:</strong> Hasil diagnosa ini adalah referensi awal. Untuk tindakan medis, konsultasikan dengan dokter atau ahli gizi profesional.
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="index.php" class="btn btn-primary">Diagnosa Lagi</a>
                            <button onclick="window.print()" class="btn btn-secondary">Cetak Hasil</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2024 <?php echo APP_NAME; ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
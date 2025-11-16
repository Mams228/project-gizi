<?php
// about.php
$page_title = "Tentang Sistem";
require_once 'includes/header.php';
?>

<div class="container my-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-12 text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">Tentang Sistem Diagnosa Gizi Anak</h1>
            <p class="lead text-muted">Sistem berbasis Machine Learning untuk memantau dan mendiagnosa status gizi anak</p>
        </div>
    </div>

    <!-- About Content -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-bullseye fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title text-center mb-3">Tujuan Sistem</h3>
                    <p class="card-text">
                        Sistem ini dikembangkan untuk membantu tenaga kesehatan, orang tua, dan masyarakat umum dalam:
                    </p>
                    <ul>
                        <li>Memantau pertumbuhan dan perkembangan anak</li>
                        <li>Mendeteksi dini masalah gizi pada anak</li>
                        <li>Memberikan rekomendasi penanganan yang tepat</li>
                        <li>Mempermudah dokumentasi data kesehatan anak</li>
                        <li>Mendukung program perbaikan gizi nasional</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-cogs fa-3x text-success"></i>
                    </div>
                    <h3 class="card-title text-center mb-3">Cara Kerja</h3>
                    <p class="card-text">
                        Sistem menggunakan teknologi Machine Learning yang telah dilatih dengan data antropometri anak berdasarkan standar WHO:
                    </p>
                    <ol>
                        <li>Input data anak (nama, usia, berat badan, tinggi badan)</li>
                        <li>Sistem menghitung Z-score berdasarkan standar WHO</li>
                        <li>Model ML menganalisis dan memprediksi status gizi</li>
                        <li>Hasil diagnosa dan rekomendasi ditampilkan</li>
                        <li>Data tersimpan untuk monitoring pertumbuhan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicators -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-line"></i> Indikator Antropometri</h4>
                </div>
                <div class="card-body">
                    <p>Sistem ini menggunakan 3 indikator utama sesuai standar WHO:</p>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h5 class="text-primary"><i class="fas fa-weight"></i> BB/U</h5>
                                <h6>Berat Badan menurut Umur</h6>
                                <p class="mb-0 small">Indikator untuk mengidentifikasi anak dengan berat badan kurang atau sangat kurang.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h5 class="text-success"><i class="fas fa-ruler-vertical"></i> TB/U</h5>
                                <h6>Tinggi Badan menurut Umur</h6>
                                <p class="mb-0 small">Indikator untuk mengidentifikasi anak yang pendek atau sangat pendek (stunting).</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h5 class="text-info"><i class="fas fa-balance-scale"></i> BB/TB</h5>
                                <h6>Berat Badan menurut Tinggi Badan</h6>
                                <p class="mb-0 small">Indikator untuk mengidentifikasi anak gizi kurang, baik, lebih, atau obesitas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Categories -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-list"></i> Kategori Status Gizi</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status Gizi</th>
                                    <th>Kriteria (Z-score)</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td><strong>Gizi Buruk</strong></td>
                                    <td>BB/TB < -3 SD</td>
                                    <td>Memerlukan penanganan segera oleh tenaga medis</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>Gizi Kurang</strong></td>
                                    <td>-3 SD ≤ BB/TB < -2 SD</td>
                                    <td>Perlu peningkatan asupan gizi dan monitoring</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Gizi Baik</strong></td>
                                    <td>-2 SD ≤ BB/TB ≤ 2 SD</td>
                                    <td>Status gizi normal, pertahankan pola makan sehat</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>Gizi Lebih</strong></td>
                                    <td>2 SD < BB/TB ≤ 3 SD</td>
                                    <td>Risiko obesitas, perlu pengaturan pola makan</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>Obesitas</strong></td>
                                    <td>BB/TB > 3 SD</td>
                                    <td>Memerlukan intervensi diet dan aktivitas fisik</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-center mb-4 fw-bold">Fitur Sistem</h2>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                    <h5>Diagnosa Otomatis</h5>
                    <p class="text-muted">Perhitungan status gizi otomatis menggunakan algoritma ML</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-3x text-success mb-3"></i>
                    <h5>Penyimpanan Data</h5>
                    <p class="text-muted">Data tersimpan aman untuk monitoring jangka panjang</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                    <h5>Laporan & Statistik</h5>
                    <p class="text-muted">Visualisasi data dan laporan lengkap untuk analisis</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-warning shadow-sm">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Disclaimer</h5>
                <hr>
                <p class="mb-0">
                    <strong>Penting:</strong> Hasil diagnosa dari sistem ini merupakan <em>screening</em> awal dan tidak menggantikan diagnosis medis profesional. 
                    Untuk penanganan lebih lanjut, terutama pada kasus gizi buruk atau obesitas, sangat disarankan untuk berkonsultasi dengan dokter anak, 
                    ahli gizi, atau tenaga kesehatan yang berkompeten. Sistem ini dibuat untuk tujuan edukasi dan membantu monitoring kesehatan anak.
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-3">Butuh Bantuan?</h4>
                    <p>Jika Anda memiliki pertanyaan atau memerlukan bantuan, silakan hubungi kami:</p>
                    <p>
                        <i class="fas fa-envelope text-primary"></i> <strong>Email:</strong> info@sistemgizi.com<br>
                        <i class="fas fa-phone text-success"></i> <strong>Telepon:</strong> (021) 1234-5678<br>
                        <i class="fas fa-clock text-info"></i> <strong>Jam Operasional:</strong> Senin - Jumat, 08:00 - 16:00 WIB
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

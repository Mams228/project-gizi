<?php
require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

requireLogin();
$db = getDB();

// Statistik
$total_anak = $db->query("SELECT COUNT(*) as total FROM data_anak")->fetch_assoc()['total'];
$total_diagnosa = $db->query("SELECT COUNT(*) as total FROM hasil_diagnosa")->fetch_assoc()['total'];

// Statistik Status Gizi
$status_gizi_stats = $db->query("
    SELECT status_gizi, COUNT(*) as jumlah 
    FROM hasil_diagnosa 
    GROUP BY status_gizi 
    ORDER BY jumlah DESC
")->fetch_all(MYSQLI_ASSOC);

// Diagnosa Terbaru
$diagnosa_terbaru = $db->query("
    SELECT hd.*, da.nama_anak, da.jenis_kelamin, da.umur_bulan
    FROM hasil_diagnosa hd
    JOIN data_anak da ON hd.data_anak_id = da.id
    ORDER BY hd.tanggal_diagnosa DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

include 'navbar.php';
?>

<div class="container mt-4">
    <h2>Dashboard</h2>
    <p class="text-muted">Selamat datang, <?php echo getCurrentAdmin()['nama']; ?>!</p>
    
    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Anak</h5>
                    <h2><?php echo $total_anak; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Diagnosa</h5>
                    <h2><?php echo $total_diagnosa; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Gizi Kurang</h5>
                    <h2><?php 
                        $gk = array_filter($status_gizi_stats, fn($s) => $s['status_gizi'] == 'Gizi Kurang');
                        echo !empty($gk) ? reset($gk)['jumlah'] : 0;
                    ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Gizi Buruk</h5>
                    <h2><?php 
                        $gb = array_filter($status_gizi_stats, fn($s) => $s['status_gizi'] == 'Gizi Buruk');
                        echo !empty($gb) ? reset($gb)['jumlah'] : 0;
                    ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistik Status Gizi -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Distribusi Status Gizi</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartStatusGizi"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Diagnosa Terbaru</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnosa_terbaru as $d): ?>
                            <tr>
                                <td><?php echo $d['nama_anak']; ?></td>
                                <td><span class="badge bg-<?php 
                                    echo ($d['status_gizi'] == 'Gizi Baik') ? 'success' : 
                                         (($d['status_gizi'] == 'Gizi Buruk') ? 'danger' : 'warning'); 
                                ?>"><?php echo $d['status_gizi']; ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($d['tanggal_diagnosa'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Status Gizi
const ctx = document.getElementById('chartStatusGizi');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($status_gizi_stats, 'status_gizi')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($status_gizi_stats, 'jumlah')); ?>,
            backgroundColor: [
                '#28a745', // Gizi Baik
                '#ffc107', // Gizi Kurang
                '#dc3545', // Gizi Buruk
                '#17a2b8', // Stunting
                '#fd7e14'  // Gizi Lebih
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
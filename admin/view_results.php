<?php
// admin/view_results.php
require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('admin/login.php');
}

$page_title = "Hasil Diagnosa";

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Get diagnosa data
try {
    $sql = "SELECT d.*, a.nama, a.jenis_kelamin, a.tanggal_lahir 
            FROM diagnosa d 
            JOIN anak a ON d.anak_id = a.id 
            WHERE 1=1";
    
    $params = [];
    
    if ($filter_status) {
        $sql .= " AND d.status_gizi = ?";
        $params[] = $filter_status;
    }
    
    if ($filter_date) {
        $sql .= " AND DATE(d.created_at) = ?";
        $params[] = $filter_date;
    }
    
    $sql .= " ORDER BY d.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $diagnosa_list = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $diagnosa_list = [];
    set_flash('danger', 'Error loading data: ' . $e->getMessage());
}

// Get unique status for filter
try {
    $stmt = $conn->query("SELECT DISTINCT status_gizi FROM diagnosa ORDER BY status_gizi");
    $status_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $status_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Sistem Gizi Anak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid py-4">
        <?php
        $flash = get_flash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> <?php echo $page_title; ?></h4>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Filter Status Gizi</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach($status_list as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $filter_status == $status ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Export Button -->
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" onclick="exportTableToCSV('dataTable', 'data_diagnosa.csv')">
                        <i class="fas fa-file-excel"></i> Export ke CSV
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover data-table" id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Anak</th>
                                <th>JK</th>
                                <th>Umur (bulan)</th>
                                <th>BB (kg)</th>
                                <th>TB (cm)</th>
                                <th>Status Gizi</th>
                                <th>BB/U</th>
                                <th>TB/U</th>
                                <th>BB/TB</th>
                                <th>Confidence</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($diagnosa_list as $diag): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($diag['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($diag['nama']); ?></td>
                                <td><?php echo $diag['jenis_kelamin']; ?></td>
                                <td><?php echo $diag['umur_bulan']; ?></td>
                                <td><?php echo $diag['berat_badan']; ?></td>
                                <td><?php echo $diag['tinggi_badan']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo get_status_color($diag['status_gizi']); ?>">
                                        <?php echo $diag['status_gizi']; ?>
                                    </span>
                                </td>
                                <td><small><?php echo $diag['kategori_bb_u'] ?? '-'; ?></small></td>
                                <td><small><?php echo $diag['kategori_tb_u'] ?? '-'; ?></small></td>
                                <td><small><?php echo $diag['kategori_bb_tb'] ?? '-'; ?></small></td>
                                <td><?php echo $diag['confidence_score']; ?>%</td>
                                <td>
                                    <button class="btn btn-info btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal<?php echo $diag['id']; ?>"
                                            title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal<?php echo $diag['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Detail Diagnosa</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="fw-bold">Data Anak:</h6>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td width="200">Nama</td>
                                                    <td><?php echo htmlspecialchars($diag['nama']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Jenis Kelamin</td>
                                                    <td><?php echo $diag['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Tanggal Diagnosa</td>
                                                    <td><?php echo date('d F Y, H:i', strtotime($diag['created_at'])); ?></td>
                                                </tr>
                                            </table>

                                            <h6 class="fw-bold mt-3">Data Antropometri:</h6>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td width="200">Umur</td>
                                                    <td><?php echo $diag['umur_bulan']; ?> bulan</td>
                                                </tr>
                                                <tr>
                                                    <td>Berat Badan</td>
                                                    <td><?php echo $diag['berat_badan']; ?> kg</td>
                                                </tr>
                                                <tr>
                                                    <td>Tinggi Badan</td>
                                                    <td><?php echo $diag['tinggi_badan']; ?> cm</td>
                                                </tr>
                                                <?php if($diag['lingkar_lengan']): ?>
                                                <tr>
                                                    <td>Lingkar Lengan</td>
                                                    <td><?php echo $diag['lingkar_lengan']; ?> cm</td>
                                                </tr>
                                                <?php endif; ?>
                                            </table>

                                            <h6 class="fw-bold mt-3">Hasil Diagnosa:</h6>
                                            <div class="alert alert-<?php echo get_status_color($diag['status_gizi']); ?>">
                                                <h5><?php echo $diag['status_gizi']; ?></h5>
                                                <p class="mb-0">Confidence Score: <?php echo $diag['confidence_score']; ?>%</p>
                                            </div>

                                            <h6 class="fw-bold mt-3">Rekomendasi:</h6>
                                            <div class="alert alert-info">
                                                <?php echo nl2br(htmlspecialchars($diag['rekomendasi'])); ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                pageLength: 25,
                order: [[1, 'desc']]
            });
        });
    </script>
</body>
</html>

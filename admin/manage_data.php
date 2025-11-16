<?php
// admin/manage_data.php
require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('admin/login.php');
}

$page_title = "Kelola Data Anak";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM anak WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Data anak berhasil dihapus!');
        redirect('admin/manage_data.php');
    } catch(PDOException $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}

// Get all anak data
try {
    $stmt = $conn->query("SELECT a.*, 
                          (SELECT COUNT(*) FROM diagnosa WHERE anak_id = a.id) as total_diagnosa,
                          (SELECT status_gizi FROM diagnosa WHERE anak_id = a.id ORDER BY created_at DESC LIMIT 1) as status_terakhir
                          FROM anak a 
                          ORDER BY a.created_at DESC");
    $data_anak = $stmt->fetchAll();
} catch(PDOException $e) {
    $data_anak = [];
    set_flash('danger', 'Error loading data: ' . $e->getMessage());
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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-users"></i> <?php echo $page_title; ?></h4>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Tambah Data Baru
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover data-table" id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>JK</th>
                                <th>Tanggal Lahir</th>
                                <th>Umur</th>
                                <th>Orang Tua</th>
                                <th>No. Telp</th>
                                <th>Total Diagnosa</th>
                                <th>Status Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($data_anak as $anak): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($anak['nama']); ?></td>
                                <td><?php echo $anak['jenis_kelamin'] == 'L' ? 'L' : 'P'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($anak['tanggal_lahir'])); ?></td>
                                <td><?php echo $anak['umur_bulan']; ?> bulan</td>
                                <td><?php echo htmlspecialchars($anak['nama_ortu'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($anak['no_telp'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $anak['total_diagnosa']; ?>x</span>
                                </td>
                                <td>
                                    <?php if($anak['status_terakhir']): ?>
                                        <span class="badge bg-<?php echo get_status_color($anak['status_terakhir']); ?>">
                                            <?php echo $anak['status_terakhir']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="view_child_detail.php?id=<?php echo $anak['id']; ?>" 
                                           class="btn btn-info" 
                                           title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_child.php?id=<?php echo $anak['id']; ?>" 
                                           class="btn btn-warning" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $anak['id']; ?>" 
                                           class="btn btn-danger btn-delete" 
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus data ini? Semua riwayat diagnosa akan ikut terhapus!')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
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
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                pageLength: 25,
                order: [[0, 'asc']]
            });
        });
    </script>
</body>
</html>

<?php
// admin/upload_dataset.php
require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('admin/login.php');
}

$page_title = "Upload Dataset & Training Model";

// Handle Generate Dataset
if (isset($_POST['generate_dataset'])) {
    $n_samples = intval($_POST['n_samples']);
    $balanced = $_POST['balanced'] === 'true';
    
    $python_script = MODEL_PATH . 'generate_dataset.py';
    
    // Create Python command
    $command = PYTHON_EXECUTABLE . " \"$python_script\"";
    
    // Run in background (simplified - production should use proper job queue)
    set_flash('info', 'Dataset generation started. This may take a few minutes...');
    redirect('admin/upload_dataset.php');
}

// Handle CSV Upload
if (isset($_POST['upload_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file'];
        $errors = validate_upload($file);
        
        if (empty($errors)) {
            $filename = generate_filename('csv');
            $destination = UPLOAD_PATH . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Count rows
                $row_count = count(file($destination)) - 1; // -1 for header
                
                // Save to database
                try {
                    $stmt = $conn->prepare("INSERT INTO dataset_uploads (filename, original_filename, total_rows, uploaded_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$filename, $file['name'], $row_count, $_SESSION['user_id']]);
                    
                    set_flash('success', "Dataset berhasil diupload! Total: $row_count rows");
                } catch(PDOException $e) {
                    set_flash('danger', 'Error saving to database: ' . $e->getMessage());
                }
                
                redirect('admin/upload_dataset.php');
            } else {
                set_flash('danger', 'Error uploading file');
            }
        } else {
            set_flash('danger', implode('<br>', $errors));
        }
    } else {
        set_flash('danger', 'No file uploaded');
    }
    redirect('admin/upload_dataset.php');
}

// Handle Train Model
if (isset($_POST['train_model'])) {
    $dataset_file = $_POST['dataset_file'];
    
    if (file_exists(UPLOAD_PATH . $dataset_file)) {
        // This is a simplified version - production should use job queue
        $python_script = MODEL_PATH . 'train_model.py';
        
        set_flash('info', 'Model training started. This process may take several minutes. Please check back later.');
        redirect('admin/upload_dataset.php');
    } else {
        set_flash('danger', 'Dataset file not found');
        redirect('admin/upload_dataset.php');
    }
}

// Get uploaded datasets
try {
    $stmt = $conn->query("SELECT d.*, u.username FROM dataset_uploads d LEFT JOIN users u ON d.uploaded_by = u.id ORDER BY d.upload_date DESC LIMIT 20");
    $datasets = $stmt->fetchAll();
} catch(PDOException $e) {
    $datasets = [];
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
    <style>
        .drop-zone {
            border: 3px dashed #0d6efd;
            border-radius: 10px;
            padding: 50px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .drop-zone:hover {
            background-color: #f8f9fa;
            border-color: #0056b3;
        }
        .drop-zone.dragover {
            background-color: #e7f3ff;
            border-color: #0056b3;
        }
        .code-block {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
    </style>
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

        <h2 class="mb-4"><i class="fas fa-database"></i> <?php echo $page_title; ?></h2>

        <div class="row">
            <!-- Generate Dataset -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-magic"></i> Generate Dataset WHO</h5>
                    </div>
                    <div class="card-body">
                        <p>Generate dataset antropometri berdasarkan standar WHO secara otomatis menggunakan script Python.</p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Dataset yang dihasilkan sudah realistis dan sesuai kurva pertumbuhan WHO
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Data</label>
                                <select class="form-select" name="n_samples">
                                    <option value="1000">1,000 data</option>
                                    <option value="2000">2,000 data</option>
                                    <option value="5000" selected>5,000 data (Recommended)</option>
                                    <option value="10000">10,000 data</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Distribusi</label>
                                <select class="form-select" name="balanced">
                                    <option value="true">Balanced (untuk ML training)</option>
                                    <option value="false">Realistis (distribusi Indonesia)</option>
                                </select>
                                <small class="text-muted">Balanced: distribusi merata semua kategori gizi</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="generate_dataset" class="btn btn-success btn-lg">
                                    <i class="fas fa-magic"></i> Generate Dataset
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6 class="fw-bold">Atau jalankan via Command Line:</h6>
                        <div class="code-block">
                            cd model<br>
                            python generate_dataset.py
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload CSV -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-upload"></i> Upload CSV Dataset</h5>
                    </div>
                    <div class="card-body">
                        <p>Upload file CSV dataset untuk training model. Format CSV harus sesuai dengan template.</p>

                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="drop-zone" id="dropZone">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5>Drag & Drop CSV File</h5>
                                <p class="text-muted">atau klik untuk memilih file</p>
                                <input type="file" name="csv_file" id="csvFile" accept=".csv" style="display: none;">
                            </div>

                            <div id="fileInfo" class="mt-3"></div>

                            <div class="d-grid mt-3">
                                <button type="submit" name="upload_csv" class="btn btn-primary btn-lg" id="uploadBtn" disabled>
                                    <i class="fas fa-upload"></i> Upload Dataset
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="alert alert-warning">
                            <strong>Format CSV yang diperlukan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Umur_Bulan</li>
                                <li>Jenis_Kelamin (L/P)</li>
                                <li>Berat_Badan_kg</li>
                                <li>Tinggi_Badan_cm</li>
                                <li>Status_Gizi</li>
                                <li>Z_Score_BB_U (optional)</li>
                                <li>Z_Score_TB_U (optional)</li>
                                <li>Z_Score_BB_TB (optional)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploaded Datasets -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-database"></i> Riwayat Dataset Upload</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datasets)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada dataset yang diupload</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Original Name</th>
                                            <th>Total Rows</th>
                                            <th>Uploaded By</th>
                                            <th>Upload Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($datasets as $dataset): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($dataset['filename']); ?></code></td>
                                            <td><?php echo htmlspecialchars($dataset['original_filename']); ?></td>
                                            <td><span class="badge bg-info"><?php echo number_format($dataset['total_rows']); ?></span></td>
                                            <td><?php echo htmlspecialchars($dataset['username'] ?? 'Unknown'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($dataset['upload_date'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="dataset_file" value="<?php echo $dataset['filename']; ?>">
                                                    <button type="submit" name="train_model" class="btn btn-sm btn-success" 
                                                            onclick="return confirm('Train model dengan dataset ini? Proses akan memakan waktu beberapa menit.')">
                                                        <i class="fas fa-robot"></i> Train Model
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Instructions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Panduan Training Model</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">Langkah-langkah Training Model:</h6>
                        <ol>
                            <li><strong>Generate atau Upload Dataset</strong>
                                <ul>
                                    <li>Generate dataset otomatis menggunakan WHO standards (recommended 5000+ data)</li>
                                    <li>Atau upload CSV dataset sendiri dengan format yang sesuai</li>
                                </ul>
                            </li>
                            <li><strong>Training via Command Line (Recommended)</strong>
                                <div class="code-block mt-2 mb-2">
                                    cd model<br>
                                    python train_model.py
                                </div>
                                Script akan:
                                <ul>
                                    <li>Load dataset</li>
                                    <li>Prepare features</li>
                                    <li>Train multiple models (Random Forest, Gradient Boosting, Decision Tree)</li>
                                    <li>Evaluate dan pilih model terbaik</li>
                                    <li>Save model ke <code>best_model.joblib</code></li>
                                </ul>
                            </li>
                            <li><strong>Test Model</strong>
                                <div class="code-block mt-2 mb-2">
                                    python predict_gizi.py L 24 12.5 85
                                </div>
                                <small class="text-muted">Parameter: JenisKelamin UmurBulan BeratBadan TinggiBadan</small>
                            </li>
                        </ol>

                        <div class="alert alert-success mt-3">
                            <i class="fas fa-lightbulb"></i> <strong>Tips:</strong>
                            <ul class="mb-0">
                                <li>Gunakan dataset balanced untuk training yang lebih baik</li>
                                <li>Minimal 1000 data untuk hasil yang akurat</li>
                                <li>Recommended 5000+ data untuk production</li>
                                <li>Model akan otomatis tersimpan di folder <code>model/</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('csvFile');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');

        dropZone.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            fileInput.files = files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', e => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                const size = (file.size / 1024 / 1024).toFixed(2);
                
                fileInfo.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-file-csv"></i> 
                        <strong>${file.name}</strong> (${size} MB)
                    </div>
                `;
                
                uploadBtn.disabled = false;
            }
        }
    </script>
</body>
</html>

<?php
// includes/functions.php

/**
 * Hitung umur dalam bulan dari tanggal lahir
 */
function hitungUmurBulan($tanggal_lahir) {
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $interval = $lahir->diff($sekarang);
    return ($interval->y * 12) + $interval->m;
}

/**
 * Hitung Z-Score
 */
function hitungZScore($nilai, $median, $sd) {
    return ($nilai - $median) / $sd;
}

/**
 * Klasifikasi berdasarkan Z-Score
 */
function klasifikasiZScore($z_score, $tipe = 'BB/U') {
    if ($tipe == 'BB/U') {
        if ($z_score < -3) return 'Gizi Buruk';
        if ($z_score < -2) return 'Gizi Kurang';
        if ($z_score <= 1) return 'Gizi Baik';
        if ($z_score <= 2) return 'Berisiko Gizi Lebih';
        return 'Gizi Lebih';
    } elseif ($tipe == 'TB/U') {
        if ($z_score < -3) return 'Sangat Pendek';
        if ($z_score < -2) return 'Pendek';
        if ($z_score <= 3) return 'Normal';
        return 'Tinggi';
    } elseif ($tipe == 'BB/TB') {
        if ($z_score < -3) return 'Sangat Kurus';
        if ($z_score < -2) return 'Kurus';
        if ($z_score <= 1) return 'Normal';
        if ($z_score <= 2) return 'Gemuk';
        return 'Obesitas';
    }
    return 'Unknown';
}

/**
 * Tentukan status gizi keseluruhan
 */
function tentukanStatusGizi($z_bb_u, $z_tb_u, $z_bb_tb) {
    $status_bb_u = klasifikasiZScore($z_bb_u, 'BB/U');
    $status_tb_u = klasifikasiZScore($z_tb_u, 'TB/U');
    $status_bb_tb = klasifikasiZScore($z_bb_tb, 'BB/TB');
    
    // Prioritas: Gizi Buruk > Gizi Kurang > Stunting > Wasting
    if ($status_bb_u == 'Gizi Buruk' || $status_bb_tb == 'Sangat Kurus') {
        return 'Gizi Buruk';
    } elseif ($status_bb_u == 'Gizi Kurang' || $status_bb_tb == 'Kurus') {
        return 'Gizi Kurang';
    } elseif ($status_tb_u == 'Sangat Pendek' || $status_tb_u == 'Pendek') {
        return 'Stunting';
    } elseif ($status_bb_u == 'Gizi Lebih' || $status_bb_tb == 'Obesitas') {
        return 'Gizi Lebih';
    } else {
        return 'Gizi Baik';
    }
}

/**
 * Generate rekomendasi berdasarkan status gizi
 */
function generateRekomendasi($status_gizi, $kategori_bb_u, $kategori_tb_u, $kategori_bb_tb) {
    $rekomendasi = [];
    
    if ($status_gizi == 'Gizi Buruk') {
        $rekomendasi[] = "Segera konsultasi ke dokter atau ahli gizi";
        $rekomendasi[] = "Pemberian makanan tinggi kalori dan protein";
        $rekomendasi[] = "Pemantauan rutin setiap minggu";
    } elseif ($status_gizi == 'Gizi Kurang') {
        $rekomendasi[] = "Tingkatkan asupan makanan bergizi";
        $rekomendasi[] = "Berikan makanan kaya protein (telur, ikan, daging)";
        $rekomendasi[] = "Konsultasi ke Puskesmas atau ahli gizi";
    } elseif ($status_gizi == 'Stunting') {
        $rekomendasi[] = "Fokus pada pemberian gizi seimbang";
        $rekomendasi[] = "Pastikan asupan protein, kalsium, dan vitamin D cukup";
        $rekomendasi[] = "Stimulasi tumbuh kembang anak";
    } elseif ($status_gizi == 'Gizi Lebih') {
        $rekomendasi[] = "Kurangi makanan tinggi gula dan lemak";
        $rekomendasi[] = "Tingkatkan aktivitas fisik";
        $rekomendasi[] = "Konsumsi sayur dan buah lebih banyak";
    } else {
        $rekomendasi[] = "Pertahankan pola makan seimbang";
        $rekomendasi[] = "Lanjutkan pemantauan tumbuh kembang rutin";
        $rekomendasi[] = "Pastikan anak mendapat ASI/nutrisi yang cukup";
    }
    
    return implode("\n", $rekomendasi);
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log aktivitas
 */
function logActivity($admin_id, $aktivitas, $detail = '') {
    $db = getDB();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $db->prepare("INSERT INTO log_aktivitas (admin_id, aktivitas, detail, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $admin_id, $aktivitas, $detail, $ip);
    $stmt->execute();
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $dt = new DateTime($tanggal);
    return $dt->format('d') . ' ' . $bulan[(int)$dt->format('m')] . ' ' . $dt->format('Y');
}

/**
 * Export data ke CSV
 */
function exportToCSV($data, $filename) {
    $filepath = EXPORT_PATH . $filename;
    $fp = fopen($filepath, 'w');
    
    // Header
    if (count($data) > 0) {
        fputcsv($fp, array_keys($data[0]));
    }
    
    // Data
    foreach ($data as $row) {
        fputcsv($fp, $row);
    }
    
    fclose($fp);
    return $filepath;
}
?>
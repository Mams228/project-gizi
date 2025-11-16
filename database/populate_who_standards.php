<?php
/**
 * Populate WHO Standards Table
 * Script untuk mengisi tabel standar_who dengan data aproksimasi WHO
 */

require_once '../config.php';
require_once '../includes/db_connect.php';

echo "=============================================================\n";
echo "Populating WHO Standards Table\n";
echo "=============================================================\n\n";

$db = getDB();

// Truncate existing data
echo "Clearing existing data...\n";
$db->query("TRUNCATE TABLE standar_who");
echo "✓ Done\n\n";

echo "Inserting WHO standards...\n";

$inserted = 0;

// Generate standards for 0-60 months
for ($umur = 0; $umur <= 60; $umur++) {
    foreach (['L', 'P'] as $jk) {
        // BB/U (Berat Badan per Umur)
        if ($jk == 'L') {
            $median_bb = 3.3 + ($umur * 0.15);
            $sd_bb = 0.4 + ($umur * 0.01);
        } else {
            $median_bb = 3.2 + ($umur * 0.14);
            $sd_bb = 0.4 + ($umur * 0.01);
        }
        
        $minus_3sd_bb = $median_bb - (3 * $sd_bb);
        $minus_2sd_bb = $median_bb - (2 * $sd_bb);
        $minus_1sd_bb = $median_bb - $sd_bb;
        $plus_1sd_bb = $median_bb + $sd_bb;
        $plus_2sd_bb = $median_bb + (2 * $sd_bb);
        $plus_3sd_bb = $median_bb + (3 * $sd_bb);
        
        $stmt = $db->prepare("INSERT INTO standar_who (jenis_kelamin, umur_bulan, indikator, median, sd, minus_3sd, minus_2sd, minus_1sd, plus_1sd, plus_2sd, plus_3sd) VALUES (?, ?, 'BB/U', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidddddddd", $jk, $umur, $median_bb, $sd_bb, $minus_3sd_bb, $minus_2sd_bb, $minus_1sd_bb, $plus_1sd_bb, $plus_2sd_bb, $plus_3sd_bb);
        $stmt->execute();
        $inserted++;
        
        // TB/U (Tinggi Badan per Umur)
        if ($jk == 'L') {
            $median_tb = 49.9 + ($umur * 1.1);
            $sd_tb = 1.9 + ($umur * 0.02);
        } else {
            $median_tb = 49.1 + ($umur * 1.0);
            $sd_tb = 1.9 + ($umur * 0.02);
        }
        
        $minus_3sd_tb = $median_tb - (3 * $sd_tb);
        $minus_2sd_tb = $median_tb - (2 * $sd_tb);
        $minus_1sd_tb = $median_tb - $sd_tb;
        $plus_1sd_tb = $median_tb + $sd_tb;
        $plus_2sd_tb = $median_tb + (2 * $sd_tb);
        $plus_3sd_tb = $median_tb + (3 * $sd_tb);
        
        $stmt = $db->prepare("INSERT INTO standar_who (jenis_kelamin, umur_bulan, indikator, median, sd, minus_3sd, minus_2sd, minus_1sd, plus_1sd, plus_2sd, plus_3sd) VALUES (?, ?, 'TB/U', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidddddddd", $jk, $umur, $median_tb, $sd_tb, $minus_3sd_tb, $minus_2sd_tb, $minus_1sd_tb, $plus_1sd_tb, $plus_2sd_tb, $plus_3sd_tb);
        $stmt->execute();
        $inserted++;
        
        // BB/TB (Berat Badan per Tinggi Badan)
        $median_bb_tb = 15 + ($umur * 0.05);
        $sd_bb_tb = 1.2;
        
        $minus_3sd_bb_tb = $median_bb_tb - (3 * $sd_bb_tb);
        $minus_2sd_bb_tb = $median_bb_tb - (2 * $sd_bb_tb);
        $minus_1sd_bb_tb = $median_bb_tb - $sd_bb_tb;
        $plus_1sd_bb_tb = $median_bb_tb + $sd_bb_tb;
        $plus_2sd_bb_tb = $median_bb_tb + (2 * $sd_bb_tb);
        $plus_3sd_bb_tb = $median_bb_tb + (3 * $sd_bb_tb);
        
        $stmt = $db->prepare("INSERT INTO standar_who (jenis_kelamin, umur_bulan, indikator, median, sd, minus_3sd, minus_2sd, minus_1sd, plus_1sd, plus_2sd, plus_3sd) VALUES (?, ?, 'BB/TB', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidddddddd", $jk, $umur, $median_bb_tb, $sd_bb_tb, $minus_3sd_bb_tb, $minus_2sd_bb_tb, $minus_1sd_bb_tb, $plus_1sd_bb_tb, $plus_2sd_bb_tb, $plus_3sd_bb_tb);
        $stmt->execute();
        $inserted++;
    }
    
    if ($umur % 10 == 0) {
        echo "  Progress: $umur/60 months...\n";
    }
}

echo "\n✓ Successfully inserted $inserted records\n";

// Verify
$count = $db->query("SELECT COUNT(*) as total FROM standar_who")->fetch_assoc()['total'];
echo "\nVerification:\n";
echo "  Total records: $count\n";
echo "  Expected: " . (61 * 2 * 3) . " records (61 months x 2 gender x 3 indicators)\n";

if ($count == 61 * 2 * 3) {
    echo "\n✓ All WHO standards populated successfully!\n";
} else {
    echo "\n⚠ Warning: Record count mismatch\n";
}

// Show sample
echo "\nSample data (24 months, Male):\n";
$result = $db->query("SELECT * FROM standar_who WHERE jenis_kelamin = 'L' AND umur_bulan = 24");
while ($row = $result->fetch_assoc()) {
    echo sprintf("  %s: Median=%.2f, SD=%.2f, -2SD=%.2f, +2SD=%.2f\n", 
                 $row['indikator'], 
                 $row['median'], 
                 $row['sd'], 
                 $row['minus_2sd'], 
                 $row['plus_2sd']);
}

echo "\n=============================================================\n";
echo "Done!\n";
echo "=============================================================\n";
?>
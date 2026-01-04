<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN (WAJIB)
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===============================
   FUNGSI: Ambil data dari semua database
================================ */
function getAllOrdersFromAllDatabases($user_id) {
    global $db_configs;
    
    $all_data = [
        'all' => [],
        'db1' => [],
        'db2' => [],
        'db3' => []
    ];
    
    $timeout = (int)env('DB_TIMEOUT', 2);
    
    // Loop semua database yang dikonfigurasi
    foreach ($db_configs as $index => $config) {
        mysqli_report(MYSQLI_REPORT_OFF);
        
        $mysqli = mysqli_init();
        mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
        
        $connected = @mysqli_real_connect(
            $mysqli,
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['db'],
            $config['port']
        );
        
        $db_key = 'db' . ($index + 1);
        
        if ($connected) {
            // Query data pesanan dari database ini
            $query = mysqli_query($mysqli, "
                SELECT id, nama, email, total, status, created_at
                FROM pesanan
                WHERE user_id = '$user_id'
                ORDER BY id DESC
            ");
            
            if ($query && mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) {
                    // Tambahkan info database sumber
                    $row['database_source'] = $config['name'];
                    $row['database_host'] = $config['host'];
                    $row['db_key'] = $db_key;
                    
                    // Masukkan ke array spesifik database
                    $all_data[$db_key][] = $row;
                    
                    // Masukkan juga ke array 'all'
                    $all_data['all'][] = $row;
                }
            }
            
            mysqli_close($mysqli);
        }
    }
    
    // Urutkan array 'all' berdasarkan tanggal terbaru
    usort($all_data['all'], function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $all_data;
}

// Ambil semua pesanan dari semua database
$all_data = getAllOrdersFromAllDatabases($user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #4f46e5;
            color: white;
        }
        h2 {
            margin-bottom: 20px;
        }
        .logout {
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .logout:hover {
            background: #dc2626;
        }
        
        /* Tab Styles */
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 0;
            border-bottom: 2px solid #e5e7eb;
        }
        .tab {
            padding: 12px 24px;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s;
            position: relative;
            top: 2px;
        }
        .tab:hover {
            background: #e5e7eb;
            color: #374151;
        }
        .tab.active {
            background: white;
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
        }
        .tab .badge-count {
            display: inline-block;
            padding: 2px 8px;
            background: #e5e7eb;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }
        .tab.active .badge-count {
            background: #4f46e5;
            color: white;
        }
        .tab-content {
            display: none;
            animation: fadeIn 0.3s;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .db-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #10b981;
            color: white;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .db-badge.db1 {
            background: #3b82f6;
        }
        .db-badge.db2 {
            background: #10b981;
        }
        .db-badge.db3 {
            background: #f59e0b;
        }
        .info-box {
            background: #e0e7ff;
            border-left: 4px solid #4f46e5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box strong {
            color: #4f46e5;
        }
        .table-wrapper {
            background: white;
            border-radius: 0 8px 8px 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .stats-footer {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 4px;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Dashboard User - Riwayat Pesanan</h2>

    <a class="logout" href="logout.php">Logout</a>

    <div class="info-box">
        <strong>Info:</strong> Pilih tab untuk melihat riwayat pesanan dari database tertentu atau lihat semua dalam tab "Semua Database"
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab active" onclick="showTab('all')">
            Semua Database
            <span class="badge-count"><?php echo count($all_data['all']); ?></span>
        </button>
        <button class="tab" onclick="showTab('db1')">
            Database 1
            <span class="badge-count"><?php echo count($all_data['db1']); ?></span>
        </button>
        <button class="tab" onclick="showTab('db2')">
            Database 2
            <span class="badge-count"><?php echo count($all_data['db2']); ?></span>
        </button>
        <button class="tab" onclick="showTab('db3')">
            Database 3
            <span class="badge-count"><?php echo count($all_data['db3']); ?></span>
        </button>
    </div>

    <!-- TAB CONTENT: SEMUA DATABASE -->
    <div id="tab-all" class="tab-content active">
        <div class="table-wrapper">
            <?php if (count($all_data['all']) == 0): ?>
                <div class="empty-state">
                    <p><strong>Belum ada transaksi</strong></p>
                    <p>Data pesanan dari semua database akan muncul di sini</p>
                </div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Sumber</th>
                    </tr>
                    <?php foreach ($all_data['all'] as $row): ?>
                        <?php
                        $badge_class = 'db-badge ' . $row['db_key'];
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <span class="<?php echo $badge_class; ?>">
                                    <?php echo $row['database_source']; ?>
                                </span>
                                <br>
                                <small style="color: #6b7280;"><?php echo $row['database_host']; ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="stats-footer">
                    <strong>Total:</strong> <?php echo count($all_data['all']); ?> pesanan dari <?php echo count($db_configs); ?> database
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB CONTENT: DATABASE 1 -->
    <div id="tab-db1" class="tab-content">
        <div class="table-wrapper">
            <?php if (count($all_data['db1']) == 0): ?>
                <div class="empty-state">
                    <p><strong>Belum ada transaksi di Database 1</strong></p>
                    <p><?php echo isset($db_configs[0]) ? $db_configs[0]['host'] : 'N/A'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                    <?php foreach ($all_data['db1'] as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="stats-footer">
                    <strong>Total:</strong> <?php echo count($all_data['db1']); ?> pesanan dari <span class="db-badge db1"><?php echo isset($db_configs[0]) ? $db_configs[0]['name'] : 'Database 1'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB CONTENT: DATABASE 2 -->
    <div id="tab-db2" class="tab-content">
        <div class="table-wrapper">
            <?php if (count($all_data['db2']) == 0): ?>
                <div class="empty-state">
                    <p><strong>Belum ada transaksi di Database 2</strong></p>
                    <p><?php echo isset($db_configs[1]) ? $db_configs[1]['host'] : 'N/A'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                    <?php foreach ($all_data['db2'] as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="stats-footer">
                    <strong>Total:</strong> <?php echo count($all_data['db2']); ?> pesanan dari <span class="db-badge db2"><?php echo isset($db_configs[1]) ? $db_configs[1]['name'] : 'Database 2'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB CONTENT: DATABASE 3 -->
    <div id="tab-db3" class="tab-content">
        <div class="table-wrapper">
            <?php if (count($all_data['db3']) == 0): ?>
                <div class="empty-state">
                    <p><strong>Belum ada transaksi di Database 3</strong></p>
                    <p><?php echo isset($db_configs[2]) ? $db_configs[2]['host'] : 'N/A'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                    <?php foreach ($all_data['db3'] as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="stats-footer">
                    <strong>Total:</strong> <?php echo count($all_data['db3']); ?> pesanan dari <span class="db-badge db3"><?php echo isset($db_configs[2]) ? $db_configs[2]['name'] : 'Database 3'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    var contents = document.getElementsByClassName('tab-content');
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    var tabs = document.getElementsByClassName('tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show selected tab content
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Add active class to clicked tab
    event.target.closest('.tab').classList.add('active');
}
</script>

</body>
</html>

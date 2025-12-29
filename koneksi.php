<?php
// Fungsi untuk load file .env
function loadEnv($path) {
    if (!file_exists($path)) {
        die("File .env tidak ditemukan di: " . $path);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse line
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Hapus quotes jika ada
            $value = trim($value, '"\'');
            
            // Set ke environment variable
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load file .env
loadEnv(__DIR__ . '/.env');

// Fungsi helper untuk get env value
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Konfigurasi Database dari .env
$db_configs = [
    [
        'name' => env('DB1_NAME', 'Database 1 (Primary)'),
        'host' => env('DB1_HOST', '127.0.0.1'),
        'user' => env('DB1_USER', 'root'),
        'pass' => env('DB1_PASS', ''),
        'db'   => env('DB1_DATABASE', 'project_sister'),
        'port' => (int)env('DB1_PORT', 3306)
    ],
    [
        'name' => env('DB2_NAME', 'Database 2 (Secondary)'),
        'host' => env('DB2_HOST', '127.0.0.1'),
        'user' => env('DB2_USER', 'root'),
        'pass' => env('DB2_PASS', ''),
        'db'   => env('DB2_DATABASE', 'project_sister'),
        'port' => (int)env('DB2_PORT', 3307)
    ],
    [
        'name' => env('DB3_NAME', 'Database 3 (Tertiary)'),
        'host' => env('DB3_HOST', '127.0.0.1'),
        'user' => env('DB3_USER', 'root'),
        'pass' => env('DB3_PASS', ''),
        'db'   => env('DB3_DATABASE', 'project_sister'),
        'port' => (int)env('DB3_PORT', 3308)
    ]
];

$conn = null;
$connected_db = null;
$connection_errors = [];

// Mencoba koneksi ke setiap database
foreach ($db_configs as $config) {
    mysqli_report(MYSQLI_REPORT_OFF);

    $mysqli = mysqli_init();

    // 🔥 SET TIMEOUT (detik) dari .env
    $timeout = (int)env('DB_TIMEOUT', 2);
    mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);

    $connected = @mysqli_real_connect(
        $mysqli,
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['db'],
        $config['port']
    );

    if ($connected) {
        $conn = $mysqli;
        $connected_db = $config;
        break;
    } else {
        $connection_errors[] = $config['name'] . ": " . mysqli_connect_error();
        mysqli_close($mysqli);
    }
}


// Jika semua database gagal
if (!$conn) {
    $error_message = "Semua koneksi database gagal!\n\n";
    $error_message .= implode("\n", $connection_errors);
    die($error_message);
}

// Fungsi untuk mendapatkan informasi koneksi
function getConnectionInfo() {
    global $conn, $connected_db;
    
    if (!$conn || !$connected_db) {
        return "Tidak ada koneksi aktif";
    }
    
    $info = [];
    $info['Database'] = $connected_db['name'];
    $info['Host'] = $connected_db['host'];
    $info['Port'] = $connected_db['port'];
    $info['Database Name'] = $connected_db['db'];
    $info['User'] = $connected_db['user'];
    $info['Connection ID'] = mysqli_thread_id($conn);
    $info['Server Info'] = mysqli_get_server_info($conn);
    $info['Protocol Version'] = mysqli_get_proto_info($conn);
    
    return $info;
}

// Jika file diakses langsung, tampilkan informasi koneksi
if (basename($_SERVER['PHP_SELF']) == 'koneksi.php') {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Informasi Koneksi Database</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 40px;
                max-width: 600px;
                width: 100%;
            }
            h1 {
                color: #667eea;
                margin-top: 0;
                text-align: center;
                font-size: 28px;
                margin-bottom: 30px;
            }
            .status {
                background: #10b981;
                color: white;
                padding: 15px;
                border-radius: 10px;
                text-align: center;
                font-weight: bold;
                margin-bottom: 30px;
                font-size: 18px;
            }
            .info-table {
                width: 100%;
                border-collapse: collapse;
            }
            .info-table tr {
                border-bottom: 1px solid #e5e7eb;
            }
            .info-table tr:last-child {
                border-bottom: none;
            }
            .info-table td {
                padding: 12px 8px;
            }
            .info-table td:first-child {
                font-weight: 600;
                color: #4b5563;
                width: 40%;
            }
            .info-table td:last-child {
                color: #1f2937;
            }
            .timestamp {
                text-align: center;
                color: #6b7280;
                margin-top: 20px;
                font-size: 14px;
            }
            .badge {
                display: inline-block;
                padding: 4px 12px;
                background: #667eea;
                color: white;
                border-radius: 20px;
                font-size: 12px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔌 Informasi Koneksi Database</h1>
            <div class="status">
                ✓ Koneksi Berhasil
            </div>
            
            <table class="info-table">
                <?php
                $info = getConnectionInfo();
                foreach ($info as $key => $value) {
                    echo "<tr>";
                    echo "<td>{$key}</td>";
                    if ($key === 'Database') {
                        echo "<td><span class='badge'>{$value}</span></td>";
                    } else {
                        echo "<td>{$value}</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </table>
            
            <div class="timestamp">
                <?php echo date('d F Y, H:i:s'); ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

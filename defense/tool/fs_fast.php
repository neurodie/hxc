<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

class PemindaiMalware {
    private $folderBerbahaya = [
        'ALFA_DATA', 'alfa_data', 'alfacgiapi', 'bypass', 'shell', 'webshell', 
        'backdoor', 'adminer', 'wp-content/uploads/php', 'cache/php', 'tmp/shell', 'temp/backdoor'
    ];
    
    private $namaFileBerbahaya = [
        'alfa', 'ALFA', 'c99', 'r57', 'wso', 'shell', 'backdoor', 'webshell', 
        'bypass', 'hack', 'adminer', 'phpinfo', 'IndoXploit', 'FilesMan', 'b374k'
    ];
    
    private $polaMencurigakan = [
        // Execution functions - lebih spesifik
        '/\beval\s*\(/i',
        '/\bbase64_decode\s*\(/i',
        '/\bshell_exec\s*\(/i',
        '/\bsystem\s*\(/i',
        '/\bexec\s*\(/i',
        '/\bpassthru\s*\(/i',
        '/\bproc_open\s*\(/i',
        '/\bpopen\s*\(/i',
        '/\bassert\s*\(/i',
        
        // Obfuscation techniques
        '/\bgzinflate\s*\(/i',
        '/\bgzuncompress\s*\(/i',
        '/\bstr_rot13\s*\(/i',
        '/\bcreate_function\s*\(/i',
        '/\bpreg_replace\s*\(.*["\']\/.*e["\']/i',
        
        // Dangerous globals
        '/\$_(GET|POST|REQUEST|COOKIE|SERVER)\s*\[/i',
        '/\$GLOBALS\s*\[/i',
        
        // File operations
        '/\bfile_get_contents\s*\(\s*["\']https?:\/\//i',
        '/\bfile_put_contents\s*\(/i',
        '/\bfopen\s*\(/i',
        '/\bfwrite\s*\(/i',
        '/\bfputs\s*\(/i',
        '/\bmove_uploaded_file\s*\(/i',
        '/\bcopy\s*\(/i',
        
        // Directory operations
        '/\bscandir\s*\(/i',
        '/\bglob\s*\(/i',
        '/\bopendir\s*\(/i',
        
        // Permission changes
        '/\bchmod\s*\(/i',
        '/\bchown\s*\(/i',
        
        // Backdoor indicators
        '/\bcall_user_func(_array)?\s*\(/i',
        '/\$\{["\']GLOBALS["\']\}/i',
        '/\bextract\s*\(\s*\$_(GET|POST|REQUEST)/i',
        
        // Remote inclusion
        '/\b(include|require)(_once)?\s*\(\s*["\']https?:\/\//i',
        
        // System info
        '/\bphp_uname\s*\(/i',
        '/\bget_current_user\s*\(/i',
        '/\bgetmyuid\s*\(/i',
        
        // Error suppression (suspicious pattern)
        '/error_reporting\s*\(\s*0\s*\)/i',
        '/@\s*(eval|system|exec|shell_exec|passthru)/i',
        
        // Encoded payloads
        '/\\\x[0-9a-f]{2}/i', // hex encoding
        '/chr\s*\(\s*\d+\s*\)\s*\./i', // character concatenation
    ];

    private $hasil = [];
    private $jumlahFile = 0;
    private $jumlahFolder = 0;
    private $waktuMulai;

    public function __construct() {
        $this->waktuMulai = microtime(true);
        echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemindai Malware Server</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            color: #00d4ff;
            text-align: center;
            padding: 30px 0;
            font-size: 2.5em;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            border-bottom: 3px solid #0f4c75;
            margin-bottom: 30px;
        }
        
        h2 {
            color: #4CAF50;
            margin: 25px 0 15px 0;
            font-size: 1.8em;
            border-left: 5px solid #4CAF50;
            padding-left: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #2a2a3e 0%, #1f1f2e 100%);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 212, 255, 0.2);
        }
        
        .stat-card h3 {
            color: #00d4ff;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
            color: #fff;
        }
        
        .table-container {
            background: #1f1f2e;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
            overflow-x: auto;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        th {
            background: linear-gradient(135deg, #0f4c75 0%, #1b6ca8 100%);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        td {
            padding: 15px 10px;
            border-bottom: 1px solid #2a2a3e;
            vertical-align: top;
        }
        
        tr {
            transition: background 0.2s;
        }
        
        tr:hover {
            background: #252535;
        }
        
        .level-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
        }
        
        .tinggi { 
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
        }
        
        .sedang { 
            background: linear-gradient(135deg, #ffbb33 0%, #ff8800 100%);
            color: white;
        }
        
        .rendah { 
            background: linear-gradient(135deg, #33b5e5 0%, #0099cc 100%);
            color: white;
        }
        
        .bersih-message {
            background: linear-gradient(135deg, #4CAF50 0%, #2e7d32 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            font-size: 1.3em;
            box-shadow: 0 8px 30px rgba(76, 175, 80, 0.3);
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 0.9em;
        }
        
        .button-danger {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3);
        }
        
        .button-danger:hover {
            background: linear-gradient(135deg, #cc0000 0%, #990000 100%);
            box-shadow: 0 6px 20px rgba(255, 68, 68, 0.5);
            transform: translateY(-2px);
        }
        
        .button-primary {
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }
        
        .button-primary:hover {
            background: linear-gradient(135deg, #0099cc 0%, #006699 100%);
            box-shadow: 0 6px 20px rgba(0, 212, 255, 0.5);
            transform: translateY(-2px);
        }
        
        pre {
            background: #0d1117;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            color: #c9d1d9;
            font-size: 13px;
            border: 1px solid #30363d;
            margin-top: 10px;
        }
        
        .code-snippet {
            background: #1f1f2e;
            padding: 10px;
            border-left: 3px solid #ff4444;
            margin-top: 10px;
            border-radius: 5px;
        }
        
        .location {
            color: #00d4ff;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #00d4ff;
        }
        
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        
        @media (max-width: 768px) {
            h1 { font-size: 1.8em; }
            .stat-card { padding: 15px; }
            table { font-size: 0.85em; }
            td, th { padding: 10px 5px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Pemindai Malware & Backdoor</h1>
HTML;
    }

    public function mulaiScan($direktori = '.') {
        echo "<p style='text-align: center; font-size: 1.1em; margin-bottom: 20px;'>📁 Memindai direktori: <b>" . htmlspecialchars(realpath($direktori)) . "</b></p>";
        $this->pindaiDirektori($direktori);
        $this->tampilkanHasil();
        $this->tampilkanStatistik();
    }

    private function pindaiDirektori($dir) {
        if (!is_dir($dir)) return;
        $this->jumlahFolder++;

        $namaFolder = basename($dir);
        foreach ($this->folderBerbahaya as $kata) {
            if (stripos($namaFolder, $kata) !== false) {
                $this->hasil[] = [
                    'jenis' => 'folder', 
                    'lokasi' => $dir, 
                    'masalah' => "Folder mencurigakan: $kata", 
                    'level' => 'tinggi',
                    'waktu' => filemtime($dir)
                ];
            }
        }

        $isi = @scandir($dir);
        if ($isi === false) return;

        foreach ($isi as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->pindaiDirektori($path);
            } else {
                $this->pindaiFile($path);
            }
        }
    }

    private function pindaiFile($path) {
        $this->jumlahFile++;
        $namaFile = basename($path);
        $ekstensi = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $ukuran = filesize($path);
        $waktuModifikasi = filemtime($path);

        foreach ($this->namaFileBerbahaya as $kata) {
            if (stripos($namaFile, $kata) !== false) {
                $this->hasil[] = [
                    'jenis' => 'file', 
                    'lokasi' => $path, 
                    'masalah' => "Nama file mencurigakan: $kata", 
                    'level' => 'tinggi',
                    'ukuran' => $this->formatUkuran($ukuran),
                    'waktu' => $waktuModifikasi
                ];
            }
        }

        $ekstensiBerbahaya = ['php', 'php3', 'phtml', 'php4', 'php5', 'phps', 'pht', 'phar', 'js'];
        if (in_array($ekstensi, $ekstensiBerbahaya)) {
            $this->pindaiIsiFile($path, $ukuran, $waktuModifikasi);
        }

        if ($namaFile == '.htaccess') {
            $this->pindaiHtaccess($path, $ukuran, $waktuModifikasi);
        }
    }

    private function pindaiIsiFile($path, $ukuran, $waktuModifikasi) {
        $isi = @file_get_contents($path);
        if ($isi === false) return;

        $temuan = [];
        foreach ($this->polaMencurigakan as $pola) {
            if (preg_match($pola, $isi)) {
                $temuan[] = str_replace(['/', 'i', '\\b', '\\s*', '\\('], '', $pola);
            }
        }

        $jumlahTemuan = count($temuan);
        if ($jumlahTemuan > 5) {
            $this->hasil[] = [
                'jenis' => 'file', 
                'lokasi' => $path,
                'masalah' => "Kode berbahaya terdeteksi ($jumlahTemuan pola): " . implode(', ', array_slice($temuan, 0, 5)),
                'level' => 'tinggi',
                'cuplikan' => substr($isi, 0, 400),
                'ukuran' => $this->formatUkuran($ukuran),
                'waktu' => $waktuModifikasi
            ];
        } elseif ($jumlahTemuan > 2) {
            $this->hasil[] = [
                'jenis' => 'file', 
                'lokasi' => $path,
                'masalah' => "Kode mencurigakan ($jumlahTemuan pola): " . implode(', ', $temuan),
                'level' => 'sedang',
                'ukuran' => $this->formatUkuran($ukuran),
                'waktu' => $waktuModifikasi
            ];
        }
    }

    private function pindaiHtaccess($path, $ukuran, $waktuModifikasi) {
        $isi = @file_get_contents($path);
        if ($isi === false) return;

        $polaHtaccess = [
            '/RewriteRule\s+.*\$.*\.php/i', 
            '/php_value\s+auto_prepend_file/i', 
            '/AddHandler\s+.*php.*\./i',
            '/SetHandler\s+application\/x-httpd-php/i',
            '/RedirectMatch\s+.*\.php/i',
            '/ErrorDocument\s+\d+\s+.*\.php/i',
            '/php_flag\s+display_errors/i'
        ];
        
        foreach ($polaHtaccess as $pola) {
            if (preg_match($pola, $isi)) {
                $this->hasil[] = [
                    'jenis' => 'file', 
                    'lokasi' => $path, 
                    'masalah' => "Konfigurasi berbahaya di .htaccess", 
                    'level' => 'tinggi',
                    'ukuran' => $this->formatUkuran($ukuran),
                    'waktu' => $waktuModifikasi,
                    'cuplikan' => substr($isi, 0, 300)
                ];
                break;
            }
        }
    }

    private function formatUkuran($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function tampilkanHasil() {
        if (empty($this->hasil)) {
            echo "<div class='bersih-message'><h3>✅ Sistem Bersih - Tidak Ditemukan Ancaman</h3><p>Semua file telah dipindai dan tidak ditemukan malware atau backdoor.</p></div>";
            return;
        }

        // Urutkan berdasarkan waktu terbaru dan level
        usort($this->hasil, function($a, $b) {
            // Prioritas level
            $prioritas = ['tinggi' => 1, 'sedang' => 2, 'rendah' => 3];
            $levelComp = $prioritas[$a['level']] <=> $prioritas[$b['level']];
            if ($levelComp !== 0) return $levelComp;
            
            // Jika level sama, urutkan berdasarkan waktu terbaru
            return $b['waktu'] <=> $a['waktu'];
        });

        echo "<h2>📌 Ancaman Terdeteksi</h2>";
        echo "<div class='table-container'>";
        echo "<table id='resultsTable'>";
        echo "<thead><tr><th>Level</th><th>Jenis</th><th>Lokasi</th><th>Masalah</th><th>Ukuran</th><th>Waktu Modifikasi</th><th>Aksi</th></tr></thead>";
        echo "<tbody>";

        foreach ($this->hasil as $index => $item) {
            $kelas = $item['level'];
            $ikon = ($item['level'] == 'tinggi') ? '🚨' : ($item['level'] == 'sedang' ? '⚠️' : 'ℹ️');
            $waktuFormat = date('Y-m-d H:i:s', $item['waktu']);

            echo "<tr class='result-row' data-index='$index'>";
            echo "<td><span class='level-badge $kelas'>$ikon " . ucfirst($item['level']) . "</span></td>";
            echo "<td>" . htmlspecialchars($item['jenis']) . "</td>";
            echo "<td class='location'>" . htmlspecialchars($item['lokasi']) . "</td>";
            echo "<td>" . htmlspecialchars($item['masalah']) . "</td>";
            echo "<td>" . ($item['ukuran'] ?? '-') . "</td>";
            echo "<td>" . $waktuFormat . "</td>";
            echo "<td>";
            if ($item['level'] === 'tinggi' && is_file($item['lokasi'])) {
                echo "<button class='button button-danger delete-btn' data-path='" . htmlspecialchars($item['lokasi'], ENT_QUOTES) . "' data-index='$index'>🗑️ Hapus</button>";
            }
            echo "</td></tr>";

            if (isset($item['cuplikan'])) {
                echo "<tr class='result-row' data-index='$index'><td colspan='7'><div class='code-snippet'><strong>Cuplikan Kode:</strong><pre>" . htmlspecialchars($item['cuplikan']) . "...</pre></div></td></tr>";
            }
        }
        
        echo "</tbody></table></div>";
        
        // JavaScript untuk penghapusan tanpa reload
        echo <<<JAVASCRIPT
        <script>
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!confirm('Apakah Anda yakin ingin menghapus file ini?')) return;
                
                const path = this.getAttribute('data-path');
                const index = this.getAttribute('data-index');
                const button = this;
                
                button.disabled = true;
                button.innerHTML = '⏳ Menghapus...';
                
                fetch('?hapus=' + encodeURIComponent(path))
                    .then(response => response.text())
                    .then(data => {
                        // Hapus baris dari tabel tanpa reload
                        document.querySelectorAll('.result-row[data-index="' + index + '"]').forEach(row => {
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';
                            setTimeout(() => row.remove(), 300);
                        });
                        
                        // Tampilkan notifikasi sukses
                        const notification = document.createElement('div');
                        notification.className = 'bersih-message';
                        notification.style.position = 'fixed';
                        notification.style.top = '20px';
                        notification.style.right = '20px';
                        notification.style.zIndex = '1000';
                        notification.style.maxWidth = '400px';
                        notification.innerHTML = '✅ File berhasil dihapus!';
                        document.body.appendChild(notification);
                        
                        setTimeout(() => {
                            notification.style.opacity = '0';
                            setTimeout(() => notification.remove(), 300);
                        }, 3000);
                        
                        // Cek apakah masih ada hasil
                        setTimeout(() => {
                            if (document.querySelectorAll('.result-row').length === 0) {
                                document.querySelector('.table-container').innerHTML = 
                                    '<div class="bersih-message"><h3>✅ Semua Ancaman Telah Dihapus</h3></div>';
                            }
                        }, 350);
                    })
                    .catch(error => {
                        alert('Gagal menghapus file: ' + error);
                        button.disabled = false;
                        button.innerHTML = '🗑️ Hapus';
                    });
            });
        });
        </script>
JAVASCRIPT;
    }

    private function tampilkanStatistik() {
        $durasi = number_format(microtime(true) - $this->waktuMulai, 2);
        $jumlahAncaman = count($this->hasil);
        $ancamanTinggi = count(array_filter($this->hasil, fn($item) => $item['level'] === 'tinggi'));
        
        echo "<h2>📊 Statistik Pemindaian</h2>";
        echo "<div class='stats-grid'>";
        
        echo "<div class='stat-card'><h3>📄 File Dipindai</h3><div class='value'>{$this->jumlahFile}</div></div>";
        echo "<div class='stat-card'><h3>📁 Folder Dipindai</h3><div class='value'>{$this->jumlahFolder}</div></div>";
        echo "<div class='stat-card'><h3>🚨 Ancaman Ditemukan</h3><div class='value' style='color: #ff4444;'>$jumlahAncaman</div></div>";
        echo "<div class='stat-card'><h3>⏱️ Durasi Scan</h3><div class='value' style='font-size: 2em;'>{$durasi}s</div></div>";
        
        echo "</div>";
        
        echo "<div class='action-buttons'>";
        echo "<a href='?' class='button button-primary'>🔄 Pindai Ulang</a>";
        echo "</div>";
    }

    public function hapusFile($path) {
        if (!file_exists($path)) {
            http_response_code(404);
            echo "File tidak ditemukan";
            return;
        }

        try {
            if (is_dir($path)) {
                $this->hapusFolder($path);
            } else {
                unlink($path);
            }
            echo "success";
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    private function hapusFolder($dir) {
        $isi = @scandir($dir);
        if ($isi === false) return;

        foreach ($isi as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->hapusFolder($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

// Mode penghapusan AJAX
if (isset($_GET['hapus'])) {
    $scanner = new PemindaiMalware();
    $scanner->hapusFile($_GET['hapus']);
    exit;
}

// Mulai pemindaian
$scanner = new PemindaiMalware();
$scanner->mulaiScan('.');
echo "</div></body></html>";
?>

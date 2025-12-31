
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

class PemindaiMalware {
    private $folderBerbahaya = [
        'ALFA_DATA', 'alfa_data', 'alfacgiapi', 'bypass', 'shell', 'webshell',
        'backdoor', 'adminer', 'wp-content/uploads', 'cache', 'tmp', 'temp'
    ];
    private $namaFileBerbahaya = [
        'alfa', 'ALFA', 'c99', 'r57', 'wso', 'shell', 'backdoor', 'webshell',
        'bypass', 'hack', 'adminer', 'phpinfo', 'test', 'backup', 'config'
    ];
    private $polaMencurigakan = [
        '/eval\s*\(/i', '/base64_decode\s*\(/i', '/shell_exec\s*\(/i', '/system\s*\(/i', '/exec\s*\(/i',
        '/passthru\s*\(/i', '/proc_open\s*\(/i', '/popen\s*\(/i', '/curl_exec\s*\(/i', '/parse_ini_file\s*\(/i',
        '/show_source\s*\(/i', '/symlink\s*\(/i', '/mkdir\s*\(/i', '/\$_GET/i', '/\$_POST/i', '/\$_REQUEST/i',
        '/file_get_contents\s*\(/i', '/file_put_contents\s*\(/i', '/fopen\s*\(/i', '/fwrite\s*\(/i',
        '/fputs\s*\(/i', '/fgets\s*\(/i', '/scandir\s*\(/i', '/readdir\s*\(/i', '/glob\s*\(/i', '/unlink\s*\(/i',
        '/rmdir\s*\(/i', '/chmod\s*\(/i', '/chown\s*\(/i', '/move_uploaded_file\s*\(/i',
        // Pola tambahan dengan regex yang lebih akurat
        '/gzinflate\s*\(/i', '/str_rot13\s*\(/i', '/create_function\s*\(/i', '/assert\s*\(/i',
        '/ob_start\s*\(/i', '/ob_get_contents\s*\(/i', '/preg_replace\s*.*\/e/i', '/call_user_func\s*\(/i',
        '/include\s*.*http/i', '/require\s*.*http/i', '/eval\s*.*base64/i', '/password_hash\s*.*backdoor/i',
        '/crypt\s*\(/i', '/md5_file\s*\(/i', '/sha1_file\s*\(/i', '/get_current_user\s*\(/i',
        '/php_uname\s*\(/i', '/ini_set\s*.*display_errors/i', '/error_reporting\s*.*0/i'
    ];
    private $polaHtaccess = [
        '/RewriteRule\s*.*\$\s*.*php/i',
        '/php_value\s*.*auto_prepend_file/i',
        '/AddHandler\s*.*php\s*.*\./i',
        '/SetHandler\s*.*application\/x-httpd-php/i',
        '/RedirectMatch\s*.*php/i',
        '/ErrorDocument\s*.*php/i'
    ];
    private $hasil = [];
    private $jumlahFile = 0;
    private $jumlahFolder = 0;
    private $waktuMulai;
    private $filterExtensions = [];
    private $filterTahun = null;

    public function __construct($filterExtensions = [], $filterTahun = null) {
        $this->filterExtensions = array_map('trim', explode(',', $filterExtensions));
        $this->filterTahun = $filterTahun ? intval($filterTahun) : null;
        $this->waktuMulai = microtime(true);
        echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemindai Malware Server</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 20px;
            background: #1a1a1a;
            color: #e0e0e0;
            line-height: 1.6;
        }
        h1, h2 {
            color: #4CAF50;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .blok {
            background: #222;
            border-radius: 8px;
            margin: 15px 0;
            padding: 15px;
            border-left: 5px solid;
            transition: all 0.3s;
        }
        .tinggi { border-left-color: #ff4444; color: #ff6666; }
        .sedang { border-left-color: #ffbb33; color: #ffcc66; }
        .rendah { border-left-color: #33b5e5; color: #66d9ff; }
        .bersih { border-left-color: #4CAF50; color: #80ff80; }
        .folder { background: #2a2a2a; }
        .file { background: #252525; }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            color: #e0e0e0;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.3s, transform 0.2s;
            cursor: pointer;
        }
        .button-danger { background: #ff4444; color: white; }
        .button-danger:hover { background: #cc0000; transform: scale(1.05); }
        .button-primary { background: #4CAF50; color: white; }
        .button-primary:hover { background: #3d8b40; transform: scale(1.05); }
        .stats {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .highlight { color: #ff4444; font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            table-layout: fixed;
        }
        th, td {
            padding: 12px;
            border: 1px solid #333;
            text-align: left;
            word-wrap: break-word;
        }
        th { background: #333; cursor: pointer; }
        th:hover { background: #444; }
        .sort-asc::after { content: ' ▲'; }
        .sort-desc::after { content: ' ▼'; }
        #loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            padding: 20px;
            border-radius: 8px;
            color: white;
        }
        form {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            background: #333;
            color: #e0e0e0;
            border: 1px solid #444;
            border-radius: 4px;
        }
    </style>
    <script>
        function hapusFile(path, rowId) {
            if (!confirm('Yakin ingin menghapus?')) return;
            document.getElementById('loading').style.display = 'block';
            fetch('?hapus=' + encodeURIComponent(path))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    if (data.includes('berhasil dihapus')) {
                        document.getElementById(rowId).remove();
                        if (document.querySelector('table tbody tr') === null) {
                            document.querySelector('table').innerHTML = '<div class="blok bersih"><h3>✅ Tidak ditemukan file mencurigakan.</h3></div>';
                        }
                    } else {
                        alert('Gagal menghapus: ' + data);
                    }
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    alert('Error: ' + error);
                });
        }

        function sortTable(colIndex, type) {
            const table = document.querySelector('table');
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.rows);
            const headers = table.tHead.rows[0].cells;
            let dir = headers[colIndex].classList.contains('sort-asc') ? -1 : 1;

            // Reset all headers
            Array.from(headers).forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
            });

            headers[colIndex].classList.add(dir === 1 ? 'sort-asc' : 'sort-desc');

            rows.sort((a, b) => {
                let valA = a.cells[colIndex].textContent.trim();
                let valB = b.cells[colIndex].textContent.trim();

                if (type === 'date') {
                    valA = new Date(valA);
                    valB = new Date(valB);
                } else if (type === 'number') {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else if (type === 'level') {
                    const levels = { 'Tinggi': 1, 'Sedang': 2, 'Rendah': 3 };
                    valA = levels[valA.split(' ')[1]] || 4;
                    valB = levels[valB.split(' ')[1]] || 4;
                }

                return dir * (valA > valB ? 1 : valA < valB ? -1 : 0);
            });

            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</head>
<body>
    <div id="loading">Menghapus...</div>
    <h1>🛡️ Pemindai Malware & Backdoor</h1>
    <form method="GET" action="">
        <label for="dir">Document Root (default: current directory):</label>
        <input type="text" id="dir" name="dir" value=".">
        
        <label for="extensions">Filter Extensions (comma-separated, e.g., php,js):</label>
        <input type="text" id="extensions" name="extensions" value="">
        
        <label for="tahun">Filter Tahun (tahun modifikasi, e.g., 2023):</label>
        <input type="number" id="tahun" name="tahun" value="" min="1900" max="2100">
        
        <button type="submit" class="button button-primary">🔍 Mulai Scan</button>
    </form>
HTML;
    }

    public function mulaiScan($direktori = '.') {
        if (!is_dir($direktori)) {
            echo "<div class='blok tinggi'>❌ Direktori tidak valid: " . htmlspecialchars($direktori) . "</div>";
            return;
        }
        echo "<p>📁 Memulai pemindaian di direktori: <b>" . htmlspecialchars(realpath($direktori)) . "</b></p>";
        if (!empty($this->filterExtensions[0])) {
            echo "<p>🔍 Filter extensions: " . implode(', ', $this->filterExtensions) . "</p>";
        }
        if ($this->filterTahun) {
            echo "<p>📅 Filter tahun: " . $this->filterTahun . "</p>";
        }
        $this->pindaiDirektori($direktori);
        $this->tampilkanHasil();
        $this->tampilkanStatistik();
    }

    private function pindaiDirektori($dir) {
        if (!is_dir($dir)) return;
        $tahunModifikasi = date('Y', filemtime($dir));
        if ($this->filterTahun && $tahunModifikasi != $this->filterTahun) {
            return; // Skip jika tidak sesuai tahun
        }
        $this->jumlahFolder++;
        $namaFolder = basename($dir);
        foreach ($this->folderBerbahaya as $kata) {
            if (stripos($namaFolder, $kata) !== false) {
                $this->hasil[] = [
                    'jenis' => 'folder',
                    'lokasi' => $dir,
                    'masalah' => "Folder mencurigakan: $kata",
                    'level' => 'tinggi',
                    'waktu' => date('Y-m-d H:i:s', filemtime($dir))
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
        $tahunModifikasi = date('Y', filemtime($path));
        if ($this->filterTahun && $tahunModifikasi != $this->filterTahun) {
            return; // Skip jika tidak sesuai tahun
        }
        $ekstensi = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!empty($this->filterExtensions[0]) && !in_array($ekstensi, $this->filterExtensions)) {
            return; // Skip jika ekstensi tidak sesuai filter
        }
        $this->jumlahFile++;
        $namaFile = basename($path);
        $ukuran = filesize($path);
        $waktuModifikasi = date('Y-m-d H:i:s', filemtime($path));
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
        $ekstensiBerbahaya = ['php', 'php3', 'phtml', 'php5', 'phps', 'js', 'py'];
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
                $temuan[] = $pola;
            }
        }
        $jumlahTemuan = count($temuan);
        if ($jumlahTemuan > 3) {
            $this->hasil[] = [
                'jenis' => 'file',
                'lokasi' => $path,
                'masalah' => "Isi file mencurigakan ($jumlahTemuan pola): " . implode(', ', array_slice($temuan, 0, 5)),
                'level' => 'tinggi',
                'cuplikan' => substr($isi, 0, 300),
                'ukuran' => $this->formatUkuran($ukuran),
                'waktu' => $waktuModifikasi
            ];
        } elseif ($jumlahTemuan > 0) {
            $this->hasil[] = [
                'jenis' => 'file',
                'lokasi' => $path,
                'masalah' => "Isi file mencurigakan ($jumlahTemuan pola): " . implode(', ', $temuan),
                'level' => 'sedang',
                'ukuran' => $this->formatUkuran($ukuran),
                'waktu' => $waktuModifikasi
            ];
        }
    }

    private function pindaiHtaccess($path, $ukuran, $waktuModifikasi) {
        $isi = @file_get_contents($path);
        if ($isi === false) return;
        foreach ($this->polaHtaccess as $pola) {
            if (preg_match($pola, $isi)) {
                $this->hasil[] = [
                    'jenis' => 'file',
                    'lokasi' => $path,
                    'masalah' => "Pola mencurigakan di .htaccess: $pola",
                    'level' => 'tinggi',
                    'ukuran' => $this->formatUkuran($ukuran),
                    'waktu' => $waktuModifikasi
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
            echo "<div class='blok bersih'><h3>✅ Tidak ditemukan file mencurigakan.</h3></div>";
            return;
        }
        echo "<h2>📌 Hasil Pemindaian</h2>";
        echo "<table><thead><tr>
            <th onclick=\"sortTable(0, 'level')\">Level</th>
            <th onclick=\"sortTable(1, 'text')\">Jenis</th>
            <th onclick=\"sortTable(2, 'text')\">Lokasi</th>
            <th onclick=\"sortTable(3, 'text')\">Masalah</th>
            <th onclick=\"sortTable(4, 'number')\">Ukuran</th>
            <th onclick=\"sortTable(5, 'date')\" class=\"sort-desc\">Waktu Modifikasi</th>
            <th>Aksi</th>
        </tr></thead><tbody>";

        // Sort default: waktu desc, lalu level asc
        usort($this->hasil, function($a, $b) {
            $timeA = strtotime($a['waktu']);
            $timeB = strtotime($b['waktu']);
            if ($timeA !== $timeB) {
                return $timeB <=> $timeA; // Terbaru di atas
            }
            $prioritas = ['tinggi' => 1, 'sedang' => 2, 'rendah' => 3];
            return $prioritas[$a['level']] <=> $prioritas[$b['level']];
        });

        foreach ($this->hasil as $index => $item) {
            $kelas = ($item['level'] == 'tinggi') ? 'tinggi' : ($item['level'] == 'sedang' ? 'sedang' : 'rendah');
            $ikon = ($item['level'] == 'tinggi') ? '🚨' : ($item['level'] == 'sedang' ? '⚠️' : 'ℹ️');
            $rowId = 'row-' . $index;
            echo "<tr id=\"$rowId\" class='$kelas'>";
            echo "<td>$ikon " . ucfirst($item['level']) . "</td>";
            echo "<td>" . htmlspecialchars($item['jenis']) . "</td>";
            echo "<td>" . htmlspecialchars($item['lokasi']) . "</td>";
            echo "<td>" . htmlspecialchars($item['masalah']) . "</td>";
            echo "<td>" . ($item['ukuran'] ?? '-') . "</td>";
            echo "<td>" . ($item['waktu'] ?? '-') . "</td>";
            echo "<td>";
            if ($item['level'] === 'tinggi' && file_exists($item['lokasi'])) {
                echo "<button class='button button-danger' onclick='hapusFile(\"" . addslashes($item['lokasi']) . "\", \"$rowId\")'>🗑️ Hapus</button>";
            }
            echo "</td></tr>";
            if (isset($item['cuplikan'])) {
                echo "<tr><td colspan='7'><pre>" . htmlspecialchars($item['cuplikan']) . "...</pre></td></tr>";
            }
        }
        echo "</tbody></table>";
    }

    private function tampilkanStatistik() {
        $durasi = number_format(microtime(true) - $this->waktuMulai, 2);
        echo "<div class='stats'>";
        echo "<h2>📊 Statistik Pemindaian</h2>";
        echo "<p><b>📄 File yang dipindai:</b> {$this->jumlahFile}</p>";
        echo "<p><b>📁 Folder yang dipindai:</b> {$this->jumlahFolder}</p>";
        echo "<p><b>⏱️ Durasi:</b> {$durasi} detik</p>";
        echo "<p><a href='?' class='button button-primary'>🔄 Pindai ulang</a></p>";
        echo "</div>";
    }

    public function hapusFile($path) {
        if (!file_exists($path)) {
            echo "⚠️ File tidak ditemukan: " . htmlspecialchars($path);
            return;
        }
        if (is_dir($path)) {
            $this->hapusFolder($path);
            echo "✅ Folder <b>" . htmlspecialchars($path) . "</b> berhasil dihapus.";
        } else {
            unlink($path);
            echo "✅ File <b>" . htmlspecialchars($path) . "</b> berhasil dihapus.";
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

// Mode penghapusan (untuk AJAX)
if (isset($_GET['hapus'])) {
    $scanner = new PemindaiMalware();
    $scanner->hapusFile($_GET['hapus']);
    exit;
}

// Mulai pemindaian jika ada parameter
$dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$extensions = isset($_GET['extensions']) ? $_GET['extensions'] : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

$scanner = new PemindaiMalware($extensions, $tahun);
$scanner->mulaiScan($dir);
echo "</body></html>";
?>

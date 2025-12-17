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
        'eval(', 'base64_decode(', 'shell_exec(', 'system(', 'exec(', 
        'passthru(', 'proc_open(', 'popen(', 'curl_exec(', 'parse_ini_file(',
        'show_source(', 'symlink(', 'mkdir(', '$_GET', '$_POST', '$_REQUEST',
        'file_get_contents(', 'file_put_contents(', 'fopen(', 'fwrite(', 
        'fputs(', 'fgets(', 'scandir(', 'readdir(', 'glob(', 'unlink(', 
        'rmdir(', 'chmod(', 'chown(', 'move_uploaded_file(',
        // Pola tambahan
        'gzinflate(', 'str_rot13(', 'create_function(', 'assert(', 
        'ob_start(', 'ob_get_contents(', 'preg_replace.*\/e', 'call_user_func(',
        'include.*http', 'require.*http', 'eval.*base64', 'password_hash.*backdoor',
        'crypt(', 'md5_file(', 'sha1_file(', 'get_current_user(',
        'php_uname(', 'ini_set.*display_errors', 'error_reporting.*0'
    ];

    private $hasil = [];
    private $jumlahFile = 0;
    private $jumlahFolder = 0;
    private $waktuMulai;

    public function __construct() {
        $this->waktuMulai = microtime(true);
        echo <<<HTML
<html>
<head>
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
            transition: background 0.3s;
        }
        .button-danger { background: #ff4444; color: white; }
        .button-danger:hover { background: #cc0000; }
        .button-primary { background: #4CAF50; color: white; }
        .button-primary:hover { background: #3d8b40; }
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
        }
        th, td {
            padding: 10px;
            border: 1px solid #333;
            text-align: left;
        }
        th { background: #333; }
    </style>
</head>
<body>
    <h1>🛡️ Pemindai Malware & Backdoor</h1>
HTML;
    }

    public function mulaiScan($direktori = '.') {
        echo "<p>📁 Memulai pemindaian di direktori: <b>" . htmlspecialchars(realpath($direktori)) . "</b></p>";
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
        $this->jumlahFile++;
        $namaFile = basename($path);
        $ekstensi = strtolower(pathinfo($path, PATHINFO_EXTENSION));
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
            if (stripos($isi, $pola) !== false) {
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

        $polaHtaccess = [
            'RewriteRule.*\$.*php', 
            'php_value.*auto_prepend_file', 
            'AddHandler.*php.*\.',
            'SetHandler.*application/x-httpd-php',
            'RedirectMatch.*php',
            'ErrorDocument.*php'
        ];
        foreach ($polaHtaccess as $pola) {
            if (preg_match("/$pola/i", $isi)) {
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
        echo "<table><tr><th>Level</th><th>Jenis</th><th>Lokasi</th><th>Masalah</th><th>Ukuran</th><th>Waktu Modifikasi</th><th>Aksi</th></tr>";

        $prioritas = ['tinggi' => 1, 'sedang' => 2, 'rendah' => 3];
        usort($this->hasil, function($a, $b) use ($prioritas) {
            return $prioritas[$a['level']] <=> $prioritas[$b['level']];
        });

        foreach ($this->hasil as $item) {
            $kelas = ($item['level'] == 'tinggi') ? 'tinggi' : ($item['level'] == 'sedang' ? 'sedang' : 'rendah');
            $ikon = ($item['level'] == 'tinggi') ? '🚨' : ($item['level'] == 'sedang' ? '⚠️' : 'ℹ️');

            echo "<tr class='$kelas'>";
            echo "<td>$ikon " . ucfirst($item['level']) . "</td>";
            echo "<td>" . htmlspecialchars($item['jenis']) . "</td>";
            echo "<td>" . htmlspecialchars($item['lokasi']) . "</td>";
            echo "<td>" . htmlspecialchars($item['masalah']) . "</td>";
            echo "<td>" . ($item['ukuran'] ?? '-') . "</td>";
            echo "<td>" . ($item['waktu'] ?? '-') . "</td>";
            echo "<td>";
            if ($item['level'] === 'tinggi' && is_file($item['lokasi'])) {
                echo "<a href='?hapus=" . urlencode($item['lokasi']) . "' class='button button-danger' onclick='return confirm(\"Yakin ingin menghapus?\")'>🗑️ Hapus</a>";
            }
            echo "</td></tr>";

            if (isset($item['cuplikan'])) {
                echo "<tr><td colspan='7'><pre>" . htmlspecialchars($item['cuplikan']) . "...</pre></td></tr>";
            }
        }
        echo "</table>";
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
            echo "<div class='blok rendah'>⚠️ File tidak ditemukan: " . htmlspecialchars($path) . "</div>";
            return;
        }

        if (is_dir($path)) {
            $this->hapusFolder($path);
            echo "<div class='blok bersih'>✅ Folder <b>" . htmlspecialchars($path) . "</b> berhasil dihapus.</div>";
        } else {
            unlink($path);
            echo "<div class='blok bersih'>✅ File <b>" . htmlspecialchars($path) . "</b> berhasil dihapus.</div>";
        }

        echo "<p><a href='?' class='button button-primary'>🔙 Kembali</a></p>";
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

// Mode penghapusan
if (isset($_GET['hapus'])) {
    $scanner = new PemindaiMalware();
    $scanner->hapusFile($_GET['hapus']);
    echo "</body></html>";
    exit;
}

// Mulai pemindaian
$scanner = new PemindaiMalware();
$scanner->mulaiScan('.');
echo "</body></html>";

?>

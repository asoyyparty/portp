<?php
// admin/api.php - Content Management API & Sync Engine
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';

// Authentication guard
if (!is_authenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Sesi login telah berakhir.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Load Content Data
function load_content() {
    if (!file_exists(CONTENT_FILE)) {
        return [];
    }
    $raw = file_get_contents(CONTENT_FILE);
    return json_decode($raw, true) ?: [];
}

// Save Content Data
function save_content($data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (file_put_contents(CONTENT_FILE, $json) !== false) {
        // Automatically sync static index.html
        sync_index_html($data);
        return true;
    }
    return false;
}

// Function to synchronize and re-render index.html
function sync_index_html($content) {
    $indexPhp = dirname(__DIR__) . '/index.php';
    $indexHtml = dirname(__DIR__) . '/index.html';

    if (file_exists($indexPhp)) {
        // Execute index.php in a subshell or capture output
        ob_start();
        include $indexPhp;
        $htmlOutput = ob_get_clean();

        if (!empty($htmlOutput) && strlen($htmlOutput) > 1000) {
            file_put_contents($indexHtml, $htmlOutput);
            return true;
        }
    }
    return false;
}

// Handle Actions
try {
    switch ($action) {
        case 'get_content':
            echo json_encode(['success' => true, 'data' => load_content()]);
            break;

        case 'save_profile':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $content = load_content();

            if (isset($input['profile'])) {
                $content['profile'] = array_merge($content['profile'] ?? [], $input['profile']);
            }
            if (isset($input['metrics'])) {
                $content['metrics'] = $input['metrics'];
            }

            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Profil & Hero berhasil diperbarui!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data profil.']);
            }
            break;

        case 'save_project':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $project = $input['project'] ?? null;

            if (!$project || empty($project['title'])) {
                echo json_encode(['success' => false, 'message' => 'Judul proyek tidak boleh kosong.']);
                exit;
            }

            $content = load_content();
            $projects = $content['projects'] ?? [];

            // If ID is provided, update existing; otherwise, create new
            $isNew = empty($project['id']);
            if ($isNew) {
                // Generate a slug-like ID
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $project['title']), '-'));
                $project['id'] = $slug ?: 'proj-' . time();
                // Prepend new project at the top
                array_unshift($projects, $project);
            } else {
                $found = false;
                foreach ($projects as &$p) {
                    if ($p['id'] === $project['id']) {
                        $p = array_merge($p, $project);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $projects[] = $project;
                }
            }

            $content['projects'] = $projects;
            if (save_content($content)) {
                echo json_encode([
                    'success' => true,
                    'message' => $isNew ? 'Proyek baru berhasil ditambahkan!' : 'Proyek berhasil diperbarui!',
                    'project' => $project
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan proyek.']);
            }
            break;

        case 'delete_project':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $id = $input['id'] ?? '';

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID proyek tidak ditemukan.']);
                exit;
            }

            $content = load_content();
            $filtered = [];
            foreach ($content['projects'] as $p) {
                if ($p['id'] !== $id) {
                    $filtered[] = $p;
                }
            }

            $content['projects'] = $filtered;
            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Proyek berhasil dihapus!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus proyek.']);
            }
            break;

        case 'reorder_projects':
            $input = json_decode(file_get_contents('php://input'), true);
            $ids = $input['order'] ?? [];

            if (!empty($ids) && is_array($ids)) {
                $content = load_content();
                $projectsById = [];
                foreach ($content['projects'] as $p) {
                    $projectsById[$p['id']] = $p;
                }

                $newProjects = [];
                foreach ($ids as $id) {
                    if (isset($projectsById[$id])) {
                        $newProjects[] = $projectsById[$id];
                        unset($projectsById[$id]);
                    }
                }
                // Append any leftovers
                foreach ($projectsById as $rem) {
                    $newProjects[] = $rem;
                }

                $content['projects'] = $newProjects;
                if (save_content($content)) {
                    echo json_encode(['success' => true, 'message' => 'Urutan proyek berhasil disimpan!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan urutan proyek.']);
                }
            }
            break;

        case 'save_experience':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $content = load_content();

            if (isset($input['experience'])) {
                $content['experience'] = $input['experience'];
            }
            if (isset($input['education'])) {
                $content['education'] = $input['education'];
            }

            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Pengalaman & Pendidikan berhasil diperbarui!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengalaman.']);
            }
            break;

        case 'save_services':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $content = load_content();

            if (isset($input['services'])) {
                $content['services'] = $input['services'];
            }

            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Layanan & Keahlian berhasil disimpan!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan layanan.']);
            }
            break;

        case 'save_certifications':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $content = load_content();

            if (isset($input['certifications'])) {
                $content['certifications'] = $input['certifications'];
            }

            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Sertifikasi berhasil disimpan!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan sertifikasi.']);
            }
            break;

        case 'save_contact':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $content = load_content();

            if (isset($input['contact'])) {
                $content['contact'] = $input['contact'];
            }

            if (save_content($content)) {
                echo json_encode(['success' => true, 'message' => 'Kontak & Sosial Media berhasil disimpan!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan kontak.']);
            }
            break;

        case 'upload_image':
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan upload.']);
                exit;
            }

            $file = $_FILES['image'];
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts)) {
                echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Harap upload PNG, JPG, WEBP, atau SVG.']);
                exit;
            }

            if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                echo json_encode(['success' => false, 'message' => 'Ukuran file melebihi batas 10MB.']);
                exit;
            }

            $uploadDir = dirname(__DIR__) . '/assets/images';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Create clean filename
            $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
            $filename = strtolower($prefix) . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $relativePath = 'assets/images/' . $filename;
                echo json_encode([
                    'success' => true,
                    'message' => 'Gambar berhasil diunggah!',
                    'file_path' => $relativePath
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file ke folder tujuan.']);
            }
            break;

        case 'change_password':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $oldPassword = $input['old_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';

            if (empty($oldPassword) || empty($newPassword)) {
                echo json_encode(['success' => false, 'message' => 'Password lama dan password baru wajib diisi.']);
                exit;
            }

            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password baru minimal harus 6 karakter.']);
                exit;
            }

            $usersData = get_users();
            $userId = $_SESSION['user_id'];
            $currentUser = null;

            foreach ($usersData['users'] as $u) {
                if ($u['id'] == $userId) {
                    $currentUser = $u;
                    break;
                }
            }

            if (!$currentUser || !password_verify($oldPassword, $currentUser['password'])) {
                echo json_encode(['success' => false, 'message' => 'Password lama salah.']);
                exit;
            }

            if (update_password($userId, $newPassword)) {
                echo json_encode(['success' => true, 'message' => 'Password berhasil diubah!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah password.']);
            }
            break;

        case 'export_backup':
            $content = load_content();
            $filename = 'portp_backup_' . date('Y-m-d_His') . '.json';
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Type: application/json');
            echo json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;

        case 'sync_html':
            $content = load_content();
            if (sync_index_html($content)) {
                echo json_encode(['success' => true, 'message' => 'File index.html berhasil di-generate dan disinkronkan dengan konten terbaru!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyinkronkan index.html. Pastikan index.php tersedia.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal: ' . htmlspecialchars($action)]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}

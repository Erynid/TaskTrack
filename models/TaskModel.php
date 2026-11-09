<?php
/**
 * TaskTrack - Task Model / Data Access Layer (DAO)
 * Modul 11 - Full-Stack CRUD, Validation, Parameterized Queries & Transactions
 */

require_once __DIR__ . '/../config/database.php';

class TaskModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil daftar tugas dengan fitur Pencarian, Filter Kategori/Status/Prioritas, & Pagination
     *
     * @param int $userId ID Pengguna
     * @param array $params Parameter pencarian & filter: ['search', 'category_id', 'status', 'priority', 'sort_by', 'order', 'page', 'limit']
     * @return array ['data' => [...], 'pagination' => [...]]
     */
    public function getTasksWithFilter(int $userId, array $params = []): array
    {
        $search = trim((string)($params['search'] ?? ''));
        $categoryId = isset($params['category_id']) && $params['category_id'] !== '' && $params['category_id'] !== 'all' ? (int)$params['category_id'] : null;
        $status = trim((string)($params['status'] ?? ''));
        if ($status === 'inprogress') $status = 'in_progress';
        if ($status === 'all') $status = '';

        $priority = strtolower(trim((string)($params['priority'] ?? '')));
        if ($priority === 'all') $priority = '';

        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, min(50, (int)($params['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $allowedSortCols = [
            'due_date' => 't.due_date',
            'created_at' => 't.created_at',
            'title' => 't.title',
            'priority' => 't.priority'
        ];
        $sortBy = $allowedSortCols[$params['sort_by'] ?? 'due_date'] ?? 't.due_date';
        $order = strtoupper($params['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        // Bangun Klausul WHERE secara dinamis & aman menggunakan parameter bindings
        $whereClauses = ['t.user_id = :user_id'];
        $bindings = ['user_id' => $userId];

        if ($search !== '') {
            $whereClauses[] = '(t.title LIKE :search OR t.description LIKE :search_desc OR c.name LIKE :search_cat)';
            $bindings['search'] = '%' . $search . '%';
            $bindings['search_desc'] = '%' . $search . '%';
            $bindings['search_cat'] = '%' . $search . '%';
        }

        if ($categoryId !== null) {
            $whereClauses[] = 't.category_id = :category_id';
            $bindings['category_id'] = $categoryId;
        }

        if ($status !== '' && in_array($status, ['todo', 'in_progress', 'done'])) {
            $whereClauses[] = 't.status = :status';
            $bindings['status'] = $status;
        }

        if ($priority !== '' && in_array($priority, ['low', 'medium', 'high'])) {
            $whereClauses[] = 't.priority = :priority';
            $bindings['priority'] = $priority;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // 1. Hitung total baris untuk pagination
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE $whereSql
        ");
        $countStmt->execute($bindings);
        $totalRows = (int)$countStmt->fetchColumn();

        // 2. Ambil data tugas
        $queryStmt = $this->db->prepare("
            SELECT 
                t.id, 
                t.user_id,
                t.category_id,
                t.title, 
                t.description, 
                t.status, 
                t.priority, 
                t.due_date, 
                t.created_at,
                c.name AS category_name, 
                c.color_code 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE $whereSql 
            ORDER BY $sortBy $order 
            LIMIT :limit OFFSET :offset
        ");

        foreach ($bindings as $key => $val) {
            $queryStmt->bindValue(':' . $key, $val);
        }
        $queryStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $queryStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $queryStmt->execute();
        $tasks = $queryStmt->fetchAll();

        return [
            'data' => $tasks,
            'pagination' => [
                'total_records' => $totalRows,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalRows > 0 ? (int)ceil($totalRows / $limit) : 1
            ]
        ];
    }

    /**
     * Ambil semua tugas milik user tertentu (kompatibilitas backward)
     */
    public function getTasksByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.id, 
                t.user_id,
                t.category_id,
                t.title, 
                t.description, 
                t.status, 
                t.priority, 
                t.due_date, 
                t.created_at,
                c.name AS category_name, 
                c.color_code 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = :user_id 
            ORDER BY t.due_date ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu tugas berdasarkan ID dengan verifikasi kepemilikan user
     *
     * @param int $taskId
     * @param int $userId
     * @return array|null
     */
    public function getTaskById(int $taskId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.id, 
                t.user_id,
                t.category_id,
                t.title, 
                t.description, 
                t.status, 
                t.priority, 
                t.due_date, 
                t.created_at,
                c.name AS category_name, 
                c.color_code 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.id = :id AND t.user_id = :user_id
        ");
        $stmt->execute([
            'id' => $taskId,
            'user_id' => $userId
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Tambah tugas baru dengan validasi data dan Transaksi Atomik
     *
     * @param int $userId
     * @param array $data ['title', 'description', 'category_id', 'category_name', 'status', 'priority', 'due_date']
     * @return array Task yang baru dibuat
     */
    public function createTask(int $userId, array $data): array
    {
        $title = trim((string)($data['title'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $status = trim((string)($data['status'] ?? 'todo'));
        if ($status === 'inprogress') $status = 'in_progress';
        if (!in_array($status, ['todo', 'in_progress', 'done'])) $status = 'todo';

        $priority = strtolower(trim((string)($data['priority'] ?? 'medium')));
        if (!in_array($priority, ['low', 'medium', 'high'])) $priority = 'medium';

        $dueDate = trim((string)($data['due_date'] ?? ''));
        if ($dueDate === '') $dueDate = date('Y-m-d', strtotime('+3 days'));
        // Normalisasi jika berformat datetime ISO
        if (strpos($dueDate, 'T') !== false) {
            $dueDate = explode('T', $dueDate)[0];
        }

        $categoryId = isset($data['category_id']) && $data['category_id'] !== '' ? (int)$data['category_id'] : null;

        // Gunakan Transaksi Atomik (Requirement Modul 11)
        $this->db->beginTransaction();
        try {
            // Jika nama kategori diberikan tapi ID belum ada, cari atau buat kategori baru
            if (!$categoryId && !empty($data['category_name'])) {
                $catName = trim((string)$data['category_name']);
                $findCat = $this->db->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
                $findCat->execute(['name' => $catName]);
                $existingCat = $findCat->fetchColumn();

                if ($existingCat) {
                    $categoryId = (int)$existingCat;
                } else {
                    $createCat = $this->db->prepare("INSERT INTO categories (name, color_code) VALUES (:name, :color)");
                    $createCat->execute(['name' => $catName, 'color' => '#3b82f6']);
                    $categoryId = (int)$this->db->lastInsertId();
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO tasks (user_id, category_id, title, description, status, priority, due_date)
                VALUES (:user_id, :category_id, :title, :description, :status, :priority, :due_date)
            ");
            $stmt->execute([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'due_date' => $dueDate
            ]);

            $newId = (int)$this->db->lastInsertId();
            $this->db->commit();

            return $this->getTaskById($newId, $userId) ?: [
                'id' => $newId,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'due_date' => $dueDate
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Perbarui data tugas (Update) dengan verifikasi eksistensi dan Transaksi
     *
     * @param int $taskId
     * @param int $userId
     * @param array $data
     * @return array|null
     */
    public function updateTask(int $taskId, int $userId, array $data): ?array
    {
        $existing = $this->getTaskById($taskId, $userId);
        if (!$existing) {
            return null; // Resource tidak ditemukan (404)
        }

        $title = isset($data['title']) ? trim((string)$data['title']) : $existing['title'];
        $description = isset($data['description']) ? trim((string)$data['description']) : $existing['description'];
        
        $status = $existing['status'];
        if (isset($data['status'])) {
            $st = trim((string)$data['status']);
            if ($st === 'inprogress') $st = 'in_progress';
            if (in_array($st, ['todo', 'in_progress', 'done'])) $status = $st;
        }

        $priority = $existing['priority'];
        if (isset($data['priority'])) {
            $p = strtolower(trim((string)$data['priority']));
            if (in_array($p, ['low', 'medium', 'high'])) $priority = $p;
        }

        $dueDate = $existing['due_date'];
        if (!empty($data['due_date'])) {
            $d = trim((string)$data['due_date']);
            if (strpos($d, 'T') !== false) {
                $d = explode('T', $d)[0];
            }
            $dueDate = $d;
        }

        $categoryId = isset($data['category_id']) && $data['category_id'] !== '' ? (int)$data['category_id'] : $existing['category_id'];

        $this->db->beginTransaction();
        try {
            // Jika ada category_name baru
            if (!empty($data['category_name']) && !$categoryId) {
                $catName = trim((string)$data['category_name']);
                $findCat = $this->db->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
                $findCat->execute(['name' => $catName]);
                $existingCat = $findCat->fetchColumn();

                if ($existingCat) {
                    $categoryId = (int)$existingCat;
                } else {
                    $createCat = $this->db->prepare("INSERT INTO categories (name, color_code) VALUES (:name, :color)");
                    $createCat->execute(['name' => $catName, 'color' => '#3b82f6']);
                    $categoryId = (int)$this->db->lastInsertId();
                }
            }

            $stmt = $this->db->prepare("
                UPDATE tasks 
                SET category_id = :category_id,
                    title = :title,
                    description = :description,
                    status = :status,
                    priority = :priority,
                    due_date = :due_date
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'due_date' => $dueDate,
                'id' => $taskId,
                'user_id' => $userId
            ]);

            $this->db->commit();
            return $this->getTaskById($taskId, $userId);
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Perbarui hanya status tugas (PATCH)
     */
    public function updateTaskStatus(int $taskId, int $userId, string $status): bool
    {
        if ($status === 'inprogress') $status = 'in_progress';
        if (!in_array($status, ['todo', 'in_progress', 'done'])) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE tasks 
            SET status = :status 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            'status' => $status,
            'id' => $taskId,
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Hapus tugas (Delete) dengan pemeriksaan keberadaan tugas (Existence check)
     *
     * @param int $taskId
     * @param int $userId
     * @return bool True jika berhasil dihapus, False jika tidak ditemukan
     */
    public function deleteTask(int $taskId, int $userId): bool
    {
        $existing = $this->getTaskById($taskId, $userId);
        if (!$existing) {
            return false; // Resource tidak ditemukan (404)
        }

        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'id' => $taskId,
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Ambil daftar kategori perkuliahan
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT id, name, color_code FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}

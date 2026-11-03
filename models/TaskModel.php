<?php
/**
 * TaskTrack - Task Model / Data Access Layer (DAO)
 * Tugas 10 - Prepared Statements & Parameterized Queries
 */

require_once __DIR__ . '/../config/database.php';

class TaskModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Ambil daftar tugas milik user tertentu menggunakan Prepared Statement
    public function getTasksByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.id, 
                t.title, 
                t.description, 
                t.status, 
                t.priority, 
                t.due_date, 
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

    // Tambah tugas baru (Prepared Statement)
    public function createTask($userId, $categoryId, $title, $description, $status, $priority, $dueDate)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, category_id, title, description, status, priority, due_date)
            VALUES (:user_id, :category_id, :title, :description, :status, :priority, :due_date)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate
        ]);
    }

    // Update status tugas (Prepared Statement)
    public function updateTaskStatus($taskId, $userId, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE tasks 
            SET status = :status 
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            'status' => $status,
            'id' => $taskId,
            'user_id' => $userId
        ]);
    }
}
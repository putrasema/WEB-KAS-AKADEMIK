<?php

class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getMonthlyIncome($month, $year, $studentId = null, $userId = null)
    {
        $filter = "";
        $params = [$month, $year];
        if ($studentId) {
            $filter = " AND student_id = ?";
            $params[] = $studentId;
        } elseif ($userId) {
            $filter = " AND created_by = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'income' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ? $filter");
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyExpense($month, $year, $studentId = null, $userId = null)
    {
        $filter = "";
        $params = [$month, $year];
        if ($studentId) {
            $filter = " AND student_id = ?";
            $params[] = $studentId;
        } elseif ($userId) {
            $filter = " AND created_by = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'expense' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ? $filter");
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTotalIncome($studentId = null, $userId = null)
    {
        $filter = "";
        $params = [];
        if ($studentId) {
            $filter = " AND student_id = ?";
            $params[] = $studentId;
        } elseif ($userId) {
            $filter = " AND created_by = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'income' $filter");
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTotalExpense($studentId = null, $userId = null)
    {
        $filter = "";
        $params = [];
        if ($studentId) {
            $filter = " AND student_id = ?";
            $params[] = $studentId;
        } elseif ($userId) {
            $filter = " AND created_by = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'expense' $filter");
        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getCategoryBreakdown($type = 'expense', $studentId = null, $userId = null)
    {
        $filter = "";
        $params = [$type];
        if ($studentId) {
            $filter = " AND t.student_id = ?";
            $params[] = $studentId;
        } elseif ($userId) {
            $filter = " AND t.created_by = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare("
            SELECT c.name, SUM(t.amount_base) as total 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.type = ? $filter
            GROUP BY c.name 
            ORDER BY total DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getWastefulCategories($studentId = null, $userId = null)
    {

        return $this->getCategoryBreakdown('expense', $studentId, $userId);
    }

    public function getSavingsAdvice($studentId = null, $userId = null)
    {
        $income = $this->getTotalIncome($studentId, $userId);
        $expense = $this->getTotalExpense($studentId, $userId);

        if ($income == 0)
            return "Belum ada pemasukan tercatat.";

        $ratio = $expense / $income;
        if ($ratio > 0.8) {
            return "Peringatan: Anda menghabiskan " . round($ratio * 100) . "% dari pemasukan Anda. Cobalah kurangi pengeluaran tidak penting.";
        } elseif ($ratio > 0.5) {
            return "Anda menghabiskan " . round($ratio * 100) . "% dari pemasukan Anda. Bagus, tapi bisa lebih baik.";
        } else {
            return "Kerja bagus! Anda menghemat " . (100 - round($ratio * 100)) . "% dari pemasukan Anda.";
        }
    }
}

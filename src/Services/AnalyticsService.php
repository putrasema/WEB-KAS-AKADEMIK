<?php

class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getMonthlyIncome($month, $year)
    {
        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'income' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
        $stmt->execute([$month, $year]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyExpense($month, $year)
    {
        $stmt = $this->db->prepare("SELECT SUM(amount_base) FROM transactions WHERE type = 'expense' AND MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?");
        $stmt->execute([$month, $year]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTotalIncome()
    {
        $stmt = $this->db->query("SELECT SUM(amount_base) FROM transactions WHERE type = 'income'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTotalExpense()
    {
        $stmt = $this->db->query("SELECT SUM(amount_base) FROM transactions WHERE type = 'expense'");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getCategoryBreakdown($type = 'expense')
    {
        $stmt = $this->db->prepare("
            SELECT c.name, SUM(t.amount_base) as total 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.type = ? 
            GROUP BY c.name 
            ORDER BY total DESC
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function getWastefulCategories()
    {
        // Simply returns the top expense categories
        return $this->getCategoryBreakdown('expense');
    }

    public function getSavingsAdvice()
    {
        $income = $this->getTotalIncome();
        $expense = $this->getTotalExpense();

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

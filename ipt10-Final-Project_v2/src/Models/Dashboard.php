<?php

namespace App\Models;

use \PDO;
use App\Models\BaseModel;


class Dashboard extends BaseModel
{
    private $db;

    public function __construct() {
        // Initialize the database connection (SQLite in this case)
        $this->db = new PDO('sqlite:inventory_system.db');
    }








    // Doughnut Chart


    public function getTotalSales() {
        $query = "SELECT SUM(sales.qty * sales.price) AS total_sales
                  FROM sales";
        $result = $this->db->query($query);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    // Get Total Number of Products
    public function getTotalProducts() {
        $query = "SELECT COUNT(id) AS total_products FROM products";
        $result = $this->db->query($query);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    // Get Monthly Sales Data (Month-wise)
    public function getMonthlySales() {
        $query = "SELECT MONTH(sales.date) AS month, SUM(sales.qty * sales.price) AS monthly_sales
                  FROM sales
                  GROUP BY MONTH(sales.date)
                  ORDER BY MONTH(sales.date)";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<?php

namespace App\Models;

use \PDO;
use App\Models\BaseModel;

class Dashboard extends BaseModel
{
    // Change the visibility of $db to 'protected' (or 'public')
    protected $db;

    public function __construct($conn = null)
    {
        $this->db = $conn ?: new PDO('mysql:host=localhost;dbname=inventory_system', 'root', '');
        
    }



    public function getSalesByCategory() {
        $query = "SELECT categories.name AS category_name, SUM(sales.qty * sales.price) AS category_sales
                  FROM sales
                  JOIN products ON sales.product_id = products.id
                  JOIN categories ON products.categorie_id = categories.id
                  GROUP BY categories.name";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }




    // Doughnut Chart


    // public function getTotalSales() {
    //     $query = "SELECT SUM(sales.qty * sales.price) AS total_sales
    //               FROM sales";
    //     $result = $this->db->query($query);
    //     return $result->fetch(PDO::FETCH_ASSOC);
    // }

    // // Get Total Number of Products
    // public function getTotalProducts() {
    //     $query = "SELECT COUNT(id) AS total_products FROM products";
    //     $result = $this->db->query($query);
    //     return $result->fetch(PDO::FETCH_ASSOC);
    // }

    // // Get Monthly Sales Data (Month-wise)
    // public function getMonthlySales() {
    //     $query = "SELECT MONTH(sales.date) AS month, SUM(sales.qty * sales.price) AS monthly_sales
    //               FROM sales
    //               GROUP BY MONTH(sales.date)
    //               ORDER BY MONTH(sales.date)";
    //     $result = $this->db->query($query);
    //     return $result->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function getDashboardSummary() {
        // Get Total Sales
        $queryTotalSales = "SELECT SUM(sales.qty * sales.price) AS total_sales FROM sales";
        $totalSales = $this->db->query($queryTotalSales)->fetch(PDO::FETCH_ASSOC);
    
        // Get Total Products
        $queryTotalProducts = "SELECT COUNT(id) AS total_products FROM products";
        $totalProducts = $this->db->query($queryTotalProducts)->fetch(PDO::FETCH_ASSOC);
    
        // Get Total Monthly Sales
        $queryMonthlySales = "SELECT SUM(sales.qty * sales.price) AS monthly_sales 
                              FROM sales 
                              WHERE MONTH(sales.date) = MONTH(CURDATE()) AND YEAR(sales.date) = YEAR(CURDATE())";
        $monthlySales = $this->db->query($queryMonthlySales)->fetch(PDO::FETCH_ASSOC);
    
        return [
            'total_sales' => $totalSales['total_sales'],
            'total_products' => $totalProducts['total_products'],
            'monthly_sales' => $monthlySales['monthly_sales']
        ];
    }


    public function getTop10ProductsBySales() {
        $query = "
            SELECT products.name AS product_name, 
                   SUM(sales.qty * sales.price) AS total_sales
            FROM sales
            JOIN products ON sales.product_id = products.id
            GROUP BY products.id
            ORDER BY total_sales DESC
            LIMIT 10
        ";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
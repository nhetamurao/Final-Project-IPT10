<?php
namespace App\Controllers;

use App\Models\Sales;
use App\Models\Product;
use App\Models\Dashboard;  // Include the Dashboard model

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->startSession(); // Ensures session is started
    }

    public function showDashboard()
{
    $salesModel = new Sales();
    $highestSellingProducts = $salesModel->getHighestSellingProducts();
    $latestSales = $salesModel->getLatestSales();
    $productModel = new Product();
    $recentProducts = $productModel->getRecentlyAddedProducts();
    
    // Get top 10 products by sales
    $top10Products = $salesModel->getTop10ProductsBySales();

    $dashboardModel = new Dashboard();
    $dashboardSummary = $dashboardModel->getDashboardSummary(); // Fetch dashboard summary

    $data = [
        'highest_selling_products' => $highestSellingProducts,
        'latest_sales' => $latestSales,
        'recent_products' => $recentProducts,
        'dashboard_summary' => json_encode($dashboardSummary), // Pass summary data
        'top_10_products' => json_encode($top10Products), // Pass top 10 products data
    ];

    unset($_SESSION['msg'], $_SESSION['msg_type']);
    echo $this->renderPage('dashboard', $data);
}
}



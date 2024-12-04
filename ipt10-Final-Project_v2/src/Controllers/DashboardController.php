<?php
namespace App\Controllers;

use App\Models\Sales;
use App\Models\Product;

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

        $data = [
            'highest_selling_products' => $highestSellingProducts,
            'latest_sales' => $latestSales,
            'recent_products' => $recentProducts
        ];
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        echo $this->renderPage('dashboard', $data);
    }


  

      //Dounut chart
      public function getChartData() {
        $totalSales = $this->model->getTotalSales();
        $totalProducts = $this->model->getTotalProducts();
        $monthlySales = $this->model->getMonthlySales();
    
        // Prepare chart data in the format needed by the view
        return [
            'total_sales' => $totalSales['total_sales'],
            'total_products' => $totalProducts['total_products'],
            'monthly_sales' => $monthlySales
        ];
    }

}

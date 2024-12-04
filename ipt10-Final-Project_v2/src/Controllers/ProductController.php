<?php
namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Media;

class ProductController extends BaseController
{
    private $productModel;
    private $mediaModel;

    public function __construct()
    {
        $this->startSession(); // Ensures session is started
        $this->productModel = new Product(); // Corrected to instantiate Product model
        $this->mediaModel = new Media();
    }

    // Manage products
    public function manageProducts()
    {
        // Fetch all products using the getAllProducts method
        $products = $this->productModel->getAllProducts();

        // Prepare data for rendering
        $data = [
            'products' => $products,
            'message' => $_SESSION['msg'] ?? null,
            'msg_type' => $_SESSION['msg_type'] ?? null
        ];
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        echo $this->renderPage('manage-products', $data);
    }

    // Show Add New Product form
    public function showAddNewProducts()
    {
        $categories = $this->productModel->getCategories();
        $mediaFiles = $this->mediaModel->getMediaFiles();

        // Prepare data for rendering
        $data = [
            'categories' => $categories,
            'media_files' => $mediaFiles
        ];

        // Render the Add Product page
        echo $this->renderPage('add-product', $data);
    }

    // Add a new product
    public function addProduct()
    {
        echo "<script>console.log('POST Data:', " . json_encode($_POST) . ");</script>";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'])) {
            // Extract POST data
            $product_name = $_POST['product_name'];
            $category_id = $_POST['category_id'];
            $quantity = $_POST['quantity'];
            $buy_price = $_POST['buy_price'];
            $sale_price = $_POST['sale_price'];
            $media_id = isset($_POST['media_id']) ? $_POST['media_id'] : null; // Default to null if no media is selected
            echo "<script>console.log('POST Data:', " . json_encode($_POST) . ");</script>";
    
            // Validate required fields
            if (empty($product_name) || empty($category_id) || empty($quantity) || empty($buy_price) || empty($sale_price)) {
                $_SESSION['msg'] = 'All fields are required.';
                $_SESSION['msg_type'] = 'danger'; // Set message type to error
            } else {
                // Call the model to save the product
                $result = $this->productModel->save($product_name, $quantity, $buy_price, $sale_price, $category_id, $media_id);
    
                if ($result > 0) {
                    $_SESSION['msg'] = 'Product added successfully.';
                    $_SESSION['msg_type'] = 'success'; // Set message type to success
                } else {
                    $_SESSION['msg'] = 'Failed to add product.';
                    $_SESSION['msg_type'] = 'danger'; // Set message type to error
                }
            }
            $this->redirect('/manage-products');
        }
    }

    // Edit a product
    public function editProduct()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['msg'] = 'Invalid product ID.';
            $_SESSION['msg_type'] = 'danger';
            $this->redirect('/manage-products');
        }
    
        $product = $this->productModel->getProductById($id);
        $categories = $this->productModel->getCategories();
        $mediaFiles = $this->mediaModel->getMediaFiles();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_name'])) {
            $product_name = $_POST['product_name'];
            $category_id = $_POST['category_id'];
            $quantity = $_POST['quantity'];
            $buy_price = $_POST['buy_price'];
            $sale_price = $_POST['sale_price'];
            $media_id = isset($_POST['media_id']) ? (int)$_POST['media_id'] : null;
    
            // Validate input
            if (empty($product_name) || empty($category_id) || empty($quantity) || empty($buy_price) || empty($sale_price)) {
                $_SESSION['msg'] = 'All fields are required.';
                $_SESSION['msg_type'] = 'danger';
                $this->redirect('/edit-product?id=' . $id);
            }
    
            if (!is_numeric($quantity) || $quantity < 0) {
                $_SESSION['msg'] = 'Quantity must be a valid positive number.';
                $_SESSION['msg_type'] = 'danger';
                $this->redirect('/edit-product?id=' . $id);
            }
    
            if (!is_numeric($buy_price) || $buy_price < 0 || !is_numeric($sale_price) || $sale_price < 0) {
                $_SESSION['msg'] = 'Prices must be valid positive numbers.';
                $_SESSION['msg_type'] = 'danger';
                $this->redirect('/edit-product?id=' . $id);
            }
    
            // Ensure category exists
            if (!$this->productModel->categoryExists($category_id)) {
                $_SESSION['msg'] = 'Selected category does not exist.';
                $_SESSION['msg_type'] = 'danger';
                $this->redirect('/edit-product?id=' . $id);
            }
    
            // Update the product
            $result = $this->productModel->update($id, $product_name, $category_id, $quantity, $buy_price, $sale_price, $media_id);
    
            if ($result > 0) {
                $_SESSION['msg'] = 'Product updated successfully.';
                $_SESSION['msg_type'] = 'success';
            } else {
                $_SESSION['msg'] = 'Failed to update product.';
                $_SESSION['msg_type'] = 'danger';
            }
    
            // Redirect to the manage products page
            $this->redirect('/manage-products');
        }

        // Set selected category
        foreach ($categories as &$category) {
            $category['is_selected'] = ($category['id'] == $product['category_id']);
        }

        // Set selected media
        foreach ($mediaFiles as &$mediaFile) {
            $mediaFile['is_selected'] = ($mediaFile['id'] == $product['media_id']);
        }

        $data = [
            'product' => $product,
            'categories' => $categories,
            'product_id' => $product['id'],
            'media_files' => $mediaFiles,
            'media_id' => $product['media_id']        
        ];
        echo $this->renderPage('edit-product', $data);
    }

    // Delete a product
    public function deleteProduct()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['msg'] = 'Invalid product ID.';
            $_SESSION['msg_type'] = 'danger'; // Set message type to error
            $this->redirect('/manage-products');
            return;
        }
        $result = $this->productModel->delete($id);

        if ($result > 0) {
            $_SESSION['msg'] = 'Product Deleted Successfully.';
            $_SESSION['msg_type'] = 'success'; // Set message type to success
        } else {
            $_SESSION['msg'] = 'Failed to delete product.';
            $_SESSION['msg_type'] = 'danger'; // Set message type to error
        }
        $this->redirect('/manage-products');
    }




}

<?php

namespace App\Models;

use \PDO;
use App\Models\BaseModel;
use App\Models\Media;


class Product extends BaseModel
{
    public function save($name, $quantity, $buy_price, $sale_price, $categorie_id, $media_id = null)
    {
        if (empty($name) || empty($quantity) || empty($buy_price) || empty($sale_price) || empty($categorie_id)) {
            return false;  // Return false if validation fails
        }

        // If no media_id is provided, use NULL in the database
        $sql = "INSERT INTO products (name, quantity, buy_price, sale_price, categorie_id, media_id, date) 
                VALUES (:name, :quantity, :buy_price, :sale_price, :categorie_id, :media_id, NOW())";

        $statement = $this->db->prepare($sql);

        try {
            $this->bindAndExecute($statement, [
                ':name' => $name,
                ':quantity' => $quantity,
                ':buy_price' => $buy_price,
                ':sale_price' => $sale_price,
                ':categorie_id' => $categorie_id,
                ':media_id' => $media_id,  // This can be null
            ]);
        } catch (Exception $e) {
            // Log or print error for debugging
            echo "Error: " . $e->getMessage();
            return false;
        }

        return $statement->rowCount();
    }


    // Get all products
    public function getAllProducts()
    {
        $sql = "
            SELECT
                p.id AS product_id,
                p.name AS product_name,
                p.quantity,
                p.buy_price,
                p.sale_price,
                p.date AS product_date,
                c.name AS category_name,
                m.file_name AS media_file_name
            FROM
                products p
            JOIN
                categories c ON p.categorie_id = c.id
            LEFT JOIN
                media m ON p.media_id = m.id
            ORDER BY
                p.id;
        ";
        
        return $this->fetchAll($sql);
    }

    // Get a single product by ID
    public function getProductById($id) 
    {
        $query = "SELECT id, name, quantity, buy_price, sale_price, categorie_id AS category_id, media_id FROM products WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function categoryExists($category_id)
    {
        $sql = "SELECT id FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    public function getCategories()
    {
        $sql = "SELECT id, name FROM categories";  
        return $this->fetchAll($sql);
    }

    public function update($id, $name, $category_id, $quantity, $buy_price, $sale_price, $media_id = null)
    {
        $sql = "UPDATE products 
                SET name = :name, 
                    categorie_id = :category_id, 
                    quantity = :quantity, 
                    buy_price = :buy_price, 
                    sale_price = :sale_price, 
                    media_id = :media_id
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_STR);  // Assuming quantity is a string, as per your table schema
        $stmt->bindValue(':buy_price', $buy_price, PDO::PARAM_STR);
        $stmt->bindValue(':sale_price', $sale_price, PDO::PARAM_STR);
        $stmt->bindValue(':media_id', $media_id, PDO::PARAM_INT);  // Corrected to media_id
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    // Delete a product by ID
    public function delete($id)
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        
        return $statement->rowCount();
    }

    // Private method to fetch all data
    private function fetchAll($query, $class = null)
    {
        $statement = $this->db->prepare($query);
        $statement->execute();
        
        return $class ? $statement->fetchAll(PDO::FETCH_CLASS, $class) : $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // Private method to bind and execute the query
    private function bindAndExecute($statement, $parameters)
    {
        foreach ($parameters as $key => $value) {
            $statement->bindValue($key, $value);
        }
        
        try {
            $statement->execute();
        } catch (\PDOException $e) {
            throw new \Exception("Error executing statement: " . $e->getMessage());
        }
    }


    public function updateProductName($product_id, $product_name)
    {
        $sql = "UPDATE products SET name = :name WHERE id = :product_id";
        
        $statement = $this->db->prepare($sql);
        $statement->execute([
            ':name' => $product_name,
            ':product_id' => $product_id
        ]);

        return $statement->rowCount() > 0; // Returns true if the update was successful
    }

    public function getRecentlyAddedProducts()
    {
        // Fetch recently added products along with the media file name (image)
        $sql = "
            SELECT 
                p.id AS product_id,
                p.name AS product_name,
                c.name AS category_name,  
                p.sale_price,
                m.file_name AS image_file,  
                p.date AS created_at
            FROM
                products p
            JOIN categories c ON p.categorie_id = c.id 
            LEFT JOIN media m ON p.media_id = m.id  
            ORDER BY
                p.date DESC
            LIMIT 10;
        ";

        // Fetch products with media details
        $products = $this->fetchAll($sql);

        // Add sequential index to products
        foreach ($products as $key => &$product) {
            $product['sequence'] = $key + 1; // Adding sequence starting from 1
        }

        return $products;
    }
}

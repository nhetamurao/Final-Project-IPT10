<?php
namespace App\Controllers;

use App\Models\Media;

class MediaFileController extends BaseController
{
    protected $mediaModel;

    public function __construct()
    {
        $this->startSession(); // Ensures session is started
        $this->mediaModel = new Media();
    }

    public function showMediaFiles() {
        // Fetch media files
        $mediaFiles = $this->mediaModel->getMediaFiles();

        // Prepare data for the template
        $data = [
            'message' => $_SESSION['msg'] ?? null,
            'msg_type' => $_SESSION['msg_type'] ?? null,
            'media_files' => $mediaFiles, // Pass media files to the template
        ];

        // Clear session messages
        unset($_SESSION['msg'], $_SESSION['msg_type']);

        // Render the Mustache template
        echo $this->renderPage('media-files', $data);
    }

    public function addMediaFile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
            // Handle the uploaded file
            $file = $_FILES['photo'];
    
            // Check if file was uploaded successfully
            if ($file['error'] == UPLOAD_ERR_OK) {
                // Set the target directory for file upload (on the server's file system)
                $targetDir = __DIR__ . "/../../views/uploads/products/"; // Correct path for server
                $targetFile = $targetDir . basename($file["name"]);
                $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
                // Check if the file is an image
                if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Move the file to the target directory
                    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                        // Insert file details into the database
                        $this->mediaModel->addMedia($file["name"], $fileType);
    
                        $_SESSION['msg'] = "File uploaded successfully!";
                        $_SESSION['msg_type'] = "success";
                    } else {
                        $_SESSION['msg'] = "Failed to upload file.";
                        $_SESSION['msg_type'] = "danger";
                    }
                } else {
                    $_SESSION['msg'] = "Only image files are allowed.";
                    $_SESSION['msg_type'] = "danger";
                }
            } else {
                $_SESSION['msg'] = "No file uploaded or error occurred.";
                $_SESSION['msg_type'] = "danger";
            }
        }
        $this->redirect('/media-files');
    }

    public function deleteMediaFile() {
        if (isset($_GET['id'])) {
            $mediaId = $_GET['id'];
            
            // Get media info
            $media = $this->mediaModel->getMediaById($mediaId);
            
            if ($media) {
                $fileName = $media['file_name'];
                $filePath = __DIR__ . "/../../views/uploads/products/" . $fileName;
    
                // Delete file from the server
                if (file_exists($filePath)) {
                    unlink($filePath);  // Delete the file
    
                    // Delete from database
                    $this->mediaModel->deleteMedia($mediaId);
    
                    $_SESSION['msg'] = "File deleted successfully!";
                    $_SESSION['msg_type'] = "success";
                } else {
                    $_SESSION['msg'] = "File does not exist.";
                    $_SESSION['msg_type'] = "danger";
                }
            } else {
                $_SESSION['msg'] = "File not found.";
                $_SESSION['msg_type'] = "danger";
            }
        }
        $this->redirect('/media-files');  // Redirect back to media page
    }

}

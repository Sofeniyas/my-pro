<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add Tour
if(isset($_POST['add_tour'])) {
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $desc = $_POST['description'];
    $itin = $_POST['itinerary'];
    $sort_order = $_POST['sort_order'];
    // Handle Add Additional Image
if(isset($_POST['add_image'])) {
    $tour_id = $_POST['tour_id'];
    
    if(isset($_FILES['additional_image']) && $_FILES['additional_image']['name'] != '') {
        $imageName = time() . '_' . $_FILES['additional_image']['name'];
        $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
        
        if(move_uploaded_file($_FILES['additional_image']['tmp_name'], $target)) {
            $stmt = $conn->prepare("INSERT INTO tour_images (tour_id, image, sort_order) VALUES (?, ?, 0)");
            $stmt->execute([$tour_id, $imageName]);
            
            $message = "✅ Image added successfully!";
        }
    }
}
    // Main Image Upload
    $imageName = time() . '_' . $_FILES['image']['name'];
    $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
    
    if(!file_exists(__DIR__ . "/../assets/images/uploads/")) {
        mkdir(__DIR__ . "/../assets/images/uploads/", 0777, true);
    }
    
    if(move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO tours (title, category_id, price, duration, description, itinerary, image, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category_id, $price, $duration, $desc, $itin, $imageName, $sort_order]);
        $tour_id = $conn->lastInsertId();
        
        // Add main image to tour_images table
        $stmt = $conn->prepare("INSERT INTO tour_images (tour_id, image, sort_order) VALUES (?, ?, 0)");
        $stmt->execute([$tour_id, $imageName]);
        
        $message = "✅ Tour added successfully!";
    } else {
        $message = "❌ Upload failed!";
    }
}

// Handle Update Tour
if(isset($_POST['update_tour'])) {
    $id = $_POST['tour_id'];
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $desc = $_POST['description'];
    $itin = $_POST['itinerary'];
    $sort_order = $_POST['sort_order'];
    
    $stmt = $conn->prepare("UPDATE tours SET title=?, category_id=?, price=?, duration=?, description=?, itinerary=?, sort_order=? WHERE id=?");
    $stmt->execute([$title, $category_id, $price, $duration, $desc, $itin, $sort_order, $id]);
    
    // Handle new image upload if provided
    if(isset($_FILES['image']) && $_FILES['image']['name'] != '') {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // Add to tour_images table
            $stmt = $conn->prepare("INSERT INTO tour_images (tour_id, image, sort_order) VALUES (?, ?, 0)");
            $stmt->execute([$id, $imageName]);
            
            // Update main image in tours table
            $stmt = $conn->prepare("UPDATE tours SET image=? WHERE id=?");
            $stmt->execute([$imageName, $id]);
        }
    }
    
    $message = "✅ Tour updated successfully!";
}

// Handle Delete Tour
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM tours WHERE id=$id");
    header("Location: manage-tours.php");
}

// Handle Delete Image
if(isset($_GET['delete_image'])) {
    $image_id = $_GET['delete_image'];
    $tour_id = $_GET['tour_id'];
    
    // Get image name
    $stmt = $conn->prepare("SELECT image FROM tour_images WHERE id=?");
    $stmt->execute([$image_id]);
    $img = $stmt->fetch();
    
    // Delete from database
    $conn->query("DELETE FROM tour_images WHERE id=$image_id");
    
    // Delete from folder
    $img_path = __DIR__ . "/../assets/images/uploads/" . $img['image'];
    if(file_exists($img_path)) {
        unlink($img_path);
    }
    
    header("Location: manage-tours.php");
}

// Get tour for editing
$edit_tour = null;
$edit_images = [];
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_tour = $stmt->fetch();
    
    $stmt = $conn->prepare("SELECT * FROM tour_images WHERE tour_id=? ORDER BY sort_order");
    $stmt->execute([$edit_id]);
    $edit_images = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins'; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #1a4d2e; color: white; height: 100vh; padding: 20px; }
        .sidebar a { display: block; color: white; padding: 10px; margin-bottom: 5px; text-decoration: none; }
        .main-content { flex: 1; padding: 40px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .form-group { margin-bottom: 15px; }
        input, textarea, select { width: 100%; padding: 10px; margin-top: 5px; }
        button { background: #1a4d2e; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn-del { background: red; padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px; }
        .btn-edit { background: #d4af37; padding: 5px 10px; color: #1a4d2e; text-decoration: none; border-radius: 3px; }
        .message { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .image-gallery { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .image-gallery img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }
        .image-gallery .delete-img { background: red; color: white; border: none; padding: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="sidebar">
    <h2><i class="fas fa-mountain"></i> ELYOS ADMIN</h2>
    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage-tours.php"><i class="fas fa-map-marked-alt"></i> Manage Tours</a>
    <a href="manage-blog.php"><i class="fas fa-newspaper"></i> Manage Blog</a>
    <a href="manage-travel-tips.php"><i class="fas fa-clipboard-list"></i> Travel Tips</a>
    <a href="manage-partners.php"><i class="fas fa-handshake"></i> Partners</a>
    <a href="manage-reviews.php"><i class="fas fa-star"></i> Reviews</a>
    <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
    <div class="main-content">
        <h1>Manage Tours</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div style="background: white; padding: 20px; margin-bottom: 30px; border-radius: 8px;">
            <h3><?php echo $edit_tour ? 'Edit Tour' : 'Add New Tour'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_tour): ?>
                    <input type="hidden" name="tour_id" value="<?php echo $edit_tour['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo $edit_tour['title'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php
                        $cats = $conn->query("SELECT * FROM tour_categories");
                        while($c = $cats->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($edit_tour && $edit_tour['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo $c['name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="<?php echo $edit_tour['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    <small style="color: #666;">Lower numbers appear first. Use 0, 1, 2, 3...</small>
                </div>
                
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" value="<?php echo $edit_tour['price'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Duration (e.g., 5 Days)</label>
                    <input type="text" name="duration" value="<?php echo $edit_tour['duration'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?php echo $edit_tour['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Itinerary (Day by Day)</label>
                    <textarea name="itinerary" rows="5"><?php echo $edit_tour['itinerary'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" <?php echo $edit_tour ? '' : 'required'; ?>>
                    <small style="color: #666;">Upload additional images after saving the tour</small>
                </div>
                
                <button type="submit" name="<?php echo $edit_tour ? 'update_tour' : 'add_tour'; ?>">
                    <?php echo $edit_tour ? 'Update Tour' : 'Save Tour'; ?>
                </button>
                <?php if($edit_tour): ?>
                    <a href="manage-tours.php" style="margin-left: 10px; color: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Existing Tours Table -->
        <h3>Existing Tours</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT t.*, c.name as category_name FROM tours t LEFT JOIN tour_categories c ON t.category_id = c.id ORDER BY t.sort_order, t.id");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    // Get images for this tour
                    $img_stmt = $conn->prepare("SELECT * FROM tour_images WHERE tour_id=?");
                    $img_stmt->execute([$row['id']]);
                    $images = $img_stmt->fetchAll();
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['category_name']; ?></td>
                    <td>$<?php echo $row['price']; ?></td>
                    <td>
                        <div class="image-gallery">
                            <?php foreach($images as $img): ?>
                                <div style="position: relative;">
                                    <img src="../assets/images/uploads/<?php echo $img['image']; ?>">
                                    <a href="?delete_image=<?php echo $img['id']; ?>&tour_id=<?php echo $row['id']; ?>" 
                                       class="delete-img" 
                                       style="position: absolute; top: -5px; right: -5px; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 12px;"
                                       onclick="return confirm('Delete this image?')">×</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Add More Images Form -->
                        <form method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
                            <input type="hidden" name="tour_id" value="<?php echo $row['id']; ?>">
                            <input type="file" name="additional_image" style="font-size: 12px;">
                            <button type="submit" name="add_image" style="padding: 5px 10px; font-size: 12px;">+ Add Image</button>
                        </form>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this tour?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
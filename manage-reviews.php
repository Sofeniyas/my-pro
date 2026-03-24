<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update Review
if(isset($_POST['save_review'])) {
    $customer_name = $_POST['customer_name'];
    $customer_location = $_POST['customer_location'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    $tour_name = $_POST['tour_name'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    if(isset($_POST['review_id']) && !empty($_POST['review_id'])) {
        $review_id = $_POST['review_id'];
        
        if(isset($_FILES['customer_photo']) && $_FILES['customer_photo']['name'] != '') {
            $imageName = time() . '_' . $_FILES['customer_photo']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            
            if(move_uploaded_file($_FILES['customer_photo']['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE reviews SET customer_name=?, customer_location=?, rating=?, review_text=?, tour_name=?, sort_order=?, status=?, customer_photo=? WHERE id=?");
                $stmt->execute([$customer_name, $customer_location, $rating, $review_text, $tour_name, $sort_order, $status, $imageName, $review_id]);
            } else {
                $stmt = $conn->prepare("UPDATE reviews SET customer_name=?, customer_location=?, rating=?, review_text=?, tour_name=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$customer_name, $customer_location, $rating, $review_text, $tour_name, $sort_order, $status, $review_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE reviews SET customer_name=?, customer_location=?, rating=?, review_text=?, tour_name=?, sort_order=?, status=? WHERE id=?");
            $stmt->execute([$customer_name, $customer_location, $rating, $review_text, $tour_name, $sort_order, $status, $review_id]);
        }
        
        $message = "✅ Review updated successfully!";
    } else {
        $imageName = '';
        if(isset($_FILES['customer_photo']) && $_FILES['customer_photo']['name'] != '') {
            $imageName = time() . '_' . $_FILES['customer_photo']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            move_uploaded_file($_FILES['customer_photo']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO reviews (customer_name, customer_location, rating, review_text, tour_name, sort_order, status, customer_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_name, $customer_location, $rating, $review_text, $tour_name, $sort_order, $status, $imageName]);
        
        $message = "✅ Review added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM reviews WHERE id=$id");
    header("Location: manage-reviews.php");
}

// Get review for editing
$edit_review = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_review = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Reviews - Elyos Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar {
            width: 250px;
            background: #1a4d2e;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h2 { color: #d4af37; margin-bottom: 30px; font-size: 1.3rem; text-align: center; }
        .sidebar a {
            display: block; color: white; padding: 12px 15px; margin-bottom: 8px;
            text-decoration: none; border-radius: 5px; transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active { background: #d4af37; color: #1a4d2e; }
        .sidebar a i { width: 25px; }
        .main-content { flex: 1; margin-left: 250px; padding: 40px; }
        .main-content h1 { color: #1a4d2e; margin-bottom: 30px; }
        .form-box {
            background: white; padding: 30px; border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1); margin-bottom: 30px;
        }
        .form-box h3 { color: #1a4d2e; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f4f4f4; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 5px; font-family: 'Poppins', sans-serif; font-size: 1rem;
        }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        
        /* Star Rating Input */
        .rating-input {
            display: flex;
            gap: 5px;
            flex-direction: row-reverse;
            justify-content: flex-start;
        }
        .rating-input input { display: none; }
        .rating-input label {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
            margin: 0;
            transition: 0.2s;
        }
        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }
        
        button {
            background: #1a4d2e; color: white; padding: 12px 30px;
            border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: 0.3s;
        }
        button:hover { background: #d4af37; color: #1a4d2e; }
        .btn-secondary { background: #666; margin-left: 10px; }
        .message { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        table {
            width: 100%; border-collapse: collapse; background: white;
            border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1a4d2e; color: white; }
        tr:hover { background: #f9f9f9; }
        .customer-photo-preview { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .review-preview { max-width: 300px; font-size: 0.9rem; color: #666; }
        .btn-edit { background: #d4af37; color: #1a4d2e; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .stars-display { color: #ffc107; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; padding: 15px; }
            .main-content { margin-left: 0; padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fas fa-mountain"></i> ELYOS ADMIN</h2>
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage-tours.php"><i class="fas fa-map-marked-alt"></i> Manage Tours</a>
        <a href="manage-blog.php"><i class="fas fa-newspaper"></i> Manage Blog</a>
        <a href="manage-travel-tips.php"><i class="fas fa-clipboard-list"></i> Travel Tips</a>
        <a href="manage-partners.php"><i class="fas fa-handshake"></i> Partners</a>
        <a href="manage-reviews.php" class="active"><i class="fas fa-star"></i> Reviews</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1><i class="fas fa-star"></i> Manage Reviews</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="form-box">
            <h3><?php echo $edit_review ? 'Edit Review' : 'Add New Review'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_review): ?>
                    <input type="hidden" name="review_id" value="<?php echo $edit_review['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Name *</label>
                        <input type="text" name="customer_name" value="<?php echo $edit_review['customer_name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="customer_location" value="<?php echo $edit_review['customer_location'] ?? ''; ?>" placeholder="e.g., United Kingdom">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Rating *</label>
                        <div class="rating-input">
                            <input type="radio" name="rating" id="star5" value="5" <?php echo ($edit_review && $edit_review['rating'] == 5) ? 'checked' : ''; ?>><label for="star5">★</label>
                            <input type="radio" name="rating" id="star4" value="4" <?php echo ($edit_review && $edit_review['rating'] == 4) ? 'checked' : ''; ?>><label for="star4">★</label>
                            <input type="radio" name="rating" id="star3" value="3" <?php echo ($edit_review && $edit_review['rating'] == 3) ? 'checked' : ''; ?>><label for="star3">★</label>
                            <input type="radio" name="rating" id="star2" value="2" <?php echo ($edit_review && $edit_review['rating'] == 2) ? 'checked' : ''; ?>><label for="star2">★</label>
                            <input type="radio" name="rating" id="star1" value="1" <?php echo ($edit_review && $edit_review['rating'] == 1) ? 'checked' : ''; ?>><label for="star1">★</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tour Name</label>
                        <input type="text" name="tour_name" value="<?php echo $edit_review['tour_name'] ?? ''; ?>" placeholder="e.g., Lalibela Historical Tour">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Photo</label>
                        <input type="file" name="customer_photo" accept="image/*">
                        <?php if($edit_review && !empty($edit_review['customer_photo'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="../assets/images/uploads/<?php echo $edit_review['customer_photo']; ?>" class="customer-photo-preview">
                                <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Current photo (upload new to replace)</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $edit_review['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo ($edit_review && $edit_review['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_review && $edit_review['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Review Text *</label>
                    <textarea name="review_text" required><?php echo $edit_review['review_text'] ?? ''; ?></textarea>
                </div>
                
                <button type="submit" name="save_review">
                    <i class="fas fa-save"></i> <?php echo $edit_review ? 'Update Review' : 'Save Review'; ?>
                </button>
                <?php if($edit_review): ?>
                    <a href="manage-reviews.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Reviews Table -->
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Reviews</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Tour</th>
                    <th>Preview</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM reviews ORDER BY sort_order, created_at DESC");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                        <small style="color: #999;"><?php echo htmlspecialchars($row['customer_location']); ?></small>
                    </td>
                    <td>
                        <span class="stars-display">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $row['rating'] ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['tour_name']); ?></td>
                    <td class="review-preview">
                        "<?php echo substr(htmlspecialchars($row['review_text']), 0, 80); ?>..."
                    </td>
                    <td>
                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this review?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update Travel Tip
if(isset($_POST['save_tip'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    if(isset($_POST['tip_id']) && !empty($_POST['tip_id'])) {
        // Update existing
        $tip_id = $_POST['tip_id'];
        
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['featured_image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE travel_tips SET title=?, category=?, content=?, author=?, sort_order=?, status=?, featured_image=? WHERE id=?");
                $stmt->execute([$title, $category, $content, $author, $sort_order, $status, $imageName, $tip_id]);
            } else {
                $stmt = $conn->prepare("UPDATE travel_tips SET title=?, category=?, content=?, author=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$title, $category, $content, $author, $sort_order, $status, $tip_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE travel_tips SET title=?, category=?, content=?, author=?, sort_order=?, status=? WHERE id=?");
            $stmt->execute([$title, $category, $content, $author, $sort_order, $status, $tip_id]);
        }
        
        $message = "✅ Travel tip updated successfully!";
    } else {
        // Add new
        $imageName = '';
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['featured_image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            move_uploaded_file($_FILES['featured_image']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO travel_tips (title, category, content, author, sort_order, status, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $content, $author, $sort_order, $status, $imageName]);
        
        $message = "✅ Travel tip added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM travel_tips WHERE id=$id");
    header("Location: manage-travel-tips.php");
}

// Get tip for editing
$edit_tip = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM travel_tips WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_tip = $stmt->fetch();
}

// Get stats
$total_tips = $conn->query("SELECT COUNT(*) FROM travel_tips")->fetchColumn();
$active_tips = $conn->query("SELECT COUNT(*) FROM travel_tips WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Travel Tips - Elyos Admin</title>
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
        .sidebar h2 {
            color: #d4af37;
            margin-bottom: 30px;
            font-size: 1.3rem;
            text-align: center;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 12px 15px;
            margin-bottom: 8px;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #d4af37;
            color: #1a4d2e;
        }
        .sidebar a i { width: 25px; }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }
        .main-content h1 {
            color: #1a4d2e;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2rem;
            color: #1a4d2e;
        }
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-box h3 {
            color: #1a4d2e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4f4f4;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        button {
            background: #1a4d2e;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
        }
        button:hover {
            background: #d4af37;
            color: #1a4d2e;
        }
        .btn-secondary {
            background: #666;
            margin-left: 10px;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #1a4d2e;
            color: white;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .btn-edit {
            background: #d4af37;
            color: #1a4d2e;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-del {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-left: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <a href="manage-travel-tips.php" class="active"><i class="fas fa-clipboard-list"></i> Travel Tips</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1><i class="fas fa-clipboard-list"></i> Manage Travel Tips</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_tips; ?></h3>
                <p>Total Tips</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $active_tips; ?></h3>
                <p>Active Tips</p>
            </div>
        </div>
        
        <!-- Add/Edit Form -->
        <div class="form-box">
            <h3><?php echo $edit_tip ? 'Edit Travel Tip' : 'Add New Travel Tip'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_tip): ?>
                    <input type="hidden" name="tip_id" value="<?php echo $edit_tip['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?php echo $edit_tip['title'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="Planning" <?php echo ($edit_tip && $edit_tip['category'] == 'Planning') ? 'selected' : ''; ?>>Planning</option>
                            <option value="Packing" <?php echo ($edit_tip && $edit_tip['category'] == 'Packing') ? 'selected' : ''; ?>>Packing</option>
                            <option value="Money" <?php echo ($edit_tip && $edit_tip['category'] == 'Money') ? 'selected' : ''; ?>>Money</option>
                            <option value="Food" <?php echo ($edit_tip && $edit_tip['category'] == 'Food') ? 'selected' : ''; ?>>Food</option>
                            <option value="Health" <?php echo ($edit_tip && $edit_tip['category'] == 'Health') ? 'selected' : ''; ?>>Health</option>
                            <option value="Culture" <?php echo ($edit_tip && $edit_tip['category'] == 'Culture') ? 'selected' : ''; ?>>Culture</option>
                            <option value="General" <?php echo ($edit_tip && $edit_tip['category'] == 'General') ? 'selected' : ''; ?>>General</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author" value="<?php echo $edit_tip['author'] ?? 'Elyos Team'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $edit_tip['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo ($edit_tip && $edit_tip['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_tip && $edit_tip['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*">
                    <?php if($edit_tip && !empty($edit_tip['featured_image'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="../assets/images/uploads/<?php echo $edit_tip['featured_image']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                            <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Current image (upload new to replace)</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Content *</label>
                    <textarea name="content" required><?php echo $edit_tip['content'] ?? ''; ?></textarea>
                    <small style="color: #666;">Use HTML tags like &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt; for formatting</small>
                </div>
                
                <button type="submit" name="save_tip">
                    <i class="fas fa-save"></i> <?php echo $edit_tip ? 'Update Tip' : 'Save Tip'; ?>
                </button>
                <?php if($edit_tip): ?>
                    <a href="manage-travel-tips.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tips Table -->
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Travel Tips</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM travel_tips ORDER BY sort_order, publish_date DESC");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['publish_date'])); ?></td>
                    <td><?php echo $row['views']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this travel tip?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
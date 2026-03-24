<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update Item
if(isset($_POST['save_item'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon = $_POST['icon'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    if(isset($_POST['item_id']) && !empty($_POST['item_id'])) {
        $item_id = $_POST['item_id'];
        $stmt = $conn->prepare("UPDATE why_travel_items SET title=?, description=?, icon=?, sort_order=?, status=? WHERE id=?");
        $stmt->execute([$title, $description, $icon, $sort_order, $status, $item_id]);
        $message = "✅ Item updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO why_travel_items (title, description, icon, sort_order, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $sort_order, $status]);
        $message = "✅ Item added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM why_travel_items WHERE id=$id");
    header("Location: manage-why-travel.php");
}

// Get item for editing
$edit_item = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM why_travel_items WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_item = $stmt->fetch();
}

// Available icons
$icons = [
    'fa-user-shield' => 'Local Expert',
    'fa-heart' => 'Authentic',
    'fa-sliders-h' => 'Custom',
    'fa-certificate' => 'Licensed',
    'fa-award' => 'Award Winning',
    'fa-globe' => 'Global Standards',
    'fa-hand-holding-heart' => 'Care',
    'fa-shield-alt' => 'Safe',
    'fa-users' => 'Team',
    'fa-clock' => '24/7 Support'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage "Why Travel With Us" - Elyos Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; display: flex; min-height: 100vh; background: #f4f4f4; }
        .sidebar {
            width: 250px; background: #1a4d2e; color: white; padding: 20px;
            position: fixed; height: 100vh; overflow-y: auto;
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
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .icon-preview {
            display: inline-flex; align-items: center; gap: 10px;
            background: #f9f9f9; padding: 10px 15px; border-radius: 5px; margin-top: 5px;
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
        .btn-edit { background: #d4af37; color: #1a4d2e; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .icon-display { font-size: 1.2rem; color: var(--secondary); }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; padding: 15px; }
            .main-content { margin-left: 0; padding: 20px; }
            .form-row { grid-template-columns: 1fr; }
        }
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
        <a href="manage-why-travel.php" class="active"><i class="fas fa-heart"></i> Why Travel</a>
        <a href="manage-licenses.php"><i class="fas fa-certificate"></i> Licenses</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="main-content">
        <h1><i class="fas fa-heart"></i> Manage "Why Travel With Us"</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-box">
            <h3><?php echo $edit_item ? 'Edit Item' : 'Add New Item'; ?></h3>
            <form method="POST">
                <?php if($edit_item): ?>
                    <input type="hidden" name="item_id" value="<?php echo $edit_item['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?php echo $edit_item['title'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="icon" id="iconSelect" onchange="updateIconPreview()">
                            <?php foreach($icons as $icon => $label): ?>
                            <option value="<?php echo $icon; ?>" <?php echo ($edit_item && $edit_item['icon'] == $icon) ? 'selected' : ''; ?>>
                                <?php echo $label; ?> (<?php echo $icon; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="icon-preview">
                            <i class="fas <?php echo $edit_item['icon'] ?? 'fa-check-circle'; ?>" id="iconPreview" style="font-size: 1.5rem; color: var(--secondary);"></i>
                            <span id="iconLabel"><?php echo $icons[$edit_item['icon'] ?? 'fa-check-circle'] ?? 'Check'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required><?php echo $edit_item['description'] ?? ''; ?></textarea>
                    <small style="color: #666;">Keep it warm, friendly, and under 150 characters</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $edit_item['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($edit_item && $edit_item['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_item && $edit_item['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="save_item">
                    <i class="fas fa-save"></i> <?php echo $edit_item ? 'Update Item' : 'Save Item'; ?>
                </button>
                <?php if($edit_item): ?>
                    <a href="manage-why-travel.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Icon</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM why_travel_items ORDER BY sort_order");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td><i class="fas <?php echo $row['icon']; ?> icon-display"></i></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td style="max-width: 300px;"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function updateIconPreview() {
            const select = document.getElementById('iconSelect');
            const icon = select.value;
            const label = select.options[select.selectedIndex].text.split(' (')[0];
            document.getElementById('iconPreview').className = 'fas ' + icon;
            document.getElementById('iconLabel').textContent = label;
        }
    </script>
</body>
</html>
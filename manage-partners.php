<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update Partner
if(isset($_POST['save_partner'])) {
    $name = $_POST['name'];
    $website_url = $_POST['website_url'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    if(isset($_POST['partner_id']) && !empty($_POST['partner_id'])) {
        // Update existing partner
        $partner_id = $_POST['partner_id'];
        
        if(isset($_FILES['logo']) && $_FILES['logo']['name'] != '') {
            $imageName = time() . '_' . $_FILES['logo']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            
            if(move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE partners SET name=?, website_url=?, sort_order=?, status=?, logo=? WHERE id=?");
                $stmt->execute([$name, $website_url, $sort_order, $status, $imageName, $partner_id]);
            } else {
                $stmt = $conn->prepare("UPDATE partners SET name=?, website_url=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$name, $website_url, $sort_order, $status, $partner_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE partners SET name=?, website_url=?, sort_order=?, status=? WHERE id=?");
            $stmt->execute([$name, $website_url, $sort_order, $status, $partner_id]);
        }
        $message = "✅ Partner updated successfully!";
    } else {
        // Add new partner
        $imageName = '';
        if(isset($_FILES['logo']) && $_FILES['logo']['name'] != '') {
            $imageName = time() . '_' . $_FILES['logo']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            move_uploaded_file($_FILES['logo']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO partners (name, logo, website_url, sort_order, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $imageName, $website_url, $sort_order, $status]);
        $message = "✅ Partner added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM partners WHERE id=$id");
    header("Location: manage-partners.php");
}

// Get partner for editing
$edit_partner = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM partners WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_partner = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Partners - Elyos Admin</title>
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
        .form-group input, .form-group select {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 5px; font-family: 'Poppins', sans-serif; font-size: 1rem;
        }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
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
        .partner-logo-preview { width: 80px; height: 60px; object-fit: contain; background: #f9f9f9; padding: 5px; border-radius: 5px; }
        .btn-edit { background: #d4af37; color: #1a4d2e; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
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
        <a href="manage-partners.php" class="active"><i class="fas fa-handshake"></i> Partners</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1><i class="fas fa-handshake"></i> Manage Partners</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="form-box">
            <h3><?php echo $edit_partner ? 'Edit Partner' : 'Add New Partner'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_partner): ?>
                    <input type="hidden" name="partner_id" value="<?php echo $edit_partner['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Partner Name *</label>
                        <input type="text" name="name" value="<?php echo $edit_partner['name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Website URL</label>
                        <input type="url" name="website_url" value="<?php echo $edit_partner['website_url'] ?? ''; ?>" placeholder="https://example.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Logo Image</label>
                        <input type="file" name="logo" accept="image/*">
                        <?php if($edit_partner && !empty($edit_partner['logo'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="../assets/images/uploads/<?php echo $edit_partner['logo']; ?>" class="partner-logo-preview">
                                <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Current logo (upload new to replace)</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $edit_partner['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?php echo ($edit_partner && $edit_partner['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_partner && $edit_partner['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" name="save_partner">
                    <i class="fas fa-save"></i> <?php echo $edit_partner ? 'Update Partner' : 'Save Partner'; ?>
                </button>
                <?php if($edit_partner): ?>
                    <a href="manage-partners.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Partners Table -->
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Partners</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Website</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM partners ORDER BY sort_order");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td>
                        <?php if(!empty($row['logo'])): ?>
                            <img src="../assets/images/uploads/<?php echo $row['logo']; ?>" class="partner-logo-preview">
                        <?php else: ?>
                            <span style="color: #999;">No logo</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <?php if(!empty($row['website_url'])): ?>
                            <a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank" style="color: var(--primary);">
                                <i class="fas fa-external-link-alt"></i> Visit
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this partner?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
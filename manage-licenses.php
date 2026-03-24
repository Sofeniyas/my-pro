<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update License
if(isset($_POST['save_license'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $document_type = $_POST['document_type'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    if(isset($_POST['license_id']) && !empty($_POST['license_id'])) {
        $license_id = $_POST['license_id'];
        
        if(isset($_FILES['image']) && $_FILES['image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE licenses SET title=?, description=?, document_type=?, sort_order=?, status=?, image=? WHERE id=?");
                $stmt->execute([$title, $description, $document_type, $sort_order, $status, $imageName, $license_id]);
            } else {
                $stmt = $conn->prepare("UPDATE licenses SET title=?, description=?, document_type=?, sort_order=?, status=? WHERE id=?");
                $stmt->execute([$title, $description, $document_type, $sort_order, $status, $license_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE licenses SET title=?, description=?, document_type=?, sort_order=?, status=? WHERE id=?");
            $stmt->execute([$title, $description, $document_type, $sort_order, $status, $license_id]);
        }
        
        $message = "✅ License updated successfully!";
    } else {
        $imageName = '';
        if(isset($_FILES['image']) && $_FILES['image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO licenses (title, description, image, document_type, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $imageName, $document_type, $sort_order, $status]);
        
        $message = "✅ License added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM licenses WHERE id=$id");
    header("Location: manage-licenses.php");
}

// Get license for editing
$edit_license = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM licenses WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_license = $stmt->fetch();
}

$doc_types = [
    'license' => 'Business License',
    'certification' => 'Certification',
    'registration' => 'Registration',
    'insurance' => 'Insurance'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Licenses - Elyos Admin</title>
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
        .license-preview { width: 100px; height: 80px; object-fit: contain; background: #f9f9f9; padding: 5px; border-radius: 5px; }
        .btn-edit { background: #d4af37; color: #1a4d2e; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .doc-type-badge {
            display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;
        }
        .doc-license { background: #e3f2fd; color: #1976d2; }
        .doc-certification { background: #e8f5e9; color: #388e3c; }
        .doc-registration { background: #fff3e0; color: #ef6c00; }
        .doc-insurance { background: #fce4ec; color: #c2185b; }
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
        <a href="manage-why-travel.php"><i class="fas fa-heart"></i> Why Travel</a>
        <a href="manage-licenses.php" class="active"><i class="fas fa-certificate"></i> Licenses</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="main-content">
        <h1><i class="fas fa-certificate"></i> Manage Licenses</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-box">
            <h3><?php echo $edit_license ? 'Edit License' : 'Add New License'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_license): ?>
                    <input type="hidden" name="license_id" value="<?php echo $edit_license['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?php echo $edit_license['title'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Document Type</label>
                        <select name="document_type">
                            <?php foreach($doc_types as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($edit_license && $edit_license['document_type'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>License Image *</label>
                    <input type="file" name="image" accept="image/*" <?php echo $edit_license ? '' : 'required'; ?>>
                    <?php if($edit_license && !empty($edit_license['image'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="../assets/images/uploads/<?php echo $edit_license['image']; ?>" class="license-preview">
                            <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Current image (upload new to replace)</p>
                        </div>
                    <?php endif; ?>
                    <small style="color: #666; display: block; margin-top: 8px;">
                        💡 Tip: Scan documents at 300 DPI, save as JPG/PNG, max 2MB
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo $edit_license['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?php echo $edit_license['sort_order'] ?? '0'; ?>" placeholder="0 = First">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?php echo ($edit_license && $edit_license['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_license && $edit_license['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="save_license">
                    <i class="fas fa-save"></i> <?php echo $edit_license ? 'Update License' : 'Save License'; ?>
                </button>
                <?php if($edit_license): ?>
                    <a href="manage-licenses.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Licenses</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM licenses ORDER BY sort_order, uploaded_at DESC");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['sort_order']; ?></td>
                    <td>
                        <?php if(!empty($row['image'])): ?>
                            <img src="../assets/images/uploads/<?php echo $row['image']; ?>" class="license-preview">
                        <?php else: ?>
                            <span style="color: #999;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>
                        <span class="doc-type-badge doc-<?php echo $row['document_type']; ?>">
                            <?php echo $doc_types[$row['document_type']]; ?>
                        </span>
                    </td>
                    <td style="max-width: 250px;"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this license?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
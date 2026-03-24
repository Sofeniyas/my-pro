<?php
require '../config.php';
if(!isset($_SESSION['admin_id'])) header("Location: login.php");

$message = "";

// Handle Add/Update Blog Post
if(isset($_POST['save_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    if(isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // Update existing
        $post_id = $_POST['post_id'];
        
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['featured_image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
                $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, featured_image=? WHERE id=?");
                $stmt->execute([$title, $content, $imageName, $post_id]);
            } else {
                $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=? WHERE id=?");
                $stmt->execute([$title, $content, $post_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=? WHERE id=?");
            $stmt->execute([$title, $content, $post_id]);
        }
        
        $message = "✅ Blog post updated successfully!";
    } else {
        // Add new
        $imageName = '';
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['name'] != '') {
            $imageName = time() . '_' . $_FILES['featured_image']['name'];
            $target = __DIR__ . "/../assets/images/uploads/" . basename($imageName);
            move_uploaded_file($_FILES['featured_image']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, featured_image) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $imageName]);
        
        $message = "✅ Blog post added successfully!";
    }
}

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM blog_posts WHERE id=$id");
    header("Location: manage-blog.php");
}

// Get post for editing
$edit_post = null;
if(isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$edit_id]);
    $edit_post = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Blog - Elyos Admin</title>
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
        .form-group input, .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 5px; font-family: 'Poppins', sans-serif; font-size: 1rem;
        }
        .form-group textarea { min-height: 250px; resize: vertical; }
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
        .post-preview { max-width: 400px; font-size: 0.9rem; color: #666; }
        .featured-img-preview { width: 100px; height: 80px; object-fit: cover; border-radius: 5px; }
        .btn-edit { background: #d4af37; color: #1a4d2e; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-del { background: #dc3545; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; }
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
        <a href="manage-blog.php" class="active"><i class="fas fa-newspaper"></i> Manage Blog</a>
        <a href="manage-travel-tips.php"><i class="fas fa-clipboard-list"></i> Travel Tips</a>
        <a href="manage-partners.php"><i class="fas fa-handshake"></i> Partners</a>
        <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
        <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1><i class="fas fa-newspaper"></i> Manage Blog</h1>
        
        <?php if($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Form -->
        <div class="form-box">
            <h3><?php echo $edit_post ? 'Edit Blog Post' : 'Add New Blog Post'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if($edit_post): ?>
                    <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Post Title *</label>
                    <input type="text" name="title" value="<?php echo $edit_post['title'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*">
                    <?php if($edit_post && !empty($edit_post['featured_image'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="../assets/images/uploads/<?php echo $edit_post['featured_image']; ?>" class="featured-img-preview">
                            <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Current image (upload new to replace)</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Content *</label>
                    <textarea name="content" required><?php echo $edit_post['content'] ?? ''; ?></textarea>
                    <small style="color: #666;">Use HTML tags like &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt; for formatting</small>
                </div>
                
                <button type="submit" name="save_post">
                    <i class="fas fa-save"></i> <?php echo $edit_post ? 'Update Post' : 'Save Post'; ?>
                </button>
                <?php if($edit_post): ?>
                    <a href="manage-blog.php" class="btn-secondary" style="padding: 12px 30px; border-radius: 5px; text-decoration: none; color: white;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Blog Posts Table -->
        <h3 style="color: #1a4d2e; margin-bottom: 20px;">All Blog Posts</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Preview</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td>
                        <?php if(!empty($row['featured_image'])): ?>
                            <img src="../assets/images/uploads/<?php echo $row['featured_image']; ?>" class="featured-img-preview">
                        <?php else: ?>
                            <span style="color: #999;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td class="post-preview">
                        "<?php echo substr(strip_tags($row['content']), 0, 80); ?>..."
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Delete this post?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
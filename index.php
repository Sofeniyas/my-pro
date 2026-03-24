<?php
require '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get counts for dashboard
$tours_count = $conn->query("SELECT COUNT(*) FROM tours")->fetchColumn();
$blog_count = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$categories_count = $conn->query("SELECT COUNT(*) FROM tour_categories")->fetchColumn();

// Get recent activity (last 5 tours added)
$recent_stmt = $conn->query("SELECT title, created_at FROM tours ORDER BY created_at DESC LIMIT 5");
$recent_tours = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - Elyos Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; display: flex; min-height: 100vh; background: #f4f4f4; }
        
        /* Sidebar */
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
        .sidebar a i {
            width: 25px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
        }
        .main-content h1 {
            color: #1a4d2e;
            margin-bottom: 30px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 2rem;
            color: #d4af37;
            margin-bottom: 15px;
        }
        .stat-card h3 {
            font-size: 2rem;
            color: #1a4d2e;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Recent Activity */
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .recent-section h3 {
            color: #1a4d2e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4f4f4;
        }
        .recent-list {
            list-style: none;
        }
        .recent-list li {
            padding: 12px 0;
            border-bottom: 1px solid #f4f4f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .recent-list li:last-child {
            border-bottom: none;
        }
        .recent-list .date {
            color: #999;
            font-size: 0.85rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .quick-actions a {
            background: #1a4d2e;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }
        .quick-actions a:hover {
            background: #d4af37;
            color: #1a4d2e;
        }
        
        /* Mobile Responsive */
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
            .stats-grid {
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
    <a href="manage-travel-tips.php"><i class="fas fa-clipboard-list"></i> Travel Tips</a>
    <a href="manage-partners.php"><i class="fas fa-handshake"></i> Partners</a>
    <a href="manage-reviews.php"><i class="fas fa-star"></i> Reviews</a>
    <a href="manage-why-travel.php"><i class="fas fa-heart"></i> Why Travel</a>
    <a href="manage-licenses.php"><i class="fas fa-certificate"></i> Licenses</a>
    <a href="../" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="manage-tours.php"><i class="fas fa-plus"></i> Add New Tour</a>
            <a href="manage-blog.php"><i class="fas fa-plus"></i> Add Blog Post</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-map-marked-alt"></i>
                <h3><?php echo $tours_count; ?></h3>
                <p>Total Tours</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-newspaper"></i>
                <h3><?php echo $blog_count; ?></h3>
                <p>Blog Posts</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-folder-open"></i>
                <h3><?php echo $categories_count; ?></h3>
                <p>Categories</p>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-section">
            <h3><i class="fas fa-clock"></i> Recently Added Tours</h3>
            <?php if(count($recent_tours) > 0): ?>
            <ul class="recent-list">
                <?php foreach($recent_tours as $tour): ?>
                <li>
                    <span><?php echo htmlspecialchars($tour['title']); ?></span>
                    <span class="date"><?php echo date('M d, Y', strtotime($tour['created_at'])); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p style="color: #666;">No tours added yet. <a href="manage-tours.php" style="color: #1a4d2e;">Add your first tour!</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
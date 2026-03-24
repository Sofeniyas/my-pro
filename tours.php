<?php 
require 'config.php'; 

// Initialize variables
$category_filter = "";
$params = [];

// Check if category is selected
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = "WHERE t.category_id = ?";
    $params = [$_GET['category']];
}

// Build SQL query with sorting
$sql = "SELECT t.*, c.name as category_name FROM tours t 
        LEFT JOIN tour_categories c ON t.category_id = c.id 
        $category_filter 
        AND t.status = 'active' 
        ORDER BY t.sort_order, t.id";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();

// Get current category name for page title
$current_category = "";
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $cat_stmt = $conn->prepare("SELECT name FROM tour_categories WHERE id = ?");
    $cat_stmt->execute([$_GET['category']]);
    $cat = $cat_stmt->fetch();
    if($cat) {
        $current_category = $cat['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $current_category ? $current_category . ' - ' : ''; ?>Tours - Elyos Ethiopia</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-logo">ELYOS ETHIOPIA</div>
        <div class="nav-contact">
            <span><i class="fas fa-phone"></i> +251 900 000 000</span>
            <span><i class="fas fa-envelope"></i> info@elyosethiopia.com</span>
        </div>
        <div class="nav-links" id="navLinks">
    <a href="index.php">Home</a>
    <a href="tours.php">Tours</a>
    <a href="travel-tips.php">Travel Tips</a>
    <a href="blog.php">Blog</a>
    <a href="contact.php">Contact</a>
    <a href="https://wa.me/251900000000" class="btn-book">Book Now</a>
</div>
    </nav>

    <section class="container">
        <h2 class="section-title">
            <?php echo $current_category ? $current_category : 'All Tours'; ?>
        </h2>
        
        <!-- Category Filter Buttons -->
        <div style="text-align: center; margin-bottom: 30px; flex-wrap: wrap;">
            <a href="tours.php" class="btn-gold" style="padding: 8px 15px; font-size: 0.9rem; margin: 5px; display: inline-block; <?php echo !isset($_GET['category']) ? 'background: #1a4d2e; color: white;' : ''; ?>">All Tours</a>
            <?php
            $cats = $conn->query("SELECT * FROM tour_categories");
            while($c = $cats->fetch(PDO::FETCH_ASSOC)):
            ?>
            <a href="tours.php?category=<?php echo $c['id']; ?>" 
               class="btn-gold" 
               style="padding: 8px 15px; font-size: 0.9rem; margin: 5px; display: inline-block; background: <?php echo isset($_GET['category']) && $_GET['category'] == $c['id'] ? '#1a4d2e' : '#fff'; ?>; color: <?php echo isset($_GET['category']) && $_GET['category'] == $c['id'] ? '#fff' : '#1a4d2e'; ?>; border: 1px solid #1a4d2e;">
                <?php echo $c['name']; ?>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- Tours Grid -->
        <div class="tour-grid">
            <?php if(count($tours) > 0): ?>
                <?php foreach($tours as $row): ?>
                <div class="tour-card">
                    <img src="assets/images/uploads/<?php echo $row['image']; ?>" class="tour-img" alt="<?php echo $row['title']; ?>">
                    <div class="tour-info">
                        <h3><?php echo $row['title']; ?></h3>
                        <p><i class="far fa-clock"></i> <?php echo $row['duration']; ?></p>
                        <p class="tour-price">$<?php echo $row['price']; ?></p>
                        <a href="tour-detail.php?id=<?php echo $row['id']; ?>" class="btn-gold">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <h3 style="color: #666;">No tours found in this category</h3>
                    <p style="color: #999;">Check back soon for new adventures!</p>
                    <a href="tours.php" class="btn-gold" style="margin-top: 20px;">View All Tours</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
        <!-- Start of Tawk.to Script -->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/69bfe0640c264f1c349a5ad8/1jkao92a3';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!-- End of Tawk.to Script -->

    <footer><p>&copy; 2023 Elyos Ethiopia Tours</p></footer>
</body>
</html>
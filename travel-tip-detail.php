<?php 
require 'config.php'; 

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM travel_tips WHERE id = ? AND status='active'");
$stmt->execute([$id]);
$tip = $stmt->fetch();

if(!$tip) {
    header("Location: travel-tips.php");
    exit;
}

// Increment view count
$conn->query("UPDATE travel_tips SET views = views + 1 WHERE id = $id");

// Get related tips
$related_stmt = $conn->prepare("SELECT * FROM travel_tips WHERE category = ? AND id != ? AND status='active' ORDER BY RAND() LIMIT 3");
$related_stmt->execute([$tip['category'], $id]);
$related_tips = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($tip['title']); ?> - Elyos Ethiopia Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tip-detail-header {
            background: linear-gradient(135deg, #1a4d2e 0%, #0d2616 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        .tip-detail-header h1 {
            color: white;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }
        .tip-detail-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            color: #d4af37;
            flex-wrap: wrap;
        }
        .tip-detail-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tip-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        .tip-content h2 {
            color: var(--primary);
            margin: 30px 0 15px;
        }
        .tip-content p {
            line-height: 1.8;
            color: #333;
            margin-bottom: 20px;
        }
        .tip-content ul {
            margin: 20px 0;
            padding-left: 25px;
        }
        .tip-content li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .tip-category-badge {
            display: inline-block;
            background: var(--secondary);
            color: var(--dark);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .related-tips {
            margin-top: 60px;
        }
        .related-tips h3 {
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .tip-detail-header h1 {
                font-size: 1.8rem;
            }
            .tip-content {
                padding: 25px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-logo">ELYOS ETHIOPIA</div>
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-contact desktop-only">
            <span><i class="fas fa-phone"></i> <a href="tel:+251900000000" style="color: inherit;">+251 900 000 000</a></span>
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

    <!-- Header -->
    <div class="tip-detail-header">
        <span class="tip-category-badge"><?php echo htmlspecialchars($tip['category']); ?></span>
        <h1><?php echo htmlspecialchars($tip['title']); ?></h1>
        <div class="tip-detail-meta">
            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($tip['author']); ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($tip['publish_date'])); ?></span>
            <span><i class="fas fa-eye"></i> <?php echo $tip['views']; ?> views</span>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="tip-content">
            <?php echo nl2br($tip['content']); ?>
        </div>

        <!-- Related Tips -->
        <?php if(count($related_tips) > 0): ?>
        <div class="related-tips">
            <h3><i class="fas fa-bookmark"></i> Related Travel Tips</h3>
            <div class="tips-grid">
                <?php foreach($related_tips as $related): ?>
                <div class="tip-card" onclick="window.location.href='travel-tip-detail.php?id=<?php echo $related['id']; ?>'">
                    <div class="tip-info">
                        <span class="tip-category"><?php echo htmlspecialchars($related['category']); ?></span>
                        <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                        <p style="color: #666; font-size: 0.95rem;">
                            <?php echo substr(strip_tags($related['content']), 0, 100); ?>...
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Back to Tips -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="travel-tips.php" class="btn-gold">
                <i class="fas fa-arrow-left"></i> Back to All Tips
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Elyos Ethiopia Tours. All Rights Reserved.</p>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const hamburger = document.querySelector('.hamburger');
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        }
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
                document.querySelector('.hamburger').classList.remove('active');
            });
        });
    </script>
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
</body>
</html>
<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Travel Tips - Elyos Ethiopia Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .tip-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .tip-img {
            height: 200px;
            width: 100%;
            object-fit: cover;
            background: linear-gradient(135deg, #1a4d2e 0%, #0d2616 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .tip-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .tip-info {
            padding: 20px;
        }
        .tip-category {
            display: inline-block;
            background: var(--secondary);
            color: var(--dark);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .tip-info h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        .tip-meta {
            display: flex;
            justify-content: space-between;
            color: #999;
            font-size: 0.85rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .tips-filter {
            text-align: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .tips-filter a {
            display: inline-block;
            padding: 8px 20px;
            margin: 5px;
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 20px;
            text-decoration: none;
            transition: 0.3s;
        }
        .tips-filter a:hover,
        .tips-filter a.active {
            background: var(--primary);
            color: white;
        }
        @media (max-width: 768px) {
            .tips-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-logo">ELYOS ETHIOPIA</div>
        
        <!-- Hamburger Menu Icon (Mobile Only) -->
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
            <a href="travel-tips.php" class="active">Travel Tips</a>
            <a href="blog.php">Blog</a>
            <a href="contact.php">Contact</a>
            <a href="https://wa.me/251900000000" class="btn-book">Book Now</a>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="container" style="text-align: center; padding: 60px 20px;">
        <h1 class="section-title">Travel Tips</h1>
        <p style="color: #666; max-width: 600px; margin: 0 auto; line-height: 1.8;">
            Expert advice and insider knowledge to help you plan the perfect Ethiopian adventure. 
            From visa requirements to packing tips, we've got you covered!
        </p>
    </section>

    <!-- Tips Section -->
    <section class="container">
        <!-- Category Filter -->
        <div class="tips-filter">
            <a href="travel-tips.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All Tips</a>
            <?php
            $categories = $conn->query("SELECT DISTINCT category FROM travel_tips WHERE status='active'");
            while($cat = $categories->fetch(PDO::FETCH_ASSOC)):
            ?>
            <a href="travel-tips.php?category=<?php echo urlencode($cat['category']); ?>" 
               class="<?php echo isset($_GET['category']) && $_GET['category'] == $cat['category'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat['category']); ?>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- Tips Grid -->
        <div class="tips-grid">
            <?php
            // Build query with optional category filter
            $sql = "SELECT * FROM travel_tips WHERE status='active'";
            $params = [];
            
            if(isset($_GET['category']) && !empty($_GET['category'])) {
                $sql .= " AND category = ?";
                $params = [$_GET['category']];
            }
            
            $sql .= " ORDER BY sort_order, publish_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($tips) > 0):
                foreach($tips as $tip):
                    // Icon based on category
                    $icon = 'fa-map-marker-alt';
                    switch($tip['category']) {
                        case 'Planning': $icon = 'fa-calendar-alt'; break;
                        case 'Packing': $icon = 'fa-suitcase'; break;
                        case 'Money': $icon = 'fa-money-bill-wave'; break;
                        case 'Food': $icon = 'fa-utensils'; break;
                        case 'Health': $icon = 'fa-heartbeat'; break;
                        case 'Culture': $icon = 'fa-users'; break;
                    }
            ?>
            <div class="tip-card" onclick="window.location.href='travel-tip-detail.php?id=<?php echo $tip['id']; ?>'">
                <div class="tip-img">
                    <?php if(!empty($tip['featured_image']) && file_exists('assets/images/uploads/' . $tip['featured_image'])): ?>
                        <img src="assets/images/uploads/<?php echo $tip['featured_image']; ?>" alt="<?php echo htmlspecialchars($tip['title']); ?>">
                    <?php else: ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="tip-info">
                    <span class="tip-category"><?php echo htmlspecialchars($tip['category']); ?></span>
                    <h3><?php echo htmlspecialchars($tip['title']); ?></h3>
                    <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">
                        <?php echo substr(strip_tags($tip['content']), 0, 120); ?>...
                    </p>
                    <div class="tip-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($tip['author']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($tip['publish_date'])); ?></span>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <i class="fas fa-clipboard-list" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #666;">No travel tips found</h3>
                <p style="color: #999;">Check back soon for expert travel advice!</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="container" style="background: linear-gradient(135deg, #1a4d2e 0%, #0d2616 100%); padding: 60px 20px; border-radius: 10px; text-align: center; color: white; margin-top: 60px;">
        <h2 style="color: white; margin-bottom: 20px;">Need Personalized Travel Advice?</h2>
        <p style="color: #eee; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Our travel experts are here to help you plan your perfect Ethiopian journey. Contact us today!
        </p>
        <a href="https://wa.me/251900000000" class="btn-gold" style="font-size: 1.1rem;">
            <i class="fab fa-whatsapp"></i> Chat with an Expert
        </a>
    </section>

    

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Elyos Ethiopia Tours. All Rights Reserved.</p>
    </footer>

    <!-- Mobile Menu Toggle Script -->
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
<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fully Licensed & Verified - Elyos Ethiopia Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .licenses-header {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 80px 20px;
            text-align: center;
            border-bottom: 4px solid var(--secondary);
        }
        .licenses-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .licenses-header p {
            color: #555;
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            font-weight: 500;
            color: var(--primary);
        }
        .trust-badge i { color: #4caf50; font-size: 1.2rem; }
        
        .licenses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        .license-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
            border: 1px solid #eee;
        }
        .license-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .license-image {
            height: 220px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .license-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
            transition: 0.3s;
        }
        .license-image:hover img { transform: scale(1.05); }
        .license-image .zoom-icon {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: var(--secondary);
            color: var(--dark);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            opacity: 0;
            transition: 0.3s;
        }
        .license-image:hover .zoom-icon { opacity: 1; }
        .license-info { padding: 25px; }
        .license-type {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .license-info h3 { color: var(--primary); margin-bottom: 10px; font-size: 1.2rem; }
        .license-info p { color: #666; line-height: 1.6; font-size: 0.95rem; }
        .license-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #999;
        }
        
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .lightbox.active { display: flex; }
        .lightbox-content { max-width: 90%; max-height: 90%; position: relative; }
        .lightbox-content img {
            max-width: 100%;
            max-height: 85vh;
            border-radius: 10px;
        }
        .lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        .lightbox-close:hover { color: var(--secondary); }
        .lightbox-caption { color: white; text-align: center; margin-top: 15px; font-size: 1.1rem; }
        
        .trust-section {
            background: linear-gradient(135deg, #1a4d2e 0%, #0d2616 100%);
            padding: 60px 20px;
            text-align: center;
            color: white;
            margin-top: 60px;
            border-radius: 15px;
        }
        .trust-section h2 { color: white; margin-bottom: 20px; }
        .trust-section p { color: #e0e0e0; max-width: 600px; margin: 0 auto 30px; line-height: 1.6; }
        
        .no-content {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        .no-content i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .licenses-header { padding: 50px 15px; }
            .licenses-header h1 { font-size: 1.8rem; }
            .trust-badges { flex-direction: column; align-items: center; }
            .licenses-grid { grid-template-columns: 1fr; }
            .license-image { height: 180px; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-logo">ELYOS ETHIOPIA</div>
        <div class="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
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
    <header class="licenses-header">
        <h1><i class="fas fa-shield-alt"></i> Fully Licensed & Verified</h1>
        <p>We're fully licensed & verified so you can relax and enjoy your Ethiopian adventure with complete peace of mind.</p>
        <div class="trust-badges">
            <div class="trust-badge"><i class="fas fa-check-circle"></i> Government Registered</div>
            <div class="trust-badge"><i class="fas fa-check-circle"></i> Insured & Protected</div>
            <div class="trust-badge"><i class="fas fa-check-circle"></i> Industry Certified</div>
        </div>
    </header>

    <!-- Licenses Grid -->
    <section class="container">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 40px;">Our Official Documents</h2>
        
        <div class="licenses-grid">
            <?php
            $licenses_stmt = $conn->query("SELECT * FROM licenses WHERE status='active' ORDER BY sort_order");
            while($license = $licenses_stmt->fetch(PDO::FETCH_ASSOC)):
                $badge_color = '#e3f2fd';
                $badge_text = '#1976d2';
                switch($license['document_type']) {
                    case 'certification': $badge_color = '#e8f5e9'; $badge_text = '#388e3c'; break;
                    case 'registration': $badge_color = '#fff3e0'; $badge_text = '#ef6c00'; break;
                    case 'insurance': $badge_color = '#fce4ec'; $badge_text = '#c2185b'; break;
                }
            ?>
            <div class="license-card">
                <div class="license-image" onclick="openLightbox('<?php echo htmlspecialchars($license['image']); ?>', '<?php echo htmlspecialchars($license['title']); ?>')">
                    <?php if(!empty($license['image']) && file_exists('assets/images/uploads/' . $license['image'])): ?>
                        <img src="assets/images/uploads/<?php echo htmlspecialchars($license['image']); ?>" alt="<?php echo htmlspecialchars($license['title']); ?>">
                        <div class="zoom-icon"><i class="fas fa-search-plus"></i></div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-file-alt" style="font-size: 4rem; color: #ccc; margin-bottom: 10px;"></i>
                            <p style="color: #999; font-size: 0.9rem;">No image uploaded</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="license-info">
                    <span class="license-type" style="background: <?php echo $badge_color; ?>; color: <?php echo $badge_text; ?>;">
                        <?php echo ucfirst(htmlspecialchars($license['document_type'])); ?>
                    </span>
                    <h3><?php echo htmlspecialchars($license['title']); ?></h3>
                    <p><?php echo htmlspecialchars($license['description']); ?></p>
                    <div class="license-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($license['uploaded_at'])); ?></span>
                        <span><i class="fas fa-check-circle"></i> Verified</span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Trust Section -->
    <section class="container">
        <div class="trust-section">
            <h2><i class="fas fa-heart"></i> Your Safety Is Our Priority</h2>
            <p>Every tour we operate is backed by official licenses, comprehensive insurance, and years of trusted service.</p>
            
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 30px;">
                <a href="contact.php" class="btn-gold" style="background: white; color: var(--primary);">
                    <i class="fas fa-question-circle"></i> Have Questions? Contact Us
                </a>
                <a href="index.php" class="btn-gold" style="background: transparent; border: 2px solid white; color: white;">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </section>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img id="lightbox-img" src="" alt="License Document">
            <div class="lightbox-caption" id="lightbox-caption"></div>
        </div>
    </div>

    <!-- Tawk.to (if exists) -->
    <?php if(file_exists('includes/tawk-chat.php')): ?>
        <?php include 'includes/tawk-chat.php'; ?>
    <?php endif; ?>

    <footer><p>&copy; 2023 Elyos Ethiopia Tours. All Rights Reserved.</p></footer>

    <script>
        function toggleMenu() {
            document.getElementById('navLinks').classList.toggle('active');
            document.querySelector('.hamburger').classList.toggle('active');
        }
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
                document.querySelector('.hamburger').classList.remove('active');
            });
        });
        
        function openLightbox(imageSrc, caption) {
            if(imageSrc) {
                document.getElementById('lightbox-img').src = 'assets/images/uploads/' + imageSrc;
                document.getElementById('lightbox-caption').textContent = caption;
                document.getElementById('lightbox').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>
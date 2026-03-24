<?php 
require 'config.php'; 
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->execute([$id]);
$tour = $stmt->fetch();

// Get all images for this tour
$img_stmt = $conn->prepare("SELECT * FROM tour_images WHERE tour_id=? ORDER BY sort_order");
$img_stmt->execute([$id]);
$images = $img_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $tour['title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 10px;
        }
        .slide {
            display: none;
            width: 100%;
            height: 100%;
        }
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slide.active {
            display: block;
        }
        .slideshow-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        .slideshow-nav button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
            background: transparent;
            cursor: pointer;
        }
        .slideshow-nav button.active {
            background: white;
        }
        .slideshow-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            padding: 15px;
            cursor: pointer;
            font-size: 18px;
        }
        .slideshow-arrow.prev { left: 10px; }
        .slideshow-arrow.next { right: 10px; }
    </style>
</head>
<body>
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

    <div class="container">
        <!-- Slideshow -->
        <?php if(count($images) > 0): ?>
        <div class="slideshow-container">
            <button class="slideshow-arrow prev" onclick="changeSlide(-1)">&#10094;</button>
            <button class="slideshow-arrow next" onclick="changeSlide(1)">&#10095;</button>
            
            <?php foreach($images as $index => $img): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="assets/images/uploads/<?php echo $img['image']; ?>" alt="<?php echo $tour['title']; ?>">
            </div>
            <?php endforeach; ?>
            
            <div class="slideshow-nav">
                <?php foreach($images as $index => $img): ?>
                <button onclick="currentSlide(<?php echo $index; ?>)" class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <img src="assets/images/uploads/<?php echo $tour['image']; ?>" style="width:100%; height:400px; object-fit:cover; border-radius:10px;">
        <?php endif; ?>
        
        <h1 style="color:var(--primary); margin-top:20px;"><?php echo $tour['title']; ?></h1>
        <h3 style="color:var(--secondary);">$<?php echo $tour['price']; ?> | <?php echo $tour['duration']; ?></h3>
        <hr>
        <div style="margin-top:20px; line-height:1.6;">
            <h4>Description</h4>
            <p><?php echo nl2br($tour['description']); ?></p>
            
            <h4>Itinerary</h4>
            <div style="background:#f4f4f4; padding:20px; border-radius:5px;">
                <?php echo nl2br($tour['itinerary']); ?>
            </div>
        </div>
        
        <div style="margin-top:30px; text-align:center;">
            <a href="https://wa.me/251900000000?text=I am interested in <?php echo urlencode($tour['title']); ?>" class="btn-gold">Book via WhatsApp</a>
        </div>
    </div>

    <script>
        let slideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slideshow-nav button');
        
        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            slideIndex = n;
            if (slideIndex >= slides.length) slideIndex = 0;
            if (slideIndex < 0) slideIndex = slides.length - 1;
            
            slides[slideIndex].classList.add('active');
            dots[slideIndex].classList.add('active');
        }
        
        function changeSlide(n) {
            showSlide(slideIndex + n);
        }
        
        function currentSlide(n) {
            showSlide(n);
        }
        
        // Auto advance slides every 5 seconds
        setInterval(() => {
            changeSlide(1);
        }, 5000);
    </script>

    <footer><p>&copy; 2023 Elyos Ethiopia Tours</p></footer>
</body>
</html>
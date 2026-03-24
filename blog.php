<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog - Elyos Ethiopia</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <section class="container">
        <h2 class="section-title">Travel Blog</h2>
        <div class="tour-grid">
            <?php
            $stmt = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
            if($stmt->rowCount() > 0):
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="tour-card">
                <div class="tour-info">
                    <h3><?php echo $row['title']; ?></h3>
                    <p><?php echo substr($row['content'], 0, 100); ?>...</p>
                    <a href="#" class="btn-gold">Read More</a>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p>No blog posts yet.</p>
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
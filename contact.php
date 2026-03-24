<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Elyos Ethiopia</title>
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
        <h2 class="section-title">Contact Us</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: var(--primary);">Get In Touch</h3>
                <div style="margin-bottom: 20px;"><i class="fas fa-phone" style="color: var(--secondary);"></i> +251 900 000 000</div>
                <div style="margin-bottom: 20px;"><i class="fas fa-envelope" style="color: var(--secondary);"></i> info@elyosethiopia.com</div>
                <div style="margin-bottom: 20px;"><i class="fas fa-map-marker-alt" style="color: var(--secondary);"></i> Addis Ababa, Ethiopia</div>
            </div>
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: var(--primary);">Send Message</h3>
                <form action="https://wa.me/251900000000" method="get" target="_blank">
                    <input type="text" name="text" placeholder="Your Message" style="width: 100%; padding: 10px; margin-bottom: 10px;" required>
                    <button type="submit" class="btn-gold" style="width: 100%; border: none; cursor: pointer;">Send via WhatsApp</button>
                </form>
            </div>
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
        </div>
    </section>
    <footer><p>&copy; 2023 Elyos Ethiopia Tours</p></footer>
</body>
</html>
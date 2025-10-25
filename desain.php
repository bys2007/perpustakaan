<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Playground</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="cursor-dot"></div>
    <div class="cursor-outline"></div>

    <header class="hero">
        <div class="hero-text">
            <h1>Halo, Selamat Datang!</h1>
            <p>Ini adalah ruang di mana ide-ide ceria tumbuh dan berkembang. Mari kita ciptakan sesuatu yang menyenangkan bersama!</p>
            <a href="#contact" class="cta-button">Mulai Proyek</a>
        </div>
        <div class="hero-doodle">
            <svg width="200" height="200" viewBox="0 0 100 100">
                <path d="M 20,50 Q 50,20 80,50 T 50,80 T 20,50" fill="none" stroke="#FFDAB9" stroke-width="5" stroke-linecap="round"/>
                <circle cx="35" cy="40" r="4" fill="#6A5ACD"/>
                <circle cx="65" cy="40" r="4" fill="#6A5ACD"/>
                <path d="M 40,60 Q 50,70 60,60" fill="none" stroke="#6A5ACD" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>
    </header>

    <main>
        <section id="services" class="content-section">
            <h2>Apa yang Kita Lakukan?</h2>
            <div class="card-container">
                <div class="card">
                    <h3>🎨 Desain Grafis</h3>
                    <p>Membuat visual yang imut dan menarik perhatian.</p>
                </div>
                <div class="card">
                    <h3>✨ Ilustrasi Unik</h3>
                    <p>Goresan tangan digital yang penuh karakter.</p>
                </div>
                <div class="card">
                    <h3>🚀 Web Development</h3>
                    <p>Membangun website yang tidak hanya berfungsi, tapi juga seru.</p>
                </div>
            </div>
        </section>

        <section id="about" class="content-section">
            <h2>Sedikit Tentang Kami</h2>
            <p>Kami percaya bahwa setiap proyek adalah sebuah petualangan. Dengan sentuhan kreativitas dan secangkir teh hangat, kami siap membantu mewujudkan imajinasi Anda menjadi kenyataan yang indah dan fungsional.</p>
        </section>
        
        <section id="contact" class="content-section">
             <h2>Yuk, Ngobrol!</h2>
             <p>Punya ide seru? Jangan ragu untuk menyapa kami. Kami sangat senang mendengar ceritamu!</p>
             <a href="mailto:halo@domain.com" class="cta-button">Kirim Email</a>
        </section>
    </main>

    <nav class="bottom-nav">
        <a href="#">Beranda</a>
        <a href="#services">Layanan</a>
        <a href="#about">Tentang</a>
        <a href="#contact">Kontak</a>
    </nav>

    <script src="assets/script.js"></script>
</body>
</html>
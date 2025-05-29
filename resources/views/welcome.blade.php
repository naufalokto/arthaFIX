<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solusi-Digital Artha Makmur Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            color: #222;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }
        header {
            padding: 24px 48px;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 18px 18px;
        }
        header .nav {
            display: flex;
            gap: 32px;
        }
        header .nav a {
            text-decoration: none;
            color: #374151;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.2s;
        }
        header .nav a:hover {
            color: #2563eb;
        }
        .btn-primary {
            padding: 12px 28px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
            transition: background 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.12);
        }
        .hero {
            padding: 100px 40px 60px 40px;
            text-align: center;
            background: linear-gradient(120deg, #f1f5f9 60%, #dbeafe 100%);
            border-radius: 0 0 32px 32px;
            box-shadow: 0 8px 32px rgba(59,130,246,0.06);
        }
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 18px;
            letter-spacing: -1px;
        }
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 32px;
            color: #64748b;
        }
        .section {
            padding: 60px 40px 40px 40px;
            text-align: center;
        }
        .section h2 {
            font-size: 2rem;
            margin-bottom: 36px;
            color: #2563eb;
            font-weight: 700;
        }
        .features {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
        }
        .feature-box {
            border: 1.5px solid #dbeafe;
            border-radius: 14px;
            padding: 32px 24px;
            width: 260px;
            background: #fff;
            box-shadow: 0 2px 16px rgba(59,130,246,0.04);
            transition: transform 0.18s, box-shadow 0.18s;
        }
        .feature-box:hover {
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 8px 32px rgba(59,130,246,0.10);
        }
        .feature-box strong {
            display: block;
            font-size: 1.18rem;
            color: #1d4ed8;
            margin-bottom: 10px;
        }
        .feature-box p {
            color: #475569;
            font-size: 1rem;
        }
        footer {
            padding: 36px 40px 24px 40px;
            background: #f1f5f9;
            text-align: center;
            font-size: 1rem;
            color: #64748b;
            border-radius: 24px 24px 0 0;
            margin-top: 60px;
            box-shadow: 0 -2px 12px rgba(59,130,246,0.04);
        }
        @media (max-width: 900px) {
            .features {
                flex-direction: column;
                align-items: center;
            }
            header, .hero, .section, footer {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
        .alert-success {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.95);
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 0.95rem;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeOut 5s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body>

@if(session('message'))
<div class="alert-success">
    {{ session('message') }}
</div>
@endif

<header>
    <div><strong>Solusi-Digital Artha Makmur Jaya</strong></div>
    <nav class="nav">
        <a href="{{ route('about') }}">Tentang Kami</a>
        <a href="{{ route('products') }}">Produk</a>
    </nav>
    <a href="{{ route('login') }}" class="btn-primary">Login</a>
</header>

<section class="hero">
    <h1>Solusi Digital untuk Bisnis Anda</h1>
    <p>Temukan berbagai layanan digital kami untuk mengembangkan bisnis Anda.</p>
    <a href="{{ route('signup') }}" class="btn-primary">Daftar Sekarang</a>
</section>

<section class="section">
    <h2>Mengapa Memilih Kami?</h2>
    <div class="features">
        <div class="feature-box">
            <strong>Kualitas Terbaik</strong>
            <p>Dibangun dengan standar tertinggi untuk performa konsisten.</p>
        </div>
        <div class="feature-box">
            <strong>Ragam Layanan</strong>
            <p>Solusi untuk setiap kebutuhan bisnis Anda.</p>
        </div>
        <div class="feature-box">
            <strong>Dukungan Ahli</strong>
            <p>Tim kami siap membantu Anda menemukan solusi terbaik.</p>
        </div>
    </div>
</section>

<footer>
    <p>© 2024 Solusi-Digital Artha Makmur Jaya. Hak cipta dilindungi.</p>
</footer>

</body>
</html>
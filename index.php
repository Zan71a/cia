<?php
require_once __DIR__ . '/includes.php';
$content = load_content();
$site = $content['site'] ?? [];
$hero = $content['hero'] ?? [];
$korelasi = $content['korelasi'] ?? ['items' => []];
$portal = $content['portal'] ?? ['items' => []];
$berita = $content['berita'] ?? ['items' => []];
$tim = $content['tim'] ?? ['items' => []];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($site['title'] ?? 'BHINEKA.SPACE') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= e($site['favicon'] ?? 'bhineka.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] },
                    colors: {
                        navy: { 50: '#F0F4F8', 100: '#D9E2EC', 800: '#102A43', 900: '#0B1D33', 950: '#050D1A' },
                        accent: { orange: '#FF5E13', sunset: '#FF8C32', peach: '#FFD3B6' }
                    },
                    boxShadow: {
                        'neon-orange': '0 0 20px rgba(255, 94, 19, 0.3)',
                        'neon-blue': '0 0 20px rgba(56, 189, 248, 0.25)'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes pulse-slow { 0%, 100% { transform: scale(1); opacity: .2; } 50% { transform: scale(1.05); opacity: .35; } }
        .animate-pulse-slow { animation: pulse-slow 8s cubic-bezier(.4,0,.6,1) infinite; }
        .glassmorphism { background: rgba(11,29,51,.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08); }
        .orange-gradient-text { background: linear-gradient(135deg,#fff 30%,#FF5E13 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        ::-webkit-scrollbar { width: 8px; } ::-webkit-scrollbar-track { background: #050D1A; } ::-webkit-scrollbar-thumb { background: #FF5E13; border-radius: 4px; } ::-webkit-scrollbar-thumb:hover { background: #FF8C32; }
        .scroll-animate { opacity: 0; transform: translate3d(0, 45px, 0); transition: opacity .85s cubic-bezier(.2,.8,.2,1), transform .85s cubic-bezier(.2,.8,.2,1); will-change: opacity, transform; }
        .scroll-animate.is-visible { opacity: 1; transform: translate3d(0,0,0) scale(1); }
        .scroll-animate[data-animate="fade-left"] { transform: translate3d(55px,45px,0); }
        .scroll-animate[data-animate="fade-right"] { transform: translate3d(-55px,45px,0); }
        .scroll-animate[data-animate="zoom-in"] { transform: translate3d(0,35px,0) scale(.94); }
        .scroll-animate.is-visible[data-animate="fade-left"], .scroll-animate.is-visible[data-animate="fade-right"], .scroll-animate.is-visible[data-animate="zoom-in"] { transform: translate3d(0,0,0) scale(1); }
        #navbar.scrolled { background: rgba(5,13,26,.88); box-shadow: 0 12px 35px rgba(0,0,0,.28); border-bottom-color: rgba(255,94,19,.18); }
        #navbar a.active-link { color: #FF5E13; }
        .story-parallax { will-change: transform; }
        @media (prefers-reduced-motion: reduce) { .scroll-animate, .scroll-animate.is-visible, .story-parallax { transition: none !important; animation: none !important; transform: none !important; opacity: 1 !important; } }
    </style>
</head>
<body class="bg-navy-950 text-gray-200 font-sans selection:bg-accent-orange selection:text-white overflow-x-hidden">
    <div id="scroll-progress" class="fixed top-0 left-0 h-1 w-0 z-[9999] bg-gradient-to-r from-accent-orange via-accent-sunset to-sky-400 shadow-neon-orange transition-[width] duration-75"></div>

    <nav id="navbar" class="fixed top-0 left-0 w-full z-50 glassmorphism transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div>
                    <span class="text-xl font-extrabold tracking-wider text-white"><?= e($site['brand_main'] ?? 'BHINEKA') ?><span class="text-accent-orange"><?= e($site['brand_accent'] ?? 'SPACE') ?></span></span>
                    <p class="text-[9px] text-gray-400 tracking-widest uppercase -mt-1"><?= e($site['tagline'] ?? '') ?></p>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#beranda" class="text-sm font-semibold text-gray-300 hover:text-accent-orange transition-colors duration-200">Beranda</a>
                    <a href="#korelasi" class="text-sm font-semibold text-gray-300 hover:text-accent-orange transition-colors duration-200">Filosofi & Teknologi</a>
                    <a href="#portal" class="text-sm font-semibold text-gray-300 hover:text-accent-orange transition-colors duration-200">Eksplorasi VR</a>
                    <a href="#berita" class="text-sm font-semibold text-gray-300 hover:text-accent-orange transition-colors duration-200">Kabar Digital</a>
                    <a href="#tim" class="text-sm font-semibold text-gray-300 hover:text-accent-orange transition-colors duration-200">Tim Kami</a>
                </div>
                <div class="hidden md:flex items-center gap-3">
                    <a href="#portal" class="px-5 py-2.5 rounded-full text-xs font-semibold bg-gradient-to-r from-accent-orange to-accent-sunset text-white hover:shadow-neon-orange hover:scale-105 transition-all duration-300"><i class="fa-solid fa-cube mr-2"></i>Mulai Tur Virtual</a>
                    <a href="admin/login.php" class="px-4 py-2.5 rounded-full text-xs font-semibold border border-white/10 text-gray-300 hover:text-white hover:border-accent-orange/50 transition-all"><i class="fa-solid fa-lock mr-2"></i>Admin</a>
                </div>
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white focus:outline-none"><i class="fa-solid fa-bars text-2xl"></i></button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden glassmorphism px-4 pt-2 pb-6 space-y-3">
            <a href="#beranda" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:bg-white/5 hover:text-accent-orange">Beranda</a>
            <a href="#korelasi" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:bg-white/5 hover:text-accent-orange">Filosofi & Teknologi</a>
            <a href="#portal" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:bg-white/5 hover:text-accent-orange">Eksplorasi VR</a>
            <a href="#berita" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:bg-white/5 hover:text-accent-orange">Kabar Digital</a>
            <a href="#tim" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-300 hover:bg-white/5 hover:text-accent-orange">Tim Kami</a>
            <a href="admin/login.php" class="block px-3 py-2 rounded-lg text-base font-medium text-accent-orange hover:bg-white/5">Login Admin</a>
        </div>
    </nav>

    <section id="beranda" class="relative min-h-screen flex items-center justify-center pt-24 overflow-hidden">
        <div class="story-parallax absolute inset-0 z-0 bg-[radial-gradient(circle_at_top_right,rgba(255,94,19,0.15),transparent_50%),radial-gradient(circle_at_bottom_left,rgba(56,189,248,0.1),transparent_50%)]" data-speed="0.08"></div>
        <div class="absolute w-[500px] h-[500px] md:w-[800px] md:h-[800px] rounded-full border border-accent-orange/10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none animate-pulse-slow"></div>
        <div class="absolute w-[300px] h-[300px] md:w-[500px] md:h-[500px] rounded-full border border-dashed border-sky-500/10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none animate-spin" style="animation-duration:40s;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-12 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glassmorphism text-xs font-medium text-accent-orange mb-6 border-accent-orange/20 shadow-neon-orange/10"><span class="w-2 h-2 rounded-full bg-accent-orange animate-ping"></span><?= e($hero['badge'] ?? '') ?></div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight mb-6 leading-none">
                <span class="block text-white font-serif italic mb-2"><?= e($hero['title_top'] ?? '') ?></span>
                <span class="block orange-gradient-text uppercase font-sans tracking-wide"><?= e($hero['title_main'] ?? '') ?></span>
            </h1>
            <p class="max-w-3xl mx-auto text-base sm:text-xl text-gray-300 mb-10 leading-relaxed font-light"><?= nl_to_br($hero['description'] ?? '') ?></p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= e($hero['primary_link'] ?? '#korelasi') ?>" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold bg-white text-navy-950 hover:bg-accent-orange hover:text-white transition-all duration-300 shadow-lg"><?= e($hero['primary_text'] ?? 'Pelajari') ?> <i class="fa-solid fa-arrow-down ml-2"></i></a>
                <a href="<?= e($hero['secondary_link'] ?? '#portal') ?>" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold glassmorphism text-accent-orange hover:bg-white/5 border border-accent-orange/30 hover:border-accent-orange hover:shadow-neon-orange transition-all duration-300"><i class="fa-solid fa-vr-cardboard mr-2"></i><?= e($hero['secondary_text'] ?? 'Eksplorasi VR') ?></a>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-65 hover:opacity-100 transition-opacity"><span class="text-[10px] tracking-widest text-gray-400 uppercase">Scroll untuk menjelajah</span><div class="w-6 h-10 rounded-full border-2 border-gray-500 flex justify-center p-1"><div class="w-1.5 h-3 bg-accent-orange rounded-full animate-bounce"></div></div></div>
    </section>

    <section id="korelasi" class="py-24 relative bg-navy-900 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16"><h3 class="text-3xl sm:text-4xl font-bold text-white font-serif italic"><?= e($korelasi['title'] ?? '') ?></h3><div class="w-24 h-1 bg-gradient-to-r from-accent-orange to-sky-400 mx-auto mt-4 rounded-full"></div></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <?php foreach (($korelasi['items'] ?? []) as $item): $isBlue = ($item['color'] ?? '') === 'blue'; ?>
                <div class="glassmorphism p-8 sm:p-10 rounded-2xl relative overflow-hidden group hover:border-<?= $isBlue ? 'sky-500' : 'accent-orange' ?>/30 transition-all duration-300 flex flex-col justify-between">
                    <div class="absolute -right-8 -bottom-8 text-white/5 text-9xl font-bold select-none"><?= e($item['number'] ?? '') ?></div>
                    <div>
                        <div class="w-12 h-12 rounded-xl <?= $isBlue ? 'bg-sky-500/10 text-sky-400 border-sky-500/30' : 'bg-accent-orange/10 text-accent-orange border-accent-orange/30' ?> flex items-center justify-center mb-6 border"><i class="<?= e($item['icon'] ?? 'fa-solid fa-star') ?> text-2xl"></i></div>
                        <h4 class="text-xl font-bold text-white mb-4"><?= e($item['title'] ?? '') ?></h4>
                        <p class="text-gray-400 leading-relaxed text-sm"><?= nl_to_br($item['description'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="portal" class="py-24 relative overflow-hidden">
        <div class="story-parallax absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(255,94,19,0.06),transparent_60%)]" data-speed="0.05"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16"><h2 class="text-sm font-semibold tracking-wider text-accent-orange uppercase mb-2"><?= e($portal['eyebrow'] ?? '') ?></h2><h3 class="text-3xl sm:text-4xl font-bold text-white font-serif"><?= e($portal['title'] ?? '') ?></h3><p class="text-gray-400 text-sm mt-3"><?= e($portal['subtitle'] ?? '') ?></p><div class="w-24 h-1 bg-gradient-to-r from-accent-orange to-sky-400 mx-auto mt-4 rounded-full"></div></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <?php foreach (($portal['items'] ?? []) as $item): $isBlue = ($item['color'] ?? '') === 'blue'; ?>
                <div class="glassmorphism rounded-3xl overflow-hidden border border-white/10 group hover:border-<?= $isBlue ? 'sky-400' : 'accent-orange' ?>/40 transition-all duration-300 shadow-lg flex flex-col h-full">
                    <div class="h-64 overflow-hidden relative">
                        <img src="<?= e(asset_url($item['image'] ?? '')) ?>" alt="<?= e($item['title'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.onerror=null; this.src='<?= e($item['fallback'] ?? 'https://placehold.co/800x600/000/fff?text=BHINEKA.SPACE') ?>';">
                        <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/40 to-transparent"></div>
                        <div class="absolute top-4 right-4 <?= $isBlue ? 'bg-sky-500/90' : 'bg-accent-orange/90' ?> text-white text-xs font-extrabold px-3 py-1.5 rounded-full uppercase tracking-widest shadow-lg"><?= e($item['category'] ?? '') ?></div>
                    </div>
                    <div class="p-8 flex flex-col flex-grow">
                        <h4 class="text-2xl font-serif font-bold text-white mb-3"><?= e($item['title'] ?? '') ?></h4>
                        <p class="text-gray-400 text-sm leading-relaxed mb-6 flex-grow"><?= nl_to_br($item['description'] ?? '') ?></p>
                        <a href="<?= e($item['button_link'] ?? '#') ?>" target="_blank" rel="noopener noreferrer" class="w-full py-4 rounded-xl font-bold <?= $isBlue ? 'bg-gradient-to-r from-sky-600 to-sky-500 hover:shadow-neon-blue' : 'bg-gradient-to-r from-accent-orange to-accent-sunset hover:shadow-neon-orange' ?> text-white hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2"><i class="fa-solid fa-vr-cardboard text-lg"></i> <?= e($item['button_text'] ?? 'Buka Portal VR') ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="berita" class="py-24 relative bg-navy-900 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14"><h2 class="text-sm font-semibold tracking-wider text-accent-orange uppercase mb-2"><?= e($berita['eyebrow'] ?? '') ?></h2><h3 class="text-3xl sm:text-4xl font-bold text-white font-serif"><?= e($berita['title'] ?? '') ?></h3><p class="text-gray-400 text-sm mt-3"><?= e($berita['subtitle'] ?? '') ?></p><div class="w-24 h-1 bg-gradient-to-r from-accent-orange to-sky-400 mx-auto mt-4 rounded-full"></div></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <?php foreach (($berita['items'] ?? []) as $item): ?>
                <article class="glassmorphism rounded-2xl p-7 border border-white/10 hover:border-accent-orange/40 transition-all duration-300 group">
                    <p class="text-xs text-accent-orange font-bold tracking-widest uppercase mb-3"><?= e($item['date'] ?? '') ?></p>
                    <h4 class="text-xl font-bold text-white mb-3 group-hover:text-accent-orange transition-colors"><?= e($item['title'] ?? '') ?></h4>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6"><?= nl_to_br($item['description'] ?? '') ?></p>
                    <a href="<?= e($item['link'] ?? '#') ?>" class="text-sm font-bold text-sky-400 hover:text-accent-orange transition-colors">Baca selengkapnya <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="tim" class="py-20 relative overflow-hidden">
        <div class="story-parallax absolute inset-0 z-0 bg-[radial-gradient(circle_at_bottom,rgba(56,189,248,0.06),transparent_60%)]" data-speed="0.04"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-12"><h3 class="text-3xl sm:text-4xl font-bold text-white font-serif"><?= e($tim['title'] ?? '') ?></h3><div class="w-16 h-1 bg-gradient-to-r from-accent-orange to-sky-400 mx-auto mt-4 rounded-full"></div></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 max-w-6xl mx-auto">
                <?php foreach (($tim['items'] ?? []) as $member): ?>
                <div class="glassmorphism rounded-2xl p-6 text-center border border-white/10 hover:border-accent-orange/40 transition-all duration-300 group">
                    <div class="w-24 h-24 rounded-full mx-auto overflow-hidden border-2 border-accent-orange p-0.5 bg-navy-950 mb-4 transition-transform group-hover:scale-105"><img src="<?= e(asset_url($member['image'] ?? '')) ?>" alt="<?= e($member['name'] ?? '') ?>" class="w-full h-full object-cover rounded-full" onerror="this.onerror=null; this.src='https://placehold.co/300x300/050D1A/FFFFFF?text=User';"></div>
                    <p class="text-[11px] uppercase tracking-widest text-accent-orange mb-1"><?= e($member['role'] ?? '') ?></p>
                    <h4 class="text-base font-bold text-white tracking-wide mb-3"><?= e($member['name'] ?? '') ?></h4>
                    <div class="flex items-center justify-center"><a href="<?= e($member['instagram'] ?? '#') ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-white/5 hover:bg-gradient-to-tr hover:from-pink-500 hover:to-orange-400 hover:text-white flex items-center justify-center text-gray-400 transition-all duration-300" aria-label="Instagram <?= e($member['name'] ?? '') ?>"><i class="fa-brands fa-instagram text-base"></i></a></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="bg-navy-950 text-gray-500 py-12 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="flex flex-col md:flex-row items-center justify-between gap-6"><span class="text-sm font-bold text-white tracking-widest uppercase"><?= e($site['brand_main'] ?? 'BHINEKA') ?><span class="text-accent-orange"><?= e($site['brand_accent'] ?? 'SPACE') ?></span></span><p class="text-xs text-gray-500 text-center md:text-right"><?= e($site['footer'] ?? '') ?></p></div></div>
    </footer>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const navbar = document.getElementById('navbar');
        const scrollProgress = document.getElementById('scroll-progress');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (mobileMenuBtn && mobileMenu) { mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden')); mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => mobileMenu.classList.add('hidden'))); }
        const updateScrollUI = () => { const scrollTop = window.scrollY || document.documentElement.scrollTop; const docHeight = document.documentElement.scrollHeight - window.innerHeight; const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0; if (scrollProgress) scrollProgress.style.width = `${progress}%`; if (navbar) navbar.classList.toggle('scrolled', scrollTop > 24); };
        window.addEventListener('scroll', updateScrollUI, { passive: true }); updateScrollUI();
        const revealGroups = [
            { selector: '#beranda .inline-flex, #beranda h1, #beranda p, #beranda .flex.flex-col', animate: 'zoom-in' },
            { selector: '#korelasi .text-center, #portal .text-center, #berita .text-center, #tim .text-center', animate: 'zoom-in' },
            { selector: '#korelasi .grid > div:nth-child(odd), #portal .grid > div:nth-child(odd), #berita .grid > article:nth-child(odd), #tim .grid > div:nth-child(odd)', animate: 'fade-right' },
            { selector: '#korelasi .grid > div:nth-child(even), #portal .grid > div:nth-child(even), #berita .grid > article:nth-child(even), #tim .grid > div:nth-child(even)', animate: 'fade-left' },
            { selector: 'footer .flex', animate: 'zoom-in' }
        ];
        revealGroups.forEach((group) => { document.querySelectorAll(group.selector).forEach((el, index) => { el.classList.add('scroll-animate'); el.dataset.animate = group.animate; el.style.transitionDelay = `${Math.min(index * 110, 440)}ms`; }); });
        const revealItems = document.querySelectorAll('.scroll-animate');
        if (prefersReducedMotion) { revealItems.forEach((el) => el.classList.add('is-visible')); } else { const revealObserver = new IntersectionObserver((entries) => { entries.forEach((entry) => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); revealObserver.unobserve(entry.target); } }); }, { threshold: .15, rootMargin: '0px 0px -70px 0px' }); revealItems.forEach((el) => revealObserver.observe(el)); }
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav a[href^="#"]');
        const setActiveLink = () => { let currentId = ''; sections.forEach((section) => { const sectionTop = section.offsetTop - 140; const sectionBottom = sectionTop + section.offsetHeight; if (window.scrollY >= sectionTop && window.scrollY < sectionBottom) currentId = section.id; }); navLinks.forEach((link) => { const linkHash = link.getAttribute('href')?.replace('#', ''); link.classList.toggle('active-link', linkHash === currentId); }); };
        window.addEventListener('scroll', setActiveLink, { passive: true }); setActiveLink();
        const parallaxItems = document.querySelectorAll('.story-parallax');
        const updateParallax = () => { if (prefersReducedMotion) return; const y = window.scrollY || 0; parallaxItems.forEach((item) => { const speed = parseFloat(item.dataset.speed || '0.04'); item.style.transform = `translate3d(0, ${y * speed}px, 0)`; }); };
        window.addEventListener('scroll', updateParallax, { passive: true }); updateParallax();
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$contentPath = __DIR__ . '/../data/content.json';
$uploadDir = __DIR__ . '/../uploads';

function load_admin_content(string $path): array
{
    $json = file_get_contents($path);
    $data = json_decode($json ?: '{}', true);
    return is_array($data) ? $data : [];
}

function save_admin_content(string $path, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function post_text(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function arr_value(array $arr, int $index, string $default = ''): string
{
    return trim((string)($arr[$index] ?? $default));
}

function upload_image(string $field, int $index, string $current, string $uploadDir): string
{
    if (!isset($_FILES[$field]['tmp_name'][$index]) || !is_uploaded_file($_FILES[$field]['tmp_name'][$index])) {
        return $current;
    }

    $error = $_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE;
    if ($error !== UPLOAD_ERR_OK) {
        return $current;
    }

    $original = (string)($_FILES[$field]['name'][$index] ?? 'image');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        return $current;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($original, PATHINFO_FILENAME));
    $base = trim((string)$base, '-');
    if ($base === '') {
        $base = 'image';
    }

    $filename = strtolower($base) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    $target = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'][$index], $target)) {
        return $current;
    }

    return 'uploads/' . $filename;
}

$content = load_admin_content($contentPath);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token tidak valid. Refresh halaman lalu coba lagi.';
    } else {
        $new = [];
        $new['site'] = [
            'title' => post_text('site_title'),
            'brand_main' => post_text('brand_main'),
            'brand_accent' => post_text('brand_accent'),
            'tagline' => post_text('site_tagline'),
            'favicon' => post_text('site_favicon'),
            'footer' => post_text('site_footer'),
        ];

        $new['hero'] = [
            'badge' => post_text('hero_badge'),
            'title_top' => post_text('hero_title_top'),
            'title_main' => post_text('hero_title_main'),
            'description' => post_text('hero_description'),
            'primary_text' => post_text('hero_primary_text'),
            'primary_link' => post_text('hero_primary_link'),
            'secondary_text' => post_text('hero_secondary_text'),
            'secondary_link' => post_text('hero_secondary_link'),
        ];

        $oldKorelasi = $content['korelasi']['items'] ?? [];
        $kTitles = $_POST['k_title'] ?? [];
        $kDescriptions = $_POST['k_description'] ?? [];
        $new['korelasi'] = ['title' => post_text('korelasi_title'), 'items' => []];
        $kCount = max(count((array)$kTitles), count((array)$oldKorelasi));
        for ($i = 0; $i < $kCount; $i++) {
            $title = arr_value((array)$kTitles, $i);
            $description = arr_value((array)$kDescriptions, $i);
            if ($title === '' && $description === '') {
                continue;
            }
            $old = $oldKorelasi[$i] ?? [];
            $new['korelasi']['items'][] = [
                'number' => $old['number'] ?? sprintf('%02d', $i + 1),
                'icon' => $old['icon'] ?? ($i % 2 === 0 ? 'fa-solid fa-om' : 'fa-solid fa-masks-theater'),
                'color' => $old['color'] ?? ($i % 2 === 0 ? 'orange' : 'blue'),
                'title' => $title,
                'description' => $description,
            ];
        }

        $oldPortal = $content['portal']['items'] ?? [];
        $pTitles = (array)($_POST['portal_title'] ?? []);
        $pCategories = (array)($_POST['portal_category'] ?? []);
        $pImages = (array)($_POST['portal_image'] ?? []);
        $pFallbacks = (array)($_POST['portal_fallback'] ?? []);
        $pDescriptions = (array)($_POST['portal_description'] ?? []);
        $pButtonTexts = (array)($_POST['portal_button_text'] ?? []);
        $pButtonLinks = (array)($_POST['portal_button_link'] ?? []);
        $pColors = (array)($_POST['portal_color'] ?? []);
        $new['portal'] = [
            'eyebrow' => post_text('portal_eyebrow'),
            'title' => post_text('portal_section_title'),
            'subtitle' => post_text('portal_subtitle'),
            'items' => [],
        ];
        $pCount = max(count($pTitles), count($oldPortal));
        for ($i = 0; $i < $pCount; $i++) {
            $title = arr_value($pTitles, $i);
            if ($title === '') {
                continue;
            }
            $currentImage = arr_value($pImages, $i, (string)($oldPortal[$i]['image'] ?? ''));
            $image = upload_image('portal_image_upload', $i, $currentImage, $uploadDir);
            $new['portal']['items'][] = [
                'title' => $title,
                'category' => arr_value($pCategories, $i),
                'image' => $image,
                'fallback' => arr_value($pFallbacks, $i, 'https://placehold.co/800x600/000/fff?text=BHINEKA.SPACE'),
                'description' => arr_value($pDescriptions, $i),
                'button_text' => arr_value($pButtonTexts, $i, 'Buka Portal VR'),
                'button_link' => arr_value($pButtonLinks, $i, '#'),
                'color' => in_array(arr_value($pColors, $i, 'orange'), ['orange', 'blue'], true) ? arr_value($pColors, $i, 'orange') : 'orange',
            ];
        }

        $oldBerita = $content['berita']['items'] ?? [];
        $bDates = (array)($_POST['berita_date'] ?? []);
        $bTitles = (array)($_POST['berita_title'] ?? []);
        $bDescriptions = (array)($_POST['berita_description'] ?? []);
        $bLinks = (array)($_POST['berita_link'] ?? []);
        $new['berita'] = [
            'eyebrow' => post_text('berita_eyebrow'),
            'title' => post_text('berita_section_title'),
            'subtitle' => post_text('berita_subtitle'),
            'items' => [],
        ];
        $bCount = max(count($bTitles), count($oldBerita));
        for ($i = 0; $i < $bCount; $i++) {
            $title = arr_value($bTitles, $i);
            if ($title === '') {
                continue;
            }
            $new['berita']['items'][] = [
                'date' => arr_value($bDates, $i),
                'title' => $title,
                'description' => arr_value($bDescriptions, $i),
                'link' => arr_value($bLinks, $i, '#'),
            ];
        }

        $oldTim = $content['tim']['items'] ?? [];
        $tNames = (array)($_POST['team_name'] ?? []);
        $tRoles = (array)($_POST['team_role'] ?? []);
        $tImages = (array)($_POST['team_image'] ?? []);
        $tInstagrams = (array)($_POST['team_instagram'] ?? []);
        $new['tim'] = ['title' => post_text('team_section_title'), 'items' => []];
        $tCount = max(count($tNames), count($oldTim));
        for ($i = 0; $i < $tCount; $i++) {
            $name = arr_value($tNames, $i);
            if ($name === '') {
                continue;
            }
            $currentImage = arr_value($tImages, $i, (string)($oldTim[$i]['image'] ?? ''));
            $image = upload_image('team_image_upload', $i, $currentImage, $uploadDir);
            $new['tim']['items'][] = [
                'name' => $name,
                'role' => arr_value($tRoles, $i, 'Anggota'),
                'image' => $image,
                'instagram' => arr_value($tInstagrams, $i, '#'),
            ];
        }

        if (save_admin_content($contentPath, $new)) {
            redirect('dashboard.php?saved=1');
        }
        $error = 'Gagal menyimpan data. Pastikan folder data bisa ditulis oleh server.';
    }
}

$content = load_admin_content($contentPath);
if (isset($_GET['saved'])) {
    $message = 'Konten berhasil disimpan. Halaman depan sudah otomatis berubah.';
}

$site = $content['site'] ?? [];
$hero = $content['hero'] ?? [];
$korelasi = $content['korelasi'] ?? ['items' => []];
$portal = $content['portal'] ?? ['items' => []];
$berita = $content['berita'] ?? ['items' => []];
$tim = $content['tim'] ?? ['items' => []];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BHINEKA.SPACE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{font-family:Inter,sans-serif}.glass{background:rgba(11,29,51,.72);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1)}
        input,textarea,select{background:rgba(255,255,255,.055)!important;border:1px solid rgba(255,255,255,.12)!important;color:#fff!important;outline:none!important}
        input:focus,textarea:focus,select:focus{border-color:#ff5e13!important;box-shadow:0 0 0 3px rgba(255,94,19,.14)}
        label{color:#CBD5E1;font-size:.85rem;font-weight:700}.section-title{position:sticky;top:0;z-index:20;background:rgba(5,13,26,.88);backdrop-filter:blur(12px)}
    </style>
</head>
<body class="bg-[#050D1A] text-white min-h-screen">
    <div class="fixed inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,94,19,.15),transparent_40%),radial-gradient(circle_at_bottom_left,rgba(56,189,248,.12),transparent_45%)] pointer-events-none"></div>

    <header class="sticky top-0 z-40 border-b border-white/10 bg-[#050D1A]/90 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-extrabold tracking-wide text-xl">BHINEKA<span class="text-orange-500">SPACE</span> Admin</h1>
                <p class="text-xs text-gray-400">Kelola konten website tanpa edit coding.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="../index.php" target="_blank" class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 text-sm text-gray-300 hover:text-white hover:border-orange-500/50"><i class="fa-solid fa-eye"></i> Lihat Website</a>
                <a href="logout.php" class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 border border-red-500/30 px-4 py-2 text-sm text-red-200 hover:bg-red-500/20"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </header>

    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-emerald-100"><i class="fa-solid fa-check-circle mr-2"></i><?= admin_e($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 rounded-2xl border border-red-500/40 bg-red-500/10 px-5 py-4 text-red-100"><i class="fa-solid fa-triangle-exclamation mr-2"></i><?= admin_e($error) ?></div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-[260px_1fr] gap-6 items-start">
            <aside class="glass rounded-3xl p-4 lg:sticky lg:top-28">
                <p class="text-xs uppercase tracking-widest text-gray-500 font-bold px-3 mb-3">Menu Edit</p>
                <nav class="space-y-2 text-sm">
                    <a href="#site" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-globe w-5"></i> Identitas Website</a>
                    <a href="#hero" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-house w-5"></i> Hero Beranda</a>
                    <a href="#korelasi" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-layer-group w-5"></i> Korelasi</a>
                    <a href="#portal" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-vr-cardboard w-5"></i> Portal VR</a>
                    <a href="#berita" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-newspaper w-5"></i> Kabar Digital</a>
                    <a href="#tim" class="block px-3 py-2 rounded-xl text-gray-300 hover:bg-white/5 hover:text-orange-400"><i class="fa-solid fa-users w-5"></i> Tim</a>
                </nav>
                <div class="mt-5 rounded-2xl bg-orange-500/10 border border-orange-500/25 p-4 text-xs text-orange-100 leading-relaxed">
                    Tips: Untuk gambar, bisa isi nama file seperti <b>borobudur.png</b> atau upload gambar baru. Gambar upload masuk ke folder <b>uploads/</b>.
                </div>
            </aside>

            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= admin_e(csrf_token()) ?>">

                <section id="site" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-globe text-orange-500 mr-2"></i>Identitas Website</h2></div>
                    <div class="p-6 grid md:grid-cols-2 gap-5">
                        <div><label>Title Browser</label><input name="site_title" value="<?= admin_e($site['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Favicon</label><input name="site_favicon" value="<?= admin_e($site['favicon'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Brand Utama</label><input name="brand_main" value="<?= admin_e($site['brand_main'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Brand Accent</label><input name="brand_accent" value="<?= admin_e($site['brand_accent'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div class="md:col-span-2"><label>Tagline Kecil</label><input name="site_tagline" value="<?= admin_e($site['tagline'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div class="md:col-span-2"><label>Footer Copyright</label><textarea name="site_footer" rows="2" class="mt-2 w-full rounded-xl px-4 py-3"><?= admin_e($site['footer'] ?? '') ?></textarea></div>
                    </div>
                </section>

                <section id="hero" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-house text-orange-500 mr-2"></i>Hero Beranda</h2></div>
                    <div class="p-6 grid md:grid-cols-2 gap-5">
                        <div class="md:col-span-2"><label>Badge</label><input name="hero_badge" value="<?= admin_e($hero['badge'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Judul Atas</label><input name="hero_title_top" value="<?= admin_e($hero['title_top'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Judul Utama</label><input name="hero_title_main" value="<?= admin_e($hero['title_main'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div class="md:col-span-2"><label>Deskripsi</label><textarea name="hero_description" rows="4" class="mt-2 w-full rounded-xl px-4 py-3"><?= admin_e($hero['description'] ?? '') ?></textarea></div>
                        <div><label>Button 1 Text</label><input name="hero_primary_text" value="<?= admin_e($hero['primary_text'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Button 1 Link</label><input name="hero_primary_link" value="<?= admin_e($hero['primary_link'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Button 2 Text</label><input name="hero_secondary_text" value="<?= admin_e($hero['secondary_text'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <div><label>Button 2 Link</label><input name="hero_secondary_link" value="<?= admin_e($hero['secondary_link'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                    </div>
                </section>

                <section id="korelasi" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-layer-group text-orange-500 mr-2"></i>Korelasi / Filosofi</h2></div>
                    <div class="p-6 space-y-5">
                        <div><label>Judul Section</label><input name="korelasi_title" value="<?= admin_e($korelasi['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <?php foreach (($korelasi['items'] ?? []) as $i => $item): ?>
                            <div class="rounded-2xl border border-white/10 bg-white/[.03] p-5">
                                <h3 class="font-bold mb-4 text-orange-300">Pilar <?= $i + 1 ?></h3>
                                <div class="grid md:grid-cols-2 gap-5">
                                    <div><label>Judul Pilar</label><input name="k_title[]" value="<?= admin_e($item['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Nomor/Icon</label><input disabled value="<?= admin_e(($item['number'] ?? '') . ' / ' . ($item['icon'] ?? '')) ?>" class="mt-2 w-full rounded-xl px-4 py-3 opacity-60"></div>
                                    <div class="md:col-span-2"><label>Deskripsi</label><textarea name="k_description[]" rows="4" class="mt-2 w-full rounded-xl px-4 py-3"><?= admin_e($item['description'] ?? '') ?></textarea></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section id="portal" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-vr-cardboard text-orange-500 mr-2"></i>Portal VR</h2></div>
                    <div class="p-6 space-y-5">
                        <div class="grid md:grid-cols-3 gap-5">
                            <div><label>Eyebrow</label><input name="portal_eyebrow" value="<?= admin_e($portal['eyebrow'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                            <div><label>Judul Section</label><input name="portal_section_title" value="<?= admin_e($portal['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                            <div><label>Subtitle</label><input name="portal_subtitle" value="<?= admin_e($portal['subtitle'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        </div>
                        <?php foreach (($portal['items'] ?? []) as $i => $item): ?>
                            <div class="rounded-2xl border border-white/10 bg-white/[.03] p-5">
                                <h3 class="font-bold mb-4 text-orange-300">Portal <?= $i + 1 ?></h3>
                                <div class="grid md:grid-cols-2 gap-5">
                                    <div><label>Judul Portal</label><input name="portal_title[]" value="<?= admin_e($item['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Kategori Badge</label><input name="portal_category[]" value="<?= admin_e($item['category'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Path Gambar</label><input name="portal_image[]" value="<?= admin_e($item['image'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Upload Gambar Baru</label><input name="portal_image_upload[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Fallback Gambar</label><input name="portal_fallback[]" value="<?= admin_e($item['fallback'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Warna Card</label><select name="portal_color[]" class="mt-2 w-full rounded-xl px-4 py-3"><option value="orange" <?= ($item['color'] ?? '') === 'orange' ? 'selected' : '' ?>>Orange</option><option value="blue" <?= ($item['color'] ?? '') === 'blue' ? 'selected' : '' ?>>Blue</option></select></div>
                                    <div class="md:col-span-2"><label>Deskripsi</label><textarea name="portal_description[]" rows="4" class="mt-2 w-full rounded-xl px-4 py-3"><?= admin_e($item['description'] ?? '') ?></textarea></div>
                                    <div><label>Text Tombol</label><input name="portal_button_text[]" value="<?= admin_e($item['button_text'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Link Tombol</label><input name="portal_button_link[]" value="<?= admin_e($item['button_link'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section id="berita" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-newspaper text-orange-500 mr-2"></i>Kabar Digital</h2></div>
                    <div class="p-6 space-y-5">
                        <div class="grid md:grid-cols-3 gap-5">
                            <div><label>Eyebrow</label><input name="berita_eyebrow" value="<?= admin_e($berita['eyebrow'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                            <div><label>Judul Section</label><input name="berita_section_title" value="<?= admin_e($berita['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                            <div><label>Subtitle</label><input name="berita_subtitle" value="<?= admin_e($berita['subtitle'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        </div>
                        <?php foreach (($berita['items'] ?? []) as $i => $item): ?>
                            <div class="rounded-2xl border border-white/10 bg-white/[.03] p-5">
                                <h3 class="font-bold mb-4 text-orange-300">Berita <?= $i + 1 ?></h3>
                                <div class="grid md:grid-cols-2 gap-5">
                                    <div><label>Tanggal</label><input name="berita_date[]" value="<?= admin_e($item['date'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Judul</label><input name="berita_title[]" value="<?= admin_e($item['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div class="md:col-span-2"><label>Deskripsi</label><textarea name="berita_description[]" rows="3" class="mt-2 w-full rounded-xl px-4 py-3"><?= admin_e($item['description'] ?? '') ?></textarea></div>
                                    <div class="md:col-span-2"><label>Link</label><input name="berita_link[]" value="<?= admin_e($item['link'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section id="tim" class="glass rounded-3xl overflow-hidden">
                    <div class="section-title px-6 py-4 border-b border-white/10"><h2 class="font-extrabold text-lg"><i class="fa-solid fa-users text-orange-500 mr-2"></i>Anggota Tim</h2></div>
                    <div class="p-6 space-y-5">
                        <div><label>Judul Section</label><input name="team_section_title" value="<?= admin_e($tim['title'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                        <?php foreach (($tim['items'] ?? []) as $i => $member): ?>
                            <div class="rounded-2xl border border-white/10 bg-white/[.03] p-5">
                                <h3 class="font-bold mb-4 text-orange-300">Anggota <?= $i + 1 ?></h3>
                                <div class="grid md:grid-cols-2 gap-5">
                                    <div><label>Nama</label><input name="team_name[]" value="<?= admin_e($member['name'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Role/Jabatan</label><input name="team_role[]" value="<?= admin_e($member['role'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Path Foto</label><input name="team_image[]" value="<?= admin_e($member['image'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div><label>Upload Foto Baru</label><input name="team_image_upload[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                    <div class="md:col-span-2"><label>Instagram URL</label><input name="team_instagram[]" value="<?= admin_e($member['instagram'] ?? '') ?>" class="mt-2 w-full rounded-xl px-4 py-3"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <div class="sticky bottom-5 z-50 glass rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xl">
                    <p class="text-sm text-gray-300"><i class="fa-solid fa-floppy-disk text-orange-500 mr-2"></i>Klik simpan setelah edit. Data tersimpan ke <b>data/content.json</b>.</p>
                    <button class="w-full sm:w-auto rounded-xl bg-gradient-to-r from-orange-600 to-orange-400 px-8 py-3 font-extrabold hover:scale-[1.02] transition-transform"><i class="fa-solid fa-save mr-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

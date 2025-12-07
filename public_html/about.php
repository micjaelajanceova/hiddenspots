<?php include 'includes/header.php'; ?>

<?php
include 'includes/db.php';

// Load site settings  
$siteSettings = new SiteSettings($pdo);
$settings = $siteSettings->getAll();

// Use null coalescing to provide defaults
$about_title1     = $settings['about_title1'] ?? '';
$about_subtitle1  = $settings['about_subtitle1'] ?? '';
$about_text1      = $settings['about_text1'] ?? '';

$about_title2     = $settings['about_title2'] ?? '';
$about_subtitle2  = $settings['about_subtitle2'] ?? '';
$about_text2      = $settings['about_text2'] ?? '';

$how_title        = $settings['how_title'] ?? '';
$how_subtitle     = $settings['how_subtitle'] ?? '';

$card1_title      = $settings['card1_title'] ?? '';
$card1_text       = $settings['card1_text'] ?? '';

$card2_title      = $settings['card2_title'] ?? '';
$card2_text       = $settings['card2_text'] ?? '';

$card3_title      = $settings['card3_title'] ?? '';
$card3_text       = $settings['card3_text'] ?? '';
?>


<main class="flex-1 bg-white min-h-screen overflow-y-auto">
<!-- LOGIN / SIGNUP -->
<?php include 'includes/profile-header.php'; ?>

<div class="py-2 md:py-10 lg:py-20">
      <div class="w-full px-4 sm:px-6 lg:px-8">

        <!-- Section 1 -->
        <div class="grid md:grid-cols-12 gap-12 items-center mb-10 md:mb-24">
            <div class="md:col-span-4">
            <h1 class="mb-3"><?php echo htmlspecialchars($about_title1); ?></h1>
            <h2 class="mb-6"><?php echo htmlspecialchars($about_subtitle1); ?></h2>
            <p class="text-justify">
                <?php echo nl2br(htmlspecialchars($about_text1)); ?>
            </p>

            </div>
            <div class="md:col-span-8">
                <img src="assets/img/about1.JPEG" alt="Hidden spot" class="shadow-lg w-full h-[450px] object-cover">
            </div>
        </div>

        <!-- Section 2  -->
        <div class="grid md:grid-cols-12 gap-12 items-center mb-2 md:mb-24">
            <div class="md:col-span-8 order-2 md:order-1">
                <img src="assets/img/about2.JPEG" alt="Community" class="shadow-lg w-full h-[450px] object-cover">
            </div>
            <div class="md:col-span-4 order-1 md:order-2">
                <h1 class="mb-3"><?php echo htmlspecialchars($about_title2); ?></h1>
                <h2 class="mb-6"><?php echo htmlspecialchars($about_subtitle2); ?></h2>
                <p class="text-justify">
                    <?php echo nl2br(htmlspecialchars($about_text2)); ?>
                </p>
            </div>
        </div>

        <!-- Section 3  -->
        <div class="pt-12">
        <h1 class="mb-4"><?php echo htmlspecialchars($how_title); ?></h1>
        <h2 class="max-w-2xl mb-4 md:mb-12">
            <?php echo htmlspecialchars($how_subtitle); ?>
        </h2>
            <div class="grid md:grid-cols-3 gap-8">

    <!-- Card 1 -->
    <a href="feed.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about3.JPEG" alt="Upload" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            <?php echo htmlspecialchars($card1_title); ?>
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            <?php echo htmlspecialchars($card1_text); ?>
        </h4>

    </a>

    <!-- Card 2 -->
    <a href="trending.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about4.JPEG" alt="Explore" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            <?php echo htmlspecialchars($card2_title); ?>
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            <?php echo htmlspecialchars($card2_text); ?>
        </h4>

    </a>

    <!-- Card 3 -->
    <a href="favourites.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about5.JPEG" alt="Community" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            <?php echo htmlspecialchars($card3_title); ?>
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            <?php echo htmlspecialchars($card3_text); ?>
        </h4>

    </a>
</div>
</main>

<?php include 'includes/footer.php'; ?>

<?php include 'header.php'; ?>

    <!-- Hlavný obsah -->
    <main class="flex-1 bg-white min-h-screen overflow-y-auto">
    <div class="py-10 lg:py-20">
      <div class="w-full px-4 sm:px-6 lg:px-8">
        <!-- Section 1 -->
        <div class="grid md:grid-cols-12 gap-12 items-center mb-24">
            <div class="md:col-span-4">
                <h1 class="mb-3">About HS</h1>
                <h2 class="mb-6">community for explorers</h2>
                <p class="text-justify">
                    HiddenSpots is a community-driven platform dedicated to uncovering and sharing the hidden gems of your city.
                We believe the best places are often the ones you stumble upon by accident – quiet cafés, secret parks, tucked-away art spots, or breathtaking viewpoints that aren’t on the tourist map. <br> Here, you can share your favorite hidden locations and discover others’ secret spots.
                </p>
            </div>
            <div class="md:col-span-8">
                <img src="assets/img/hiddenspot1.jpg" alt="Hidden spot" class="shadow-lg w-full h-95 object-cover">
            </div>
        </div>

        <!-- Section 2 (reversed) -->
        <div class="grid md:grid-cols-12 gap-12 items-center mb-24">
            <div class="md:col-span-8 order-2 md:order-1">
                <img src="assets/img/hiddenspot7.jpg" alt="Community" class="shadow-lg w-full h-95 object-cover">
            </div>
            <div class="md:col-span-4 order-1 md:order-2">
                <h1 class="mb-3">EXPLORE</h1>
                <h2 class="mb-6">Filter by location, type, and keep your favorite spots handy</h2>
                <p class="text-justify">
                HiddenSpots makes exploring effortless. Use filters to find spots by area or category – from cozy cafés and secret parks to hidden art installations and scenic viewpoints.
                <br>
                Found a place you love? Save it to your favorites and return anytime to continue your adventure. HiddenSpots helps you track your discoveries, revisit your spots, and explore at your own pace.
                </p>
            </div>
        </div>
        <!-- Section 3 (headline + 3 columns) -->
        <div class="pt-12">
            <h1 class="mb-4">HOW IT WORKS?</h1>
            <h2 class="max-w-2xl mb-12">
                Discover, save, and share hidden spots
            </h2>
<div class="grid md:grid-cols-3 gap-8">
    <!-- Karta 1 -->
    <a href="feed.php" class="group relative block overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
        <img src="assets/img/hiddenspot2.jpg" alt="Upload" class="h-80 w-full object-cover">
        <!-- Gradient overlay iba pri hover -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <!-- H3 hore -->
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Upload Your Spots
        </h3>
        <!-- H4 dole, zarovnané napravo, viditeľné iba pri hover -->
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            Easily share hidden gems with the community.
        </h4>
    </a>

    <!-- Karta 2 -->
    <a href="upload.php" class="group relative block overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
        <img src="assets/img/hiddenspot4.jpg" alt="Explore" class="h-80 w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Explore & Discover
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            Find hidden places using search and filters.
        </h4>
    </a>

    <!-- Karta 3 -->
    <a href="favourites.php" class="group relative block overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
        <img src="assets/img/hiddenspot5.jpg" alt="Community" class="h-80 w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Join the Community
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            Save favourites and connect with like-minded explorers.
        </h4>
    </a>
</div>








<?php include 'footer.php'; ?>

<?php include 'includes/header.php'; ?>

    
    <main class="flex-1 bg-white min-h-screen overflow-y-auto">
    <!-- LOGIN / SIGNUP -->
    <?php include 'includes/profile-header.php'; ?>

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
                <img src="assets/img/about1.JPEG" alt="Hidden spot" class="shadow-lg w-full h-[450px] object-cover">
            </div>
        </div>

        <!-- Section 2  -->
        <div class="grid md:grid-cols-12 gap-12 items-center mb-24">
            <div class="md:col-span-8 order-2 md:order-1">
                <img src="assets/img/about2.JPEG" alt="Community" class="shadow-lg w-full h-[450px] object-cover">
            </div>
            <div class="md:col-span-4 order-1 md:order-2">
                <h1 class="mb-3">Explore</h1>
                <h2 class="mb-6">Filter by location, type, and keep your favorite spots handy</h2>
                <p class="text-justify">
                HiddenSpots makes exploring effortless. Use filters to find spots by area or category – from cozy cafés and secret parks to hidden art installations and scenic viewpoints.
                <br>
                Found a place you love? Save it to your favorites and return anytime to continue your adventure. HiddenSpots helps you track your discoveries, revisit your spots, and explore at your own pace.
                </p>
            </div>
        </div>
        <!-- Section 3  -->
        <div class="pt-12">
            <h1 class="mb-4">How it works?</h1>
            <h2 class="max-w-2xl mb-12">
                Discover, save, and share hidden spots
            </h2>
<div class="grid md:grid-cols-3 gap-8">
    <!-- Card 1 -->
    <a href="feed.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about3.JPEG" alt="Upload" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Explore New Spots
        </h3>

        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            Browse the latest hidden places shared by the community.
        </h4>
    </a>

    <!-- Card 2 -->
    <a href="trending.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about4.JPEG" alt="Explore" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Most Popular Spots
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            See what locations are currently getting the most attention.
        </h4>
    </a>

    <!-- Card 3 -->
    <a href="favourites.php" class="group relative block overflow-hidden hover:shadow-2xl">
        <img src="assets/img/about5.JPEG" alt="Community" class="h-80 w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <h3 class="absolute top-4 left-4 text-white font-bold text-xl drop-shadow-lg z-10">
            Join the Community
        </h3>
        <h4 class="absolute bottom-4 right-4 text-white text-right opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-lg z-10 max-w-[90%]">
            Save favourites and connect with like-minded explorers.
        </h4>
    </a>
</div>








<?php include 'includes/footer.php'; ?>

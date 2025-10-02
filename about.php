<?php include 'header.php'; ?>

<div class="flex h-screen bg-gray-white">
    <!-- Hlavný obsah -->
    <main class="flex-1 overflow-y-auto px-6 py-12 space-y-20">

        <!-- Section 1 -->
        <div class="grid md:grid-cols-12 gap-12 items-center">
            <div class="md:col-span-4">
                <h1 class="h1 font-bold mb-4">Welcome to HiddenSpots</h1>
                <h2 class="font-bold mb-4">About HiddenSpots</h2>
                <p class="text-lg text-gray-600">
                    Discover secret places and share them with the world. HiddenSpots is a platform 
                    where explorers like you reveal unique locations that others might overlook.
                </p>
            </div>
            <div class="md:col-span-8">
                <img src="assets/img/hiddenspot1.jpg" alt="Hidden spot" class="shadow-lg w-full h-95 object-cover">
            </div>
        </div>

        <!-- Section 2 (reversed) -->
        <div class="grid md:grid-cols-12 gap-12 items-center">
            <div class="md:col-span-8 order-2 md:order-1">
                <img src="assets/img/hiddenspot2.jpg" alt="Community" class="shadow-lg w-full h-95 object-cover">
            </div>
            <div class="md:col-span-4 order-1 md:order-2">
                <h2 class="text-4xl font-bold mb-4">A Community of Explorers</h2>
                <p class="text-lg text-gray-600">
                    From hidden cafes to breathtaking natural escapes, you can connect with others who 
                    love to explore and share their discoveries.
                </p>
            </div>
        </div>

        <!-- Section 3 (headline + 3 columns) -->
        <div>
            <h2 class="text-4xl font-bold mb-4">What We Offer</h2>
            <p class="text-lg text-gray-600 max-w-2xl mb-12">
                HiddenSpots provides tools to upload your spots, save favourites, and explore trending places.
            </p>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                    <img src="assets/about3.jpg" alt="Upload" class="h-48 w-full object-cover">
                    <div class="p-6 text-right">
                        <h3 class="font-bold text-xl mb-2">Upload Your Spots</h3>
                        <p class="text-gray-600">Easily share hidden gems with the community.</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                    <img src="assets/about4.jpg" alt="Explore" class="h-48 w-full object-cover">
                    <div class="p-6 text-right">
                        <h3 class="font-bold text-xl mb-2">Explore & Discover</h3>
                        <p class="text-gray-600">Find hidden places using search and filters.</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                    <img src="assets/about5.jpg" alt="Community" class="h-48 w-full object-cover">
                    <div class="p-6 text-right">
                        <h3 class="font-bold text-xl mb-2">Join the Community</h3>
                        <p class="text-gray-600">Save favourites and connect with like-minded explorers.</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<?php include 'footer.php'; ?>

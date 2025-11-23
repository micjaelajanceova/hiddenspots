<?php
// PHOTO FEED TEMPLATE

?>
      <div class="masonry-item mb-4">
        <a href="../spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" 
           class="block overflow-hidden group relative">
           
          <img 
            src="../<?= htmlspecialchars($spot['file_path']) ?>" 
            alt="<?= htmlspecialchars($spot['name']) ?>" 
            class="w-full max-h-[clamp(200px,50vh,600px)] object-cover block transition-transform duration-300 group-hover:scale-105"
          >

          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex 
                      items-center justify-center text-white text-sm font-semibold">
            <?= htmlspecialchars($spot['name']) ?>
          </div>

          <div class="absolute bottom-1 left-1 text-white text-xs bg-black/50 px-1">
            @<?= htmlspecialchars($spot['user_name']) ?>
          </div>

        </a>
      </div>




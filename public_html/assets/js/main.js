function initMasonry() {
    const grid = document.querySelector('#masonry');
    if (!grid) return;

    if (window.masonry) {
        window.masonry.remove();
        window.masonry = null;
    }

    window.masonry = Macy({
        container: '#masonry',
        columns: 4,
        margin: 12,
        breakAt: { 1024: 3, 640: 2, 0: 1 },
        trueOrder: false,
        waitForImages: true
    });

    window.masonry.recalculate(true);
    document.getElementById('masonry').style.display = 'block';
}

window.addEventListener('load', initMasonry);
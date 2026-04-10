document.addEventListener('alpine:init', () => {
  Alpine.data('productGallery', (images = []) => ({
    images,
    lightboxOpen: false,
    currentIndex: 0,
    zoomed: false,
    zoomOrigin: { x: 50, y: 50 },
    _keyHandler: null,

    openLightbox(index) {
      this.currentIndex = index;
      this.zoomed = false;
      this.lightboxOpen = true;
      document.body.style.overflow = 'hidden';
      this._keyHandler = this.handleKeyDown.bind(this);
      document.addEventListener('keydown', this._keyHandler);
    },

    closeLightbox() {
      this.lightboxOpen = false;
      this.zoomed = false;
      document.body.style.overflow = '';
      if (this._keyHandler) {
        document.removeEventListener('keydown', this._keyHandler);
        this._keyHandler = null;
      }
    },

    prevSlide() {
      if (this.currentIndex > 0) {
        this.currentIndex--;
        this.zoomed = false;
      }
    },

    nextSlide() {
      if (this.currentIndex < this.images.length - 1) {
        this.currentIndex++;
        this.zoomed = false;
      }
    },

    handleKeyDown(e) {
      if (!this.lightboxOpen) return;
      if (e.key === 'Escape') this.closeLightbox();
      if (e.key === 'ArrowLeft') this.prevSlide();
      if (e.key === 'ArrowRight') this.nextSlide();
    },

    toggleZoom(e) {
      if (this.zoomed) {
        this.zoomed = false;
      } else {
        const rect = e.currentTarget.getBoundingClientRect();
        this.zoomOrigin.x = ((e.clientX - rect.left) / rect.width) * 100;
        this.zoomOrigin.y = ((e.clientY - rect.top) / rect.height) * 100;
        this.zoomed = true;
      }
    },

    updateZoomOrigin(e) {
      if (!this.zoomed) return;
      const rect = e.currentTarget.getBoundingClientRect();
      this.zoomOrigin.x = ((e.clientX - rect.left) / rect.width) * 100;
      this.zoomOrigin.y = ((e.clientY - rect.top) / rect.height) * 100;
    },
  }));
});

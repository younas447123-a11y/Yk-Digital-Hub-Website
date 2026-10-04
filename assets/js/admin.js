/**
 * YK Digital Hub - Admin Dashboard JavaScript
 * Handles sidebar toggle, overlay, and other UI interactions.
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    // Toggle sidebar on mobile
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }

    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Close sidebar when window resizes to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
    });

    // Optional: dismiss flash messages on click
    const flash = document.querySelector('.flash-message');
    if (flash) {
        flash.addEventListener('click', function() {
            this.style.opacity = '0';
            setTimeout(() => this.remove(), 300);
        });
    }
});
<?php
/**
 * Admin Footer – closes main content, includes JavaScript.
 */
?>
            </div> <!-- .admin-content -->
        </div> <!-- .admin-main -->
    </div> <!-- .admin-wrapper -->

    <!-- Admin JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/admin.js" defer></script>
    <script>
        // Flash messages auto-dismiss after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.querySelector('.flash-message');
            if (flash) {
                setTimeout(function() {
                    flash.style.transition = 'opacity 0.5s';
                    flash.style.opacity = '0';
                    setTimeout(function() {
                        flash.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
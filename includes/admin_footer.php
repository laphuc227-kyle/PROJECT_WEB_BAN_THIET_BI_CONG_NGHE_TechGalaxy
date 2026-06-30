<?php
/**
 * includes/admin_footer.php
 */
?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Logic Toggle Sidebar cho Mobile/Tablet
    document.addEventListener('DOMContentLoaded', function() {
      const toggleBtn = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('adminSidebar');
      const overlay = document.getElementById('sidebarOverlay');

      if (toggleBtn && sidebar && overlay) {
        // Mở sidebar
        toggleBtn.addEventListener('click', function() {
          sidebar.classList.add('show');
          overlay.classList.add('show');
        });

        // Đóng sidebar khi bấm ra ngoài lớp phủ
        overlay.addEventListener('click', function() {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }
    });
  </script>
</body>
</html>
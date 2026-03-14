<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // ── Sidebar open/close (mobile) ─────────────────
    function toggleSidebar() {
      document.getElementById('adminSidebar').classList.toggle('open');
      document.getElementById('sidebarOverlay').classList.toggle('d-none');
    }
    function closeSidebar() {
      document.getElementById('adminSidebar').classList.remove('open');
      document.getElementById('sidebarOverlay').classList.add('d-none');
    }

    // ── Confirm delete dialog ────────────────────────
    function confirmDelete(url, name) {
      Swal.fire({
        title: 'Delete ' + (name || 'this item') + '?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#16a34a',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
      }).then(function(result) {
        if (result.isConfirmed) window.location.href = url;
      });
    }

    // ── Auto-dismiss alert banners after 5 s ─────────
    setTimeout(function() {
      document.querySelectorAll('.admin-alert').forEach(function(el) {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity = '0';
        setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 500);
      });
    }, 5000);
  </script>
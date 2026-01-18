// Sidebar Toggle for Mobile
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarOpen = document.getElementById('sidebar-open');
  const sidebarClose = document.getElementById('sidebar-close');
  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  
  if (sidebarOpen) {
    sidebarOpen.addEventListener('click', function() {
      sidebar.classList.add('open');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.add('show');
      }
    });
  }
  
  if (sidebarClose) {
    sidebarClose.addEventListener('click', function() {
      sidebar.classList.remove('open');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.remove('show');
      }
    });
  }
  
  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function() {
      sidebar.classList.remove('open');
      sidebarBackdrop.classList.remove('show');
    });
  }
});


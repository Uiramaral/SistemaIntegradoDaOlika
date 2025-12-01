document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const openBtn = document.getElementById('sidebar-open');
  const closeBtn = document.getElementById('sidebar-close');

  openBtn?.addEventListener('click', () => sidebar.classList.remove('-translate-x-full'));
  closeBtn?.addEventListener('click', () => sidebar.classList.add('-translate-x-full'));
});

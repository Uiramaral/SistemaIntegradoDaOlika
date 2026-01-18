document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector("#sidebar");
  const openBtn = document.querySelector("#sidebar-open");
  const closeBtn = document.querySelector("#sidebar-close");

  openBtn?.addEventListener("click", () => {
    sidebar.classList.toggle("-translate-x-full");
  });

  closeBtn?.addEventListener("click", () => {
    sidebar.classList.add("-translate-x-full");
  });
});

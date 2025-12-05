document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-tab]").forEach((tab) => {
    tab.addEventListener("click", () => {
      document.querySelectorAll("[data-tab-content]").forEach((c) => c.classList.add("hidden"));
      const target = document.querySelector(tab.dataset.tab);
      if (target) {
        target.classList.remove("hidden");
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab a');
  
  tabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      
      // Remove active class from all tabs
      tabs.forEach(t => t.classList.remove('active'));
      
      // Add active class to clicked tab
      tab.classList.add('active');
      
      // Here you can add logic to show/hide tab content
      const targetId = tab.getAttribute('data-tab');
      if (targetId) {
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
          content.classList.add('hidden');
        });
        const targetContent = document.getElementById(targetId);
        if (targetContent) {
          targetContent.classList.remove('hidden');
        }
      }
    });
  });
});

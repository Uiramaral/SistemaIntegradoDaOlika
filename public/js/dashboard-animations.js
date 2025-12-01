document.addEventListener('DOMContentLoaded', () => {
  // Add fade-in animation to cards
  const cards = document.querySelectorAll('.card, .card-metric, .box');
  cards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.05}s`;
    card.classList.add('fade-in');
  });
  
  // Add hover effects
  const hoverElements = document.querySelectorAll('.card-hover, .card-metric, .box');
  hoverElements.forEach(element => {
    element.classList.add('card-hover');
  });
});

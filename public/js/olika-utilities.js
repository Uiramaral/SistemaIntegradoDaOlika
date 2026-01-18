// OLIKA Dashboard JavaScript Utilities
// Exportado em: 01/12/2025, 11:49:41

// Função para formatar moeda BRL
function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
}

// Função para formatar data
function formatDate(date) {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(date));
}

// Função para aplicar animação suave aos cards
function initCardAnimations() {
  const cards = document.querySelectorAll('.card');
  cards.forEach((card, index) => {
    card.style.animation = `fadeInUp 0.3s ease-out ${index * 0.05}s forwards`;
  });
}

// Animação CSS
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
`;
document.head.appendChild(style);

// Inicializar animações ao carregar
document.addEventListener('DOMContentLoaded', initCardAnimations);


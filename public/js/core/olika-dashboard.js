/* ======================================
   OLIKA DASHBOARD v3.1
   Script Principal do Dashboard
   ====================================== */

import { initCardAnimations, showToast } from './olika-utilities.js';

document.addEventListener('DOMContentLoaded', () => {
  console.log('Olika Dashboard v3.1 carregado');
  
  // Inicializar animações de cards
  initCardAnimations();

  // ==================== RIPPLE EFFECT ====================
  document.querySelectorAll('.btn-primary').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      ripple.classList.add('ripple');
      
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      
      this.style.position = 'relative';
      this.style.overflow = 'hidden';
      this.appendChild(ripple);
      
      setTimeout(() => ripple.remove(), 400);
    });
  });

  // ==================== FOCUS SUAVE EM INPUTS ====================
  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('focus', function() {
      this.classList.add('focused');
    });
    
    el.addEventListener('blur', function() {
      this.classList.remove('focused');
    });
  });

  // ==================== SIDEBAR MOBILE ====================
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.sidebar') || document.querySelector('#sidebar') || document.querySelector('aside#sidebar');
  const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
  
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      if (sidebar) {
        sidebar.classList.toggle('open');
        if (sidebarBackdrop) {
          sidebarBackdrop.classList.toggle('active');
          sidebarBackdrop.style.display = 'block';
        }
      }
    });
  }
  
  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', () => {
      if (sidebar) {
        sidebar.classList.remove('open');
      }
      sidebarBackdrop.classList.remove('active');
      setTimeout(() => {
        sidebarBackdrop.style.display = 'none';
      }, 300);
    });
  }
  
  // Sidebar toggle existente (admin.blade.php)
  const existingSidebarOpen = document.getElementById('sidebar-open');
  const existingSidebarClose = document.getElementById('sidebar-close');
  
  if (existingSidebarOpen && sidebar) {
    existingSidebarOpen.addEventListener('click', () => {
      sidebar.classList.add('open');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.add('active');
        sidebarBackdrop.style.display = 'block';
      }
    });
  }
  
  if (existingSidebarClose && sidebar) {
    existingSidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('open');
      if (sidebarBackdrop) {
        sidebarBackdrop.classList.remove('active');
        setTimeout(() => {
          sidebarBackdrop.style.display = 'none';
        }, 300);
      }
    });
  }

  // ==================== TABLES - ROW CLICK ====================
  document.querySelectorAll('.table tbody tr[data-href]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', function() {
      const href = this.dataset.href;
      if (href) {
        window.location.href = href;
      }
    });
  });

  // ==================== AUTO-SAVE FORMS ====================
  document.querySelectorAll('form[data-autosave]').forEach(form => {
    const formId = form.dataset.autosave;
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Carregar dados salvos
    const saved = localStorage.getItem(`form_${formId}`);
    if (saved) {
      try {
        const data = JSON.parse(saved);
        inputs.forEach(input => {
          if (data[input.name]) {
            input.value = data[input.name];
          }
        });
      } catch (e) {
        console.error('Erro ao carregar dados salvos:', e);
      }
    }
    
    // Salvar ao digitar
    inputs.forEach(input => {
      input.addEventListener('input', debounce(() => {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
          data[key] = value;
        });
        localStorage.setItem(`form_${formId}`, JSON.stringify(data));
      }, 500));
    });
    
    // Limpar ao submeter
    form.addEventListener('submit', () => {
      localStorage.removeItem(`form_${formId}`);
    });
  });

  // ==================== DEBOUNCE HELPER ====================
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // ==================== LAZY LOADING IMAGES ====================
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            observer.unobserve(img);
          }
        }
      });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img);
    });
  }

  // ==================== TOOLTIPS ====================
  document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.addEventListener('mouseenter', function() {
      const tooltip = document.createElement('div');
      tooltip.className = 'tooltip';
      tooltip.textContent = this.dataset.tooltip;
      tooltip.style.cssText = `
        position: absolute;
        background: hsl(var(--color-sidebar-bg));
        color: hsl(var(--color-sidebar-text));
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius);
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
        box-shadow: var(--shadow-lg);
      `;
      
      document.body.appendChild(tooltip);
      
      const rect = this.getBoundingClientRect();
      tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
      tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
      
      this._tooltip = tooltip;
    });
    
    el.addEventListener('mouseleave', function() {
      if (this._tooltip) {
        this._tooltip.remove();
        this._tooltip = null;
      }
    });
  });
});


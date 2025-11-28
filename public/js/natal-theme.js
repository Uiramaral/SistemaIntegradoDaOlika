/**
 * Scripts específicos do tema Natal
 */
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Filtro de categorias
        const categoryButtons = document.querySelectorAll('[data-category]');
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category');
                if (categoryId === 'all') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                const categorySection = document.querySelector(`[data-category-id="${categoryId}"]`);
                if (categorySection) {
                    categorySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Busca de produtos
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = this.value.trim();
                    if (query) {
                        window.location.href = '{{ route("natal.menu.search") }}?q=' + encodeURIComponent(query);
                    }
                }
            });
        }
    });
})();


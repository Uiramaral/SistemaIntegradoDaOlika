@extends('dashboard.layouts.app')

@section('page_title', 'Painel')
@section('page_subtitle', '')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
@php
  $theme = \App\Models\Setting::getSettings()->getThemeSettings();
  $nomeEstabelecimento = $theme['theme_brand_name'] ?? (auth()->user()->name ?? 'Olika');
  $receitasMes = (float)($receitasMes ?? 0);
  $despesasMes = (float)($despesasMes ?? 0);
  $lucroMes = (float)($lucroMes ?? 0);
@endphp

{{-- Bem-vinda / Olá --}}
<div class="mb-8">
  <p class="text-sm" style="color: #6b7280;">Bem-vinda</p>
  <h1 class="text-3xl font-bold" style="color: #111827;">
    Olá, {{ $nomeEstabelecimento }}! 👋
  </h1>
</div>

{{-- 3 Cards de Resumo Financeiro --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
  {{-- Receitas --}}
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #dcfce7;">
        <i data-lucide="trending-up" class="w-4 h-4" style="color: #16a34a;"></i>
      </div>
      <span class="text-sm" style="color: #4b5563;">Receitas</span>
    </div>
    <p class="text-2xl font-bold mb-1" style="color: #16a34a;">R$ {{ number_format($receitasMes, 2, ',', '.') }}</p>
    <p class="text-xs" style="color: #6b7280;">Este mês</p>
  </div>
  
  {{-- Despesas --}}
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #fee2e2;">
        <i data-lucide="trending-down" class="w-4 h-4" style="color: #dc2626;"></i>
      </div>
      <span class="text-sm" style="color: #4b5563;">Despesas</span>
    </div>
    <p class="text-2xl font-bold mb-1" style="color: #dc2626;">R$ {{ number_format($despesasMes, 2, ',', '.') }}</p>
    <p class="text-xs" style="color: #6b7280;">Este mês</p>
  </div>
  
  {{-- Lucro --}}
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #fed7aa;">
        <i data-lucide="wallet" class="w-4 h-4" style="color: #ea580c;"></i>
      </div>
      <span class="text-sm" style="color: #4b5563;">Lucro</span>
    </div>
    <p class="text-2xl font-bold mb-1" style="color: #ea580c;">R$ {{ number_format($lucroMes, 2, ',', '.') }}</p>
    <p class="text-xs" style="color: #6b7280;">Este mês</p>
  </div>
</div>

{{-- Ações rápidas --}}
<div class="mb-8">
  <h2 class="text-lg font-semibold mb-4" style="color: #111827;">Ações rápidas</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <button type="button" data-financas-open-modal-revenue class="rounded-xl border shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col items-center text-center" style="background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-3" style="background: #dcfce7;">
        <i data-lucide="plus" class="w-5 h-5" style="color: #16a34a;"></i>
      </div>
      <span class="text-sm font-medium" style="color: #4b5563;">Adicionar receita</span>
    </button>
    <button type="button" data-financas-open-modal-expense class="rounded-xl border shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col items-center text-center" style="background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-3" style="background: #fee2e2;">
        <i data-lucide="trending-down" class="w-5 h-5" style="color: #dc2626;"></i>
      </div>
      <span class="text-sm font-medium" style="color: #4b5563;">Adicionar despesa</span>
    </button>
    <a href="{{ route('dashboard.products.index') }}" class="rounded-xl border shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col items-center text-center" style="background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-3" style="background: #fed7aa;">
        <i data-lucide="package" class="w-5 h-5" style="color: #ea580c;"></i>
      </div>
      <span class="text-sm font-medium" style="color: #4b5563;">Novo produto</span>
    </a>
    <a href="{{ route('dashboard.pdv.index') }}" class="rounded-xl border shadow-sm p-6 hover:shadow-md transition-shadow flex flex-col items-center text-center" style="background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
      <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-3" style="background: #fed7aa;">
        <i data-lucide="clipboard-list" class="w-5 h-5" style="color: #ea580c;"></i>
      </div>
      <span class="text-sm font-medium" style="color: #4b5563;">Novo pedido</span>
    </a>
  </div>
</div>

{{-- Consultar por Data --}}
<div class="rounded-xl border shadow-sm p-6" style="background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" x-data="dateRangePicker()">
  <div class="flex items-center gap-2 mb-4">
    <i data-lucide="calendar-days" class="w-5 h-5" style="color: #4b5563;"></i>
    <h2 class="text-lg font-semibold" style="color: #111827;">Consultar por Data</h2>
  </div>
  <p class="text-sm mb-6" style="color: #6b7280;">
    Clique numa data para selecionar o início, depois clique noutra para selecionar o fim do período
  </p>
  
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Calendário Visual --}}
    <div>
      <div class="flex items-center justify-between mb-4">
        <button type="button" @click="previousMonth()" class="p-1 rounded hover:bg-gray-100 transition-colors">
          <i data-lucide="chevron-left" class="w-5 h-5" style="color: #4b5563;"></i>
        </button>
        <h3 class="text-base font-semibold capitalize" style="color: #111827;" x-text="currentMonthLabel"></h3>
        <button type="button" @click="nextMonth()" class="p-1 rounded hover:bg-gray-100 transition-colors">
          <i data-lucide="chevron-right" class="w-5 h-5" style="color: #4b5563;"></i>
        </button>
      </div>
      
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">seg</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">ter</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">qua</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">qui</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">sex</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">sáb</div>
        <div class="text-center text-xs font-medium py-2" style="color: #6b7280;">dom</div>
      </div>
      
      <div class="grid grid-cols-7 gap-1" x-show="calendarDays && calendarDays.length > 0">
        <template x-for="(day, index) in calendarDays" :key="index">
          <button
            type="button"
            @click="selectDate(day)"
            class="h-9 w-9 rounded text-sm transition-colors relative flex items-center justify-center"
            :class="{
              'bg-orange-500 text-white': day.isSelected,
              'bg-orange-100': day.isInRange && !day.isSelected,
              'text-orange-700': day.isInRange && !day.isSelected,
              'text-gray-400': day.isOtherMonth,
              'hover:bg-gray-100': !day.isSelected && !day.isInRange && !day.isOtherMonth
            }"
            :style="day.isOtherMonth ? 'color: #9ca3af;' : (day.hasTransaction ? 'text-decoration: underline;' : '')"
            x-text="day.day"
          ></button>
        </template>
      </div>
    </div>
    
    {{-- Instruções e Resumo --}}
    <div class="flex flex-col items-center justify-center text-center" style="color: #6b7280;">
      <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background: #f3f4f6;">
        <i data-lucide="calendar-days" class="w-8 h-8" style="color: #9ca3af;"></i>
      </div>
      <p class="font-medium mb-1" style="color: #111827;">Selecione um período no calendário</p>
      <p class="text-sm">Clique na data inicial e depois na data final</p>
      <p class="text-sm mt-2">Datas com transações estão sublinhadas</p>
      
      <div x-show="selectedStart && selectedEnd" class="mt-6 w-full" x-cloak style="display: none;">
        <div class="mb-4">
          <p class="text-sm font-medium mb-2" style="color: #111827;">Período selecionado</p>
          <p class="text-sm" style="color: #4b5563;" x-text="selectedPeriodLabel"></p>
          <p class="text-xs mt-1" style="color: #6b7280;" x-text="selectedDaysCount + ' dias'"></p>
        </div>
        
        <div class="grid grid-cols-3 gap-2 mb-4">
          <div class="rounded-lg p-3" style="background: #dcfce7;">
            <div class="flex items-center gap-1 mb-1">
              <i data-lucide="trending-up" class="w-4 h-4" style="color: #16a34a;"></i>
              <p class="text-xs" style="color: #16a34a;">Receitas</p>
            </div>
            <p class="text-sm font-bold" style="color: #16a34a;" x-text="'R$ ' + formatCurrency(periodReceitas)">R$ 0,00</p>
          </div>
          <div class="rounded-lg p-3" style="background: #fee2e2;">
            <div class="flex items-center gap-1 mb-1">
              <i data-lucide="trending-down" class="w-4 h-4" style="color: #dc2626;"></i>
              <p class="text-xs" style="color: #dc2626;">Despesas</p>
            </div>
            <p class="text-sm font-bold" style="color: #dc2626;" x-text="'R$ ' + formatCurrency(periodDespesas)">R$ 0,00</p>
          </div>
          <div class="rounded-lg p-3 border" style="background: #fff; border-color: #e5e7eb;">
            <div class="flex items-center gap-1 mb-1">
              <i data-lucide="wallet" class="w-4 h-4" style="color: #4b5563;"></i>
              <p class="text-xs" style="color: #4b5563;">Lucro</p>
            </div>
            <p class="text-sm font-bold" style="color: #111827;" x-text="'R$ ' + formatCurrency(periodLucro)">R$ 0,00</p>
          </div>
        </div>
        
        <button type="button" @click="clearSelection()" class="w-full px-4 py-2 rounded-lg border text-sm transition-colors" style="border-color: #d1d5db; color: #4b5563;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          Limpar seleção
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Adicionar Receita/Despesa --}}
<div id="modal-financas" role="dialog" aria-modal="true" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50" style="display: none;">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md border border-gray-200 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
    <div class="px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
      <h3 id="modal-financas-title" class="text-lg font-semibold text-gray-900">Adicionar lançamento</h3>
    </div>
    <form action="{{ route('dashboard.financas.store') }}" method="POST" class="p-6 space-y-4">
      @csrf
      <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">
      <input type="hidden" name="type" id="transaction-type" value="revenue">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
        <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0,00" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" value="{{ old('amount') }}">
        @error('amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
        <input type="text" name="description" maxlength="500" placeholder="Ex.: Venda delivery" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" value="{{ old('description') }}">
        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
        <input type="date" name="transaction_date" required class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" value="{{ old('transaction_date', now()->format('Y-m-d')) }}">
        @error('transaction_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria (opcional)</label>
        <input type="text" name="category" maxlength="64" placeholder="Ex.: Vendas, Compras" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" value="{{ old('category') }}">
        @error('category') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div class="flex gap-2 pt-2">
        <button type="button" data-financas-close-modal class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">Cancelar</button>
        <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors">Salvar</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dateRangePicker', () => ({
            currentDate: new Date(),
            selectedStart: null,
            selectedEnd: null,
            periodReceitas: 0,
            periodDespesas: 0,
            periodLucro: 0,
            datesWithTransactions: [],
            
            init() {
                this.loadTransactionsDates();
                this.$watch('currentDate', () => {
                    if (typeof lucide !== 'undefined') {
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                });
            },
        
        get currentMonthLabel() {
            const months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
            return months[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
        },
        
        get calendarDays() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1; // Segunda = 0
            
            const days = [];
            
            // Dias do mês anterior
            const prevMonthLastDay = new Date(year, month, 0).getDate();
            for (let i = startDay - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                const date = new Date(year, month - 1, day);
                days.push({
                    day: day,
                    date: date,
                    isOtherMonth: true,
                    isSelected: this.isDateSelected(date),
                    isInRange: this.isDateInRange(date),
                    hasTransaction: this.hasTransaction(date)
                });
            }
            
            // Dias do mês atual
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const date = new Date(year, month, day);
                days.push({
                    day: day,
                    date: date,
                    isOtherMonth: false,
                    isSelected: this.isDateSelected(date),
                    isInRange: this.isDateInRange(date),
                    hasTransaction: this.hasTransaction(date)
                });
            }
            
            // Dias do próximo mês para completar a grade
            const remaining = 42 - days.length;
            for (let day = 1; day <= remaining; day++) {
                const date = new Date(year, month + 1, day);
                days.push({
                    day: day,
                    date: date,
                    isOtherMonth: true,
                    isSelected: this.isDateSelected(date),
                    isInRange: this.isDateInRange(date),
                    hasTransaction: this.hasTransaction(date)
                });
            }
            
            return days;
        },
        
        isDateSelected(date) {
            if (!this.selectedStart) return false;
            const d = this.formatDate(date);
            const startStr = this.formatDate(this.selectedStart);
            if (!this.selectedEnd) {
                return d === startStr;
            }
            const endStr = this.formatDate(this.selectedEnd);
            return d === startStr || d === endStr;
        },
        
        isDateInRange(date) {
            if (!this.selectedStart || !this.selectedEnd) return false;
            const d = date.getTime();
            const start = this.selectedStart.getTime();
            const end = this.selectedEnd.getTime();
            const minTime = Math.min(start, end);
            const maxTime = Math.max(start, end);
            return d >= minTime && d <= maxTime;
        },
        
        hasTransaction(date) {
            const dateStr = this.formatDate(date);
            return this.datesWithTransactions.includes(dateStr);
        },
        
        selectDate(day) {
            if (day.isOtherMonth) {
                this.currentDate = new Date(day.date.getFullYear(), day.date.getMonth(), 1);
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
                return;
            }
            
            const clickedDate = new Date(day.date);
            
            if (!this.selectedStart || (this.selectedStart && this.selectedEnd)) {
                // Primeira seleção ou resetar seleção
                this.selectedStart = clickedDate;
                this.selectedEnd = null;
                this.periodReceitas = 0;
                this.periodDespesas = 0;
                this.periodLucro = 0;
            } else {
                // Segunda seleção - definir período
                if (clickedDate < this.selectedStart) {
                    this.selectedEnd = this.selectedStart;
                    this.selectedStart = clickedDate;
                } else {
                    this.selectedEnd = clickedDate;
                }
                this.loadPeriodData();
            }
        },
        
        clearSelection() {
            this.selectedStart = null;
            this.selectedEnd = null;
            this.periodReceitas = 0;
            this.periodDespesas = 0;
            this.periodLucro = 0;
        },
        
        previousMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
        },
        
        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
        },
        
        formatDate(date) {
            return date.toISOString().split('T')[0];
        },
        
        get selectedPeriodLabel() {
            if (!this.selectedStart || !this.selectedEnd) return '';
            const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            const startDay = this.selectedStart.getDate();
            const startMonth = months[this.selectedStart.getMonth()];
            const startYear = this.selectedStart.getFullYear();
            const endDay = this.selectedEnd.getDate();
            const endMonth = months[this.selectedEnd.getMonth()];
            const endYear = this.selectedEnd.getFullYear();
            return `${startDay} De ${startMonth} A ${endDay} De ${endMonth} De ${endYear}`;
        },
        
        get selectedDaysCount() {
            if (!this.selectedStart || !this.selectedEnd) return 0;
            const diffTime = Math.abs(this.selectedEnd - this.selectedStart);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        },
        
        async loadTransactionsDates() {
            try {
                const response = await fetch('{{ route("dashboard.financas.index") }}?ajax=dates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.dates) {
                        this.datesWithTransactions = data.dates;
                    }
                }
            } catch (e) {
                console.error('Erro ao carregar datas:', e);
            }
        },
        
        async loadPeriodData() {
            if (!this.selectedStart || !this.selectedEnd) return;
            
            try {
                const start = this.formatDate(this.selectedStart);
                const end = this.formatDate(this.selectedEnd);
                const response = await fetch(`{{ route("dashboard.financas.index") }}?ajax=period&start=${start}&end=${end}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data) {
                        this.periodReceitas = parseFloat(data.receitas || 0);
                        this.periodDespesas = parseFloat(data.despesas || 0);
                        this.periodLucro = this.periodReceitas - this.periodDespesas;
                    }
                }
            } catch (e) {
                console.error('Erro ao carregar dados do período:', e);
            }
        },
        
        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        }
    }));
});

(function() {
    var modal = document.getElementById('modal-financas');
    if (!modal) return;

    function openModal(type) {
        document.getElementById('transaction-type').value = type;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        modal.focus();
    }
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-financas-open-modal-revenue]').forEach(function(btn) {
        btn.addEventListener('click', function() { openModal('revenue'); });
    });
    document.querySelectorAll('[data-financas-open-modal-expense]').forEach(function(btn) {
        btn.addEventListener('click', function() { openModal('expense'); });
    });
    document.querySelectorAll('[data-financas-close-modal]').forEach(function(btn) {
        btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
        openModal('revenue');
        @endif
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
})();
</script>
@endpush
@endsection

@props([
    'name',
    'label' => null,
    'options' => [],
    'alpineOptions' => null, 
    'placeholder' => 'Selecione...',
    'required' => false,
    'id' => null,
])

<div class="space-y-1.5"
    x-data="{
        search: '',
        open: false,
        selected: @entangle($attributes->wire('model')),
        
        // Items can come from static 'options' prop or dynamic 'alpineOptions'
        get items() {
            return {{ $alpineOptions ?? 'JSON.parse(atob(\'' . base64_encode(json_encode($options)) . '\'))' }};
        },

        get filteredItems() {
            if (this.search === '') {
                return this.items;
            }
            return this.items.filter(item => {
                return item.name.toLowerCase().includes(this.search.toLowerCase());
            });
        },

        get selectedItem() {
            return this.items.find(i => i.id == this.selected); // Use loose number equality
        },

        select(item) {
            this.selected = item.id;
            this.search = item.name;
            this.open = false;
            // Dispatch change event for legacy listens or other Alpine watchers
            this.$nextTick(() => {
                this.$dispatch('change', { value: item.id });
                // Also trigger native change on hidden input if needed
                const input = this.$refs.hiddenInput;
                if(input) {
                    input.value = item.id;
                    input.dispatchEvent(new Event('change'));
                }
            });
        },
        
        init() {
            // Watch for external changes to selected value
            this.$watch('selected', (value) => {
                if (value) {
                    const found = this.items.find(i => i.id == value);
                    if (found) {
                        this.search = found.name;
                    } else {
                        // If ID exists but not in list (yet), keep search if needed or clear?
                        // For now, if we can't find the name, we might show the ID or nothing.
                        // Ideally items list is loaded.
                    }
                } else {
                    this.search = '';
                }
            });
            
            // Initial set
            if (this.selected) {
                 const found = this.items.find(i => i.id == this.selected);
                 if (found) this.search = found.name;
            }
        }
    }"
    x-init="init()"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    @if($label)
        <label class="block text-sm font-semibold mb-1.5">
            {{ $label }} {!! $required ? '<span class="text-red-500">*</span>' : '' !!}
        </label>
    @endif

    <div class="relative" @click.outside="open = false">
        <div class="relative">
            <!-- Hidden input for standard form submission -->
            <input type="hidden" name="{{ $name }}" x-model="selected" x-ref="hiddenInput"
                @if($required) required @endif
                @if($id) id="{{ $id }}" @endif
            >

            <input
                type="text"
                x-model="search"
                @focus="open = true"
                @input="open = true"
                placeholder="{{ $placeholder }}"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                autocomplete="off"
            >
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                <i data-lucide="chevron-down" class="w-4 h-4 opacity-50" x-show="!selected"></i>
                <i data-lucide="x" class="w-4 h-4 cursor-pointer pointer-events-auto hover:text-destructive" 
                   x-show="selected" 
                   @click.stop="selected = ''; search = ''; open = false; $dispatch('change', { value: '' })"></i>
            </div>
        </div>

        <div x-show="open && filteredItems.length > 0"
             x-cloak
             class="absolute z-50 w-full mt-1 bg-popover text-popover-foreground rounded-md border border-border shadow-md max-h-60 overflow-auto animate-in fade-in-0 zoom-in-95">
            <ul class="p-1">
                <template x-for="item in filteredItems" :key="item.id">
                    <li @click="select(item)"
                        class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        :class="{'bg-accent text-accent-foreground': selected == item.id}">
                        <span x-text="item.name"></span>
                        <span x-show="item.price" class="ml-auto text-xs opacity-50" x-text="'R$ ' + item.price"></span>
                    </li>
                </template>
            </ul>
        </div>
        
        <div x-show="open && filteredItems.length === 0"
             x-cloak
             class="absolute z-50 w-full mt-1 bg-popover text-popover-foreground rounded-md border border-border shadow-md p-2 text-sm text-muted-foreground text-center">
            Nenhum resultado encontrado.
        </div>
    </div>
</div>

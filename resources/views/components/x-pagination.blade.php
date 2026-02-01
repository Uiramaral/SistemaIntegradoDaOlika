@props(['items'])

@if(isset($items) && method_exists($items, 'links') && $items->hasPages())
  <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20 rounded-b-lg">
    {{ $items->onEachSide(1)->links() }}
  </div>
@endif
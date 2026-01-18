@props(['items'])

<nav class="pagination mt-6">
  {{ $items->onEachSide(1)->links('vendor.pagination.compact') }}
</nav>


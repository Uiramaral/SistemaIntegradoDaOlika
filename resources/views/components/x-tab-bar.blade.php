@props(['tabs' => [], 'active' => null, 'type' => 'links'])

<div class="tab-bar">
  @foreach ($tabs as $tab)
    @if($type === 'buttons' || isset($tab['data-tab']))
      <button type="button" 
              role="tab"
              data-tab="{{ $tab['data-tab'] ?? $tab['id'] }}"
              class="tab-button {{ ($active === $tab['id'] || (is_null($active) && $loop->first)) ? 'active' : '' }}"
              @if(isset($tab['onclick'])) onclick="{{ $tab['onclick'] }}" @endif>
        {{ $tab['label'] ?? '' }}
      </button>
    @else
      <a href="{{ $tab['url'] ?? '#' }}"
         class="{{ ($active === $tab['id'] || (is_null($active) && $loop->first)) ? 'active' : '' }}"
         @if(isset($tab['onclick'])) onclick="{{ $tab['onclick'] }}" @endif>
         {{ $tab['label'] ?? '' }}
      </a>
    @endif
  @endforeach
</div>


@props([
  'label' => 'Label',
  'value' => '0',
  'delta' => null,    // ex: +12% vs último ciclo
  'hint'  => null,    // ex: "Hoje"
])

<div {{ $attributes->merge(['class' => 'card']) }} style="padding:16px">
  <div class="stat">
    <div class="label">{{ $label }}</div>
    <div class="value">{{ $value }}</div>
  </div>
  @if($delta || $hint)
    <div style="margin-top:8px;display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--color-muted)">
      <div>{{ $hint }}</div>
      @if($delta)
        <span class="pill">{{ $delta }}</span>
      @endif
    </div>
  @endif
</div>

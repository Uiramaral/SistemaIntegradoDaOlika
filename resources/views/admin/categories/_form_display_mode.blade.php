{{-- resources/views/admin/categories/_form_display_mode.blade.php --}}
<div class="mb-3">
  <label for="display_mode" class="form-label">Modo de exibição no cardápio</label>
  <select name="display_mode" id="display_mode" class="form-select">
    <option value="grid2" {{ old('display_mode', $category->display_mode ?? 'grid2')=='grid2'?'selected':'' }}>2 colunas (fotos maiores)</option>
    <option value="list"  {{ old('display_mode', $category->display_mode ?? 'grid2')=='list' ?'selected':'' }}>Lista (aproveita espaço)</option>
  </select>
  <small class="text-muted">Você também pode alternar na loja; o padrão é salvo aqui.</small>
</div>

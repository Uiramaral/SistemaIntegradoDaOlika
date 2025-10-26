@extends('layouts.app')

@section('content')
  @php
    // garanta que temos uma Collection
    $allProducts = $products instanceof \Illuminate\Support\Collection ? $products : collect($products);
    // remove duplicatas por id
    $list = $allProducts->unique('id')->values();

    // idem para categorias (se vier duplicado por join)
    $allCats = $categories instanceof \Illuminate\Support\Collection ? $categories : collect($categories);
    $cats = $allCats->unique('id')->values();
  @endphp

  {{-- HERO com imagem de capa + overlay escuro suave --}}
  <section class="relative h-[320px] overflow-hidden">
    <img src="{{ $coverImage ?? asset('images/hero-breads.jpg') }}"
         alt="Capa Olika"
         class="absolute inset-0 w-full h-full object-cover" />
    <div class="absolute inset-0 bg-black/40"></div>
  </section>

  {{-- Header "avatar + nome + status" sobreposto ao hero --}}
  <div class="mx-auto w-full max-w-[1200px] px-4 -mt-12">
    <div class="flex items-end gap-4">
      <img src="{{ $storeLogo ?? asset('images/logo-olika.png') }}"
           class="w-24 h-24 rounded-full border-4 border-white shadow-md"
           alt="Olika" />
      <div class="space-y-1">
        <h1 class="text-2xl font-extrabold leading-none">Olika</h1>
        <div class="text-[hsl(var(--muted-foreground))] font-semibold">Pães Artesanais</div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-white bg-[hsl(var(--success))] text-sm font-semibold">
          Aberto Agora
        </span>
      </div>
    </div>
  </div>

  {{-- Cartões: informação da loja + cupons --}}
  <div class="mx-auto w-full max-w-[1200px] px-4 mt-4 grid grid-cols-1 lg:grid-cols-[1.6fr_.9fr] gap-5">
    {{-- Info da Loja --}}
    <div class="bg-white rounded-[var(--radius)] border border-[hsl(var(--border))] shadow-[var(--shadow-sm)] p-5">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-bold text-lg">Olika - Pães Artesanais</h3>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-white bg-[hsl(var(--success))] text-xs font-semibold">
          Aberto
        </span>
      </div>
      <div class="grid gap-2 text-[hsl(var(--muted-foreground))]">
        <div><strong>⭐ 4.9</strong> (250+ avaliações)</div>
        <div>
          <div class="font-semibold">Horário de Funcionamento</div>
          <div>Seg–Sex: 7h – 19h</div>
          <div>Sáb–Dom: 8h – 14h</div>
        </div>
        <div>
          <div class="font-semibold">Endereço</div>
          <div>Rua dos Pães Artesanais, 123<br/>Bairro Gourmet – São Paulo, SP</div>
        </div>
        <div>
          <div class="font-semibold">Contato</div>
          <div>(11) 98765-4321</div>
        </div>
        <p class="text-[hsl(var(--muted-foreground))] mt-1">
          Pães artesanais com fermentação natural. Tradição e qualidade em cada fornada.
        </p>
      </div>
    </div>

    {{-- Cupons --}}
    <div class="rounded-[var(--radius)] border border-[hsl(var(--border))] shadow-[var(--shadow-sm)] p-5"
         style="background: var(--gradient-subtle);">
      <h3 class="font-bold mb-3">Cupons Disponíveis</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-white border border-[hsl(var(--border))] rounded-[var(--radius)] p-4 shadow-[var(--shadow-sm)]">
          <div class="font-extrabold">BEM-VINDO</div>
          <div class="text-[hsl(var(--muted-foreground))] font-semibold">10% OFF na primeira compra</div>
        </div>
        <div class="bg-white border border-[hsl(var(--border))] rounded-[var(--radius)] p-4 shadow-[var(--shadow-sm)]">
          <div class="font-extrabold">Frete Grátis</div>
          <div class="text-[hsl(var(--muted-foreground))] font-semibold">Em pedidos acima de R$ 100</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Pills de categorias (sem duplicar) --}}
  <div class="mx-auto w-full max-w-[1200px] px-4 mt-4 flex gap-2 overflow-x-auto pb-1">
    <a href="{{ route('menu.index') }}"
       class="inline-flex items-center gap-2 rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3.5 py-2 font-semibold
              {{ request()->routeIs('menu.index') ? 'bg-[hsl(var(--primary))] text-white border-[hsl(var(--primary))]' : '' }}">
      Todos
    </a>
    @foreach($cats as $cat)
      <a href="{{ route('menu.category', $cat->id) }}"
         class="inline-flex items-center gap-2 rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3.5 py-2 font-semibold
                {{ (isset($currentCategory) && $currentCategory->id === $cat->id) ? 'bg-[hsl(var(--primary))] text-white border-[hsl(var(--primary))]' : '' }}">
        {{ $cat->name }}
      </a>
    @endforeach
  </div>

  {{-- Cabeçalho da listagem --}}
  <div class="mx-auto w-full max-w-[1200px] px-4 mt-3 mb-4 flex items-center justify-between">
    <h2 class="font-extrabold text-xl">Nossos Produtos</h2>
    <div class="hidden sm:flex items-center gap-3 text-[hsl(var(--muted-foreground))]">
      <a href="{{ route('menu.download') }}" class="inline-flex items-center rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3.5 py-2 font-semibold">Download</a>
      <div class="flex items-center gap-2">
        <span>Visualização:</span>
        <button class="inline-flex items-center rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3 py-1.5 font-semibold">3 col</button>
        <button class="inline-flex items-center rounded-full border border-[hsl(var(--primary))] bg-[hsl(var(--primary))] text-white px-3 py-1.5 font-semibold">4 col</button>
        <button class="inline-flex items-center rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3 py-1.5 font-semibold">Lista</button>
      </div>
    </div>
  </div>

  {{-- Grade de produtos — 4 col no desktop (idêntico ao mock) --}}
  <section class="mx-auto w-full max-w-[1200px] px-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach($list as $p)
      <article class="bg-white rounded-[var(--radius)] border border-[hsl(var(--border))] shadow-[var(--shadow-sm)] overflow-hidden">
        <div class="aspect-[4/3] bg-[hsl(var(--muted))]">
          <img src="{{ $p->image_url ?? asset('images/produto-placeholder.jpg') }}"
               alt="{{ $p->name }}" class="w-full h-full object-cover" />
        </div>
        <div class="p-4">
          <div class="font-bold line-clamp-1">{{ $p->name }}</div>
          @if(!empty($p->short_description))
            <div class="text-[hsl(var(--muted-foreground))] line-clamp-1">{{ $p->short_description }}</div>
          @endif
          <div class="text-orange-500 font-extrabold mt-1">
            R$ {{ number_format($p->price, 2, ',', '.') }}
          </div>
          <div class="mt-2 flex justify-end">
            <button type="button"
                    class="w-10 h-10 rounded-full bg-[hsl(var(--primary))] text-white text-xl leading-none"
                    title="Adicionar"
                    data-open-product="{{ $p->id }}">+</button>
          </div>
        </div>
      </article>

      {{-- Modal do produto --}}
      <div class="js-modal hidden fixed inset-0 z-50" data-modal="{{ $p->id }}">
        <div class="js-backdrop absolute inset-0 bg-black/45"></div>
        <div class="relative z-10 mx-auto w-[92vw] max-w-[680px] mt-24 sm:mt-32">
          <div class="bg-white rounded-[var(--radius)] border border-[hsl(var(--border))] shadow-[var(--shadow-lg)] overflow-hidden">
            <div class="h-[280px]">
              <img src="{{ $p->image_url ?? asset('images/produto-placeholder.jpg') }}"
                   alt="{{ $p->name }}" class="w-full h-full object-cover" />
            </div>
            <div class="p-5">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="font-extrabold text-xl">{{ $p->name }}</h3>
                  @if(!empty($p->description))
                    <p class="text-[hsl(var(--muted-foreground))] mt-1">{{ $p->description }}</p>
                  @endif
                  <div class="text-orange-500 font-extrabold mt-2">
                    R$ {{ number_format($p->price, 2, ',', '.') }}
                  </div>
                </div>
                <button class="inline-flex items-center rounded-full border border-[hsl(var(--border))] bg-[hsl(var(--secondary))] px-3 py-1.5 font-semibold"
                        data-close-modal>Fechar</button>
              </div>
              <div class="flex items-center gap-3 my-4">
                <div class="text-[hsl(var(--muted-foreground))] font-semibold">Quantidade:</div>
                <div class="js-qty inline-flex items-center border border-[hsl(var(--border))] rounded-xl overflow-hidden"
                     data-price="{{ (float) $p->price }}">
                  <button type="button" data-qty="minus" class="w-10 h-10 bg-neutral-100 font-extrabold text-lg">−</button>
                  <input type="text" value="1" inputmode="numeric" class="w-12 text-center outline-none" />
                  <button type="button" data-qty="plus" class="w-10 h-10 bg-neutral-100 font-extrabold text-lg">+</button>
                </div>
              </div>
              <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $p->id }}">
                <input type="hidden" name="qty" value="1" data-bind-qty>
                <button class="w-full rounded-xl bg-[hsl(var(--primary))] text-white font-extrabold py-3"
                        type="submit">
                  <span data-total>Adicionar · {{ 'R$ '.number_format($p->price, 2, ',', '.') }}</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </section>
@endsection
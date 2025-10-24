<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // Helpers centralizados -----------------------------

    private function getCartFromSession(): array
    {
        // ajuste conforme sua estrutura
        return session('cart', []); // ex: [product_id => ['qty'=>..., 'price'=>...], ...]
    }

    private function saveCartToSession(array $cart): void
    {
        session(['cart' => $cart]);
    }

    private function cartSummary(array $cart): array
    {
        $count = 0;
        $total = 0.0;
        $items = [];

        // Buscar produtos do banco para enriquecer os dados
        $productIds = array_keys($cart);
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $productId => $row) {
            $qty   = (int)($row['qty']   ?? 0);
            $price = (float)($row['price'] ?? 0);
            $count += $qty;
            $total += $qty * $price;

            $product = $products->get($productId);

            $items[] = [
                'product_id' => (int)$productId,
                'qty'        => $qty,
                'price'      => $price,
                'subtotal'   => $qty * $price,
                'name'       => $product ? $product->name : "Produto #{$productId}",
                'image_url'  => $product ? $product->image_url : null,
            ];
        }

        return [$count, round($total, 2), $items];
    }

    private function jsonCart(array $extra = [])
    {
        [$count, $total, $items] = $this->cartSummary($this->getCartFromSession());

        return response()->json(array_merge([
            'success'    => true,
            'cart_count' => $count,
            'total'      => $total,
            'items'      => $items,
        ], $extra));
    }

    // Endpoints de LEITURA ------------------------------

    public function count(Request $request)
    {
        [$count] = $this->cartSummary($this->getCartFromSession());
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function items(Request $request)
    {
        return $this->jsonCart();
    }

    // Endpoints de ESCRITA ------------------------------

    public function add(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $qty       = max(1, (int)$request->input('qty', 1));
        $price     = (float) $request->input('price', 0);

        $cart = $this->getCartFromSession();

        if (!isset($cart[$productId])) {
            $cart[$productId] = ['qty' => 0, 'price' => $price];
        }

        $cart[$productId]['qty'] += $qty;
        if ($price > 0) { $cart[$productId]['price'] = $price; }

        $this->saveCartToSession($cart);

        return $this->jsonCart(['message' => 'Item adicionado']);
    }

    public function update(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $qty       = max(0, (int)$request->input('qty', 0));

        $cart = $this->getCartFromSession();

        if ($qty === 0) {
            unset($cart[$productId]);
        } else {
            if (!isset($cart[$productId])) {
                // opcional: retornar erro
                $cart[$productId] = ['qty' => 0, 'price' => (float)$request->input('price', 0)];
            }
            $cart[$productId]['qty'] = $qty;
        }

        $this->saveCartToSession($cart);

        return $this->jsonCart(['message' => 'Carrinho atualizado']);
    }

    public function remove(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $cart = $this->getCartFromSession();

        unset($cart[$productId]);

        $this->saveCartToSession($cart);

        return $this->jsonCart(['message' => 'Item removido']);
    }

    public function clear(Request $request)
    {
        $this->saveCartToSession([]);
        return $this->jsonCart(['message' => 'Carrinho limpo']);
    }

    // Página HTML --------------------------------------

    public function show(Request $request)
    {
        // Renderiza a view SEM depender de $cart cru na sessão.
        // A própria view pode consumir /cart/items via JS para hidratar.
        return view('cart.index');
    }

    // Compatibilidade com rotas antigas
    public function index(Request $request)
    {
        // compat com rotas antigas: /cart -> index
        return $this->show($request);
    }
}
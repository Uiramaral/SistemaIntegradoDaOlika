<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Exibe o carrinho
     */
    public function index()
    {
        $cart = $this->getCart();
        $total = $this->calculateTotal($cart);

        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Adiciona produto ao carrinho
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::active()->available()->findOrFail($request->product_id);
        $cart = $this->getCart();

        $cartKey = $request->product_id;
        
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'product' => $product,
                'quantity' => $request->quantity,
                'unit_price' => $product->price,
            ];
        }

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Produto adicionado ao carrinho',
            'cart_count' => $this->getCartCount(),
        ]);
    }

    /**
     * Atualiza quantidade no carrinho
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = $this->getCart();
        $cartKey = $request->product_id;

        if ($request->quantity == 0) {
            unset($cart[$cartKey]);
        } else {
            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] = $request->quantity;
            }
        }

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Carrinho atualizado',
            'cart_count' => $this->getCartCount(),
            'total' => $this->calculateTotal($cart),
        ]);
    }

    /**
     * Remove produto do carrinho
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = $this->getCart();
        $cartKey = $request->product_id;

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            $this->saveCart($cart);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produto removido do carrinho',
            'cart_count' => $this->getCartCount(),
            'total' => $this->calculateTotal($cart),
        ]);
    }

    /**
     * Limpa o carrinho
     */
    public function clear()
    {
        Session::forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Carrinho limpo',
        ]);
    }

    /**
     * Obtém o carrinho da sessão
     */
    private function getCart()
    {
        return Session::get('cart', []);
    }

    /**
     * Salva o carrinho na sessão
     */
    private function saveCart($cart)
    {
        Session::put('cart', $cart);
    }

    /**
     * Calcula o total do carrinho
     */
    private function calculateTotal($cart)
    {
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }

        return $total;
    }

    /**
     * Obtém a quantidade de itens no carrinho
     */
    private function getCartCount()
    {
        $cart = $this->getCart();
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * API: Retorna apenas a contagem do carrinho
     */
    public function getCount()
    {
        $count = $this->getCartCount();
        
        return response()->json([
            'count' => $count,
            'formatted_count' => $count > 0 ? $count : '0'
        ]);
    }
}

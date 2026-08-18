<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): View
    {
        $cart = $this->cartService->getCurrentCart();
        $cart?->load(['itemGroups.product.category', 'itemGroups.product.images', 'itemGroups.variants.productVariant']);

        return view('cart.index', ['cart' => $cart, 'total' => $this->cartService->getTotal()]);
    }

    public function add(AddCartItemRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $this->cartService->addToCart(Product::query()->findOrFail($data['product_id']), $data['selected_variants'] ?? [], $data['bundle_quantity'], $data);

        if ($request->expectsJson()) {
            return response()->json([
                'cart_url' => route('cart.index'),
                'count' => $this->cartService->getItemCount(),
                'message' => 'Buket berhasil ditambahkan ke keranjang.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Buket berhasil ditambahkan ke keranjang.');
    }

    public function update(UpdateCartItemRequest $request, int $cartItem): JsonResponse|RedirectResponse
    {
        $item = $this->cartService->updateQuantity($cartItem, $request->integer('quantity'));

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $this->cartService->getItemCount(),
                'subtotal' => $item->subtotal,
                'total' => $this->cartService->getTotal(),
            ]);
        }

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function remove(int $cartItem): JsonResponse|RedirectResponse
    {
        $this->cartService->removeItem($cartItem);

        if (request()->expectsJson()) {
            return response()->json(['count' => $this->cartService->getItemCount(), 'total' => $this->cartService->getTotal()]);
        }

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}

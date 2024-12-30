<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function getOrCreateCart(Request $request)
    {
        if (auth()->check()) {
            $cart = Cart::firstOrCreate([
                'user_id' => auth()->id()
            ]);
        } else {
            $sessionId = $request->cookie('cart_session_id', Str::uuid()->toString());
            $cart = Cart::firstOrCreate([
                'session_id' => $sessionId
            ]);
        }
        return $cart;
    }

    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product', 'items.variant']);

        $total = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'status' => true,
            'cart' => $cart,
            'total' => $total
        ]);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cart = $this->getOrCreateCart($request);
        $product = Product::findOrFail($request->product_id);
        $variant = $request->product_variant_id ? ProductVariant::findOrFail($request->product_variant_id) : null;

        // Check if item already exists in cart
        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant ? $variant->id : null)
            ->first();

        if ($cartItem) {
            // Update quantity if item exists
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Create new cart item
            $cartItem = new CartItem([
                'product_id' => $product->id,
                'product_variant_id' => $variant ? $variant->id : null,
                'quantity' => $request->quantity,
                'price' => $variant ? $variant->price : $product->price
            ]);
            $cart->items()->save($cartItem);
        }

        $cart->load(['items.product', 'items.variant']);

        return response()->json([
            'status' => true,
            'message' => 'Item added to cart',
            'cart' => $cart
        ]);
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        // Verify cart item belongs to current cart
        $cart = $this->getOrCreateCart($request);
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $cart->load(['items.product', 'items.variant']);

        return response()->json([
            'status' => true,
            'message' => 'Cart item updated',
            'cart' => $cart
        ]);
    }

    public function removeItem(Request $request, CartItem $cartItem)
    {
        // Verify cart item belongs to current cart
        $cart = $this->getOrCreateCart($request);
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $cartItem->delete();

        $cart->load(['items.product', 'items.variant']);

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart',
            'cart' => $cart
        ]);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared'
        ]);
    }

    public function mergeGuestCart(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $sessionId = $request->cookie('cart_session_id');
        if (!$sessionId) {
            return response()->json([
                'status' => true,
                'message' => 'No guest cart to merge'
            ]);
        }

        $guestCart = Cart::where('session_id', $sessionId)->first();
        if (!$guestCart) {
            return response()->json([
                'status' => true,
                'message' => 'No guest cart to merge'
            ]);
        }

        $userCart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        // Merge guest cart items into user cart
        foreach ($guestCart->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $item->quantity;
                $existingItem->save();
            } else {
                $newItem = $item->replicate();
                $newItem->cart_id = $userCart->id;
                $newItem->save();
            }
        }

        // Delete guest cart
        $guestCart->delete();

        $userCart->load(['items.product', 'items.variant']);

        return response()->json([
            'status' => true,
            'message' => 'Guest cart merged successfully',
            'cart' => $userCart
        ]);
    }
}

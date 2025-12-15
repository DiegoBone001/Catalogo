<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of user's favorites.
     */
    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        return response()->json($favorites, 200);
    }

    /**
     * Add a product to favorites.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Verificar si el producto existe
        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // Verificar si ya está en favoritos
        $existingFavorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'El producto ya está en favoritos'
            ], 409); // 409 Conflict
        }

        // Crear favorito
        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
        ]);

        return response()->json($favorite, 201);
    }

    /**
     * Remove a product from favorites.
     */
    public function destroy(Request $request, string $productId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorito no encontrado'
            ], 404);
        }

        $favorite->delete();

        return response()->json(null, 204);
    }
}

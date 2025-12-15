<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'comentario' => 'required|string|max:200',
        ]);

        // Verificar que el producto exista
        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // Crear comentario
        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
            'comentario' => $validated['comentario'],
        ]);

        // Cargar la relación con el usuario para la respuesta
        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }

    /**
     * Get all comments for a specific product.
     */
    public function getByProduct(string $productId)
    {
        // Verificar que el producto exista
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // Obtener comentarios con información del usuario
        $comments = Comment::where('product_id', $productId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments, 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'url_imagen' => 'nullable|string',
        ]);

        $product = Product::create($validated);
        return response()->json($product,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Buscar el producto (si no existe, falla automáticamente)
        $product = Product::findOrFail($id);

        // 2. Validación de los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            'url_imagen' => 'nullable|string', 
        ]);

        // 3. Actualizar
        $product->update($validated);

        // 4. Retornar el producto actualizado
        return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // 1. Buscar el producto
        $product = Product::findOrFail($id);

        // 2. Eliminar
        $product->delete();

        // 3. Responder (204 significa "Sin contenido", es el estándar para borrar)
        return response()->json(null, 204);
    }
}

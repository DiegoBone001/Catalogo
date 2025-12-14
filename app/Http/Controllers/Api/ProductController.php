<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

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
            'url_imagen' => 'nullable|image|max:5120',
        ]);

        $url_imagen_final = null;

        if ($request->hasFile('url_imagen')) {
            $file = $request->file('url_imagen');
            
            // Generar un nombre único para el archivo
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Guardar en storage/app/public/products
            $path = $file->storeAs('products', $filename, 'public');
            
            // Generar la URL pública
            $url_imagen_final = asset('storage/' . $path);
        }

        $product = Product::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'precio' => $validated['precio'],
            'url_imagen' => $url_imagen_final,
        ]);
        
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

        // 2. Eliminar imagen del storage si existe
        if ($product->url_imagen) {
            $imagePath = str_replace(asset('storage/'), '', $product->url_imagen);
            Storage::disk('public')->delete($imagePath);
        }

        // 3. Eliminar producto
        $product->delete();

        // 4. Responder (204 significa "Sin contenido", es el estándar para borrar)
        return response()->json(null, 204);
    }

    /**
     * Eliminar todos los productos excepto el más reciente (útil para desarrollo)
     */
    public function cleanupOldProducts()
    {
        // Obtener el último producto creado
        $latestProduct = Product::latest('id')->first();

        if (!$latestProduct) {
            return response()->json([
                'message' => 'No hay productos en la base de datos'
            ], 404);
        }

        // Obtener todos los productos excepto el último
        $productsToDelete = Product::where('id', '!=', $latestProduct->id)->get();

        $deletedCount = 0;

        // Eliminar cada producto y su imagen
        foreach ($productsToDelete as $product) {
            // Eliminar imagen del storage si existe
            if ($product->url_imagen) {
                $imagePath = str_replace(asset('storage/'), '', $product->url_imagen);
                Storage::disk('public')->delete($imagePath);
            }

            $product->delete();
            $deletedCount++;
        }

        return response()->json([
            'message' => "Se eliminaron {$deletedCount} producto(s)",
            'latest_product_kept' => [
                'id' => $latestProduct->id,
                'nombre' => $latestProduct->nombre
            ]
        ], 200);
    }
}

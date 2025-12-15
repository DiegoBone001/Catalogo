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
            $path = 'products/' . $filename;
            
            // Subir el archivo a Firebase Storage
            Storage::disk('firebase')->put($path, file_get_contents($file));
            
            // Generar la URL pública de Firebase Storage
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $encodedPath = str_replace('/', '%2F', $path);
            $url_imagen_final = "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media";
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
        $product = Product::find($id);

        // 2. Si no existe, devolvemos un error 404
        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // 3. Si existe, devolvemos el producto con estado 200
        return response()->json($product, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Buscar el producto (si no existe, falla automáticamente)
        $product = Product::findOrFail($id);

        if (!$product) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // 2. Validación de los datos (solo los campos que vienen en la petición)
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|required|numeric',
            'url_imagen' => 'nullable|image|max:5120', 
        ]);

        // 3. Manejar la subida de nueva imagen
        if ($request->hasFile('url_imagen')) {
            
            // 3.1 Eliminar la imagen anterior si existe
            if ($product->url_imagen) {
                try {
                    // Detectar si es URL de Firebase o storage local
                    if (str_contains($product->url_imagen, 'firebasestorage.googleapis.com')) {
                        // Es Firebase Storage
                        $urlParts = parse_url($product->url_imagen);
                        $pathWithBucket = ltrim($urlParts['path'], '/');
                        $bucketName = env('FIREBASE_STORAGE_BUCKET');
                        $pathToDelete = str_replace($bucketName . '/', '', $pathWithBucket);
                        
                        Storage::disk('firebase')->delete($pathToDelete);
                    } else {
                        // Es storage local
                        $imagePath = str_replace(asset('storage/'), '', $product->url_imagen);
                        Storage::disk('public')->delete($imagePath);
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo eliminar la imagen anterior:', ['error' => $e->getMessage()]);
                    // Si falla el borrado, seguimos adelante (no detenemos la actualización)
                }
            }

            // 3.2 Subir la nueva imagen a Firebase
            $file = $request->file('url_imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'products/' . $filename;

            // Subir el archivo a Firebase Storage
            Storage::disk('firebase')->put($path, file_get_contents($file));
            
            // 3.3 Generar la URL pública de Firebase Storage manualmente
            // Formato: https://firebasestorage.googleapis.com/v0/b/{bucket}/o/{path_encoded}?alt=media
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $encodedPath = str_replace('/', '%2F', $path);
            $validated['url_imagen'] = "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media";
        }

        // 4. Actualizar solo los campos que vinieron en la petición
        $product->update(array_filter($validated, function($value) {
            return $value !== null;
        }));

        // 5. Retornar el producto actualizado (refrescar desde la BD)
        return response()->json($product->fresh(), 200);
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
            try {
                // Detectar si es URL de Firebase o storage local
                if (str_contains($product->url_imagen, 'firebasestorage.googleapis.com')) {
                    // Es Firebase Storage - extraer path y eliminar
                    $urlParts = parse_url($product->url_imagen);
                    $pathWithBucket = ltrim($urlParts['path'], '/');
                    $bucketName = env('FIREBASE_STORAGE_BUCKET');
                    $pathToDelete = str_replace($bucketName . '/', '', $pathWithBucket);
                    
                    Storage::disk('firebase')->delete($pathToDelete);
                } else {
                    // Es storage local
                    $imagePath = str_replace(asset('storage/'), '', $product->url_imagen);
                    Storage::disk('public')->delete($imagePath);
                }
            } catch (\Exception $e) {
                \Log::warning('No se pudo eliminar la imagen del producto:', ['error' => $e->getMessage()]);
                // Continuar con la eliminación del producto aunque falle la eliminación de la imagen
            }
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

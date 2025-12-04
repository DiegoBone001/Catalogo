<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; // ¡Necesario para encriptar la contraseña!
use Illuminate\Validation\ValidationException; // ¡Necesario para manejar las validaciones!

class AuthController extends Controller
{
    //
    public function register(Request $request){
        // 1. VALIDACIÓN
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // 'confirmed' requiere un campo 'password_confirmation'
        ]);

        // 2. CREACIÓN DEL USUARIO
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        // 3. GENERACIÓN DEL TOKEN
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. Validar
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Buscar usuario
        $user = User::where('email', $request->email)->first();

        // 3. Verificar usuario y contraseña
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas' // Mensaje si falla
            ], 401);
        }

        // 4. Generar Token (¡Esto es lo que faltaba antes del return!)
        $token = $user->createToken('auth-token')->plainTextToken;

        // 5. Respuesta
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }
}

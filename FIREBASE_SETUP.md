# 🔥 Firebase Setup Instructions

## Para poder usar el panel de administración, necesitas configurar Firebase:

### 1. Obtener Credenciales de Firebase

1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Selecciona tu proyecto (el mismo que usaste para el backend)
3. Ve a **Project Settings** (⚙️ icono)
4. En la sección **"Your apps"**, busca la app web o créala
5. Copia las credenciales de configuración

### 2. Configurar `firebase-config.js`

Abre el archivo: `public/js/firebase-config.js`

Reemplaza estos valores:

```javascript
const firebaseConfig = {
    apiKey: "TU_API_KEY_AQUI",           // ← Reemplazar
    authDomain: "TU_PROJECT_ID.firebaseapp.com",  // ← Reemplazar
    projectId: "TU_PROJECT_ID",          // ← Reemplazar
    storageBucket: "TU_PROJECT_ID.appspot.com",  // ← Reemplazar
    messagingSenderId: "TU_MESSAGING_SENDER_ID",  // ← Reemplazar
    appId: "TU_APP_ID"                   // ← Reemplazar
};
```

Con los valores exactos de tu proyecto Firebase.

### 3. Configurar Reglas de Storage (Importante!)

En Firebase Console:
1. Ve a **Storage**
2. Click en **Rules**
3. Usa estas reglas:

```
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /products/{allPaths=**} {
      allow read: if true;  // Todos pueden leer
      allow write: if request.auth != null;  // Solo usuarios autenticados
    }
  }
}
```

4. Click en **Publish**

### 4. Probar el Panel de Administración

1. Asegúrate de que el servidor Laravel está corriendo (`php artisan serve`)
2. Abre `http://127.0.0.1:8000/admin.html` en tu navegador
3. Inicia sesión con una cuenta de **admin**
4. Prueba crear un producto con imagen

---

## Crear Usuario Admin

Si aún no tienes un usuario admin, ejecuta:

```bash
php artisan tinker
```

Luego:

```php
$user = User::find(1); // O el ID de tu usuario
$user->role = 'admin';
$user->save();
exit
```

---

## ¿Problemas?

### Error: "No config para Firebase"
→ Asegúrate de haber reemplazado los valores en `firebase-config.js`

### Error: "Upload failed"
→ Verifica las reglas de Storage en Firebase Console

### Error: "Acceso denegado"
→ Verifica que tu usuario tenga `role = 'admin'`

### Error: "CORS"
→ Firebase maneja CORS automáticamente, pero asegúrate de usar HTTPS en producción

---

¡Listo! Ahora puedes gestionar productos con imágenes desde el panel de admin.

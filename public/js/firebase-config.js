// Firebase Configuration
// INSTRUCCIONES: Reemplaza estos valores con tus credenciales de Firebase Web
// Puedes obtenerlas desde: Firebase Console > Project Settings > General > Your apps > Web app

const firebaseConfig = {
    apiKey: "TU_API_KEY_AQUI",
    authDomain: "TU_PROJECT_ID.firebaseapp.com",
    projectId: "TU_PROJECT_ID",
    storageBucket: "TU_PROJECT_ID.appspot.com",
    messagingSenderId: "TU_MESSAGING_SENDER_ID",
    appId: "TU_APP_ID"
};

// NO MODIFICAR EL CÓDIGO DEBAJO DE ESTA LÍNEA
// ============================================

// Importar Firebase SDK desde CDN
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getStorage, ref, uploadBytes, getDownloadURL } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-storage.js';

// Inicializar Firebase
const app = initializeApp(firebaseConfig);
const storage = getStorage(app);

/**
 * Upload an image to Firebase Storage
 * @param {File} file - Image file to upload
 * @param {string} folder - Folder name in storage (default: 'products')
 * @returns {Promise<string>} URL of uploaded image
 */
export async function uploadImageToFirebase(file, folder = 'products') {
    try {
        if (!file) {
            throw new Error('No file provided');
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            throw new Error('Invalid file type. Only JPEG, PNG, and WebP are allowed.');
        }

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            throw new Error('File size exceeds 5MB limit.');
        }

        // Create unique filename
        const timestamp = Date.now();
        const filename = `${timestamp}_${file.name}`;
        const filePath = `${folder}/${filename}`;

        // Create storage reference
        const storageRef = ref(storage, filePath);

        // Upload file
        console.log(`Uploading to Firebase: ${filePath}`);
        const snapshot = await uploadBytes(storageRef, file);
        console.log('Upload successful:', snapshot);

        // Get download URL
        const downloadURL = await getDownloadURL(snapshot.ref);
        console.log('Download URL:', downloadURL);

        return downloadURL;
    } catch (error) {
        console.error('Firebase upload error:', error);
        throw error;
    }
}

/**
 * Get storage bucket name
 * @returns {string} Storage bucket name
 */
export function getStorageBucket() {
    return firebaseConfig.storageBucket;
}

export { storage };

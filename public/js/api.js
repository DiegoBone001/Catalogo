// API Configuration
const API_BASE = 'http://127.0.0.1:8000/api';

// Helper function to get auth token
function getToken() {
    return localStorage.getItem('auth_token');
}

// Helper function to set auth token
function setToken(token) {
    localStorage.setItem('auth_token', token);
}

// Helper function to remove auth token
function removeToken() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
}

// Helper function to make authenticated requests
async function authFetch(url, options = {}) {
    const token = getToken();
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, {
        ...options,
        headers,
    });

    return response;
}

// ========== AUTH ENDPOINTS ==========

export async function login(email, password) {
    try {
        const response = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });

        const data = await response.json();

        if (response.ok) {
            setToken(data.token);
            localStorage.setItem('user_data', JSON.stringify(data.user));
            return { success: true, data };
        }

        return { success: false, message: data.message || 'Error al iniciar sesión' };
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function register(name, email, password, password_confirmation) {
    try {
        const response = await fetch(`${API_BASE}/auth/register`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name, email, password, password_confirmation }),
        });

        const data = await response.json();

        if (response.ok) {
            setToken(data.token);
            localStorage.setItem('user_data', JSON.stringify(data.user));
            return { success: true, data };
        }

        return { success: false, message: data.message || 'Error al registrarse' };
    } catch (error) {
        console.error('Register error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function logout() {
    try {
        await authFetch(`${API_BASE}/auth/logout`, { method: 'POST' });
        removeToken();
        return { success: true };
    } catch (error) {
        console.error('Logout error:', error);
        removeToken(); // Remove token anyway
        return { success: true };
    }
}

export function isAuthenticated() {
    return !!getToken();
}

export function getCurrentUser() {
    const userData = localStorage.getItem('user_data');
    return userData ? JSON.parse(userData) : null;
}

// ========== PRODUCTS ENDPOINTS ==========

export async function getProducts() {
    try {
        const response = await fetch(`${API_BASE}/products`);
        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: 'Error al cargar productos' };
    } catch (error) {
        console.error('Get products error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function getProduct(id) {
    try {
        const response = await authFetch(`${API_BASE}/products/${id}`);
        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: 'Producto no encontrado' };
    } catch (error) {
        console.error('Get product error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// ========== FAVORITES ENDPOINTS ==========

export async function getFavorites() {
    try {
        const response = await authFetch(`${API_BASE}/favorites`);
        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: 'Error al cargar favoritos' };
    } catch (error) {
        console.error('Get favorites error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function addToFavorites(productId) {
    try {
        const response = await authFetch(`${API_BASE}/favorites`, {
            method: 'POST',
            body: JSON.stringify({ product_id: productId }),
        });

        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: data.message || 'Error al agregar a favoritos' };
    } catch (error) {
        console.error('Add to favorites error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function removeFromFavorites(productId) {
    try {
        console.log(`[API] DELETE ${API_BASE}/favorites/${productId}`);
        const response = await authFetch(`${API_BASE}/favorites/${productId}`, {
            method: 'DELETE',
        });

        console.log('[API] Response status:', response.status);
        console.log('[API] Response ok:', response.ok);

        if (response.status === 204 || response.ok) {
            console.log('[API] ✅ Successfully removed from favorites');
            return { success: true };
        }

        // Try to get error message from response
        const data = await response.json().catch(() => ({}));
        console.log('[API] Error response data:', data);

        return { success: false, message: data.message || 'Error al quitar de favoritos' };
    } catch (error) {
        console.error('[API] Remove from favorites error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// ========== COMMENTS ENDPOINTS ==========

export async function getComments(productId) {
    try {
        const response = await fetch(`${API_BASE}/comments/${productId}`);
        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: 'Error al cargar comentarios' };
    } catch (error) {
        console.error('Get comments error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

export async function addComment(productId, comentario) {
    try {
        const response = await authFetch(`${API_BASE}/comments`, {
            method: 'POST',
            body: JSON.stringify({ product_id: productId, comentario }),
        });

        const data = await response.json();

        if (response.ok) {
            return { success: true, data };
        }

        return { success: false, message: data.message || 'Error al agregar comentario' };
    } catch (error) {
        console.error('Add comment error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// ========== EXTERNAL API ENDPOINT ==========

export async function getWeather() {
    try {
        const response = await fetch(`${API_BASE}/external`);
        const data = await response.json();

        if (response.ok && data.success) {
            return { success: true, data };
        }

        return { success: false, message: data.message || 'Error al cargar clima' };
    } catch (error) {
        console.error('Get weather error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

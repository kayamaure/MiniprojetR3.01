// Gestionnaire d'authentification
class AuthManager {
    constructor() {
        this.tokenKey = 'auth_token';
        this.tokenExpiryKey = 'token_expiry';
        this.checkInterval = 30000; // Vérifier toutes les 30 secondes
        this.startTokenCheck();
    }

    // Démarre la vérification périodique du token
    startTokenCheck() {
        setInterval(() => this.checkTokenExpiration(), this.checkInterval);
    }

    // Sauvegarde le token et sa date d'expiration
    setToken(token, expiresIn = 900) { // 900 secondes = 15 minutes
        localStorage.setItem(this.tokenKey, token);
        const expiryTime = new Date().getTime() + (expiresIn * 1000);
        localStorage.setItem(this.tokenExpiryKey, expiryTime);
    }

    // Récupère le token
    getToken() {
        return localStorage.getItem(this.tokenKey);
    }

    // Vérifie si l'utilisateur est connecté
    isLoggedIn() {
        return this.getToken() !== null && !this.isTokenExpired();
    }

    // Vérifie si le token est expiré
    isTokenExpired() {
        const expiry = localStorage.getItem(this.tokenExpiryKey);
        if (!expiry) return true;
        return new Date().getTime() > parseInt(expiry);
    }

    // Déconnecte l'utilisateur
    logout() {
        const token = this.getToken();
        if (token) {
            // Appel à l'API de déconnexion
            fetch('http://127.0.0.1/MiniprojetR3.01/api-auth/index.php?action=logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            }).finally(() => {
                this.clearAuth();
                window.location.href = 'connexion.php';
            });
        } else {
            this.clearAuth();
            window.location.href = 'connexion.php';
        }
    }

    // Efface les données d'authentification
    clearAuth() {
        localStorage.removeItem(this.tokenKey);
        localStorage.removeItem(this.tokenExpiryKey);
    }

    // Vérifie l'expiration du token et déconnecte si nécessaire
    checkTokenExpiration() {
        if (this.getToken() && this.isTokenExpired()) {
            console.log('Token expiré, déconnexion automatique...');
            this.logout();
        }
    }
}

// Création de l'instance globale
const authManager = new AuthManager();

// Fonction pour protéger les pages qui nécessitent une authentification
function requireAuth() {
    if (!authManager.isLoggedIn()) {
        window.location.href = 'connexion.php';
    }
}

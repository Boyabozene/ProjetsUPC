/**
 * LOGIN.JS - Gestion de la page de connexion Energy+
 * Fonctionnalités : Animation de chargement, curseur personnalisé, formulaire de connexion
 */

// ==================== ANIMATION DE CHARGEMENT ====================

/**
 * Gestion de l'animation de chargement au démarrage de la page
 * Masque le loader après 1.5 secondes
 */
window.addEventListener('load', () => {
  setTimeout(() => {
    document.getElementById('pageLoader').classList.add('hidden');
  }, 1500);
});

// ==================== CURSEUR PERSONNALISÉ ====================

// Éléments du curseur personnalisé
const cursor = document.getElementById('cursor');
const cursorFollower = document.getElementById('cursorFollower');

/**
 * Suit le mouvement de la souris avec un curseur personnalisé
 * Crée un effet visuel avec deux éléments qui suivent la souris
 */
document.addEventListener('mousemove', (e) => {
  // Positionner le curseur principal (petit cercle)
  cursor.style.transform = `translate(${e.clientX - 10}px, ${e.clientY - 10}px)`;
  
  // Positionner le curseur suiveur (cercle plus grand, avec délai)
  cursorFollower.style.transform = `translate(${e.clientX - 20}px, ${e.clientY - 20}px)`;
});

// ==================== GESTION DU FORMULAIRE ====================

/**
 * Gestion du formulaire de connexion
 * Validation, envoi à l'API, gestion des erreurs et redirection
 */
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Récupération des éléments du formulaire
  const email = document.getElementById('email');
  const password = document.getElementById('password');
  const loginAlert = document.getElementById('loginAlert');
  const submitBtn = e.target.querySelector('button[type="submit"]');
  
  // Sauvegarde du texte original du bouton
  const originalText = submitBtn.textContent;
  
  // Animation du bouton pendant le chargement
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Connexion...';
  
  try {
    // Envoi de la requête de connexion à l'API
    const res = await fetch('../api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        email: email.value.trim(), 
        password: password.value 
      }),
      credentials: 'include'  // Inclure les cookies de session
    });
    
    const data = await res.json();
    
    if (res.ok) {
      // ==================== CONNEXION RÉUSSIE ====================
      
      // Animation de succès sur le bouton
      submitBtn.innerHTML = '✓ Connexion réussie';
      submitBtn.style.background = '#059669';  // Vert de succès
      
      // Masquer les alertes d'erreur précédentes
      loginAlert.classList.add('d-none');
      
      // Redirection vers le tableau de bord après 1 seconde
      setTimeout(() => {
        window.location.href = 'dashboard.html';
      }, 1000);
      
    } else {
      // ==================== ERREUR DE CONNEXION ====================
      
      // Affichage du message d'erreur
      loginAlert.textContent = data.error || 'Erreur de connexion';
      loginAlert.classList.remove('d-none');
      
      // Animation d'erreur (tremblement)
      loginAlert.style.animation = 'shake 0.5s ease-in-out';
      
      // Réinitialisation du bouton
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
      submitBtn.style.background = '';  // Retour au style original
      
      // Masquer l'alerte automatiquement après 5 secondes
      setTimeout(() => {
        loginAlert.classList.add('d-none');
        loginAlert.style.animation = '';  // Arrêter l'animation
      }, 5000);
    }
    
  } catch (error) {
    // ==================== ERREUR RÉSEAU ====================
    
    console.error('Erreur réseau:', error);
    
    // Affichage d'un message d'erreur générique
    loginAlert.textContent = 'Erreur de connexion au serveur';
    loginAlert.classList.remove('d-none');
    
    // Réinitialisation du bouton
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
    submitBtn.style.background = '';
  }
});

// ==================== ANIMATIONS DES CHAMPS ====================

/**
 * Ajoute des animations aux champs de saisie lors du focus/blur
 * Effet de légère mise à l'échelle pour améliorer l'UX
 */
document.querySelectorAll('.form-control').forEach(input => {
  // Animation au focus (champ sélectionné)
  input.addEventListener('focus', (e) => {
    e.target.parentElement.style.transform = 'scale(1.02)';
    e.target.parentElement.style.transition = 'transform 0.2s ease';
  });
  
  // Animation au blur (champ désélectionné)
  input.addEventListener('blur', (e) => {
    e.target.parentElement.style.transform = 'scale(1)';
  });
});

// ==================== STYLES CSS DYNAMIQUES ====================

/**
 * Injection de styles CSS supplémentaires pour les animations
 * Keyframes pour l'effet de tremblement et le spinner
 */
const style = document.createElement('style');
style.textContent = `
  /* Animation de tremblement pour les erreurs */
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
  }
  
  /* Spinner de chargement Bootstrap */
  .spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
  }
`;

// Ajouter les styles au document
document.head.appendChild(style);
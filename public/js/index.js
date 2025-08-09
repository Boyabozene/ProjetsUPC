/**
 * INDEX.JS - Animations et interactions de la page d'accueil Energy+
 * Fonctionnalités : Curseur personnalisé, animations de particules
 */

// ==================== INITIALISATION ====================

/**
 * Démarre les animations après le chargement complet du DOM
 * Évite les erreurs si les éléments ne sont pas encore chargés
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // ==================== ANIMATION CURSEUR BOUTON ====================
    
    // Récupération du bouton principal "Se connecter"
    const btnPrimary = document.querySelector('.btn-primary');
    
    if (btnPrimary) {
        // Variables pour suivre la position du curseur
        let cursorX = 0;
        let cursorY = 0;
        
        /**
         * Suit la position de la souris sur le bouton
         * Met à jour les variables CSS personnalisées pour l'effet de curseur
         */
        btnPrimary.addEventListener('mousemove', function(e) {
            // Calculer la position relative à l'élément
            const rect = this.getBoundingClientRect();
            cursorX = e.clientX - rect.left;
            cursorY = e.clientY - rect.top;
            
            // Mettre à jour les propriétés CSS personnalisées
            this.style.setProperty('--cursor-x', cursorX + 'px');
            this.style.setProperty('--cursor-y', cursorY + 'px');
        });
        
        /**
         * Animation d'entrée du curseur sur le bouton
         * Active l'effet visuel lors du survol
         */
        btnPrimary.addEventListener('mouseenter', function() {
            this.style.setProperty('--cursor-scale', '1');
        });
        
        /**
         * Animation de sortie du curseur du bouton
         * Désactive l'effet visuel quand la souris sort
         */
        btnPrimary.addEventListener('mouseleave', function() {
            this.style.setProperty('--cursor-scale', '0');
        });
        
        /**
         * Effet de clic - Agrandissement temporaire
         * Feedback visuel lors du clic
         */
        btnPrimary.addEventListener('mousedown', function() {
            this.style.setProperty('--cursor-scale', '1.5');
        });
        
        /**
         * Retour à la normale après le clic
         */
        btnPrimary.addEventListener('mouseup', function() {
            this.style.setProperty('--cursor-scale', '1');
        });
    }
    
    // ==================== ANIMATIONS DES PARTICULES ====================
    
    // Récupération du titre principal
    const title = document.querySelector('header h1');
    
    if (title) {
        /**
         * Active l'animation des particules au survol du titre
         * Ajoute une classe CSS qui déclenche des animations spéciales
         */
        title.addEventListener('mouseenter', function() {
            // Ajouter une classe pour déclencher l'animation des particules
            const particles = document.querySelector('.particles');
            if (particles) {
                particles.classList.add('active');
            }
        });
        
        /**
         * Désactive l'animation des particules quand la souris sort du titre
         */
        title.addEventListener('mouseleave', function() {
            const particles = document.querySelector('.particles');
            if (particles) {
                particles.classList.remove('active');
            }
        });
    }
    
    // ==================== EFFETS SUPPLÉMENTAIRES ====================
    
    /**
     * Ajoute un effet de parallaxe léger aux formes décoratives
     * Suit les mouvements de la souris pour un effet de profondeur
     */
    document.addEventListener('mousemove', function(e) {
        // Récupérer les particules décoratives
        const particles = document.querySelectorAll('.particle');
        
        // Calculer la position relative de la souris (0-1)
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        // Appliquer un léger décalage aux particules
        particles.forEach((particle, index) => {
            // Décalage différent pour chaque particule (effet de profondeur)
            const moveX = (mouseX - 0.5) * (10 + index * 5);
            const moveY = (mouseY - 0.5) * (10 + index * 5);
            
            // Appliquer la transformation
            particle.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    });
    
    // ==================== PERFORMANCE ====================
    
    /**
     * Optimisation : Throttle des événements mousemove
     * Évite de surcharger le navigateur avec trop d'animations
     */
    let animationFrame;
    
    function throttledMouseMove(e) {
        // Annuler la frame précédente si elle existe
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        
        // Programmer la prochaine frame
        animationFrame = requestAnimationFrame(() => {
            // Les animations mousemove sont déjà définies ci-dessus
            // Cette fonction peut être étendue pour d'autres optimisations
        });
    }
    
    // Remplacer l'événement mousemove par la version optimisée si nécessaire
    // document.addEventListener('mousemove', throttledMouseMove);
});
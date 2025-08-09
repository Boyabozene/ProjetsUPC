<?php
/**
 * Fonctions utilitaires et de sécurité - Energy+ App
 * 
 * Ce fichier contient toutes les fonctions communes pour la sécurité,
 * la validation des données et la gestion des sessions
 */

/**
 * Démarre une session de manière sécurisée
 * Configure les paramètres de sécurité et régénère l'ID périodiquement
 */
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration sécurisée des cookies de session
        session_set_cookie_params([
            'lifetime' => 3600,    // Durée de vie: 1 heure
            'path' => '/',         // Chemin valide pour le cookie
            'domain' => '',        // Domaine (vide = domaine actuel)
            'secure' => false,     // HTTPS uniquement (false pour développement)
            'httponly' => true,    // Pas d'accès JavaScript au cookie
            'samesite' => 'Lax'    // Protection CSRF
        ]);
        
        session_start();
        
        // Régénération périodique de l'ID de session (sécurité)
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Envoie une réponse JSON formatée avec les headers appropriés
 * 
 * @param mixed $data Les données à retourner
 * @param int $status_code Le code de statut HTTP (par défaut 200)
 */
function respond($data, $status_code = 200) {
    // Nettoyer tout output précédent pour éviter les erreurs JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Configuration des headers HTTP
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    
    // Encodage JSON avec support Unicode
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Hash un mot de passe de manière sécurisée
 * 
 * @param string $password Le mot de passe à hasher
 * @return string Le hash sécurisé
 */
function hash_password($password) {
    // Utilisation de PASSWORD_DEFAULT (bcrypt) pour la compatibilité
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Valide la force d'un mot de passe selon les critères de sécurité
 * 
 * @param string $password Le mot de passe à valider
 * @return bool|string true si valide, message d'erreur sinon
 */
function validate_password($password) {
    if (strlen($password) < 8) {
        return "Le mot de passe doit contenir au moins 8 caractères";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Le mot de passe doit contenir au moins une majuscule";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Le mot de passe doit contenir au moins une minuscule";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Le mot de passe doit contenir au moins un chiffre";
    }
    return true;
}

/**
 * Valide qu'un champ requis n'est pas vide
 * 
 * @param mixed $value La valeur à valider
 * @return bool true si la valeur n'est pas vide
 */
function validate_required($value) {
    return !empty(trim($value));
}

/**
 * Valide le format d'une adresse email
 * 
 * @param string $email L'email à valider
 * @return bool true si l'email est valide
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Nettoie une entrée utilisateur contre les attaques XSS
 * 
 * @param string $input L'entrée à nettoyer
 * @return string L'entrée nettoyée
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Vérifie si un utilisateur est authentifié et sa session est valide
 * 
 * @return int|false L'ID utilisateur si authentifié, false sinon
 */
function is_authenticated() {
    start_session();
    
    // Vérifier la présence de l'ID utilisateur en session
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Vérifier l'expiration de la session (timeout d'inactivité)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 3600) {
        session_destroy();
        return false;
    }
    
    // Vérifier l'user agent pour détecter le vol de session
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        return false;
    }
    
    // Mettre à jour le timestamp de dernière activité
    $_SESSION['last_activity'] = time();
    return $_SESSION['user_id'];
}

/**
 * Système de limitation du taux de requêtes par IP (rate limiting)
 * 
 * @param string $action L'action à limiter
 * @param int $max_requests Nombre maximum de requêtes autorisées
 * @param int $time_window Fenêtre de temps en secondes
 */
function rate_limit($action, $max_requests = 60, $time_window = 3600) {
    start_session();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";
    
    // Initialiser les données de rate limiting si nécessaire
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 0,
            'start_time' => time()
        ];
    }
    
    $data = &$_SESSION[$key];
    
    // Réinitialiser le compteur si la fenêtre de temps est expirée
    if (time() - $data['start_time'] > $time_window) {
        $data['count'] = 0;
        $data['start_time'] = time();
    }
    
    $data['count']++;
    
    // Bloquer si le nombre maximum de requêtes est dépassé
    if ($data['count'] > $max_requests) {
        respond(['error' => 'Trop de requêtes, veuillez patienter'], 429);
    }
}

/**
 * Valide et nettoie une entrée selon son type
 * 
 * @param mixed $input L'entrée à traiter
 * @param string $type Le type de validation ('string', 'email', 'int', 'float')
 * @param int $max_length Longueur maximale pour les chaînes
 * @return mixed|false La valeur validée ou false en cas d'erreur
 */
function validate_and_sanitize($input, $type = 'string', $max_length = 255) {
    if (empty($input)) {
        return false;
    }
    
    switch ($type) {
        case 'email':
            $input = filter_var(trim($input), FILTER_VALIDATE_EMAIL);
            break;
        case 'int':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            break;
        case 'float':
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            break;
        case 'string':
        default:
            $input = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            if (strlen($input) > $max_length) {
                return false;
            }
            break;
    }
    
    return $input;
}

/**
 * Système de logging sécurisé pour tracer les activités
 * 
 * @param string $level Niveau de log ('INFO', 'WARNING', 'ERROR')
 * @param string $message Message à enregistrer
 * @param array $context Contexte supplémentaire
 */
function secure_log($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    
    // Structure du log en JSON
    $log_entry = [
        'timestamp' => $timestamp,
        'level' => $level,
        'message' => $message,
        'user_id' => $user_id,
        'ip' => $ip,
        'context' => $context
    ];
    
    // En production, utiliser un système de logging plus robuste (files, syslog, etc.)
    error_log(json_encode($log_entry));
}

/**
 * Protection avancée contre l'injection SQL
 * Détecte les patterns dangereux dans les entrées utilisateur
 * 
 * @param string $input L'entrée à valider
 * @param string $type Le type de validation
 * @return mixed|false La valeur validée ou false si dangereuse
 */
function validate_sql_input($input, $type = 'string') {
    // Patterns SQL dangereux à détecter
    $dangerous_patterns = [
        '/union\s+select/i',    // UNION SELECT attacks
        '/drop\s+table/i',      // DROP TABLE attacks
        '/delete\s+from/i',     // DELETE attacks
        '/update\s+.*set/i',    // UPDATE attacks
        '/insert\s+into/i',     // INSERT attacks
        '/exec\s*\(/i',         // Code execution
        '/script\s*>/i'         // Script injection
    ];
    
    // Vérifier chaque pattern dangereux
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            secure_log('WARNING', 'Tentative d\'injection SQL détectée', ['input' => $input]);
            return false;
        }
    }
    
    return validate_and_sanitize($input, $type);
}

/**
 * Gestionnaire d'erreurs de base de données avec logging sécurisé
 * 
 * @param Exception $exception L'exception PDO capturée
 * @param string $context Contexte de l'erreur
 */
function handle_db_error($exception, $context = '') {
    // Log de l'erreur avec détails techniques
    secure_log('ERROR', "Erreur base de données: $context", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    
    // Réponse générique pour éviter la fuite d'informations sensibles
    respond(['error' => 'Erreur de base de données'], 500);
}
?>

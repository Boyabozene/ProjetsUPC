## Compte démo

- demo@example.com / demo123
- alice@example.com / alice123
- bob@example.com / bob123

## Structure des fichiers

energy-monitor/
│
├── 📂 api/                          # API endpoints PHP
│   ├── 📄 auth.php                  # Authentification utilisateur
│   ├── 📄 consumption.php           # Gestion consommation électrique
│   ├── 📄 generate_pdf.php          # Génération des factures
│   ├── 📄 invoice.php               # Gestion des factures
│   └── 📄 logout.php                # Gestion de la déconnexion
│
├── 📂 config/                       # Configuration base de données
│   └── 📄 db.php                    # Connexion PDO MySQL
│
├── 📂 includes/                     # Fonctions utilitaires
│   └── 📄 functions.php             # Fonctions sécurité & helpers
│
├── 📂 public/                       # Interface utilisateur (Frontend)
│   ├── 📂 css/                      # Styles CSS personnalisés
│   │   ├── 📄 index.css             # Styles page d'accueil
│   │   ├── 📄 login.css             # Styles page connexion
│   │   └── 📄 dashboard.css         # Styles tableau de bord
│   │
│   ├── 📂 js/                       # Scripts JavaScript
│   │   ├── 📄 login.js              # Logique connexion
│   │   ├── 📄 index.js              # Logique Formulaire
│   │   └── 📄 dashboard.js          # Logique compteur & factures
│   │
│   ├── 📄 index.html                # Page d'accueil
│   ├── 📄 login.html                # Page de connexion
│   └── 📄 dashboard.html            # Tableau de bord utilisateur
│
├── 📂 sql/                          # Scripts base de données
│   └── 📄 energy_db.sql             # Structure + données utilisateurs
│
└── 📄 README.md                     # Comptes de test

# Analyse MERISE complète - Energy+

## MCD (Modèle Conceptuel des Données)

## Entités identifiées :

### 1. UTILISATEUR
**Propriétés :**
- id_utilisateur (Identifiant)
- nom (Chaîne de caractères, 100)
- email (Chaîne de caractères, 100, UNIQUE)
- mot_de_passe (Chaîne de caractères, 255)

### 2. CONSOMMATION  
**Propriétés :**
- id_consommation (Identifiant)
- date_mesure (Date/Heure)
- kwh_consomme (Entier)

### 3. FACTURE
**Propriétés :**
- id_facture (Identifiant)
- montant (Entier)
- statut (Énumération : "impayé", "payé")
- date_emission (Date/Heure)
- date_paiement (Date/Heure, optionnel)

## Relations identifiées :

### R1 : CONSOMMER
**Entre :** UTILISATEUR (1,n) ↔ (0,n) CONSOMMATION
**Signification :** Un utilisateur peut avoir plusieurs mesures de consommation

### R2 : FACTURER
**Entre :** UTILISATEUR (1,n) ↔ (0,n) FACTURE  
**Signification :** Un utilisateur peut avoir plusieurs factures

### R3 : CALCULER (optionnelle - relation ternaire)
**Entre :** CONSOMMATION (1,n) ↔ (1,1) FACTURE
**Signification :** Une facture est calculée à partir de plusieurs consommations

## Règles de gestion identifiées :

1. **RG01 :** Un utilisateur est identifié par son email unique
2. **RG02 :** Une consommation est toujours liée à un utilisateur
3. **RG03 :** Une facture est toujours liée à un utilisateur
4. **RG04 :** Le tarif est de 100 FC par kWh
5. **RG05 :** Une facture peut être "impayée" ou "payée"
6. **RG06 :** Seules les factures payées peuvent générer un PDF
7. **RG07 :** La suppression d'un utilisateur supprime ses données (CASCADE)

## Dictionnaire des données :

-------------------------------------------------------------------------------------------------
| Code           | Signification            | Type     | Taille | Remarques                     |
|----------------|--------------------------|----------|--------|-------------------------------|
| id_utilisateur | Identifiant utilisateur  | Entier   | -      | Clé primaire, auto-incrémenté |
| nom            | Nom complet              | Texte    | 100    | Obligatoire                   |
| email          | Adresse email            | Texte    | 100    | Unique, obligatoire           |
| mot_de_passe   | Mot de passe hashé       | Texte    | 255    | Hashé bcrypt                  |
| id_consommation| Identifiant consommation | Entier   | -      | Clé primaire                  |
| date_mesure    | Horodatage mesure        | DateTime | -      | Défaut: NOW()                 |
| kwh_consomme   | Consommation en kWh      | Entier   | -      | Positif                       |
| id_facture     | Identifiant facture      | Entier   | -      | Clé primaire                  |
| montant        | Montant en FC            | Entier   | -      | Positif                       |
| statut         | État paiement            | Enum     | -      | "impayé" ou "payé"            |
| date_emission  | Date création facture    | DateTime | -      | Défaut: NOW()                 |
| date_paiement  | Date paiement            | DateTime | -      | NULL si impayée               |
-------------------------------------------------------------------------------------------------

## Contraintes d'intégrité :

### Fonctionnelles :
- id_utilisateur → nom, email, mot_de_passe
- id_consommation → date_mesure, kwh_consomme, id_utilisateur  
- id_facture → montant, statut, date_emission, date_paiement, id_utilisateur

### Référentielles :
- CONSOMMATION.id_utilisateur → UTILISATEUR.id_utilisateur
- FACTURE.id_utilisateur → UTILISATEUR.id_utilisateur

### Métier :
- Un email ne peut être associé qu'à un seul utilisateur
- Le montant d'une facture doit être positif
- La date de paiement ne peut être antérieure à la date d'émission

## MLD (Modèle Logique des Données)

### Transformation MCD → MLD selon les règles MERISE :

**Table USERS :**
- id (Clé primaire, INT, AUTO_INCREMENT)
- name (VARCHAR(100), NOT NULL)
- email (VARCHAR(100), UNIQUE, NOT NULL)
- password (VARCHAR(255), NOT NULL)

**Table CONSUMPTION :**
- id (Clé primaire, INT, AUTO_INCREMENT)
- user_id (Clé étrangère → USERS.id, INT, NOT NULL)
- date (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- kwh (INT, NOT NULL)

**Table INVOICES :**
- id (Clé primaire, INT, AUTO_INCREMENT)  
- user_id (Clé étrangère → USERS.id, INT, NOT NULL)
- amount (INT, NOT NULL)
- status (ENUM('unpaid','paid'), DEFAULT 'unpaid')
- issued_at (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- paid_at (DATETIME, NULL)

### Contraintes référentielles :
- CONSUMPTION.user_id REFERENCES USERS.id ON DELETE CASCADE
- INVOICES.user_id REFERENCES USERS.id ON DELETE CASCADE

### Index recommandés :
- INDEX idx_email ON USERS(email)
- INDEX idx_user_date ON CONSUMPTION(user_id, date)  
- INDEX idx_user_status ON INVOICES(user_id, status)

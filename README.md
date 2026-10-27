# 🌿 Uber-Cueillette

**Mini-projet Web2 2025-2026 — ISG Tunis**

---

## Structure du projet

```
uber-cueillette/
│
├── index.php                    ← Page d'accueil (choix agriculteur / ouvrier)
├── config.php                   ← Connexion PDO à la base de données
├── db.sql                       ← Script SQL de création de la base de données
│
├── css/
│   └── style.css                ← Feuille de style principale (thème olive/terre)
│
├── js/
│   └── validation.js            ← Contrôle de saisie JavaScript côté client
│
├── agriculteur/
│   ├── inscription.php          ← Formulaire d'inscription agriculteur
│   ├── login.php                ← Authentification agriculteur
│   ├── logout.php               ← Déconnexion
│   ├── dashboard.php            ← Tableau de bord agriculteur
│   ├── ajouter_offre.php        ← Publier une nouvelle offre de récolte
│   ├── mes_offres.php           ← Lister et gérer ses offres
│   ├── postulants.php           ← Voir et gérer les candidats d'une offre
│   ├── noter_ouvriers.php       ← Évaluer les ouvriers après chantier
│   ├── profil.php               ← Voir et modifier son profil
│   └── get_photo.php            ← Serveur de photos ouvriers (depuis DB)
│
└── ouvrier/
    ├── inscription.php          ← Formulaire d'inscription ouvrier
    ├── login.php                ← Authentification ouvrier
    ← logout.php               ← Déconnexion
    ├── dashboard.php            ← Tableau de bord ouvrier
    ├── offres.php               ← Lister les offres + filtres + postuler
    ├── mes_candidatures.php     ← Suivi des candidatures soumises
    ├── mes_chantiers.php        ← Historique chantiers clôturés + notes + gains
    ├── profil.php               ← Voir et modifier son profil
    └── get_photo.php            ← Serveur de sa propre photo
```

---

## 🚀 Installation

### 1. Prérequis

- **XAMPP** (ou WAMP/MAMP) avec Apache + PHP 8+ + MySQL
- Un navigateur web moderne

### 2. Base de données

1. Démarrer MySQL via XAMPP Control Panel
2. Ouvrir **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Cliquer sur **Importer** → choisir le fichier `db.sql`
4. Cliquer sur **Exécuter**

### 3. Déploiement

1. Copier tout le dossier `uber-cueillette/` dans `C:\xampp\htdocs\`
2. Vérifier les paramètres dans `config.php` :
   ```php
   $host   = "localhost";
   $dbname = "uber-cueillette";
   $user   = "root";
   $pass   = "";   // Vide par défaut sur XAMPP
   ```
3. Ouvrir `http://localhost/uber-cueillette/` dans le navigateur

---

## 🎯 Fonctionnalités implémentées

### 👤 Partie commune

- [x] Page d'accueil avec choix du rôle
- [x] Inscription avec validation JS + PHP (CIN 8 chiffres, pseudo lettres, MDP finissant par $ ou #)
- [x] Authentification sécurisée (password_hash / password_verify)
- [x] Gestion de session (`$_SESSION`)
- [x] Modification du profil

### 🧑‍🌾 Agriculteur

- [x] S'inscrire / Se connecter
- [x] Tableau de bord avec statistiques
- [x] Ajouter une offre de récolte (fruit, gouvernorat, dates, prix, nombre ouvriers)
- [x] Lister ses offres (actives et clôturées)
- [x] Supprimer une offre sans postulants
- [x] Consulter la liste des postulants d'une offre
- [x] Voir le profil d'un postulant (photo, note moyenne, commentaires)
- [x] Accepter / Refuser une candidature
- [x] Clôture automatique quand le quota est atteint
- [x] Noter les ouvriers après chantier (0 à 10) + commentaire + rémunération

### 👷 Ouvrier de récolte

- [x] S'inscrire avec photo d'identité / Se connecter
- [x] Tableau de bord avec statistiques (candidatures, gains, note moyenne)
- [x] Lister les offres disponibles (non clôturées)
- [x] Filtrer par type de fruit, gouvernorat
- [x] Trier par prix à la journée (croissant / décroissant)
- [x] Postuler en un clic
- [x] Suivre ses candidatures (en attente / acceptée / refusée)
- [x] Consulter ses chantiers clôturés (notes, commentaires, gains)
- [x] Modifier son profil

---

## 🔒 Règles de validation (JS + PHP)

| Champ        | Règle                                                    |
| ------------ | -------------------------------------------------------- |
| CIN          | Exactement 8 chiffres                                    |
| Pseudo       | Lettres uniquement (A-Z, a-z)                            |
| Mot de passe | ≥ 8 caractères (lettres/chiffres) + finir par `$` ou `#` |
| Email        | Format valide `xxx@yyy.zzz`                              |
| Note         | Entier entre 0 et 10                                     |
| Date début   | Aujourd'hui ou future                                    |
| Date fin     | Après la date de début                                   |
| Date limite  | Avant la date de début                                   |

---

## 🗄️ Schéma de la base de données

```
type_fruit (id_type_fruit, libelle)
gouvernorat (id_gouvernorat, libelle)
agriculteur (id_agriculteur, nom, prenom, CIN, email, adresse, pseudo, password)
ouvrier     (id_ouvrier, nom, prenom, CIN, email, photo, description, pseudo, password)
offre       (id_offre, id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin,
             nombre_ouvriers, prix_journee, date_limite, id_agriculteur)
candidature (id_candidature, id_offre, id_ouvrier, decision, date_candidature,
             note, commentaire, remuneration)
```

---

## 🛠️ Technologies utilisées

| Côté            | Technologie                                |
| --------------- | ------------------------------------------ |
| Frontend        | HTML5, CSS3 (Variables CSS, Grid, Flexbox) |
| Validation      | JavaScript (Vanilla JS, Regex)             |
| Backend         | PHP 8+ (PDO, Sessions, password_hash)      |
| Base de données | MySQL (via phpMyAdmin)                     |
| Polices         | Google Fonts (Playfair Display + DM Sans)  |

---

## 👥 Auteurs

| Nom | Prénom | Email |
| --- | ------ | ----- |
| ... | ...    | ...   |
| ... | ...    | ...   |

# ⚔️ Battle Arena - Smash Bros Ultimate Tournament

Un système complet de gestion de tournoi pour **Smash Bros Ultimate** avec authentification, CRUD complet, API REST sécurisée, modération des matchs et génération de PDF asynchrone.

## 🎮 Fonctionnalités

- **🏆 Gestion Complète des Tournois**
  - Création/modification/suppression des tournois
  - Système d'élimination directe (pas de double affrontement)
  - Statuts: En attente, Actif, Terminé, Annulé

- **👥 Gestion des Joueurs**
  - Profil utilisateur avec pseudo, niveau (1-15), personnage principal
  - Statistiques (victoires/défaites/taux de victoire)
  - Interface de mise à jour du compte

- **🎯 Système de Matchs**
  - Création et planification des matchs
  - Soumission des résultats par les joueurs
  - Validation automatique si résultats cohérents
  - Modération admin si résultats en désaccord
  - Mise à jour automatique des statistiques

- **🛡️ Authentification et Autorisation**
  - Inscription avec confirmation par email
  - Rôles: User, Admin
  - Accès contrôlé aux zones protégées

- **👨‍💼 Interface Admin**
  - CRUD complet pour joueurs et matchs
  - Panel de modération pour les litiges
  - Génération de PDF des récapitulatifs (asynchrone via Messenger)
  - Vue d'ensemble des tournois

- **📊 API REST Sécurisée**
  - Endpoints pour récupérer les matchs d'un tournoi
  - Soumission des résultats via API
  - Classements en temps réel

- **📱 Vue Publique**
  - Liste des tournois (à venir, actifs, terminés)
  - Détails et avancement de chaque tournoi
  - Classements et résultats

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer (utilisez `php ..\composer.phar` sur Windows)
- SQLite (préconfiguré) ou une autre base de données

### Étapes

1. **Cloner ou utiliser le projet**
```bash
cd c:\Users\thoma\dev\battle_arena
```

2. **Installer les dépendances**
```bash
php ..\composer.phar install
```

3. **Configurer la base de données** (déjà en SQLite)
```bash
php bin/console doctrine:database:create --if-not-exists
```

4. **Exécuter les migrations**
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

5. **Charger les données de test**
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

6. **Lancer le serveur de développement**
```bash
php bin/console server:run
# ou
symfony serve
```

7. **Accéder à l'application**
- Application: http://localhost:8000
- Admin: http://localhost:8000/admin (email: admin@battlearena.com / password: admin123)

## 📊 Données de Test

Les fixtures créent automatiquement:
- **1 Admin**: admin@battlearena.com / admin123
- **10 Joueurs**: player1-10@battlearena.com / password123
- **1 Tournoi actif** avec matchs en attente et résultats

## 📚 Structure du Projet

```
src/
├── Controller/
│   ├── Admin/          # Contrôleurs admin
│   ├── Api/            # Endpoints API REST
│   ├── PlayerController.php     # Interface joueur
│   ├── TournamentController.php # Vue publique
│   └── SecurityController.php   # Authentification
├── Entity/             # Entités Doctrine
├── Repository/         # Accès aux données
├── Service/            # Logique métier
├── Message/            # Messages Messenger
├── MessageHandler/     # Traitement des messages
├── Validator/          # Contraintes personnalisées
└── DataFixtures/       # Données de test

templates/
├── tournament/         # Vues publiques
├── player/            # Interface joueur
├── admin/             # Interface admin
├── security/          # Login/Logout
├── pdf/               # Template PDF
└── email/             # Templates emails

config/
├── packages/          # Configuration des bundles
└── routes/            # Routes
```

## 🔐 Authentification

### Utilisateurs par défaut
| Email | Password | Rôle |
|-------|----------|------|
| admin@battlearena.com | admin123 | ROLE_ADMIN |
| player1@battlearena.com | password123 | ROLE_USER |
| player2-10@battlearena.com | password123 | ROLE_USER |

### Rôles et Permissions
- **ROLE_USER**: Accès au dashboard joueur, soumission de résultats
- **ROLE_ADMIN**: Accès complet au panel admin, modération

## 🎯 Workflows Principaux

### Inscription Joueur
1. Cliquer sur "Inscription"
2. Remplir le formulaire (email, pseudo, password, gamertag, niveau, personnage)
3. Email de confirmation envoyé
4. Accès au dashboard

### Créer un Match (Admin)
1. Aller dans Admin > Tournois
2. Sélectionner un tournoi
3. Créer un match en spécifiant deux joueurs
4. Le système empêche les affrontements doubles

### Soumettre un Résultat (Joueur)
1. Aller dans "Mon Compte"
2. Voir les matchs en attente
3. Cliquer sur "Saisir résultat"
4. Choisir son résultat (Victoire/Défaite/Égalité)
5. Attendre la soumission du second joueur
6. Si cohérent: match finalisé automatiquement
7. Si incohérent: envoyé en modération

### Modérer un Litige (Admin)
1. Aller dans Admin > Modération
2. Voir les matchs en désaccord
3. Approuver le résultat d'un joueur ou déclarer égalité
4. Ajouter des notes de modération optionnelles

### Générer PDF Récapitulatif (Admin)
1. Aller dans Admin > Tournois
2. Cliquer sur un tournoi
3. Cliquer "Générer PDF"
4. Message envoyé au queue Messenger (asynchrone)
5. Email reçu avec le PDF une fois traité

## 📡 API REST

### GET /api/tournament/{id}/matches
Récupère tous les matchs d'un tournoi
```json
[
  {
    "id": 1,
    "player1": { "id": 1, "gamertag": "Sonic", "character": "Sonic" },
    "player2": { "id": 2, "gamertag": "Mario", "character": "Mario" },
    "status": "pending",
    "player1_score": null,
    "player2_score": null
  }
]
```

### POST /api/tournament/{id}/match/{matchId}/submit-result
Soumettre un résultat pour un match
```json
{
  "result": "win"
}
```

### GET /api/tournament/{id}/standings
Classement d'un tournoi
```json
[
  {
    "id": 1,
    "gamertag": "Sonic",
    "character": "Sonic",
    "wins": 3,
    "losses": 1
  }
]
```

## 🔧 Configuration

### Variables d'environnement (.env)
```dotenv
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
MAILER_DSN=smtp://user:pass@localhost
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

### Messenger (Async Tasks)
Par défaut, utilise Doctrine comme transport. Pour tester l'asynchrone:
```bash
php bin/console messenger:consume doctrine
```

## ✅ Contraintes Implémentées

1. **Contrainte Custom: UniqueMatchOpponents** - Empêche deux joueurs de s'affronter deux fois
2. **Contrainte: @UniqueEntity** - Email unique pour les utilisateurs
3. **Contrainte: @GreaterThanOrEqual** - Niveau joueur >= 1
4. **Contrainte: @LessThanOrEqual** - Niveau joueur <= 15
5. **Contrainte: @Length** - Longueur des chaînes (pseudo, username, etc.)
6. **Contrainte: @NotBlank** - Champs requis
7. **Contrainte: @Email** - Format email valide

## 📝 Commits Git

Voir l'historique des commits pour suivre le développement:
```bash
git log --oneline
```

Structure des commits:
- `feat:` Nouvelles fonctionnalités
- `fix:` Corrections de bugs
- `refactor:` Refactorisation du code
- `docs:` Documentation
- `test:` Tests

## 🐛 Troubleshooting

### "could not find driver" lors des migrations
Assurez-vous que SQLite est activé dans PHP:
```bash
php -m | grep -i sqlite
```

### Les emails ne sont pas envoyés
Vérifiez la configuration MAILER_DSN dans .env. Par défaut: `null://null` (pas d'envoi)

### Messenger/PDF ne fonctionne pas
Installez wkhtmltopdf (requis pour Knp Snappy):
```bash
# Windows avec Chocolatey
choco install wkhtmltopdf
```

## 📚 Technologies Utilisées

- **Backend**: Symfony 7.4
- **ORM**: Doctrine
- **Base de données**: SQLite
- **Frontend**: Twig + Bootstrap 5
- **API**: REST
- **Async**: Symfony Messenger + Doctrine Transport
- **PDF**: Knp Snappy (wkhtmltopdf)
- **Validation**: Symfony Validator

## 🎓 Apprentissages

Ce projet démontre:
- Architecture MVC complète avec Symfony
- Gestion des relations Doctrine (OneToOne, OneToMany)
- Enums PHP 8.1+
- Validations custom avec Constraints
- Authentification avec form_login
- CRUD complet avec contrôleurs
- Système de rôles et autorisation
- Templating Twig avancé
- API REST sécurisée
- Messages asynchrones avec Messenger
- Génération de PDF
- Fixtures pour les données de test

## 📄 Licence

Propriétaire - Projet éducatif

## 👨‍💻 Auteur

Développé en tant que projet de gestion de tournoi Battle Arena Smash Bros Ultimate

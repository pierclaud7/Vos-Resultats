# 🎓 Vos Résultats — Plateforme de gestion des résultats d'examens

## 📋 Description
Plateforme complète de gestion et publication des résultats d'examens
pour une école supérieure. Architecture découplée Vue.js + Symfony API REST.

## 🛠 Stack technique
- **Back-end** : Symfony 8 (PHP 8.4) — API REST
- **Front-end** : Vue.js 3 + Vite + Pinia + Vue Router
- **Base de données** : MySQL (XAMPP)

## 🚀 Lancement du projet

### Back (Symfony)
```bash
cd back
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-admin
php -S 127.0.0.1:8000 -t public
```

### Front (Vue.js)
```bash
cd front
npm install
npm run dev
```

## 🌐 URLs
- **API** : http://localhost:8000
- **Application** : http://localhost:5173

## 👤 Compte admin par défaut
- Email : admin@test.com
- Mot de passe : 

## 📁 Structure
```
Vos-Resultats/
├── back/    → API Symfony
└── front/   → Interface Vue.js
```

## ✨ Fonctionnalités
- Gestion des filières, diplômes, sessions et étudiants
- Validation et publication des résultats
- Calcul automatique du taux de réussite
- Partage social simulé
- Import CSV d'étudiants
- Consultation publique des résultats
- Statistiques par filière et diplôme

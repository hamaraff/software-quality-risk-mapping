# Cartographie des risques de gestionRendezVous

## 1. Partie A - Fiche de présentation de l'application

**Nom de l'application :** `gestionRendezVous`

### Contexte et objectif métier

L'application permet à un client de s'inscrire, d'obtenir une référence unique, puis de prendre un rendez-vous avec un avocat selon des créneaux prédéfinis.

Le système aide à organiser le planning de l'avocat tout en permettant au client de consulter, modifier ou supprimer ses rendez-vous.

Le projet suit une logique métier simple de réservation de rendez-vous, avec une gestion de créneaux horaires fixes dans la journée.

### Technologies utilisées

* **Interface utilisateur :** Vue.js
* **API backend :** PHP / POO
* **Base de données :** MySQL
* **Couche d'accès aux données :** PDO
* **Architecture :** Modèle MVC (Contrôleurs + Modèles + Vues)
* **Communication client-serveur :** API JSON via contrôleurs PHP

### Rôles utilisateurs

#### Client / Utilisateur

* **Description :** Personne qui s'inscrit et prend un rendez-vous.
* **Responsabilités :** S'inscrire, obtenir une référence, vérifier son identité, réserver, consulter, modifier et supprimer ses rendez-vous.

#### Avocat

* **Description :** Personne qui reçoit les rendez-vous.
* **Responsabilités :** Recevoir les demandes et gérer le planning selon les créneaux disponibles.

#### Système

* **Description :** Couche logicielle de traitement.
* **Responsabilités :** Valider les données, gérer les accès à la base de données, exécuter les opérations CRUD et de consultation.

### Fonctions principales

* Inscription d'un client
* Vérification d'un utilisateur via référence
* Création d'un rendez-vous
* Consultation des créneaux disponibles
* Consultation de l'historique des rendez-vous
* Modification d'un rendez-vous
* Suppression d'un rendez-vous
* Gestion des créneaux de disponibilité
* Gestion des informations utilisateur

### Fonctions critiques retenues

1. **Création d'un rendez-vous :** Fonction centrale du système, elle conditionne la validité du planning et la satisfaction des clients.
2. **Vérification des créneaux disponibles :** Essentielle pour éviter les réservations conflictuelles.
3. **Vérification du client via référence :** Clé d'accès au compte utilisateur et à ses rendez-vous.
4. **Modification d'un rendez-vous :** Impact direct sur le planning et la cohérence des données.
5. **Inscription d'un client :** Point d'entrée du service, conditionne l'accès au système.
6. **Gestion des créneaux fixes de la journée :** Base de la planification et des règles métier.

---

## 2. Partie B - Cartographie des risques

| Fonction critique | Cause possible | Conséquence | Niveau de risque |
|---|---|---|---|
| **Création d'un rendez-vous** | Conflit entre la date sélectionnée, le créneau choisi et la vérification de disponibilité ; double soumission du formulaire ; logique de contrôle insuffisante. | Plusieurs rendez-vous peuvent être créés pour le même créneau ; destruction de la cohérence du planning ; mécontentement des utilisateurs. | Élevé |
| **Vérification des créneaux disponibles** | Mauvaise logique SQL ou validation insuffisante ; omission du filtrage des créneaux déjà réservés ; erreur de date. | Un client peut réserver un créneau déjà occupé ; conflits dans le calendrier ; surbooking et incompréhension du système. | Élevé |
| **Vérification du client via référence** | Référence mal générée, mal stockée ou mal comparée ; collision de références ; incohérence entre les données utilisateur et les données de rendez-vous. | Un client peut être refusé alors qu'il est inscrit ; un mauvais compte peut être associé à un rendez-vous ; perte de confiance et données incohérentes. | Élevé |
| **Modification d'un rendez-vous** | Absence de vérification de l'existence du rendez-vous ; changement vers un créneau occupé ; modification d'un rendez-vous déjà échu. | Perturbation du planning ; rendez-vous incohérents ; impossibilité de gérer correctement les réservations ; frustration utilisateur. | Élevé |
| **Inscription d'un client** | Champs obligatoires non validés ; génération de référence incohérente ; données incomplètes ou dupliquées. | Comptes incomplets ou mal formés ; blocage du parcours utilisateur ; difficulté de retrouver les clients et leurs rendez-vous. | Élevé |
| **Gestion des créneaux fixes de la journée** | Erreur de configuration des créneaux ; mauvaise synchronisation avec la base ; logique métier non respectée. | Désaccord entre le planning réel et les créneaux proposés ; impossibilité de réserver certains horaires ; perception d'un système peu fiable. | Élevé |
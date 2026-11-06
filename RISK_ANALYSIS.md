# 🛡️ Analyse Qualité & Cartographie des Risques — gestionRendezVous

## 1. Contexte & Objectifs

Ce document présente l'évaluation de la qualité et la cartographie des risques réalisée à partir du code source du projet public **gestionRendezVous**.

Les objectifs principaux de cette démarche sont :

- **Distinguer les concepts fondamentaux de la qualité logicielle :** Clarifier la différence entre Erreur, Défaut et Défaillance.
- **Analyser le projet existant :** Identifier les rôles utilisateurs, l'architecture logicielle, les règles métier et les fonctions clés en analysant le dépôt cloné.
- **Identifier et évaluer les fonctions critiques :** Hiérarchiser les fonctionnalités selon leur fréquence d'utilisation, l'impact d'une défaillance sur les données, les utilisateurs et le métier, leur complexité technique et leur visibilité pour l'utilisateur final.
- **Établir une cartographie des risques qualité :** Formuler une matrice structurée évaluant, pour chaque risque, les causes possibles, les conséquences observables et le niveau de risque (Faible, Moyen, Élevé).

---

## 2. Cadre Théorique : Erreur vs. Défaut vs. Défaillance

Afin de garantir une analyse de qualité rigoureuse, nous appliquons les définitions normalisées suivantes au contexte de l'application :

| Concept | Définition | Exemple dans gestionRendezVous |
|---|---|---|
| **Erreur (Human Error)** | Action humaine incorrecte, mauvaise conception ou omission commise par le développeur lors de la spécification ou du codage. | Le développeur oublie d'inclure une transaction SQL d'isolation ou d'exécuter une validation stricte des dates côté backend. |
| **Défaut / Faille (Defect / Bug)** | Manifestation statique de l'erreur au sein du code source, du schéma de base de données ou de la configuration. | L'absence d'une contrainte d'unicité `UNIQUE (id_avocat, date_rdv, heure_rdv)` dans la base MySQL ou une requête SQL incomplète. |
| **Défaillance (Failure)** | Comportement dynamique et observable survenant à l'exécution lorsque le système ne fournit pas le service attendu. | Deux clients réussissent à réserver simultanément le même créneau horaire (surbooking), entraînant une incohérence du planning. |

---

## 3. Analyse du Projet & Structure

### 🛠️ Architecture & Stack Technique

- **Interface Utilisateur (Frontend) :** Vue.js (Single Page Application).
- **API Backend :** PHP orienté objet (Architecture MVC).
- **Base de données :** MySQL avec couche d'accès PDO (requêtes préparées).
- **Communication :** API JSON RESTful via les contrôleurs PHP.

### 👤 Rôles Utilisateurs & Responsabilités

- **Client / Utilisateur :**
  - S'inscrire.
  - Obtenir une référence unique d'authentification.
  - Consulter les créneaux disponibles.
  - Réserver, modifier ou annuler des rendez-vous.
  - Consulter son historique.

- **Avocat :**
  - Recevoir les demandes de rendez-vous.
  - Gérer son planning sur des créneaux fixes.

- **Système (Couche Logicielle) :**
  - Valider les données entrantes.
  - Contrôler les accès à la base de données.
  - Exécuter les traitements CRUD.
  - Appliquer les règles métier.

### 📋 Règles Métier Clés Identifiées

1. **Référence Unique Client :** Chaque utilisateur inscrit doit posséder une référence unique qui sert de clé d'accès à ses données et rendez-vous.
2. **Créneaux Horaires Fixes :** Les rendez-vous s'effectuent sur des plages horaires définies et structurées dans la journée.
3. **Exclusivité du Créneau :** Un créneau donné pour un avocat ne peut être attribué qu'à une seule réservation active.
4. **Validité Temporelle :** Les créations et modifications de rendez-vous doivent porter sur des dates et heures futures valides.

---

## 4. Évaluation des Fonctions Critiques

Les fonctions du système ont été évaluées selon quatre critères majeurs :

- **Fréquence d'utilisation :** Sollicitation de la fonction par les utilisateurs.
- **Impact de défaillance :** Gravité des conséquences sur les données, les utilisateurs ou le métier.
- **Complexité technique & métier :** Degré d'intrication de la logique de contrôle et d'accès aux données.
- **Visibilité :** Exposition directe auprès de l'utilisateur final.

### Liste des Fonctions Critiques Retenues

1. **Création d'un rendez-vous :** Fonction centrale, très sollicitée, à forte complexité transactionnelle et à impact critique en cas de surbooking.
2. **Vérification des créneaux disponibles :** Fréquence et visibilité maximales ; essentielle pour éviter les réservations conflictuelles.
3. **Vérification du client via référence :** Clé d'accès à l'application ; conditionne la confidentialité et la cohérence des données.
4. **Modification d'un rendez-vous :** Impact direct sur le calendrier et l'historique des réservations.
5. **Inscription d'un client :** Point d'entrée du service ; conditionne l'accès initial au système.
6. **Gestion des créneaux fixes de la journée :** Socle de la planification et du respect des règles métier.

---

## 5. Cartographie des Risques (Matrice de Risque)

La cartographie ci-dessous détaille les risques qualité identifiés selon le modèle suivant :

- **Cause possible :** Ce qui pourrait mal se passer techniquement ou fonctionnellement.
- **Conséquence :** L'effet observable pour l'utilisateur ou le système si le risque se réalise.
- **Niveau de risque :** Faible, Moyen ou Élevé.
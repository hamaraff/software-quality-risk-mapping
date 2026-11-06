# gestion de RendezVous

# Contexte du projet
Dans le cadre d’informatiser son service un avocat vous a contacté afin de digitaliser son service de prise de rendez-vous.

un client se connecte ,renseigne ses informations(Nom, Prénom, âge, profession..) , puis le système lui affecte une référence unique générée, qu’il doit la fournir à chaque fois quand il veut prendre un RDV. le RDV est définie par ( sujet RDV, Date, Créneau..) le système vous permet de choisir la date du rdv et vous propose les créneaux libre.

le système vous permet de visualiser l'historique de vous RDVs et les modifier si la date n'est pas échue.

on suppose que l’avocat reçoit les RDV en 5 créneaux seulement dans la journée créneau N°1 de 10 h à 10:30h créneau N°2 de 11 h à 11:30h créneau N°3 de 14 h à 14:30h créneau N°4 de 15 h à 15:30h créneau N°5 de 16 h à 16:30h

https://lucid.app/lucidspark/invitations/accept/inv_ad22da81-17e1-4022-9d96-0076d4f1a206?viewport_loc=-229%2C-10%2C2048%2C963%2C0_0.

# Contraintes technologiques:

le design Pattern MVC

PHP/ POO/API

Vue.js


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

---

## 6. Périmètre de la campagne de test

Le plan détaillé de la campagne, ses niveaux de test, son environnement et ses critères d'entrée et de sortie sont disponibles dans le [rapport PDF](<./Périmètre de la Campagne de Test.pdf>).

### Fonctions couvertes

| Référence | Fonction |
|---|---|
| F01 | Inscription d'un client et génération de sa référence |
| F02 | Vérification d'un client à partir de sa référence |
| F03 | Affichage des créneaux disponibles pour une date |
| F04 | Création d'un rendez-vous et contrôle de l'exclusivité du créneau |
| F05 | Consultation de l'historique des rendez-vous |
| F06 | Modification d'un rendez-vous futur |
| F07 | Suppression d'un rendez-vous |
| F08 | Respect des cinq créneaux fixes : 10 h, 11 h, 14 h, 15 h et 16 h |

Les six fonctions critiques identifiées dans la cartographie des risques de la semaine 1 sont couvertes : création d'un rendez-vous, disponibilité des créneaux, vérification du client, modification, inscription et gestion des créneaux fixes.

### Lien entre risques et tests

| Risque de la cartographie | Fonctions / contrôles de test |
|---|---|
| Surbooking d'un créneau | F03 et F04 : vérifier qu'un créneau réservé n'est plus proposé et tester deux tentatives de réservation du même créneau |
| Créneau ou horaire invalide | F03, F04 et F08 : contrôler les cinq horaires autorisés et leur disponibilité selon la date |
| Date passée ou invalide | F04 et F06 : vérifier le refus des dates non valides et l'acceptation des dates futures |
| Référence client invalide ou mal vérifiée | F01 et F02 : vérifier la génération, la recherche de la référence et le refus d'une référence inconnue |
| Modification incorrecte d'un rendez-vous | F06 : vérifier les règles de date, la disponibilité du nouveau créneau et la mise à jour de l'historique |
| Inscription incorrecte | F01 : vérifier les champs requis, l'enregistrement du client et la génération de sa référence |

Les fonctions faisant intervenir des règles métier sont vérifiées par des tests unitaires ; les accès à la base par des tests d'intégration ; les parcours complets par des tests système. Les règles et horaires doivent également être validés par le représentant métier (tests d'acceptation). La consultation de l'historique et la suppression sont couvertes aux niveaux intégration et système.

### Environnement et critères de campagne

- **Environnement :** poste Windows, interface Vue.js, API PHP et base MariaDB/MySQL. Les versions PHP 8.0.2 et MariaDB 10.4.17 figurent dans le dump SQL ; elles doivent être vérifiées sur l'environnement utilisé.
- **Données :** base de test réinitialisable, données fictives uniquement et scénarios reproductibles.
- **Outils proposés :** PHPUnit (à configurer), client API tel que Postman ou Insomnia, navigateur, outils de développement et requêtes SQL de vérification.
- **Critères d'entrée :** règles métier validées, version du code identifiée, interface/API/base accessibles, données et scénarios de test préparés.
- **Critères de sortie :** 100 % des scénarios critiques exécutés, aucun défaut bloquant ou majeur ouvert, aucune double réservation observée et résultats attendus sur les parcours critiques. Une couverture d'au moins 80 % des règles métier unitaires est visée si la mesure est configurée.
- **Arrêt faute de temps :** consigner les scénarios non exécutés et les défauts restants ; la campagne est alors déclarée incomplète, et non validée.

**Point de vigilance :** le parcours de modification côté interface doit être vérifié avant la campagne, car sa soumission semble inachevée dans `vue/src/components/DashbordForm.vue`.

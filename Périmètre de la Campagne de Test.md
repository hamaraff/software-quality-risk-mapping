# Plan de test de gestionRendezVous

> Proposition établie à partir de la présentation du projet, du schéma `rdv.sql` et de la cartographie des risques.

## 4.1 - Périmètre du test

### Fonctions incluses

* **F01 :** Inscription d’un client et génération de sa référence.
* **F02 :** Vérification d’un client à partir de sa référence.
* **F03 :** Affichage des créneaux disponibles pour une date.
* **F04 :** Création d’un rendez-vous, y compris le contrôle de l’exclusivité du créneau.
* **F05 :** Consultation de l’historique des rendez-vous.
* **F06 :** Modification d’un rendez-vous futur.
* **F07 :** Suppression d’un rendez-vous.
* **F08 :** Respect des cinq créneaux fixes : 10 h, 11 h, 14 h, 15 h et 16 h.

*Les six fonctions critiques de la semaine 1 sont couvertes : création, disponibilité, vérification du client, modification, inscription et gestion des créneaux fixes.*

### Fonctions exclues pour cette campagne

* **Gestion d’un compte ou d’un planning administrateur pour l’avocat :** Elle n’est pas décrite comme une fonction disponible.
* **Notifications par courriel / SMS :** Elles ne sont pas prévues dans le périmètre fonctionnel décrit.
* **Tests de charge, de compatibilité multi-navigateurs, d’accessibilité et audit de sécurité approfondi :** À planifier dans des campagnes dédiées. *(Les contrôles fonctionnels d’accès aux données du client restent inclus).*

---

## 4.2 - Niveaux de test

| Fonction critique | Niveaux retenus | Pourquoi |
|---|---|---|
| **Inscription d’un client** | Unitaire, intégration, système, acceptation | Vérifier les champs et la référence générée, l’enregistrement en base et le parcours d’inscription ; faire valider les règles par le représentant métier. |
| **Vérification du client par référence** | Unitaire, intégration, système | Tester la recherche de référence, la lecture en base et l’accès au parcours correspondant ; vérifier qu’une référence inconnue est refusée. |
| **Créneaux disponibles** | Unitaire, intégration, système, acceptation | Tester les règles de date et les cinq horaires, la requête qui exclut les créneaux réservés, puis l’affichage côté client ; faire valider les horaires par l’avocat. |
| **Création d’un rendez-vous** | Unitaire, intégration, système, acceptation | Tester les règles de date/créneau, la persistance et l’absence de double réservation, puis la réservation de bout en bout ; confirmer le comportement attendu avec le métier. |
| **Modification d’un rendez-vous futur** | Unitaire, intégration, système, acceptation | Tester les règles de date, la mise à jour en base et les conflits de créneaux, puis le parcours de modification ; faire confirmer les règles métier. |
| **Gestion des créneaux fixes** | Unitaire, intégration, système, acceptation | Contrôler les horaires définis, leur présence en base et leur présentation dans l’application ; validation finale par l’avocat. |

*Note : La consultation de l’historique et la suppression sont également vérifiées au niveau intégration et système.*

---

## 4.3 - Environnement et critères

### Environnement de test
* **Infrastructure :** Poste de développement Windows ; application Vue.js, API PHP et base MariaDB/MySQL.
* **Versions :** Le dump indique PHP 8.0.2 et MariaDB 10.4.17 au moment de sa génération ; vérifier les versions réellement installées avant la campagne.
* **Base de données :** Distincte, préparée à partir du schéma du projet et réinitialisable entre les scénarios.
* **Données :** Données fictives uniquement (clients de test, références générées, rendez-vous futurs, créneaux libres et réservés). Aucun renseignement personnel réel.
* **Outils proposés :** 
  * PHPUnit pour les tests PHP *(à configurer, aucun outil de test n’apparaît dans les fichiers consultés)*
  * Client API tel que Postman ou Insomnia
  * Navigateur web et outils de développement (DevTools)
  * Requêtes SQL de vérification

### Critères d’entrée
1. Exigences et règles métier, notamment les cinq horaires et les règles de modification, validées par le représentant métier.
2. Version du code identifiée ; interface, API et base de test accessibles.
3. Schéma et données de test préparés ; jeux de données reproductibles.
4. Scénarios de test rédigés pour toutes les fonctions critiques et outils nécessaires disponibles.

### Critères de sortie / d’arrêt
1. 100 % des scénarios critiques exécutés, y compris un contrôle de deux tentatives de réservation du même créneau.
2. Aucun défaut bloquant ou majeur ouvert ; aucune double réservation observée.
3. Tous les parcours critiques donnent le résultat attendu ; au moins 80 % de couverture des règles métier unitaires (si l’outil de couverture est configuré).
4. **Arrêt prématuré :** Si le temps imparti est écoulé avant ces seuils, arrêter la campagne en consignant les scénarios non exécutés et les défauts restants (la campagne est alors considérée comme *incomplète*, pas *validée*).

> **Point de vigilance avant exécution :** La soumission de modification côté interface semble inachevée dans `DashbordForm.vue`. Il faut vérifier que le parcours peut réellement transmettre et enregistrer une modification avant de compter ce test comme exécutable.

---

### Lien explicite avec la cartographie des risques

| Risque identifié en semaine 1 | Fonction concernée | Tests associés | Vérification attendue |
|---|---|---|---|
| **Surbooking :** deux clients obtiennent le même créneau | Disponibilités et création d’un rendez-vous | Intégration, système | Un créneau déjà réservé n’est plus proposé ; deux réservations concurrentes ne peuvent pas aboutir pour le même créneau. |
| **Créneau invalide** ou horaires non respectés | Gestion des cinq créneaux fixes | Unitaire, intégration, acceptation | Seuls les horaires prévus (10 h, 11 h, 14 h, 15 h, 16 h) sont proposés et enregistrés. |
| **Date passée** ou invalide | Création et modification d’un rendez-vous | Unitaire, système | Une date passée ou invalide est refusée ; une date future valide est acceptée. |
| **Référence client absente, invalide ou mal vérifiée :** accès aux données du mauvais client ou refus d'accès injustifié | Inscription et vérification du client | Unitaire, intégration, système | La référence générée permet de retrouver le bon client ; une référence inconnue ne donne pas accès à ses rendez-vous. |
| **Modification incorrecte :** rendez-vous déplacé sur un créneau indisponible ou modifié après échéance | Modification d’un rendez-vous | Unitaire, intégration, système, acceptation | La modification respecte les règles de date et de disponibilité ; l’historique reflète la mise à jour. |
| **Inscription incorrecte :** client non enregistré ou référence non générée correctement | Inscription d’un client | Unitaire, intégration, système | Les champs requis sont contrôlés, le client est enregistré et une référence lui est attribuée. |
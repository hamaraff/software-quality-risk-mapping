# Tests d'intégration — Semaine 7

La suite `tests/run-integration.php` vérifie les opérations CRUD des rendez-vous à travers le modèle `Appointements` et une vraie base MySQL/MariaDB de test. Elle comprend aussi deux contrôles des clés étrangères : utilisateur inexistant et créneau inexistant.

## Préparer la base de test

Créer une base dédiée dont le nom contient `test` (par exemple `gestionrendezvous_test`), puis exécuter une fois `tests/integration/schema.sql` dans cette base. Ce schéma reprend les tables et les clés étrangères du dump du projet. N'utilisez jamais la base de production.

Dans PowerShell, configurer la connexion puis lancer la suite depuis le dossier `gestionRendezVous` :

```powershell
$env:TEST_DATABASE_DSN = 'mysql:host=127.0.0.1;dbname=gestionrendezvous_test;charset=utf8mb4'
$env:TEST_DATABASE_USER = 'root'
$env:TEST_DATABASE_PASSWORD = ''
php tests\run-integration.php
```

Le nom de base est contrôlé par le runner : l'exécution est refusée si le DSN ne contient pas `test`. Le compte configuré doit pouvoir lire et écrire dans la base dédiée.

## Isolation et reproductibilité

Chaque cas ouvre une transaction, insère ses propres fixtures (client et créneaux à identifiants fixes), effectue les assertions, puis annule la transaction par `ROLLBACK`, y compris en cas d'échec. Les données de chaque cas sont ainsi indépendantes et la suite peut être relancée sans nettoyage manuel.

## Jeux de données (Fixtures)

Avant chaque scénario, la fonction `seedAppointmentFixtures()` et l'helper `insertAppointment()` initialisent les enregistrements suivants en base :

* **Utilisateur de test (`users`)**
  * `userId` : `10001`
  * `userFirstName` : `'Test'`
  * `userLastName` : `'Client'`
  * `userEmail` : `'client-10001@example.test'`
* **Créneaux horaires (`timeslots`)**
  * Créneau initial (`timeslot_id` : `10001`) : de `'10:00:00'` à `'10:30:00'`
  * Créneau secondaire (`timeslot_id` : `10002`) : de `'11:00:00'` à `'11:30:00'`
* **Rendez-vous par défaut (`appointements`)**
  * `userId_fk` : `10001`
  * `timeslot_id_fk` : `10001`
  * `user_subject` : `'Consultation de test'`
  * `c_date` : `'2030-05-15'`
* **Valeurs de mutation & cas d'erreur**
  * **Mise à jour (`update_changesPersistedAppointment`)** : bascule vers le créneau `10002`, sujet `'Sujet modifié'`, date `'2030-05-16'`.
  * **Clés étrangères invalides** : identifiant inexistant `99999` utilisé pour `userId_fk` puis pour `timeslot_id_fk`.

## Cas de test exécutés

| ID test | Opération testée | Jeu de données utilisé | Résultat attendu |
|---|---|---|---|
| `create_insertsAppointment` | Create | Client `10001`, créneau `10001`, sujet `'Consultation de test'`, date `'2030-05-15'` | Rendez-vous retrouvé avec ses valeurs |
| `read_returnsRequestedAppointment` | Read | Rendez-vous créé dans le test (`10001` / `10001`) | Le rendez-vous demandé et son sujet sont lus |
| `update_changesPersistedAppointment` | Update | Passage du créneau `10001` au `10002`, sujet `'Sujet modifié'`, date `'2030-05-16'` | Créneau, sujet et date modifiés en base |
| `delete_removesPersistedAppointment` | Delete | Rendez-vous créé dans le test | Aucun rendez-vous ne subsiste (`COUNT(*) = 0`) |
| `create_rejectsUnknownUserForeignKey` | Clé étrangère client | Client `99999` inexistant, créneau `10001` | Insertion rejetée par la base (`PDOException`) |
| `create_rejectsUnknownTimeslotForeignKey` | Clé étrangère créneau | Client `10001`, créneau `99999` inexistant | Insertion rejetée par la base (`PDOException`) |
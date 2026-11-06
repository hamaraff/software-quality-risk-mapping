# Tests unitaires — Semaine 6

## Suite automatisée

La suite cible `AppointmentPolicy`, un composant métier isolé responsable des validations élémentaires pour une réservation : date strictement future, sujet de 1 à 110 caractères, identifiant de créneau compris entre 1 et 5, client connu et disponibilité du créneau.

La borne du sujet reprend le type `varchar(110)` du schéma comme hypothèse de validation, en continuité avec le livrable de semaine 4. Elle doit être confirmée par le responsable métier.

L'accès à la disponibilité est représenté par l'interface `AppointmentAvailability`. La suite utilise `StubAppointmentAvailability`, un stub contrôlable qui répond « disponible » ou « occupé » et conserve la liste des appels pour vérifier l'interaction. Les tests n'accèdent pas à une base de données et ne dépendent pas les uns des autres.

### Exécution

Depuis la racine du projet, avec PHP CLI installé :

```powershell
php tests/run.php
```

Sur l'environnement XAMPP vérifié pour ce livrable :

```powershell
C:\xampp\php\php.exe tests\run.php
```

Le lanceur retourne un code de sortie non nul si un test échoue et affiche le nom du test concerné.

## Synthèse des tests

| Service testé | Nom du test | Scénario | Résultat attendu | Statut |
|---|---|---|---|---|
| AppointmentPolicy::isDateFuture | `isDateFuture_acceptsTomorrow` | Date du lendemain | `true` | Passant |
| AppointmentPolicy::isDateFuture | `isDateFuture_rejectsTodayAndPastDates` | Date du jour et date passée | `false` pour les deux | Passant |
| AppointmentPolicy::isDateFuture | `isDateFuture_rejectsMalformedCalendarDate` | Date impossible (30 février) | `false` | Passant |
| AppointmentPolicy::isSubjectValid | `isSubjectValid_acceptsOneAnd110Characters` | Sujet aux limites de 1 et 110 caractères | `true` pour les deux | Passant |
| AppointmentPolicy::isSubjectValid | `isSubjectValid_rejectsBlankAndTooLongValues` | Sujet blanc et sujet de 111 caractères | `false` pour les deux | Passant |
| AppointmentPolicy::isTimeslotIdValid | `isTimeslotIdValid_acceptsOnlyConfiguredIds` | Créneau 5, puis IDs 0 et 6 | `true`, puis `false` et `false` | Passant |
| AppointmentPolicy::canBook | `canBook_returnsTrueAndChecksAvailabilityStub` | Client connu, données valides, créneau disponible | Réservation admissible et stub appelé avec date/ID attendus | Passant |
| AppointmentPolicy::canBook | `canBook_rejectsOccupiedSlotFromAvailabilityStub` | Client connu, créneau occupé | Réservation refusée après vérification par le stub | Passant |
| AppointmentPolicy::canBook | `canBook_rejectsUnknownClientWithoutCallingAvailabilityStub` | Client inconnu | Réservation refusée ; stub non appelé | Passant |
| AppointmentPolicy::canBook | `canBook_rejectsInvalidInputWithoutCallingAvailabilityStub` | Date du jour, ID invalide et sujet blanc | Réservation refusée ; stub non appelé | Passant |

## Limite de cette première suite

Le dépôt existant place les opérations métier directement dans les contrôleurs et modèles PDO. `AppointmentPolicy` fournit une unité pure et testable, mais elle n'est pas encore appelée par l'API ni par `Appointements::createAnAppointement()`. Cette suite valide donc le composant isolé, pas l'intégralité du parcours de réservation. Son raccordement à l'API et les tests d'accès à la base relèvent des étapes d'intégration et nécessitent des règles métier confirmées.

**Résultat d'exécution :** 10 tests, 0 échec. La syntaxe des quatre fichiers PHP de la suite et du service a également été vérifiée avec `php -l`.

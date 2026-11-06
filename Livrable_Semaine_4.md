# Atelier test et validation des logiciels

## Livrable — Semaine 4

### Techniques boîte noire : partitions d'équivalence, valeurs limites et table de décision

**Application fil rouge :** gestion de rendez-vous d'un cabinet d'avocat  
**Niveau :** 3ᵉ année Licence — Technologies de l'Information  
**Périmètre :** formulaire de réservation d'un rendez-vous

## 1. Références et hypothèses de test

Le formulaire retenu demande une date, un créneau et un sujet. Les cinq créneaux définis par le projet sont : S1 (10 h–10 h 30), S2 (11 h–11 h 30), S3 (14 h–14 h 30), S4 (15 h–15 h 30) et S5 (16 h–16 h 30). La réservation est rattachée à un client identifié.

Les attentes reprennent les exigences proposées en semaine 3, notamment une date strictement future, un créneau libre et un sujet renseigné. Le schéma de données définit le sujet sur 110 caractères (`varchar(110)`), sans préciser de règle de validation côté métier. Pour permettre l'analyse des valeurs limites, la longueur acceptée **1 à 110 caractères** est utilisée ici comme hypothèse provisoire à faire confirmer. Les résultats attendus sont des exigences de test proposées, pas une affirmation sur le comportement actuel du code.

### Jeu de données

- **D** : date du jour au moment du test.
- **D-1 / D+1** : veille / lendemain de D.
- **U+** : référence d'un client existant ; **U-** : référence inconnue.
- **S1 libre** : créneau fixe sans rendez-vous pour la date utilisée.
- **S2 occupé** : créneau fixe ayant déjà un rendez-vous à la date utilisée.
- **S99** : identifiant ne correspondant à aucun des cinq créneaux fixes.
- **SUB(n)** : sujet composé de n caractères ordinaires, sans espaces seuls.
- Sauf indication contraire, les champs non mentionnés sont valides et le sujet vaut `Consultation`.

Les fixtures S1 libre et S2 occupé sont préparées pour la date et le client correspondant à chaque cas. Pour les essais de décision, « créneau libre » signifie qu'un des cinq créneaux fixes n'est pas réservé à la date choisie.

## 2. Partie A — Partitions d'équivalence et valeurs limites

### 2.1 Partitions d'équivalence

| Champ | Partition / valeur | Type | Donnée d'entrée représentative | Résultat attendu |
|---|---|---|---|---|
| Date | Date antérieure au jour du test | Invalide | D-1 | Réservation refusée ; aucun rendez-vous créé. |
| Date | Jour du test, considéré non futur | Invalide | D | Réservation refusée ; aucun rendez-vous créé. |
| Date | Date postérieure au jour du test | Valide | D+1 | La date peut être retenue si les autres conditions sont satisfaites. |
| Date | Date absente ou non interprétable | Invalide | Valeur vide ou `2026-02-30` via l'API | Réservation refusée ; aucun rendez-vous créé. |
| Créneau | Créneau fixe et disponible à la date | Valide | S1 libre | Le créneau est proposé et peut être réservé si les autres conditions sont satisfaites. |
| Créneau | Créneau fixe mais déjà réservé à la date | Invalide | S2 occupé | Créneau non réservable ; aucun doublon n'est créé. |
| Créneau | Valeur qui ne correspond pas à un créneau fixe | Invalide | S99 | Réservation refusée ; aucun rendez-vous créé. |
| Créneau | Aucun créneau sélectionné | Invalide | Valeur vide | Réservation refusée ; aucun rendez-vous créé. |
| Sujet | Sujet vide | Invalide | `SUB(0)` | Réservation refusée ; aucun rendez-vous créé. |
| Sujet | Sujet de 1 à 110 caractères | Valide* | `SUB(1)` | Le sujet est accepté et conservé à l'identique. |
| Sujet | Sujet de plus de 110 caractères | Invalide* | `SUB(111)` | Réservation refusée avec indication de longueur ; aucun rendez-vous créé. |
| Client | Référence existante | Valide | U+ | Le rendez-vous, si créé, est rattaché à ce client. |
| Client | Référence inconnue | Invalide | U- | Accès/réservation refusé ; aucun rendez-vous créé. |

\* Bornes proposées à titre provisoire d'après la colonne `user_subject varchar(110)` du schéma. À confirmer avec le responsable métier avant d'en faire une règle contractuelle.

### 2.2 Analyse des valeurs limites — longueur du sujet

La plage testée est **1 à 110 caractères inclus** selon l'hypothèse provisoire ci-dessus. La limite basse est zéro / un caractère ; la limite haute est 110 caractères.

| Valeur testée | Position | Type | Résultat attendu |
|---|---|---|---|
| 0 caractère | Juste sous la limite basse acceptée | Limite invalide | Refus de la réservation ; aucun rendez-vous créé. |
| 1 caractère | Sur la limite basse acceptée | Limite valide | Le sujet est accepté. |
| 110 caractères | Sur la limite haute acceptée | Limite valide | Le sujet est accepté et conservé sans troncature. |
| 111 caractères | Juste au-dessus de la limite haute | Limite invalide | Refus de la réservation ; aucun rendez-vous créé. |

### 2.3 Analyse des valeurs limites — date

La règle retenue en semaine 3 exige une date strictement future. Comme le champ est une date de calendrier, les valeurs adjacentes à la frontière sont la veille, le jour même et le lendemain.

| Valeur testée | Position | Type | Résultat attendu |
|---|---|---|---|
| D-1 | Juste avant la frontière | Invalide | Refus de la réservation. |
| D | Sur la frontière « date du jour » | Invalide selon la règle stricte « future » | Refus de la réservation. |
| D+1 | Juste après la frontière | Valide | La date est acceptée si le client, le créneau et le sujet sont valides. |

## 3. Partie B — Table de décision de la réservation

La règle métier est : **un rendez-vous est créé uniquement si le client est reconnu, la date est future et le créneau choisi est disponible**. Un créneau disponible est l'un des cinq créneaux fixes et n'est pas encore réservé à cette date. Pour isoler cette règle dans la table, tous les champs autres que les trois conditions sont valides.

| Condition / action | R1 | R2 | R3 | R4 | R5 | R6 | R7 | R8 |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Client reconnu ? | Oui | Oui | Oui | Oui | Non | Non | Non | Non |
| Date future ? | Oui | Oui | Non | Non | Oui | Oui | Non | Non |
| Créneau fixe libre ? | Oui | Non | Oui | Non | Oui | Non | Oui | Non |
| **Action : créer le rendez-vous** | Oui | Non | Non | Non | Non | Non | Non | Non |
| **Action : refuser, ne créer aucun rendez-vous** | Non | Oui | Oui | Oui | Oui | Oui | Oui | Oui |
| Motif à signaler si refus | — | Créneau occupé | Date non future | Date non future et créneau occupé | Client inconnu | Client inconnu et créneau occupé | Client inconnu et date non future | Client inconnu, date non future et créneau occupé |

Les 8 combinaisons sont identifiées. R1 est l'unique combinaison qui autorise la création. Lorsqu'il y a plusieurs causes de refus, le système peut signaler chacune d'elles ; il ne doit en aucun cas créer le rendez-vous.

## 4. Partie C — Catalogue de cas de test consolidé

Les cas ci-dessous forment un catalogue fonctionnel à exécuter. Ils ne décrivent pas le résultat actuellement observé dans l'application. Les huit derniers cas couvrent toutes les règles de la table de décision.

| ID cas de test | Objectif | Donnée(s) d'entrée | Résultat attendu | Technique |
|---|---|---|---|---|
| CT-W4-01 | Refuser une date passée | U+, date D-1, S1 libre, `SUB(10)` | Réservation refusée ; aucune ligne de rendez-vous créée. | Partition / valeur limite |
| CT-W4-02 | Refuser la date du jour | U+, date D, S1 libre, `SUB(10)` | Réservation refusée, car la date doit être strictement future ; aucune ligne créée. | Partition / valeur limite |
| CT-W4-03 | Accepter une date valide avec les autres champs valides | U+, date D+1, S1 libre, `SUB(10)` | Rendez-vous créé pour U+ à la date et au créneau demandés. | Partition d'équivalence |
| CT-W4-04 | Refuser une date absente | U+, date vide, S1, `SUB(10)` | Réservation refusée ; aucune ligne créée. | Partition d'équivalence |
| CT-W4-05 | Accepter le sujet à la limite basse | U+, D+1, S1 libre, `SUB(1)` | Réservation créée ; sujet stocké avec un caractère. | Valeur limite |
| CT-W4-06 | Accepter le sujet à la limite haute | U+, D+1, S1 libre, `SUB(110)` | Réservation créée ; les 110 caractères sont conservés sans troncature. | Valeur limite |
| CT-W4-07 | Refuser un sujet au-delà de la limite haute | U+, D+1, S1 libre, `SUB(111)` | Réservation refusée ; aucune ligne créée et longueur signalée. | Valeur limite |
| CT-W4-08 | Refuser un sujet vide | U+, D+1, S1 libre, `SUB(0)` | Réservation refusée ; aucune ligne créée. | Partition d'équivalence |
| CT-W4-09 | Refuser un créneau inexistant | U+, D+1, S99, `SUB(10)` | Réservation refusée ; aucun rendez-vous créé. | Partition d'équivalence |
| CT-W4-10 | Refuser l'absence de sélection de créneau | U+, D+1, créneau vide, `SUB(10)` | Réservation refusée ; aucun rendez-vous créé. | Partition d'équivalence |
| CT-W4-11 | Refuser un créneau déjà réservé | U+, D+1, S2 occupé, `SUB(10)` | Réservation refusée ; le rendez-vous existant reste inchangé et aucun doublon n'est créé. | Partition d'équivalence |
| CT-W4-12 | Refuser une référence client inconnue | U-, D+1, S1 libre, `SUB(10)` | Accès/réservation refusé ; aucun rendez-vous créé. | Partition d'équivalence |
| CT-W4-13 | R1 — toutes les conditions de création sont satisfaites | Client reconnu, date D+1, S1 libre | Rendez-vous créé et associé au client, à la date et au créneau sélectionnés. | Table de décision |
| CT-W4-14 | R2 — créneau indisponible | Client reconnu, date D+1, S2 occupé | Création refusée ; aucun doublon sur S2 à cette date. | Table de décision |
| CT-W4-15 | R3 — date non future | Client reconnu, date D, S1 libre à D | Création refusée ; aucun rendez-vous créé. | Table de décision |
| CT-W4-16 | R4 — date non future et créneau occupé | Client reconnu, date D, S2 occupé à D | Création refusée ; aucun rendez-vous créé. | Table de décision |
| CT-W4-17 | R5 — client inconnu | Client inconnu, date D+1, S1 libre | Création refusée ; aucun rendez-vous créé. | Table de décision |
| CT-W4-18 | R6 — client inconnu et créneau occupé | Client inconnu, date D+1, S2 occupé | Création refusée ; aucun rendez-vous créé. | Table de décision |
| CT-W4-19 | R7 — client inconnu et date non future | Client inconnu, date D, S1 libre à D | Création refusée ; aucun rendez-vous créé. | Table de décision |
| CT-W4-20 | R8 — client inconnu, date non future et créneau occupé | Client inconnu, date D, S2 occupé à D | Création refusée ; aucun rendez-vous créé. | Table de décision |

## 5. Conditions d'exécution et points à confirmer

- Préparer une base de test isolée avec au moins un client existant, une date future et les créneaux libres/occupés nécessaires ; vérifier l'état de la base avant et après chaque création.
- Vérifier le résultat observable à la fois dans la réponse affichée à l'utilisateur et dans les données enregistrées.
- Exécuter les cas de longueur de sujet après validation métier de la borne 110 ; sans arbitrage, conserver ces cas comme tests exploratoires de la limite technique, pas comme critères d'acceptation définitifs.
- Confirmer que la date du jour est refusée pour une réservation et définir le fuseau horaire de référence de l'application.
- La présente table couvre les trois conditions de la règle de réservation. La validité du format e-mail, les formats de date non transmis par le navigateur, les appels concurrents et les règles de modification relèvent de cas complémentaires à ajouter au catalogue de campagne.


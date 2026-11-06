# Livrable — Semaine 3

## Analyse des exigences, testabilité et matrice de traçabilité

**Application fil rouge :** gestion de rendez-vous d'un cabinet d'avocat  
**Niveau :** 3ᵉ année Licence — Technologies de l'Information  
**Version :** exigences proposées à valider avec le porteur du projet

## Sources et périmètre

Ce livrable s'appuie sur le contexte et les fonctions critiques décrits dans le [README du projet](./README.md), ainsi que sur les éléments présents dans le schéma SQL et les écrans/API existants. Les fonctions critiques reprises sont : inscription, vérification du client par référence, consultation des créneaux disponibles, création et modification d'un rendez-vous, et gestion des créneaux fixes.

Les exigences ci-dessous décrivent le comportement attendu à tester ; elles ne certifient pas que l'implémentation actuelle le respecte. Les cas de test restent à identifier lors des séances suivantes. Les seuils de performance et autres règles absents des sources ne sont pas inventés.

## Partie A — Exigences corrigées

| ID | Exigence initiale / source | Problème identifié | Exigence reformulée |
|---|---|---|---|
| EX-01 | « Le client renseigne ses informations (Nom, Prénom, âge, profession…). » | La liste est ouverte et ambiguë. L'âge et la profession figurent dans la description, mais pas dans le formulaire ni le schéma de données observés. | Le formulaire d'inscription demande le nom, le prénom, le numéro CIN et l'adresse e-mail. Il n'accepte l'inscription que lorsque ces quatre champs sont renseignés et génère une référence non vide, distincte de celles déjà attribuées. L'inclusion de l'âge et de la profession doit être arbitrée. |
| EX-02 | « Il doit fournir sa référence chaque fois qu'il veut prendre un RDV. » | « Se connecter » et le rôle de la référence ne sont pas définis ; aucun mot de passe n'est décrit. | Lorsqu'un client soumet une référence existante, le système l'associe au compte correspondant et autorise l'accès aux fonctions de rendez-vous pour ce compte. Une référence inconnue ne donne accès à aucun compte. |
| EX-03 | « Le système vous permet de choisir la date du RDV et vous propose les créneaux libres. » | « Libres » ne précise ni la liste des créneaux ni le critère de disponibilité. | Pour une date sélectionnée, le système propose uniquement les créneaux fixes (10 h–10 h 30, 11 h–11 h 30, 14 h–14 h 30, 15 h–15 h 30 et 16 h–16 h 30) qui ne sont associés à aucun rendez-vous existant pour cette date. |
| EX-04 | « Le client peut prendre un RDV. » (fonction du projet) | Les données requises et les conditions de succès ne sont pas précisées. | Un client identifié peut créer un rendez-vous en fournissant une date future, un créneau proposé comme libre et un sujet non vide. En cas de succès, le rendez-vous est enregistré et rattaché à ce client, à la date et au créneau choisis. |
| EX-05 | « Un créneau donné pour un avocat ne peut être attribué qu'à une seule réservation active. » (règle métier du projet) | La règle n'indique pas comment traiter deux demandes simultanées ni une réservation supprimée. | Pour une même date, un créneau fixe ne peut être associé qu'à un seul rendez-vous existant. Toute nouvelle tentative sur ce couple date-créneau déjà réservé est refusée ; un créneau libéré par la suppression du rendez-vous redevient disponible. |
| EX-06 | « Le système permet de visualiser l'historique de ses RDVs. » | « Ses » n'est pas traduit en règle d'accès ; le contenu et l'étendue de l'historique sont imprécis. | Un client identifié consulte uniquement les rendez-vous rattachés à son compte. Chaque rendez-vous affiché présente au minimum son sujet, sa date et ses heures de début et de fin. |
| EX-07 | « Le système permet de modifier [les RDVs] si la date n'est pas échue. » | « Date échue » est ambiguë pour un rendez-vous le jour même ; les champs modifiables et la disponibilité du nouveau créneau ne sont pas précisés. | Un client peut modifier le sujet, la date ou le créneau d'un de ses rendez-vous uniquement si son heure de début n'est pas passée. Le nouveau créneau doit être libre à la nouvelle date. La règle de frontière pour un rendez-vous le jour même est à valider (voir arbitrages). |

### Critères d'acceptation

Chaque exigence ci-dessous comporte un scénario nominal et un scénario alternatif. Les résultats décrivent les attentes proposées ; les détails de présentation des erreurs et codes HTTP restent à préciser si nécessaire.

#### EX-01 — Inscription

**Scénario nominal**

- Étant donné un formulaire contenant un nom, un prénom, un CIN et une adresse e-mail renseignés,
- Quand le client soumet l'inscription,
- Alors le système crée le compte et lui attribue une référence non vide qui n'est attribuée à aucun autre compte.

**Scénario alternatif**

- Étant donné un formulaire dont l'un des quatre champs obligatoires est vide,
- Quand le client soumet l'inscription,
- Alors le système refuse la création du compte et indique que les informations obligatoires doivent être complétées.

#### EX-02 — Vérification par référence

**Scénario nominal**

- Étant donné une référence attribuée à un compte existant,
- Quand le client soumet cette référence,
- Alors le système l'associe au compte correspondant et autorise l'accès aux fonctions de rendez-vous de ce compte.

**Scénario alternatif**

- Étant donné une référence qui n'est attribuée à aucun compte,
- Quand le client la soumet,
- Alors le système n'ouvre aucun compte et indique que la référence n'est pas reconnue.

#### EX-03 — Consultation des créneaux disponibles

**Scénario nominal**

- Étant donné une date pour laquelle les créneaux 1 et 3 sont déjà réservés,
- Quand le client consulte les disponibilités de cette date,
- Alors le système propose les créneaux 2, 4 et 5, avec leurs heures fixes.

**Scénario alternatif**

- Étant donné une date à laquelle les cinq créneaux sont réservés,
- Quand le client consulte les disponibilités de cette date,
- Alors le système n'affiche aucun créneau réservable et indique qu'il n'y a pas de disponibilité.

#### EX-04 — Création d'un rendez-vous

**Scénario nominal**

- Étant donné un client identifié, une date future, un créneau disponible et un sujet renseigné,
- Quand le client confirme sa réservation,
- Alors un rendez-vous est enregistré avec ce client, cette date, ce créneau et ce sujet.

**Scénario alternatif**

- Étant donné un client identifié et une date passée,
- Quand le client tente de réserver un créneau,
- Alors le système refuse la création du rendez-vous et indique que la date ne peut pas être réservée.

#### EX-05 — Exclusivité du créneau

**Scénario nominal**

- Étant donné un créneau libre à une date donnée,
- Quand un client confirme un rendez-vous sur ce créneau,
- Alors le rendez-vous est créé et le créneau n'est plus proposé comme libre pour cette date.

**Scénario alternatif**

- Étant donné un rendez-vous déjà enregistré pour un couple date-créneau,
- Quand un autre client tente de réserver ce même couple,
- Alors le système refuse le second rendez-vous et conserve un seul rendez-vous pour ce couple.

#### EX-06 — Consultation des rendez-vous du client

**Scénario nominal**

- Étant donné un client identifié qui possède plusieurs rendez-vous,
- Quand il consulte ses rendez-vous,
- Alors le système affiche ces rendez-vous avec leur sujet, leur date et leurs heures.

**Scénario alternatif**

- Étant donné deux clients distincts ayant chacun des rendez-vous,
- Quand le premier client consulte ses rendez-vous,
- Alors aucun rendez-vous rattaché au second client n'est affiché.

#### EX-07 — Modification d'un rendez-vous

**Scénario nominal**

- Étant donné un rendez-vous appartenant au client dont l'heure de début n'est pas passée et un nouveau créneau libre à la date choisie,
- Quand le client modifie son sujet, sa date ou son créneau,
- Alors le système enregistre les nouvelles valeurs sur son rendez-vous.

**Scénario alternatif**

- Étant donné un rendez-vous dont l'heure de début est passée,
- Quand le client tente de le modifier,
- Alors le système refuse la modification et conserve les valeurs initiales.

## Arbitrages à demander avant de figer les exigences

1. **Données d'inscription :** l'âge et la profession sont mentionnés dans le contexte, mais absents du formulaire et de la base observés. Confirmer s'ils sont requis.
2. **Authentification :** confirmer si la référence seule est le mécanisme d'accès attendu ou si un autre facteur d'authentification est requis.
3. **Rendez-vous le jour même :** confirmer si un rendez-vous reste modifiable lorsque sa date est aujourd'hui mais que son heure de début est encore future. La proposition EX-07 utilise l'heure de début comme frontière.
4. **Réservation simultanée :** confirmer que l'exclusivité s'applique à un seul avocat et qu'une suppression libère immédiatement le créneau.
5. **Sujet :** confirmer la longueur minimale/maximale et les caractères autorisés, non définis par les exigences disponibles.

## Points de vigilance issus de l'implémentation examinée

- La référence est générée à l'inscription, mais l'unicité n'apparaît pas imposée par une contrainte du schéma SQL.
- La disponibilité est calculée à partir des réservations existantes ; la création d'un rendez-vous doit aussi être vérifiée pour empêcher une double réservation, y compris si deux demandes arrivent simultanément.
- Les données d'inscription du code examiné ne contiennent ni âge ni profession.

Ces constats motivent des tests ; ils ne changent pas à eux seuls les exigences métier.

## Partie B — Matrice de traçabilité

| ID exigence | Libellé | Fonction associée | ID cas de test | Statut |
|---|---|---|---|---|
| EX-01 | Inscription avec champs obligatoires et référence distincte | Inscription d'un client | À venir | À faire |
| EX-02 | Accès au compte par référence valide ; refus d'une référence inconnue | Vérification du client via référence | À venir | À faire |
| EX-03 | Affichage des créneaux fixes non réservés pour une date | Vérification des créneaux disponibles / gestion des créneaux fixes | À venir | À faire |
| EX-04 | Création d'un rendez-vous avec données valides et date future | Création d'un rendez-vous | À venir | À faire |
| EX-05 | Unicité d'une réservation par date et créneau | Création d'un rendez-vous / exclusivité du créneau | À venir | À faire |
| EX-06 | Consultation des seuls rendez-vous du client identifié | Historique des rendez-vous | À venir | À faire |
| EX-07 | Modification d'un rendez-vous futur avec un créneau disponible | Modification d'un rendez-vous | À venir | À faire |

*Les identifiants de cas de test seront renseignés lors de leur conception. Le statut « À faire » signifie que la couverture de test n'a pas encore été exécutée dans le cadre de ce livrable.*

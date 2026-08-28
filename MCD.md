# Proposition d'amelioration du MCD

## Objectif

Faire evoluer le modele actuel vers un modele centre sur l'annonce, l'utilisateur, la localisation, les images et les interactions entre acheteur et vendeur.

## 1. Constats sur le modele actuel

Le projet contient aujourd'hui deux sous-ensembles bien distincts :

### Geographie

- `Region`
- `Department`
- `City`

### Annonces

- `Category`
- `Post`
- `Image`

Problemes principaux :

- `Post` n'est pas relie a la geographie
- la localisation est stockee en texte libre dans `Post.localisation`
- `Category.idParent` est un entier, pas une vraie relation recursive
- `Country` est isole
- `Localization` est vide
- le booleen `active` ne suffit pas toujours pour exprimer l'etat metier

## 2. Orientation recommandee

Pour une application type Leboncoin, le coeur du modele doit devenir :

- `User`
- `Listing` (ou `Ad`) a la place de `Post`
- `Category`
- `City`
- `Image`
- `Favorite`
- `Conversation`
- `Message`

## 3. MCD cible minimal recommande

Relations principales :

- `User 1,n Listing`
- `Category 0,1 -> 0,n Category`
- `Category 1,n Listing`
- `Region 1,n Department`
- `Department 1,n City`
- `City 1,n Listing`
- `Listing 1,n Image`
- `User n,n Listing via Favorite`
- `Listing 1,n Conversation`
- `Conversation 1,n Message`
- `Message n,1 User`

Representation simplifiee :

```text
Country 1 --- n Region 1 --- n Department 1 --- n City 1 --- n Listing
User 1 --- n Listing
Category 0..1 --- n Category
Category 1 --- n Listing 1 --- n Image
User n --- n Listing (Favorite)
Listing 1 --- n Conversation 1 --- n Message
```

## 4. Entites recommandees

### 4.1 User

Attributs conseilles :

- `id`
- `email`
- `phone`
- `displayName`
- `passwordHash`
- `isVerified`
- `createdAt`
- `updatedAt`

Role :

- proprietaire des annonces
- expediteur / destinataire des messages
- auteur des favoris

### 4.2 Listing

Renommer `Post` en `Listing` ou `Ad`.

Attributs conseilles :

- `id`
- `title`
- `description`
- `price`
- `status`
- `publishedAt`
- `createdAt`
- `updatedAt`
- `user_id`
- `category_id`
- `city_id`

Valeurs possibles pour `status` :

- `draft`
- `published`
- `reserved`
- `sold`
- `hidden`
- `deleted`

Role :

- entite centrale du domaine metier

### 4.3 Category

Conserver `Category` mais remplacer `idParent` par une vraie relation recursive.

Modele recommande :

- une categorie peut avoir `0 ou 1` parent
- une categorie peut avoir `0 a n` enfants

Attributs utiles :

- `id`
- `name`
- `slug`
- `description`
- `parent_id`
- `isActive`

### 4.4 Image

Modele deja pertinent.

Attributs utiles :

- `id`
- `path`
- `position`
- `listing_id`

### 4.5 Geographie

Si l'application est mono-pays :

- `Country` peut etre ignore dans un premier temps

Si l'application est multi-pays :

- `Country 1,n Region`

Modele recommande :

- `Country`
- `Region`
- `Department`
- `City`

Lien metier critique :

- `Listing` doit pointer vers `City`

Pourquoi :

- filtrage fiable
- recherche geographique
- affichage homogene
- pas de saisie libre incoherente

### 4.6 Favorite

Table d'association entre utilisateur et annonce.

Attributs :

- `user_id`
- `listing_id`
- `createdAt`

Role :

- sauvegarde d'annonces par les utilisateurs

### 4.7 Conversation

Permet d'ouvrir une discussion autour d'une annonce.

Attributs :

- `id`
- `listing_id`
- `buyer_id`
- `seller_id`
- `createdAt`

### 4.8 Message

Messages echanges dans une conversation.

Attributs :

- `id`
- `conversation_id`
- `sender_id`
- `content`
- `isRead`
- `createdAt`

## 5. Evolutions utiles apres le noyau minimal

Selon les categories, il faudra souvent ajouter :

- `brand`
- `condition`
- `surface`
- `mileage`
- `energy`
- `color`
- `model`

Deux strategies possibles :

### 1. Ajouter des colonnes metier sur Listing

- simple
- rapide
- devient vite rigide

### 2. Ajouter un systeme d'attributs dynamiques

- `ListingAttribute`
- `CategoryAttribute`
- plus souple
- meilleur pour une marketplace multi-categories

### 5.1 Gestion des differences entre categories

Certaines annonces n'ont pas les memes besoins metier :

- une offre d'emploi a des champs comme `contractType`, `salary`, `remote`, `company`
- une annonce de logement a des champs comme `surface`, `rooms`, `furnished`, `charges`

Dans ce cas, il faut garder un `Listing` commun pour les champs partages et sortir les champs specifiques dans un modele plus souple.

### Approche recommandee

Conserver dans `Listing` uniquement le socle commun :

- `id`
- `title`
- `description`
- `price`
- `status`
- `category_id`
- `city_id`
- `user_id`
- `created_at`
- `updated_at`

Puis ajouter des attributs definis par categorie.

### Tables recommandees

#### `CategoryAttribute`

Definit les champs autorises pour une categorie.

Attributs utiles :

- `id`
- `category_id`
- `code`
- `label`
- `type`
- `isRequired`

Exemples de `type` :

- `text`
- `number`
- `boolean`
- `select`
- `date`

#### `AttributeOption`

Utile pour les champs de type liste.

Attributs utiles :

- `id`
- `category_attribute_id`
- `value`
- `label`

#### `ListingAttributeValue`

Stocke la valeur d'un attribut pour une annonce donnee.

Attributs utiles :

- `id`
- `listing_id`
- `category_attribute_id`
- `valueText`
- `valueNumber`
- `valueBoolean`
- `valueDate`
- `valueOptionId`

### Exemples

#### Categorie Travail

Attributs possibles :

- `contract_type`
- `salary`
- `company`
- `remote`

#### Categorie Logement

Attributs possibles :

- `surface`
- `rooms`
- `furnished`
- `charges`

### Pourquoi cette approche

- evite une table `listing` avec trop de colonnes nulles
- permet d'ajouter de nouvelles categories sans refonte majeure
- garde une structure commune pour la recherche, l'affichage et les API
- convient bien a une marketplace multi-categories

### Variante pour les categories tres riches

Si une categorie devient beaucoup plus complexe que les autres, on peut ajouter une table specialisee en `1 -> 1` avec `Listing`, par exemple :

- `JobListingDetails`
- `HousingListingDetails`

Bon compromis recommande :

1. commencer avec `Listing` + attributs dynamiques
2. n'ajouter des tables specialisees que pour les categories vraiment complexes

## 6. Corrections prioritaires a faire dans le projet actuel

### Priorite 1

- renommer `Post` en `Listing`
- relier `Listing` a `City`
- supprimer le champ texte libre `localisation`

### Priorite 2

- ajouter `User`
- relier `Listing` a `User`

### Priorite 3

- transformer `Category.idParent` en vraie relation recursive

### Priorite 4

- ajouter `Favorite`

### Priorite 5

- ajouter `Conversation` et `Message`

### Priorite 6

- decider du role reel de `Country` et `Localization`

## 7. Proposition de schema conceptuel final

Noyau recommande :

- `User`
- `Listing`
- `Category`
- `Image`
- `Region`
- `Department`
- `City`
- `Favorite`
- `Conversation`
- `Message`

Relations cle :

- `User 1,n Listing`
- `Category 1,n Listing`
- `City 1,n Listing`
- `Listing 1,n Image`
- `User n,n Listing via Favorite`
- `Listing 1,n Conversation`
- `Conversation 1,n Message`
- `Message n,1 User`
- `Region 1,n Department`
- `Department 1,n City`
- `Category 0,1 -> 0,n Category`

## 8. Resume

Pour une API type Leboncoin, ton modele doit etre structure autour de :

- qui publie ? -> `User`
- quoi ? -> `Listing`
- dans quelle categorie ? -> `Category`
- ou ? -> `City`
- avec quelles photos ? -> `Image`
- qui sauvegarde ? -> `Favorite`
- qui discute avec qui ? -> `Conversation / Message`

Le point le plus important a corriger immediatement est :

- remplacer la localisation texte par une relation entre `Listing` et `City`

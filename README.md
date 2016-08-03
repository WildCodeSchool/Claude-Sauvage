Claude-Sauvage
==============

Un projet Symfony 2.8 créé le 14 juin 2016.

Bienvenue sur ce projet réalisé par 3 étudiants de la Wild Code School: [Sylvian](https://github.com/PIARDSylvian), [Julian](https://github.com/Julianxiaoyu) et [Augustin](https://github.com/Gugusteh/).

Ce projet comporte deux Bundles construits pour Symfony 2.8:

- un système de GED, conçu pour la gestion des documents d'une petite entreprise.

- un système de GRC (ticketing), conçu pour permettre le dialogue entre des commerciaux et leurs clients


# Prérequis

-  [FOS_USER Bundle](https://github.com/FriendsOfSymfony/FOSUserBundle)

-  Symfony 2.8

-  Base de Données mySQL


# Installation



### Cloner ou télécharger ce répertoire


```
$ git clone https://github.com/WildCodeSchool/Claude-Sauvage
```


### Installer composer ainsi que la base de données


```
$ cd Claude-Sauvage/

$ composer install

$ composer update
```

--> mettre à jour la base de données

```
$ php app/console doctrine:schema:update --force
```

--> créer la catégorie "brouillon"

```
$ ged:init
```

### Gestion des droits


```
$ sudo chmod 777 -R web/uploads/
```

--> créer un administrateur

```
$ fos:user:create adminuser --super-admin
```

#Informations utiles

### À propos de FOS_USER

Le bundle FOS_USER de ce projet à été mis en place dans AppBundle, les appels sont donc effectués vis à vis de ce bundle.

### Dépendances des Bundles GED/GRC

Les deux Bundles ont été conçus pour être le moins dépendant possible.

Ils nécessitent cependant des fichiers de configuration:

- dans ./app/config/ 

`routing.yml` , `security.yml`

- dans ./web/

les dossiers css/ fonts/ img/ js/ uploads/
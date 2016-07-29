Claude-Sauvage
==============

Un projet Symfony 2.8 créé le 14 juin 2016.

Bienvenue sur ce projet réalisé par 3 étudiants de la Wild Code School: Sylvian, Julian et Augustin.

Ce projet comporte deux Bundles construits pour Symfony 2.8:

- un système de GED, conçu pour la gestion d'une petite entreprise.

- un système de GRC (ticketing), conçu pour permettre le dialogue entre des commerciaux et leurs clients


Installation
============


1. Cloner ou télécharger ce répertoire


$ git clone https://github.com/WildCodeSchool/Claude-Sauvage


2. Installer composer ainsi que la base de données


--> entrer dans le dossier.

$ cd Claude-Sauvage/

--> installer composer

$ composer install

-->créer une base de données relative au projet

$ php app/console doctrine:database:create

-->mettre à jour composer vis à vis de la base de données

$ composer update

--> construire la base de données

$ php app/console doctrine:schema:update --force


3. Gestion des droits


--> donner les droits d'accès au dossier pour l'upload de fichiers

sudo chmod 777 web/uploads/

-->vider le cache et les logs

$ sudo chmod -R 777 app/cache/ app/logs/
$ sudo rm -rf app/cache/* app/logs/*
$ sudo chmod -R 777 app/cache/ app/logs/




Claude-Sauvage
==============

Un projet Symfony 2.8 créé le 14 juin 2016.

Bienvenue sur ce projet réalisé par 3 étudiants de la Wild Code School: Sylvian, Julian et Augustin.

Ce projet comporte deux Bundles construits pour Symfony 2.8:

- un système de GED, conçu pour la gestion d'une petite entreprise.

- un système de GRC (ticketing), conçu pour permettre le dialogue entre des commerciaux et leurs clients


# Installation



### Cloner ou télécharger ce répertoire


```
$ git clone https://github.com/WildCodeSchool/Claude-Sauvage
```


### Installer composer ainsi que la base de données


```
$ cd Claude-Sauvage/

$ composer install

$ php app/console doctrine:database:create

$ composer update
```

--> construire la base de données

```
$ php app/console doctrine:schema:update --force
```

### Gestion des droits


```
sudo chmod 777 web/uploads/
```

-->vider le cache et les logs

```
$ sudo chmod -R 777 app/cache/ app/logs/
$ sudo rm -rf app/cache/* app/logs/*
$ sudo chmod -R 777 app/cache/ app/logs/
```




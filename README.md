# PHP to Eggdrop
This is a simple PHP library for sending messages to an Eggdrop IRC bot.

Ce projet vous permet de communiquer avec votre robot Eggdrop depuis PHP. Vous pouvez envoyer des messages et des commandes à votre robot, qui les exécutera sur le serveur IRC sur lequel il est connecté.

## Prérequis
* Vous devez avoir accès à un robot Eggdrop en cours d'exécution sur un serveur IRC.
* Vous devez connaître l'adresse IP et le port de votre robot Eggdrop, ainsi que votre nom d'utilisateur et votre mot de passe.

## Utilisation
Pour utiliser ce projet, vous devez d'abord créer une nouvelle instance de la classe Eggdrop. Vous devez fournir l'adresse IP, le port, votre nom d'utilisateur et votre mot de passe lors de la construction de l'objet. Vous pouvez également spécifier le canal ou l'utilisateur à qui vous souhaitez envoyer un message et le message en lui-même.

Voici un exemple de création d'un objet Eggdrop :
```
$eggdrop = new Eggdrop("192.168.1.100", 16667, "mon_nom_dutilisateur", "mon_mot_de_passe", "#mon_canal", "Bonjour, je suis un robot Eggdrop!"

```
sendCommand($command)
Cette méthode permet d'envoyer une commande à votre robot Eggdrop
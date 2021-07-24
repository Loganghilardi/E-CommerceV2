# E-CommerceV2

![Symfony](https://img.shields.io/badge/Symfony-5.0-brightgreen)

Site e-commerce simple qui comporte juste un panier, des produits que l'utilisateur peut consulter et ajouter à son panier.

## Fonctionnalités

- Il existe un compte **admin** qui permet de pouvoir de modifier et supprimer des produits.
- Un panier vide est crée au moment où l'utilisateur crée son compte sur le site.
- On stocke son panier dans une autre table pour pouvoir ensuite récupérer le panier de l'utilisateur lorsqu'il se reconnecte. (On ne travaille pas aves les sessions)
- L'admin peut ajouter le rôle admin à un utilisateur via un back-office.
- L'utilisateur peut supprimer des produits depuis son panier.
- Un nouveau panier est crée lorsque l'utilisateur achète son panier et on lui assigne un nouveau panier pour cet utilisateur.

# Credits

Romain BOUCHEZ

Hakan CAVDAR

Logan GHILARDI

Alexandre TO

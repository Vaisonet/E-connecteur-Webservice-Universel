SDK webservice générique E-connecteur

Cette archive contient des tests permettant de simuler les appels au webservice développé selon la documentation :
 - "Spécifications techniques webservice e-commerce générique e-connecteur 5.9"
 - "Spécifications techniques webservice e-commerce générique e-connecteur 5.8"

Principes :
1) Il y a un fichier de test par paragraphe de la documentation,
2) Le test simule une communication avec le webservice,
3) Le test valide seulement la structure basique des données,
4) Il vous appartient de valider la qualité et la cohérence des données, les tests ne pouvant pas le garantir.

Pour utiliser les tests :
A la racine du dossier, le fichier config.php contient les informations de connexion au webservice, c'est à renseigner.
Dans le dossier "Tests", vous retrouvez les tests référencés selon le paragraphe de la documentation.
Ils sont prévus pour être lancé en ligne de commande avec php 5.4 ou supérieur, mais théoriquement sur un serveur web cela doit fonctionner aussi.

La réponse du webservice est validée à l'aide des fichiers XSD se trouvant dans le dossier XSD.
En cas de différence avec le format prévu, une alerte php est affichée.

Pour toute assistance, vous pouvez consulter notre base de connaissance : http://assistance.e-connecteur.fr
Ou contacter le service technique par email : support@vaisonet.com ou 

© 2015 Vaisonet
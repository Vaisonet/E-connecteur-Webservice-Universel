Kit de développement webservice universel E-connecteur

Le logiciel Vaisonet E-connecteur supporte plusieurs webservices nativement, comme Prestashop, Magento, Woocommerce.
Pour les autres CMS, comme Joomla Hikashop, Joomla Virtuemarkt, Drupal Commerce, Thelia, etc ... vous devez, si vous souhaitez utiliser E-connecteur, implémenter un webservice répondant aux spécifications E-connecteur.

Cette archive contient des tests (sous-dossier WSTests) permettant de simuler les appels au webservice développé selon la documentation :
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

Nous proposons également des exemples d'implémentation du webservice universel E-connecteur pour différents CMS, dans le sous-dossier WS.
Ces scripts sont là à titre d'exemple d'implémentation. Il ne s'agit pas de logiciel ou de script supportés par notre équipe, mais vous pouvez les réutiliser comme base de vos développements.

Si vous souhaitez développer et publier un plugin spécifique à un CMS e-commerce, n'hésitez pas à contacter notre équipe, nous serions ravi de mettre en place un partenariat.


Pour toute assistance, vous pouvez consulter notre base de connaissance : https://assistance.e-connecteur.fr
Ou contacter le service technique par email : support@vaisonet.com

© 2015-2017 Vaisonet
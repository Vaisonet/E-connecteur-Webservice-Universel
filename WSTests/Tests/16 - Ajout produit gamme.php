<?php
require '../config.php';
require '../libs/functions.php';

$opt['data'] = '<?xml version="1.0"?>
			<connecteur>
				<resource>add_product</resource>
				<url>'.$url.'</url>
				<key>'.$key.'</key>
				<reference>123456</reference>
				<EAN13>1234567891230</EAN13>
				<UPC></UPC>
				<largeur>10</largeur>
				<hauteur>20</hauteur>
				<profondeur>30</profondeur>
				<p_vente>40.54</p_vente>
				<nom>Mon produit éàù</nom>
				<meta_description>Ma méta description</meta_description>
				<meta_keywords>Mon méta mot clef</meta_keywords>
				<meta_title>Mon méta titre</meta_title>
				<desc_longue>Ma description longue bla bla éàù</desc_longue>
				<desc_courte>Ma description courte bla bla éàù</desc_courte>
				<tva>5.50</tva>
				<ecotaxe>0</ecotaxe>
				<stock>1540</stock>
				<poids>12.01</poids>
				<actif>1</actif>
                                <gammes>
                                    <VOIT0010> <!--//Une déclinaison produit, c’est son SKU, à réutiliser dans les commandes-->
                                        <Couleur> <!--//La première option-->
                                            <name>Couleur</name>
                                            <value>Bleu</value>
                                            <quantity>0</quantity>
                                            <p_vente>55</p_vente>
                                            <weight>0</weight>
                                        </Couleur>
                                        <Taille> <!--//La seconde option-->
                                            <name>Taille</name>
                                            <value>L</value>
                                            <quantity>0</quantity>
                                            <p_vente>55</p_vente>
                                            <weight>0</weight>
                                        </Taille>
                                    </VOIT0010>
                                    <VOIT0011>
                                        <Couleur>
                                            <name>Couleur</name>
                                            <value>Bleu</value>
                                            <quantity>-1</quantity>
                                            <p_vente>0</p_vente>
                                            <weight>0</weight>
                                        </Couleur>
                                        <Taille>
                                            <name>Taille</name>
                                            <value>XL</value>
                                            <quantity>-1</quantity>
                                            <p_vente>0</p_vente>
                                            <weight>0</weight>
                                        </Taille>
                                    </VOIT0011>
                                </gammes>
			</connecteur>';

$result = call_ws_post($opt);

validation($result, __FILE__);
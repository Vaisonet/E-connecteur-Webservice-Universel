<?php
function call_ws_post($opt)
{
	global $url;
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_HEADER, false);
	curl_setopt($oCurl, CURLOPT_POST, TRUE);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $opt);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
	$iResult = curl_exec($oCurl);

	if ($iResult === false)
	{
	   die('Erreur CURL : ' . curl_error($oCurl));
	}

	curl_close($oCurl);
	
	return $iResult;
}

function call_ws_put($opt)
{
	global $url;
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_HEADER, false);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $opt); 
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($oCurl, CURLOPT_URL, $url);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($oCurl, CURLINFO_HEADER_OUT, true);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array( 'Expect:' ));
	curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, 'PUT');
	$iResult = curl_exec($oCurl);

	if ($iResult === false)
	{
	   die('Erreur CURL : ' . curl_error($oCurl));
	}

	curl_close($oCurl);
	
	return $iResult;
}

function validation($result, $fichier)
{
	$doc = new DOMDocument();
	$doc->loadXML($result);
	$r = $doc->schemaValidate('../xsd/' . basename($fichier)  . '.xsd');

	if ($r === true)
	{
		echo "\nSyntaxe XML de la réponse valide\n";
	}
	else
	{
		echo "\n!!!!!!\nSyntaxe XML de la réponse invalide\n";
		echo $result;
	}
}
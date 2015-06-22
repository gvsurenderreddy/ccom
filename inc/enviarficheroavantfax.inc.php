<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function sendFileAvantFax( $filename, $faxnumber){

	$url = getParametro("avantfax_sendservice_url");
	
	//$url =  $url?$url:"http://localhost/avantfax/sendservice.php";
	$url =  $url?$url:"http://frutas/avantfax/sendservice.php";

	$postData[ 'modem' ] = "any";
	$postData[ 'MAX_FILE_SIZE' ] = "2097152";
	$postData[ 'destinations' ] = $faxnumber;
	$postData[ '_submit_check' ] = "1";


	$file_name_with_full_path  = $filename;

	$postData['file_0'] = '@'.$file_name_with_full_path;


	$ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_POST, 1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData );

	$response = curl_exec( $ch );

	return $response;
}







?>
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */




function agnadirACategoria($id_comm,$text,$pais){
	global $nb;
	
	if (!strlen($text)) return;
	
	$docid = $id_comm;

	if(!$id_comm) return;

	$doc =trim($text);

	$cat =$pais;

    if ($nb->train($docid, $cat, $doc)) {
        $nb->updateProbabilities();
    } else {

    }
}


function deducirCategoria($text,$humbral = 0.51){
	global $nb;
	
	$doc = trim($text);
	if (!strlen($doc)) return;

	$scores = $nb->categorize($doc);

	$max = $humbral;//el humbral de deteccion, esto significa que si nuestra seguridad no es del al menos humbral%.. entonces no decimos nada
	$selected = false;

    //echo "<table><caption>Scores</caption>\n";
    //echo "<tr><th>Catégories</th><th>Scores</th></tr>\n";
    while(list($cat,$score) = each($scores)) {
      //  echo "<tr><td>$cat</td><td>$score</td></tr>\n";

		if($score>$max){
			$max = $score;
			$selected = $cat;
		}

    }
    //echo "</table>";


	return $selected;
}






?>
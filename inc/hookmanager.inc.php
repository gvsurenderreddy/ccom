<?php




function runHooksOnView($id_comm){
	global $hook_onview;

	foreach( $hook_onview as $mime => $onView ){
		if (function_exist($onView)){
			$html = $onView();
		}
	}



}













?>
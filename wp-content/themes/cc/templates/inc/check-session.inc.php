<?php
@session_start();

/**
* Checks whether session is alive.
*
* @param  void
* @return boolean $session_state
*/
function is_session_exists(){
    $session_state = false;
    if(isset($_SESSION['username']) && !empty($_SESSION['username']))
        $session_state = true;
        
    return $session_state;
}


<?php


class PublicController extends X_Controller_Action {
	
	function init() {
    	// uses the device helper for wiimc recognition
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
			// wiimc 1.0.9 e inferiori nn accetta redirect
			$this->_forward('collections', 'index');
		} else {
			$this->_helper->redirector('index','index');
		}
				
	}

}
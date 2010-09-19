<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/ResolverInterface.php';
require_once 'Zend/Config.php';

/**
 * Filter out hidden/system item from FileSystem lists
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_HideHidden extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('filterShareItems');
	}
	
	/**
	 * @param array $item
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 * @return boolean|null true or null if file is ok, false otherwise (will be filtered out)
	 */
	public function filterShareItems($item, $provider, Zend_Controller_Action $controller) {
		try {
			$plugin = X_VlcShares_Plugins::broker()->getPlugins($provider);
			if ( is_a($plugin, 'X_VlcShares_Plugins_FileSystem' ) && 
				$plugin instanceof X_VlcShares_Plugins_ResolverInterface ) {
				// i use instanceof ResolverInterface
				// so i have code suggestions
				X_Debug::i('Plugin triggered');
				// X_VlcShares_Plugins_FileSystem register a custom param in item
				// for location lookup
				$location = $plugin->resolveLocation(@$item['X_VlcShares_Plugins_FileSystem:location']);
				// i must check for $location !== false as a fallback for no custom param case
				if ( $location !== false && file_exists($location) ) {
					// i have a location to check for hidden files:
					if ( $this->_checkEntry($location) === false) {
						X_Debug::i("Plugin triggered, item filtered: $location");
						return false;
					}
				}
			}
		} catch (Exception $e) {
			X_Debug::w("Problem while filtering: {$e->getMessage()}");
		}
	}

	/**
	 * 
	 * @param DirectoryIterator $entry
	 */
	private function _checkEntry($entry) {
		if ( X_Env::isWindows() ) {
			return !$this->_isHiddenOnWindows($entry);
		} else {
			// in linux, hidden files start with .
			if ( substr(pathinfo($entry,PATHINFO_BASENAME ) ,0,1) == '.') {
				return false;
			}
		}
		return true;
	}
	
	private function _isHiddenOnWindows($fileName) {
	    $attr = trim(exec('FOR %A IN ("'.$fileName.'") DO @ECHO %~aA'));
	
	    if($attr[3] === 'h' /* || $attr[4] === 's' */ ) return true;
	    
	    // do check for system flag only if hideSystem conf is true
	    if ( ((bool) $this->config('hideSystem', false)) && $attr[4] === 's' ) return true;
	
	    return false;
	}
	
}

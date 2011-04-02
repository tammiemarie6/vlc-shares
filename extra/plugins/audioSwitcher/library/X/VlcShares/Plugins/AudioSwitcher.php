<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_AudioSwitcher extends X_VlcShares_Plugins_Abstract {
	
	const FILE = 'file';
	const STREAM = 'stream';

	public function __construct() {
		
		$this->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('registerVlcArgs')
			->setPriority('getSelectionItems')
			->setPriority('gen_beforeInit')
			->setPriority('getIndexManageLinks');
		
		
	}	

	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}

	/**
	 * Give back the link for change subs
	 * and the default config for this location
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		}
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');

		$subLabel = X_Env::_('p_audioswitcher_selection_none');

		$subParam = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $subParam !== false ) {
			$subParam = X_Env::decode($subParam);
			list($type, $source) = explode(':', $subParam, 2);
			$subLabel = X_Env::_("p_audioswitcher_subtype_$type")." ($source)";
		}
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_audioswitcher_sub').": $subLabel");
		$link->setIcon('/images/manage/plugin.png')
			->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'	=>	'selection',
					'pid'		=>	$this->getId()
				), 'default', false);

		return new X_Page_ItemList_PItem(array($link));
		
	}

	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		} 
		
		X_Debug::i('Plugin triggered');		
		
		$urlHelper = $controller->getHelper('url');
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_audioswitcher_selection_title'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		return new X_Page_ItemList_PItem();
		
	}
	
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		} 
		$providerClass = get_class($provider);
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentSub = $controller->getRequest()->getParam($this->getId(), false);
		if ( $currentSub !== false ) $currentSub = X_Env::decode($currentSub);

		$return = new X_Page_ItemList_PItem();
		$item = new X_Page_Item_PItem($this->getId().'-none', X_Env::_('p_audioswitcher_selection_none'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'	=>	'mode',
					$this->getId() => null, // unset this plugin selection
					'pid'		=>	null
				), 'default', false)
			->setHighlight($currentSub === false);
		$return->append($item);
		
		
		// i do the check for this on top
		// location param come in a plugin encoded way
		$location = $provider->resolveLocation($location);
		
		// check if infile support is enabled
		// by default infile.enabled is true
		if ( $this->config('infile.enabled', true) ) {
			// check for infile tracks
			$infileTracks = $this->helpers()->stream()->setLocation($location)->getAudiosInfo();
			//X_Debug::i(var_export($infileSubs, true));
			foreach ($infileTracks as $streamId => $track) {
				X_Debug::i("Valid infile-sub: [{$streamId}] {$track['language']} ({$track['format']})");
				$item = new X_Page_Item_PItem($this->getId().'-stream-'.$streamId, X_Env::_("p_audioswitcher_subtype_".self::STREAM)." {$streamId} {$track['language']} {$track['format']}");
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':sub', self::STREAM.":{$streamId}")
					->setLink(array(
							'action'	=>	'mode',
							'pid'		=>	null,
							$this->getId() => X_Env::encode(self::STREAM.":{$streamId}") // set this plugin selection as stream:$streamId
						), 'default', false)
					->setHighlight($currentSub == self::STREAM.":{$streamId}");
				$return->append($item);
			}
		}
		
		// for file system source i will search for subs in filename notation
		// by default file.enabled is true
		if ( $this->config('file.enabled', true) && is_a($provider, 'X_VlcShares_Plugins_FileSystem') ) {
			
			$dirname = pathinfo($location, PATHINFO_DIRNAME);
			$filename = pathinfo($location, PATHINFO_FILENAME);
			
			$extTracks = $this->getFSTracks($dirname, $filename);
			foreach ($extTracks as $streamId => $track) {
				X_Debug::i("Valid extfile-sub: {$track['language']} ({$track['format']})");
				$item = new X_Page_Item_PItem($this->getId().'-file-'.$streamId, X_Env::_("p_audioswitcher_subtype_".self::FILE)." {$track['language']} ({$track['format']})");
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':sub', self::FILE.":{$streamId}")
					->setLink(array(
							'action'	=>	'mode',
							'pid'		=>	null,
							$this->getId() => X_Env::encode(self::FILE.":{$streamId}") // set this plugin selection as stream:$streamId
						), 'default', false)
					->setHighlight($currentSub == self::FILE.":{$streamId}");
				$return->append($item);
				
			}
			
		}
		
		// general profiles are in the bottom of array
		return $return;
	}
	

	/**
	 * This hook can be used to add normal priority args in vlc stack
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function registerVlcArgs(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {

		X_Debug::i('Plugin triggered');
		
		$subParam = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $subParam !== false ) {
			
			$subParam = X_Env::decode($subParam);
			list ($type, $sub) = explode(':', $subParam, 2);

			 if ( $type == self::FILE ) {
			 	
			 	$source = trim($vlc->getArg('source'), '"');
			 	
			 	$filename = pathinfo($source, PATHINFO_FILENAME); // only the name of file, without ext
			 	$dirname = pathinfo($source, PATHINFO_DIRNAME);
			 	
			 	$subFile = $dirname.'/'.$filename.'.'.ltrim($sub,'.');

			 	$subFile = realpath($subFile);
			 	
			 	X_Debug::i("Alternative audio file selected: $subFile");
			 	
			 	$vlc->registerArg('audio', "--input-slave=\"{$subFile}\"");
			 	
			 } elseif ( $type == self::STREAM ) {
			 	
			 	$sub = (int) $sub;
			 	
			 	X_Debug::i("Alternative audio track selected: $sub");
			 	
			 	$vlc->registerArg('audio', "--audio-track=\"$sub\"");
			 	
			 }
			
		}	
	}
	
	
	
	
	/**
	 * Search in $dirPath for sub valid for $filename
	 * @param string $dirPath
	 * @param string $filename
	 */
	public function getFSTracks($dirPath, $filename) {

		$validTracks = explode('|', $this->config('file.extensions', 'mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3'));
		X_Debug::i("Check for subs in $dirPath for $filename (valid: {$this->config('file.extensions', 'mp3|wav|mpa|mp2a|mpga|wma|ogg|aac|ac3')})");
		
		
		$dir = new DirectoryIterator($dirPath);
		$tracksFound = array();
		foreach ($dir as $entry) {
			if ( $entry->isFile() ) {
				// se e' un file sub valido
				if ( array_search(pathinfo($entry->getFilename(), PATHINFO_EXTENSION), $validTracks ) !== false ) {
					// stessa parte iniziale
					if ( X_Env::startWith($entry->getFilename(), $filename) ) {
						X_Debug::i("$entry is valid");
						$trackName = substr($entry->getFilename(), strlen($filename));
						$tracksFound[$validTracks] = array(
							'language'	=> trim(pathinfo($trackName, PATHINFO_FILENAME), '.'),
							'format'	=> pathinfo($trackName, PATHINFO_EXTENSION)
						);						
					}
				}
			}
		}
		return $tracksFound;
		
	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_audioswitcher_mlink'));
		$link->setTitle(X_Env::_('p_audioswitcher_managetitle'))
			->setIcon('/images/manage/configs.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'audioSwitcher'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	
	
}

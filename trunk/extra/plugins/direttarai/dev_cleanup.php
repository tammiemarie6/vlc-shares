<?php 
// context of this file is /scripts/cleanup.php

defined('APPLICATION_PATH') or die("This script can't be called directly. Please, use scripts/cleanup.php");
// function rm_recursive($path) definited in /scripts/cleanup.php

// dev cleanup file
// this file have to remove all things created by dev_bootstrap.php
$basePath = dirname(__FILE__);

$neededDirectories = array(
	//APPLICATION_PATH.'/../data/animeftw/'
);

$neededLinks = array(
	$basePath.'/public/images/direttarai/' => APPLICATION_PATH.'/../public/images/direttarai',
	$basePath.'/languages/X_VlcShares_Plugins_DirettaRai.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_DirettaRai.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_DirettaRai.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_DirettaRai.it_IT.ini',
);

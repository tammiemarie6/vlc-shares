<?php

require_once 'X/Controller/Action.php';

class ManageController extends X_Controller_Action
{
	protected $vlc = null;

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	$this->vlc = new X_Vlc($this->options->vlc);
    }

    public function indexAction() {
    	
    	$coreLinks = array(
    		array(
    			'label'		=>	X_Env::_('manage_goto_browse'),
    			'link'		=>	$this->_helper->url('collections', 'index'),
    			'icon'		=>	'/images/manage/browse.png',
    			'title'		=>	X_Env::_('manage_goto_browsetitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_test'),
    			'link'		=>	$this->_helper->url('index', 'test'),
    			'icon'		=>	'/images/manage/test.png',
    			'title'		=>	X_Env::_('manage_goto_testtitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_configs'),
    			'link'		=>	$this->_helper->url('configs', 'manage'),
    			'icon'		=>	'/images/manage/configs.png',
    			'title'		=>	X_Env::_('manage_goto_configstitle'),
    		),
    	);

    	$messages = X_VlcShares_Plugins::broker()->getIndexMessages($this);
    	
    	$news = X_VlcShares_Plugins::broker()->getIndexNews($this);
    	
    	// fetch links from plugins
		$actionLinks = X_VlcShares_Plugins::broker()->getIndexActionLinks($this);
		$manageLinks = X_VlcShares_Plugins::broker()->getIndexManageLinks($this);
		$statistics = X_VlcShares_Plugins::broker()->getIndexStatistics($this);
		
		
		
		
		$this->view->version = X_VlcShares::VERSION;
		$this->view->configPath = X_VlcShares::config();
		$this->view->test = $this->_helper->url('index', 'test');
		$this->view->pcstream = $this->_helper->url('pcstream', 'index');
		$this->view->pcstream_enabled = ($this->options->pcstream->get('commanderEnabled', false) && $this->vlc->isRunning());
		$this->view->coreLinks = $coreLinks;
		$this->view->actionLinks = $actionLinks;
		$this->view->manageLinks = $manageLinks;
		$this->view->statistics = $statistics;
		$this->view->news = $news;
		$this->view->messages = $messages;
    	
		
		// i need to get first class links
		
		
		
		
    }
}

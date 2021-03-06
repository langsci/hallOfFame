<?php

/**
 * @file plugins/generic/hallOfFame/HallOfFamePlugin.inc.php
 *
 * Copyright (c) 2015 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HallOfFamePlugin
 * Hall of fame plugin main class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class HallOfFamePlugin extends GenericPlugin {

	/**
	 * Get the plugin's display (human-readable) name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.hallOfFame.displayName');
	}

	/**
	 * Get the plugin's display (human-readable) description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.hallOfFame.description');
	}

	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {

		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				// Register the hall of fame DAO.
				import('plugins.generic.hallOfFame.HallOfFameDAO');
				$hallOfFameDao = new HallOfFameDAO();
				DAORegistry::registerDAO('HallOfFameDAO', $hallOfFameDao);
				HookRegistry::register('LoadHandler', array($this, 'handleLoadRequest'));
			}
			return true;
		}
		return false;
	}

	// handle load request
	function handleLoadRequest($hookName, $args) {

		$request = $this->getRequest();
		$press   = $request->getPress();		

		// get url path components to overwrite them 
		$pageUrl =& $args[0];
		$opUrl =& $args[1];

		$goToHallOfFame = $this->checkUrl($pageUrl,$opUrl);

		if ($goToHallOfFame) {

			$pageUrl = '';
			$opUrl = 'viewHallOfFame';

			define('HANDLER_CLASS', 'HallOfFameHandler');
			define('HALLOFFAME_PLUGIN_NAME', $this->getName());
			$this->import('HallOfFameHandler');
			return true;
		}
		return false;
	}
	
	// PKPPlugin::getManagementVerbs()
	function getManagementVerbs() {
		$verbs = parent::getManagementVerbs();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.hallOfFame.settings'));
		}
		return $verbs;
	}

	/**
	 * @see Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

 	/**
	 * @see Plugin::manage()
	 */
	function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();
				$this->import('HallOfFameSettingsForm');
				$form = new HallOfFameSettingsForm($this, $context->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	private function checkUrl($pageUrl,$opUrl) {

		$request = $this->getRequest();
		$context = $request->getContext();
	
		// get path components
		$urlArray = array();
		$urlArray[] = $pageUrl;
		$urlArray[] = $opUrl;
		$urlArray = array_merge($urlArray,$request->getRequestedArgs());
		$urlArrayLength = sizeof($urlArray);

		// get path components specified in the plugin settings
		$settingPath = $this->getSetting($context->getId(),'langsci_hallOfFame_path');

		if (!ctype_alpha(substr($settingPath,0,1))&&!ctype_digit(substr($settingPath,0,1))) {
			return false;
		}
		$settingPathArray = explode("/",$settingPath);
		$settingPathArrayLength = sizeof($settingPathArray);
		if ($settingPathArrayLength==1) {
			$settingPathArray[] = 'index';
		}

		// compare path and path settings
		$goToHallOfFame = false;
		if ($settingPathArray==$urlArray){
			$goToHallOfFame = true;
		}
		return $goToHallOfFame;
	}

	// PKPPlugin::getTemplatePath
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
}

?>

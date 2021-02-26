<?php

/**
 * @file plugins/generic/hallOfFame/SettingsForm.inc.php
 *
 * Copyright (c) Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_generic_HallOfFame
 *
 * @brief Form for adding/editing the settings for the AddThis plugin
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

	/** @var The plugin being edited */
	private $_plugin;

	/** @var int Associated context ID */
	private $_contextId;

	/**
	 * Constructor.
	 * @param $plugin 
	 * @param $press Press
	 */
	function __construct($plugin,$contextId) {

		$this->_contextId = $contextId;
		$this->_plugin = $plugin;
		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
		
		//$this->addCheck(new FormValidatorPost($this));
		//$this->addCheck(new FormValidatorCSRF($this));
	 
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the plugin.
	 */
	function initData() {
	
		$contextId = $this->_contextId;
		$plugin = $this->_plugin;

		$this->setData('langsci_hallOfFame_userGroups', $plugin->getSetting($contextId, 'langsci_hallOfFame_userGroups'));
		$this->setData('langsci_hallOfFame_path', $plugin->getSetting($contextId, 'langsci_hallOfFame_path'));
		$this->setData('langsci_hallOfFame_recentDate', $plugin->getSetting($contextId, 'langsci_hallOfFame_recentDate'));
		$this->setData('langsci_hallOfFame_linksToPublicProfile', $plugin->getSetting($contextId, 'langsci_hallOfFame_linksToPublicProfile'));
		$this->setData('langsci_hallOfFame_unifiedStyleSheetForLinguistics', $plugin->getSetting($contextId, 'langsci_hallOfFame_unifiedStyleSheetForLinguistics'));
		$this->setData('langsci_hallOfFame_startCounting', $plugin->getSetting($contextId, 'langsci_hallOfFame_startCounting'));
		$this->setData('langsci_hallOfFame_percentileRanks', $plugin->getSetting($contextId, 'langsci_hallOfFame_percentileRanks'));
		$this->setData('langsci_hallOfFame_minNumberOfSeries', $plugin->getSetting($contextId, 'langsci_hallOfFame_minNumberOfSeries'));
		$this->setData('langsci_hallOfFame_medalCount', $plugin->getSetting($contextId, 'langsci_hallOfFame_medalCount'));
		$this->setData('langsci_hallOfFame_includeCommentators', $plugin->getSetting($contextId, 'langsci_hallOfFame_includeCommentators'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {

		$this->readUserVars(array(
			'langsci_hallOfFame_path',
			'langsci_hallOfFame_userGroups',
			'langsci_hallOfFame_recentDate',
			'langsci_hallOfFame_linksToPublicProfile',
			'langsci_hallOfFame_unifiedStyleSheetForLinguistics',
			'langsci_hallOfFame_startCounting',
			'langsci_hallOfFame_percentileRanks',
			'langsci_hallOfFame_minNumberOfSeries',
			'langsci_hallOfFame_medalCount',
			'langsci_hallOfFame_includeCommentators',
		));
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	 /*
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		$templateMgr->assign('pluginBaseUrl', $request->getBaseUrl() . '/' . $this->_plugin->getPluginPath());

		return parent::fetch($request);
	}*/
	
	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	public function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}	

	/**
	 * Save the plugin's data.
	 * @see Form::execute()
	 */
	function execute(...$functionArgs) {

		$plugin = $this->_plugin;
		$contextId = $this->_contextId;

		$plugin->updateSetting($contextId, 'langsci_hallOfFame_userGroups', trim($this->getData('langsci_hallOfFame_userGroups')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_path', trim($this->getData('langsci_hallOfFame_path')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_recentDate', trim($this->getData('langsci_hallOfFame_recentDate')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_linksToPublicProfile', trim($this->getData('langsci_hallOfFame_linksToPublicProfile')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_unifiedStyleSheetForLinguistics', trim($this->getData('langsci_hallOfFame_unifiedStyleSheetForLinguistics')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_startCounting', trim($this->getData('langsci_hallOfFame_startCounting')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_percentileRanks', trim($this->getData('langsci_hallOfFame_percentileRanks')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_minNumberOfSeries', trim($this->getData('langsci_hallOfFame_minNumberOfSeries')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_medalCount', trim($this->getData('langsci_hallOfFame_medalCount')));
		$plugin->updateSetting($contextId, 'langsci_hallOfFame_includeCommentators', trim($this->getData('langsci_hallOfFame_includeCommentators')));
		
		parent::execute(...$functionArgs);
	}

}
?>

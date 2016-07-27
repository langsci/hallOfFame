<?php

/**
 * @file plugins/generic/addThis/AddThisSettingsForm.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddThisSettingsForm
 * @ingroup plugins_generic_AddThis
 *
 * @brief Form for adding/editing the settings for the AddThis plugin
 */

import('lib.pkp.classes.form.Form');

class HallOfFameSettingsForm extends Form {

	/** @var AddThisBlockPlugin The plugin being edited */
	var $_plugin;

	/** @var int Associated context ID */
	private $_contextId;

	/**
	 * Constructor.
	 * @param $plugin AddThisBlockPlugin
	 * @param $press Press
	 */
	function HallOfFameSettingsForm($plugin, $contextId) {

		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));

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
	 * Fetch the form.
	 * @see Form::fetch()
	 * @param $request PKPRequest
	 */
	function fetch($request) {

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		$templateMgr->assign('pluginBaseUrl', $request->getBaseUrl() . '/' . $this->_plugin->getPluginPath());

		return parent::fetch($request);
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
	 * Save the plugin's data.
	 * @see Form::execute()
	 */
	function execute() {

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
		

	}
}
?>

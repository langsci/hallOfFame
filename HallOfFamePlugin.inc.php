<?php

/**
 * @file plugins/generic/hallOfFame/HallOfFamePlugin.inc.php
 *
 * Copyright (c) 2016-2021 Language Science Press
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if ($success && $this->getEnabled($mainContextId)) {
			HookRegistry::register('LoadHandler', array($this, 'handleLoadRequest'));	
			HookRegistry::register('userdao::getAdditionalFieldNames', array($this, 'addFieldName'));
			HookRegistry::register('User::PublicProfile::AdditionalItems', array($this, 'publicProfileFieldEdit'));
			HookRegistry::register('publicprofileform::initdata', array($this, 'publicProfileInitData'));
			HookRegistry::register('publicprofileform::readuservars', array($this, 'publicProfileReadUserVars'));
			HookRegistry::register('publicprofileform::execute', array($this, 'publicProfileExecute'));
			//HookRegistry::register('publicprofileform::Constructor', array($this, 'publicProfileAddCheck'));			
		}
		return $success;
	}
	
	function handleLoadRequest($hookName, $args) {
		$page = $args[0];	
		if ($page == 'halloffame') {
			$args[1] =$page;
			define('HALLOFFAME_PLUGIN_NAME', $this->getName());
			define('HANDLER_CLASS', 'HallOfFameHandler');
			$this->import('HallOfFameHandler');		
			return true;
		}		
		return false;
	}	

	/**
	 * Renders additional content for the PublicProfileForm.
	 * 
	 * Called by @see lib/pkp/templates/user/publicProfileForm.tpl
	 *
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return bool
	 */
	function publicProfileFieldEdit($hookName, $params) {
		
		$templateMgr =& $params[1];
		$output  =& $params[2];
		$templateMgr->assign(array(
			'consentLabel' => "I agree that my name is displayed in the hall of fame",
		));		
		$output = $templateMgr->fetch($this->getTemplateResource('consentToHallOfFame.tpl'));	
		return true;
	}
	
	/**
	 * Init public profile consent checkbox
	 */
	function publicProfileInitData($hookName, $params) {
		$form =& $params[0];
		$user = $form->getUser();		
		if ($user) {
			$form->setData('hallOfFame', $user->getData('hallOfFame'));
		}
		return false;
	}	

	/**
	 * Read the value of the hall of fame checkbox in the user form
	 */
	function publicProfileReadUserVars($hookName, $params) {
		$form =& $params[0];
		$vars =& $params[1];
		$vars[] = 'hallOfFame';		
		return false;
	}

	/**
	 * Set the user setting
	 */
	function publicProfileExecute($hookName, $params) {
		$form =& $params[0];
		$user = $form->getUser();
		if ($user) {
			$user->setData('hallOfFame', $form->getData('hallOfFame'));	
		}
		return false;
	}
	
	/**
	 * Add the validation check for the vgWortCardNo field (2-7 numbers)
	 */
	 /*
	function publicProfileAddCheck($hookName, $params) {
		$form =& $params[0];
		$form->addCheck(new FormValidator($form, 'hallOfFame', 'optional', 'editor.review.errorAddingReviewer'));
		return false;
	}	*/	

	/**
	 * Consider hallOfFame filed in the user DAO
	 */
	function addFieldName($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'hallOfFame';
		return false;
	}

	//
	// Implement template methods from GenericPlugin.
	//
	/**
	 * @copydoc Plugin::getActions()
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

				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->registerPlugin('function', 'plugin_url', array($this, 'smartyPluginUrl'));

				$this->import('SettingsForm');
				$form = new SettingsForm($this, $context->getId());

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

}

?>

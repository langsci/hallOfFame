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
			//	import('plugins.generic.hallOfFame.HallOfFameDAO');
			//	$hallOfFameDao = new HallOfFameDAO();
			//	DAORegistry::registerDAO('HallOfFameDAO', $hallOfFameDao);
				HookRegistry::register('LoadHandler', array($this, 'handleLoadRequest'));
			}
			return true;
		}
		return false;
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

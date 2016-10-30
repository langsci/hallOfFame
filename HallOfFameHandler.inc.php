<?php

/**
 * @file plugins/generic/hallOfFame/HallOfFameHandler.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HallOfFameHandler
 */

import('classes.handler.Handler');
import('plugins.generic.hallOfFame.HallOfFameDAO');
import('plugins.generic.hallOfFame.LangsciCommonDAO');
import('classes.monograph.MonographDAO');
import('classes.monograph.PublishedMonographDAO');
import('classes.press.SeriesDAO');

include('LangsciCommonFunctions.inc.php');

class HallOfFameHandler extends Handler {

	private $prizes = array('gold','silver','bronze','series','recent');

	function HallOfFameHandler() {
		parent::Handler();
	}

	function viewHallOfFame($args, $request) {

		$press = $request->getPress();
		$context = $request->getContext();
		$contextId = $context->getId();
		$hallOfFameDAO = new HallOfFameDAO;
		$langsciCommonDAO = new LangsciCommonDAO;
		$userDao = DAORegistry::getDAO('UserDAO');		
		
		// get setting parameters
		$plugin = PluginRegistry::getPlugin('generic', HALLOFFAME_PLUGIN_NAME);
		$settingPath = $plugin->getSetting($contextId,'langsci_hallOfFame_path');
		$settingUserGroups = $plugin->getSetting($contextId,'langsci_hallOfFame_userGroups');
		$settingUnifiedStyleSheetForLinguistics = $plugin->getSetting($contextId,'langsci_hallOfFame_unifiedStyleSheetForLinguistics');
		$settingLinksToPublicProfile = $plugin->getSetting($contextId,'langsci_hallOfFame_linksToPublicProfile');
		$settingStartCounting = $plugin->getSetting($contextId,'langsci_hallOfFame_startCounting');
		$settingRecency = $plugin->getSetting($contextId,'langsci_hallOfFame_recentDate');
		$settingMinNumberOfSeries = $plugin->getSetting($contextId,'langsci_hallOfFame_minNumberOfSeries');
		$settingPercentileRanks = $plugin->getSetting($contextId,'langsci_hallOfFame_percentileRanks');
		$settingMedalCount = $plugin->getSetting($contextId,'langsci_hallOfFame_medalCount');
		$settingIncludeCommentators = $plugin->getSetting($contextId,'langsci_hallOfFame_includeCommentators');

		// check and transform setting parameters
		$userGroupsArray = explode(",",$settingUserGroups);
		if ($settingUserGroups=="") {
			$userGroupsArray = array();
		}
		$settingPercentileRanksArray = explode(",",$settingPercentileRanks);
		if ($settingPercentileRanks=="") {
			$settingPercentileRanksArray = array();
		}
		// empty string or no number: all users will be display in the medal count
		if (!ctype_digit($settingMedalCount)) {
			$settingMedalCount='';
		}
		$recencyDate=null;
		if (ctype_digit($settingRecency)) {
			$recencyDate = new DateTime();
			$recencyDate->sub(new DateInterval('P'.$settingRecency.'M'));
		}

		// get data for the hall of fame
		$userGroups = array();
		$medalCount = array();
		$userGroupNames = array();
		$maxNameLength=0;
		for ($i=0; $i<sizeof($userGroupsArray); $i++) {

			// get user group
			$userGroupName = trim($userGroupsArray[$i]);
			$userGroupId = $langsciCommonDAO->getUserGroupIdByName($userGroupName,$contextId);

			if ($userGroupId) {

			$userGroups[$i]['userGroupId'] = $userGroupId;
			$userGroups[$i]['userGroupName'] = $userGroupName;

			$userGroupNames[$userGroupId] = $userGroupName;

			// get achievements of this user group
			$achievements = $hallOfFameDAO->getAchievements($userGroupId);

			// remove userIds who do not exists anymore
			$this->removeNonExistingUsers($achievements);

			// remove users who do not want to be listed in the hall of fame
			$this->removeAnonymousUsers($achievements);

			// remove submissions that where published before date x
			if (strlen($settingStartCounting)==8 && ctype_digit($settingStartCounting)) {
				$this->removeSubmissionsBeforeDate($achievements,$settingStartCounting);
			}

			// get rank percentile for all users 
			$rankPercentiles = $this->getRankPercentiles($achievements);

			// get number of achievements for each user
			$numberOfAchievements = $this->getNumberOfAchievements($achievements);

			// get users who have achievements in max number of series
			$maxSeriesUsers = array();
			if (ctype_digit($settingMinNumberOfSeries)) {
				$maxSeriesResults = $this->getMaxSeriesUsers($achievements);
				$userGroups[$i]['maxSeries'] = $maxSeriesResults['maxSeries']; 
				if ($userGroups[$i]['maxSeries']>=$settingMinNumberOfSeries) {
					$maxSeriesUsers = $maxSeriesResults['maxSeriesUsers'];
				}
			}
 
			// get users who have max achievements since $recencyDate
			$recentMaxAchievementUsers = array();
			if ($recencyDate) {
				$recentMaxAchievementResults = $this->getMaxAchievementUsers($achievements,$recencyDate->format('Ymd'));
				$userGroups[$i]['maxRecentAchievements']= $recentMaxAchievementResults['maxAchievements'];
				$recentMaxAchievementUsers = $recentMaxAchievementResults['maxAchievementUsers'];							
			}

			$userData = array();
			$userData['gold'] = array();
			$userData['silver'] = array();
			$userData['bronze'] = array();
			$keys = array_keys($achievements);
			// loop through all achievements
			for ($ii=0; $ii<sizeof($achievements); $ii++) { 

				$userId = $achievements[$keys[$ii]]['user_id'];
				$submissionId = $achievements[$keys[$ii]]['submission_id'];
				$user = $userDao->getById($userId);

				$numberOfAchievementsUser = $numberOfAchievements[$userId];
				$rankPercentile = round($rankPercentiles[$numberOfAchievementsUser],1);
				// reserve spance for the name: look for the longest name or 20
				$maxNameLength = max($maxNameLength,strlen($user->getFirstName(). " " . $user->getLastName()));
				if ($maxNameLength>20) {
					$maxNameLength = 20;
				}

				// add the link to the profile

				$linkToProfile = false;

				if ($langsciCommonDAO->existsTable('langsci_website_settings') &&
					$langsciCommonDAO->getUserSetting($userId,'publicProfile')=='true' &&
					$settingLinksToPublicProfile) {
					$publicProfilesPlugin = PluginRegistry::getPlugin('generic','publicprofilesplugin');
					if ($publicProfilesPlugin) {
						$pathPublicProfiles = explode("/", $publicProfilesPlugin->getSetting($contextId, 'langsci_publicProfiles_path'));
						$numberOfElementsInPath = sizeof($pathPublicProfiles);
						if ($numberOfElementsInPath==1) {
							$linkToProfile = $request->url(null,$pathPublicProfiles[0],$userId);
						} else if ($numberOfElementsInPath==2) {
							$linkToProfile = $request->url(null,$pathPublicProfiles[0],$pathPublicProfiles[1],$userId);
						} else if ($numberOfElementsInPath>2) {
							$tail="";
							for ($iii=2; $iii<$numberOfElementsInPath;$iii++) {
								$tail = $tail."/".$pathPublicProfiles[$iii];
							}
							$tail=$tail."/".$userId;
							$linkToProfile = $request->url(null,$pathPublicProfiles[0],$pathPublicProfiles[1]).$tail;		
						}
					}
				}

				// initialize medal count for each user with medals
				if (!strcmp($settingMedalCount,'0')==0 && !isset($medalCount[$userId])) {
					$this->initializeMedalCount($medalCount,$userId,$user,$linkToProfile);
				}

				// get medal for this user in this user group
				$medal = 'bronze';
				if ($rankPercentile<=$settingPercentileRanksArray[0]) {
					$medal = 'gold';
				} else if ($rankPercentile<=$settingPercentileRanksArray[1]) {
					$medal = 'silver';
				}
				if (!strcmp($settingMedalCount,'0')==0) {
					$medalCount[$userId]['type'][$medal][$userGroupId]=true;
				}

				// get user data
				$userData[$medal]['user'][$userId]['rankPercentile'] = 100-round($rankPercentile,0);
				$userData[$medal]['user'][$userId]['userId'] = $userId;
				$userData[$medal]['user'][$userId]['fullName'] = $user->getFirstName(). " " . $user->getLastName();
				$userData[$medal]['user'][$userId]['lastName'] = $user->getLastName();
				$userData[$medal]['user'][$userId]['linkToProfile'] = $linkToProfile;

				// get submission data
				$userData[$medal]['user'][$userId]['submissionId'] = $submissionId;
				if (isset($userData[$medal]['user'][$userId]['numberOfSubmissions'])) {
					$userData[$medal]['user'][$userId]['numberOfSubmissions']++;
				} else {
					$userData[$medal]['user'][$userId]['numberOfSubmissions']=1;
				}
				if ($settingUnifiedStyleSheetForLinguistics) {   
					$userData[$medal]['user'][$userId]['submissions'][$submissionId]['name'] =
						getBiblioLinguistStyle($submissionId);
				} else {
					$userData[$medal]['user'][$userId]['submissions'][$submissionId]['name'] =
						getSubmissionPresentationString($submissionId);
				}

				$userData[$medal]['user'][$userId]['submissions'][$submissionId]['path'] =
						$request->url(null,'catalog','book',$submissionId);

				// get users with a series star
				$userData[$medal]['user'][$userId]['maxSeriesUser'] = false;
				if (in_array($userId,$maxSeriesUsers)) {
					$userData[$medal]['user'][$userId]['maxSeriesUser'] = true;
					if (!strcmp($settingMedalCount,'0')==0) {
						$medalCount[$userId]['type']['series'][$userGroupId] = true;
					}
				}

				// get users with a recent star
				if (in_array($userId,$recentMaxAchievementUsers)) {
					$userData[$medal]['user'][$userId]['recentMaxAchievementUser'] = true;
					if (!strcmp($settingMedalCount,'0')==0) {
						$medalCount[$userId]['type']['recent'][$userGroupId]=true;
					}
				}
			}

			// get number of prizes for each user (sum up over user groups)
			if (!strcmp($settingMedalCount,'0')==0) {
				$userIds = array_keys($medalCount);
				for ($ii=0; $ii<sizeof($medalCount); $ii++) {
					for ($iii=0; $iii<sizeof($this->prizes); $iii++) {
						if (!empty($medalCount[$userIds[$ii]]['type'][$this->prizes[$iii]][$userGroupId])) {
							$medalCount[$userIds[$ii]]['numberOf'.$this->prizes[$iii]]++;						
						}
					}
				}
			}

			if (!empty($userData['gold'])) usort($userData['gold']['user'],'sort_users');
			if (!empty($userData['silver'])) usort($userData['silver']['user'],'sort_users');
			if (!empty($userData['bronze'])) usort($userData['bronze']['user'],'sort_users');

			$userGroups[$i]['userData'] = $userData;
			$userGroups[$i]['maxAchievements'] = max($numberOfAchievements);

			} // end if ($userGroupId)

		} // end for ($i=0; $i<sizeof($userGroupsArray); $i++)

		if (!strcmp($settingMedalCount,'0')==0) {

			uasort($medalCount,'sort_for_medal_count');
			// only display a certain number of users in the medal count?
			if (!strcmp($settingMedalCount,'')==0) {
				$keys = array_keys($medalCount);
				$end = sizeof($medalCount);
				for ($i=$settingMedalCount; $i<$end; $i++) {
					unset($medalCount[$keys[$i]]);
				}
			}
			// get medal count ranks
			$this->getMedalCountRanks($medalCount);
		}

		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request); // important for getting the correct menu
		$templateMgr->assign('pageTitle','plugins.generic.hallOfFame.title');
		$templateMgr->assign('userGroups',$userGroups);
		$templateMgr->assign('medalCount',$medalCount);
		$templateMgr->assign('settingMedalCount',$settingMedalCount);
		$templateMgr->assign('userGroups',$userGroups);
		$templateMgr->assign('maxNameLength',$maxNameLength);
		$templateMgr->assign('maxPrizes',$this->getMaxPrizes($medalCount));
		$templateMgr->assign('settingRecency',$settingRecency);
		$templateMgr->assign('percentileRankGold',$settingPercentileRanksArray[0]);
		$templateMgr->assign('percentileRankSilver',$settingPercentileRanksArray[1]);
		$templateMgr->assign('userGroupNames',$userGroupNames);
		$templateMgr->assign('baseUrl',$request->getBaseUrl());	
		$templateMgr->assign('imageDirectory','plugins/generic/hallOfFame/img');
		
		$hallOfFamePlugin = PluginRegistry::getPlugin('generic', HALLOFFAME_PLUGIN_NAME);
		$templateMgr->display($hallOfFamePlugin->getTemplatePath()."hallOfFame.tpl");
	}

	function getMaxPrizes(&$medalCount) {
		$maxPrizes = 0;
		$keys = array_keys($medalCount);
		for ($i=0; $i<sizeof($medalCount); $i++) {
			$numberOfPrizes = 0;
			for ($ii=0; $ii<sizeof($this->prizes); $ii++) {
				$numberOfPrizes = $numberOfPrizes + $medalCount[$keys[$i]]['numberOf'.$this->prizes[$ii]];
			}
			$maxPrizes = max($maxPrizes,$numberOfPrizes);
		}
		return $maxPrizes;
	}

	function initializeMedalCount(&$medalCount, $userId, $user, $linkToProfile) {
		for ($i=0; $i<sizeof($this->prizes); $i++) {
			$medalCount[$userId]['numberOf'.$this->prizes[$i]] = 0;
			$medalCount[$userId]['type'][$this->prizes[$i]] = array();
		}
		$medalCount[$userId]['name'] = $user->getFirstName(). " " . $user->getLastName();
		$medalCount[$userId]['linkToProfile'] = $linkToProfile;
	}

	function removeSubmissionsBeforeDate(&$achievements,$cutoffDate) {

		if ($achievements) {
			$end = sizeof($achievements);
			$keys = array_keys($achievements);
			for ($i=0; $i<$end; $i++) {
				$submissionId = $achievements[$keys[$i]]['submission_id'];
				// to do strcmp
				$publicationDate = getPublicationDate($submissionId);
				if (!$publicationDate || strcmp($publicationDate,$cutoffDate)<0) {
					unset($achievements[$keys[$i]]);
				}
			} 
		}
	}

	// get the users with maximal achievements before date "date"
	function getMaxAchievementUsers($achievements,$date) {

		$results = array();
		$results['maxAchievements'] = 0;
		$results['maxAchievementUsers'] = array();
		if (!$achievements) {
			return $results;
		}

		// remove outdated achievements
		$this->removeSubmissionsBeforeDate($achievements,$date);
		// count achievements
		$keys = array_keys($achievements);
		$numberOfAchievements = array();
		for ($i=0; $i<sizeof($achievements); $i++) {
			$userId = $achievements[$keys[$i]]['user_id'];
			$numberOfAchievements[$userId]=0;
		}
		for ($i=0; $i<sizeof($achievements); $i++) {
			$userId = $achievements[$keys[$i]]['user_id'];
			$submissionId = $achievements[$keys[$i]]['submission_id'];
			$publicationDate = getPublicationDate($submissionId);
			if ($publicationDate && strcmp($publicationDate,$date)>=0) {
				$numberOfAchievements[$userId]++;
			}
		}
		if (empty($numberOfAchievements)) return $results;
		
		// get users
 		$maxAchievements = max($numberOfAchievements);
		$maxAchievementUsers = array();
		$keys = array_keys($numberOfAchievements);
		for ($i=0; $i<sizeof($numberOfAchievements); $i++) {
			if ($numberOfAchievements[$keys[$i]]==$maxAchievements) {
				$maxAchievementUsers[] = $keys[$i];
			}
		}
		$results = array();
		$results['maxAchievements'] = $maxAchievements;
		$results['maxAchievementUsers'] = $maxAchievementUsers;
		return $results;
	}

	// remove users who do not exist anymore
	function removeNonExistingUsers(&$achievements) {

		if ($achievements) {
			$userDao = DAORegistry::getDAO('UserDAO');
			$keys = array_keys($achievements);
			$start = sizeof($achievements)-1;
			for ($i=$start; $i>=0; $i--) { 
				$userId = $achievements[$keys[$i]]['user_id'];
				if (!$userDao->getById($userId)) {
					unset($achievements[$keys[$i]]);
				}
			}
		}
	}

	// remove users that do not want to be included in the hall of fame
	function removeAnonymousUsers(&$achievements) {

		if ($achievements) {
			$langsciCommonDAO = new LangsciCommonDAO;
			$existsLangSciSettings = $langsciCommonDAO->existsTable("langsci_website_settings");
			if ($existsLangSciSettings) {
				$keys = array_keys($achievements);
				$start = sizeof($achievements)-1;
				for ($i=$start; $i>=0; $i--) { 
					$userId = $achievements[$keys[$i]]['user_id'];
					if (!($langsciCommonDAO->getUserSetting($userId,"HallOfFame")=='true')) {
						unset($achievements[$keys[$i]]);
					}
				}
			}
		}
	}

	// return the maximum number of series that users have worked for
	// return users who worked on the maximal number of series
	function getMaxSeriesUsers($achievements) {

		// if $achievements is empty return max value = 0 and empty user array
		$results = array();
		$results['maxSeriesUsers'] = array();
		$results['maxSeries'] = 0;
		if (!$achievements) {
			return $results;
		}

		// for each user: for which series did s/he work
		$seriesOfUsers = array();
		foreach ($achievements as $key => $achievement)	{	
			$seriesIdOfAchievement = $this->getSeriesId($achievement['submission_id']);
			if ($seriesIdOfAchievement) {
				$seriesOfUsers[$achievement['user_id']][$seriesIdOfAchievement] = true;
			}			
		}	
	
		// for each user: for how many series did s/he work
		$numberOfSeriesOfUsers = array();
		foreach ($seriesOfUsers as $userId => $achievement)	{	
			$numberOfSeriesOfUsers[$userId] = sizeof($seriesOfUsers[$userId]);
		}

		// maximum number of series by one user
		if (empty($numberOfSeriesOfUsers)) {
			return results;
		}
		$maxSeries = max($numberOfSeriesOfUsers);
	
		// users with the maximum number of series
		$maxUsers = array();
		foreach ($numberOfSeriesOfUsers as $userId => $value)	{
			if ($value==$maxSeries) {
				$maxUsers[] = $userId;
			}
		}
		
		$results = array();
		$results['maxSeriesUsers'] = $maxUsers;
		$results['maxSeries'] = $maxSeries;
		return $results;
	}

	function getSeriesId($submissionId) {
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submission = $submissionDao->getById($submissionId);
		if ($submission) {
			return $submission->getSeriesId();
		}
		return null;
	}

	// get the rank percentiles for different number of submissions
	function getRankPercentiles($achievements) {

		if (!$achievements) {
			return array();
		}

		$numberOfSubmissionsPerUser = array();
		$keys = array_keys($achievements);
		// count the number of achievements of each user
		for ($i=0; $i<sizeof($achievements); $i++) { 
			$userId = $achievements[$keys[$i]]['user_id'];
			$numberOfSubmissionsPerUser[$userId]['numberOfSubmissions']=0;
		}
		for ($i=0; $i<sizeof($achievements); $i++) { 
			$userId = $achievements[$keys[$i]]['user_id'];
			$numberOfSubmissionsPerUser[$userId]['userId'] = $userId;
			$numberOfSubmissionsPerUser[$userId]['numberOfSubmissions']++;
		}
	
		usort($numberOfSubmissionsPerUser,'sort_by_number_of_submissions');
		$numberOfUsers = sizeof($numberOfSubmissionsPerUser);

		// get data on frequencies
		$frequencyOfNumberOfSubmission = array();
		for ($i=0; $i<$numberOfUsers; $i++) { 
			$freq = $numberOfSubmissionsPerUser[$i]['numberOfSubmissions'];
			$frequencyOfNumberOfSubmission[$freq]['count']=0;	
			$frequencyOfNumberOfSubmission[$freq]['sum']=0;
		}
		for ($i=0; $i<$numberOfUsers; $i++) { 
			$userId = $numberOfSubmissionsPerUser[$i]['userId'];
			$freq = $numberOfSubmissionsPerUser[$i]['numberOfSubmissions'];
			$frequencyOfNumberOfSubmission[$freq]['value'] = $freq;
			$frequencyOfNumberOfSubmission[$freq]['count']++;	
			$numberOfSubmissionsPerUser[$userId]['rank'] = $i + 1;		
			$frequencyOfNumberOfSubmission[$freq]['sum'] += $numberOfSubmissionsPerUser[$userId]['rank'] ;
		}

		// get more data on frequencies
		$values = array_keys($frequencyOfNumberOfSubmission);
		for ($i=0; $i<sizeof($frequencyOfNumberOfSubmission); $i++) {
			$numberOfSubmissions=$values[$i];
			$frequencyOfNumberOfSubmission[$numberOfSubmissions]['mean'] =
				$frequencyOfNumberOfSubmission[$numberOfSubmissions]['sum'] /
				$frequencyOfNumberOfSubmission[$numberOfSubmissions]['count']; 
			$frequencyOfNumberOfSubmission[$numberOfSubmissions]['rankPercentile'] =
				$frequencyOfNumberOfSubmission[$numberOfSubmissions]['mean']/ $numberOfUsers*100; 
		}	

		// get rank percentiles
		$rankPercentiles = array();
		$values = array_keys($frequencyOfNumberOfSubmission);
		for ($i=0; $i<sizeof($frequencyOfNumberOfSubmission); $i++) {
			$numberOfSubmissions=$values[$i];
			$rankPercentiles[$numberOfSubmissions] = $frequencyOfNumberOfSubmission[$numberOfSubmissions]['rankPercentile'];
		}	

		return $rankPercentiles;
	}

	function getNumberOfAchievements($achievements) {
		
		if (!$achievements) {
			return array();
		}

		// initalize array
		$numberOfAchievements = array();
		foreach ($achievements as $key => $achievement) {
			$numberOfAchievements[$achievement['user_id']]=0;
		}
		// count achievements for each users
		foreach ($achievements as $key => $achievement) {
			$numberOfAchievements[$achievement['user_id']]++;
		}
		
		return $numberOfAchievements;
	}

	function getMedalCountRanks(&$medalCount) {
		$keys = array_keys($medalCount);
		$rank = 0;
		$rankSave = 0;
		
		for ($i=0; $i<sizeof($medalCount); $i++) {

			$achievements2 = array($medalCount[$keys[$i]]['numberOfgold'],
								   $medalCount[$keys[$i]]['numberOfsilver'],
								   $medalCount[$keys[$i]]['numberOfbronze'],
								   $medalCount[$keys[$i]]['numberOfseries'],
								   $medalCount[$keys[$i]]['numberOfrecent']
							);

			if ($i==0) {
				$better = true;
			} else {
				$better = false;
				for ($ii=0; $ii<sizeof($achievements1); $ii++) {
					if ($achievements1[$ii]>$achievements2[$ii]) {
						$better = true;
						$rank = $rank + $rankSave;
						$rankSave = 0;
						break;
					}
				}
			}

			if ($better) {
				$rank++;
			} else {
				$rankSave++;
			}
			$medalCount[$keys[$i]]['rank'] = $rank;
			$achievements1 = $achievements2;
		}
	}

}

// sort functions

function sort_users($a, $b) {

	if ($a['numberOfSubmissions']==$b['numberOfSubmissions']) {
		return  strcasecmp($a['lastName'],$b['lastName']);
	} else {
		return  $b['numberOfSubmissions'] - $a['numberOfSubmissions'];
	}
}

function sort_by_number_of_submissions($a, $b) {
	return  $b['numberOfSubmissions'] - $a['numberOfSubmissions'];
}

function sort_for_medal_count($a, $b) {

	if ($a['numberOfgold']!= $b['numberOfgold']) {
		return $b['numberOfgold']-$a['numberOfgold'];
	} elseif ($a['numberOfsilver']!= $b['numberOfsilver']) {
		return $b['numberOfsilver']-$a['numberOfsilver'];
	} elseif ($a['numberOfbronze']!= $b['numberOfbronze']) {
		return $b['numberOfbronze']-$a['numberOfbronze'];
	} elseif ($a['numberOfseries']!= $b['numberOfseries']) {
		return $b['numberOfseries']-$a['numberOfseries'];
	} elseif ($a['numberOfrecent']!= $b['numberOfrecent']) {
		return $b['numberOfrecent']-$a['numberOfrecent'];
	} else {
		return 0;
	}
}

?>

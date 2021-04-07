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
//import('plugins.generic.hallOfFame.LangsciCommonDAO');
//import('classes.monograph.MonographDAO');
//import('classes.monograph.PublishedMonographDAO');
//import('classes.press.SeriesDAO');

//include('LangsciCommonFunctions.inc.php');

class HallOfFameHandler extends Handler {

	private $_plugin;
	private $prizes = array('gold','silver','bronze','series','recent');
	
	/**
	 * Constructor
	 */	
	function __construct() {
		$this->_plugin = PluginRegistry::getPlugin('generic', HALLOFFAME_PLUGIN_NAME);
		parent::__construct();
	}
	
	function getDataForUsergroup($userGroupName,&$request) {
		
		$context = $request->getContext();
		$contextId = $context->getId();
		$hallOfFameDAO = new HallOfFameDAO();
		$userDao = DAORegistry::getDAO('UserDAO');	
		$userGroupId = $hallOfFameDAO->getUserGroupIdByName($userGroupName,$contextId);
		$userGroupInfo = array();
		
		if ($userGroupId) {		
			
			$settingStartCounting = $this->_plugin->getSetting($contextId,'langsci_hallOfFame_startCounting');
			$settingMinNumberOfSeries = $this->_plugin->getSetting($contextId,'langsci_hallOfFame_minNumberOfSeries');
			$settingRecency = $this->_plugin->getSetting($contextId,'langsci_hallOfFame_recentDate');
			$settingMedalCount = $this->_plugin->getSetting($contextId,'langsci_hallOfFame_medalCount');
			$settingPercentileRanks = $this->_plugin->getSetting($contextId,'langsci_hallOfFame_percentileRanks');
	
	
			// check and transform setting parameters
			$settingPercentileRanksArray = explode(",",$settingPercentileRanks);
			if ($settingPercentileRanks=="") {
				$settingPercentileRanksArray = array();
			}
			
			$maxNameLength=0;			
			
			// get achievements of this user group (=array of user-submission-arrays)
			$achievements = $hallOfFameDAO->getAchievements($userGroupId);

			// remove userIds who do not exists anymore (nicht mehr nötig? merge scheint auch die assignments zu entfernen)
			$this->removeNonExistingUsers($achievements);

			// remove users who do not want to be listed in the hall of fame, xxx todo: Eingabe
			$this->removeAnonymousUsers($achievements);		
			
			// get publication dates
			$publicationDates = $hallOfFameDAO->getPublicationDates($contextId);
/*		
$myfile = 'test.txt';
$newContentCF5344 = print_r(sizeof($this->getNumberOfAchievements($achievements)), true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n noa : " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );*/	

			// remove submissions that where published before date x
			if (strlen($settingStartCounting)==8 && ctype_digit($settingStartCounting)) {					
				$this->removeSubmissionsBeforeDate($achievements,$publicationDates,$settingStartCounting);
			}	

			// get rank percentile for all users 
			$rankPercentiles = $this->getRankPercentiles($achievements);

			// get number of achievements for each user
			$numberOfAchievements = $this->getNumberOfAchievements($achievements);

			// get users who have achievements in max number of series
			if (ctype_digit($settingMinNumberOfSeries)) {
				$maxSeriesResults = $this->getMaxSeriesUsers($achievements);
				$userGroupInfo['maxSeries'] = $maxSeriesResults['maxSeries'];
				$userGroupInfo['maxSeriesUsers'] = array();
				if ($userGroupInfo['maxSeries']>=$settingMinNumberOfSeries) {
					$userGroupInfo['maxSeriesUsers'] = $maxSeriesResults['maxSeriesUsers'];
				}
			}

			// get users who have max achievements since $recencyDate
			$recencyDate=null;
			if (ctype_digit($settingRecency)) {
				$recencyDate = new DateTime();
				$recencyDate->sub(new DateInterval('P'.$settingRecency.'M'));
			}
			if ($recencyDate) {
				$recencyAchievements = $achievements;								
				$this->removeSubmissionsBeforeDate($recencyAchievements,$publicationDates,$recencyDate->format('Ymd'));				
				$recentMaxAchievementResults = $this->getMaxAchievementUsers($recencyAchievements);
				$userGroupInfo['maxRecentAchievements']= $recentMaxAchievementResults['maxAchievements'];
				$userGroupInfo['maxRecentAchievementUsers'] = $recentMaxAchievementResults['maxAchievementUsers'];										
			}

			$userData = array();
			$userData['gold'] = array();
			$userData['silver'] = array();
			$userData['bronze'] = array();
			$keys = array_keys($achievements);	
				
			// loop through all achievements
			for ($ii=0; $ii<sizeof($achievements); $ii++) {
				//if ($ii==2) {break;}

				$userId = $achievements[$keys[$ii]]['user_id'];
				$submissionId = $achievements[$keys[$ii]]['submission_id'];
				$user = $userDao->getById($userId);
					
				$numberOfAchievementsUser = $numberOfAchievements[$userId];
				$rankPercentile = round($rankPercentiles[$numberOfAchievementsUser],1);
				// reserve spance for the name: look for the longest name or 20
				$maxNameLength = max($maxNameLength,strlen($user->getGivenName('en_US'). " " . $user->getFamilyName('en_US')));
				if ($maxNameLength>17) {  // orig: 20
					$maxNameLength = 17;
				}
				
				// add the link to the profile 
				$linkToProfile = false;

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
				$userData[$medal]['user'][$userId]['fullName'] = $user->getGivenName('en_US'). " " . $user->getFamilyName('en_US');
				$userData[$medal]['user'][$userId]['lastName'] = $user->getFamilyName('en_US');
				$userData[$medal]['user'][$userId]['linkToProfile'] = $linkToProfile;

				// get submission data
				$userData[$medal]['user'][$userId]['submissionId'] = $submissionId;
				if (isset($userData[$medal]['user'][$userId]['numberOfSubmissions'])) {
					$userData[$medal]['user'][$userId]['numberOfSubmissions']++;
				} else {
					$userData[$medal]['user'][$userId]['numberOfSubmissions']=1;
				}
				if ($settingUnifiedStyleSheetForLinguistics) {   
					$userData[$medal]['user'][$userId]['submissions'][$submissionId]['name'] = "getBiblioLinguistStyle";
						//getBiblioLinguistStyle($submissionId);
				} else {
					$userData[$medal]['user'][$userId]['submissions'][$submissionId]['name'] = "getSubmissionPresentationString";
						//getSubmissionPresentationString($submissionId);
				}

				$userData[$medal]['user'][$userId]['submissions'][$submissionId]['path'] =
					$request->url(null,'catalog','book',$submissionId);
	
				// get users with a series star
				$userData[$medal]['user'][$userId]['maxSeriesUser'] = false;
				if (in_array($userId,$userGroupInfo['maxSeriesUsers'])) {
					$userData[$medal]['user'][$userId]['maxSeriesUser'] = true;
					if (!strcmp($settingMedalCount,'0')==0) {
						$medalCount[$userId]['type']['series'][$userGroupId] = true;
					}
				}	
					
				// get users with a recent star
				if (in_array($userId,$userGroupInfo['maxRecentAchievementUsers'])) {
					$userData[$medal]['user'][$userId]['recentMaxAchievementUser'] = true;
					if (!strcmp($settingMedalCount,'0')==0) {
						$medalCount[$userId]['type']['recent'][$userGroupId]=true;
					}
				}	
			}	// end loop through all achievements
			
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

			$userGroupInfo['userData'] = $userData;
			$userGroupInfo['maxAchievements'] = max($numberOfAchievements);
			$userGroupInfo['medalCount'] = $medalCount;
			$userGroupInfo['maxNameLength'] = $maxNameLength;
			
			return $userGroupInfo;
		}
		return null;
	}
	
	function halloffame($args, $request) {
	
		$reload = false;
		$fileProofreader = 'plugins/generic/hallOfFame/json/proofreader.json';
		$fileTypesetter = 'plugins/generic/hallOfFame/json/typesetter.json';		
		
		$dataProofreader = array();
		$dataTypesetter = array();		
		if ($reload) {
			$dataProofreader = $this->getDataForUsergroup("Proofreader",$request);		
			$dataTypesetter = $this->getDataForUsergroup("Typesetter",$request);
			file_put_contents($fileProofreader, json_encode($dataProofreader));
			file_put_contents($fileTypesetter, json_encode($dataTypesetter));
		} else {
			$dataProofreader = json_decode(file_get_contents($fileProofreader),TRUE);
			$dataTypesetter = json_decode(file_get_contents($fileTypesetter),TRUE);
		}

		$maxNameLength = max($dataProofreader['maxNameLength'],$dataTypesetter['maxNameLength']);
		
		$medalCount = $dataProofreader['medalCount'];
		foreach($dataTypesetter['medalCount'] as $userId => $data) {
			if (!$medalCount[$userId]) {
				$medalCount[$userId] = $data;
			} else {
				$medalCount[$userId]['numberOfgold'] += $data['numberOfgold'];
				$medalCount[$userId]['numberOfsilver'] += $data['numberOfsilver'];
				$medalCount[$userId]['numberOfbronze'] += $data['numberOfbronze'];
				$medalCount[$userId]['numberOfseries'] += $data['numberOfseries'];
				$medalCount[$userId]['numberOfrecent'] += $data['numberOfrecent'];
				$medalCount[$userId]['type']['gold']+=$data['type']['gold'];
				$medalCount[$userId]['type']['silver']+=$data['type']['silver'];
				$medalCount[$userId]['type']['bronze']+=$data['type']['bronze'];					
			}			
		}
		

		


		



		
		
		
	
	
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
		$this->setupTemplate($request); 
		
		$templateMgr->assign('pageTitle','plugins.generic.hallOfFame.title');
		//$templateMgr->assign('userGroups',$userGroups);
		$templateMgr->assign('medalCount',$medalCount);
		$templateMgr->assign('settingMedalCount',$settingMedalCount);
		$templateMgr->assign('maxNameLength',$maxNameLength);
		//$templateMgr->assign('maxPrizes',$this->getMaxPrizes($medalCount));
		//$templateMgr->assign('settingRecency',$settingRecency);
		//$templateMgr->assign('percentileRankGold',$settingPercentileRanksArray[0]);
		//$templateMgr->assign('percentileRankSilver',$settingPercentileRanksArray[1]);
		//$templateMgr->assign('userGroupNames',$userGroupNames);
		$templateMgr->assign('proofreader',$dataProofreader);
		$templateMgr->assign('typesetter',$dataTypesetter);
		$templateMgr->assign('baseUrl',$request->getBaseUrl());	
		$templateMgr->assign('imageDirectory','plugins/generic/hallOfFame/img');
		$templateMgr->display($this->_plugin->getTemplateResource('hallOfFame.tpl'));	
	
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
	
	function initializeMedalCount(&$medalCount, $userId, $user, $linkToProfile) {
		for ($i=0; $i<sizeof($this->prizes); $i++) {
			$medalCount[$userId]['numberOf'.$this->prizes[$i]] = 0;
			$medalCount[$userId]['type'][$this->prizes[$i]] = array();
		}
		$medalCount[$userId]['name'] = $user->getGivenName('en_US'). " " . $user->getFamilyName('en_US');
		$medalCount[$userId]['linkToProfile'] = $linkToProfile;
	}		
	
	// get the users with maximal achievements before date "date"
	function getMaxAchievementUsers($achievements) {

		$results = array();
		$results['maxAchievements'] = 0;
		$results['maxAchievementUsers'] = array();
		if (!$achievements) {
			return $results;
		}

		// count achievements
		$keys = array_keys($achievements);
		$numberOfAchievements = array();
		for ($i=0; $i<sizeof($achievements); $i++) {
			$userId = $achievements[$keys[$i]]['user_id'];
			$numberOfAchievements[$userId]=0;
		}
		for ($i=0; $i<sizeof($achievements); $i++) {
			$userId = $achievements[$keys[$i]]['user_id'];
			$numberOfAchievements[$userId]++;
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
	
	// remove users before date
	function removeSubmissionsBeforeDate(&$achievements,$publicationDates,$settingStartCounting) {
		if ($achievements) {
			$keys = array_keys($achievements);
			$end = sizeof($achievements);			
			for ($i=0; $i<$end; $i++) {				
				$submissionId = $achievements[$keys[$i]]['submission_id'];
				$publicationDate = str_replace("-","",$publicationDates[$submissionId]);
				if (!$publicationDate || strcmp($publicationDate,$settingStartCounting)<0) {
					unset($achievements[$keys[$i]]);
				}
			} 
		}
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
		
		$submission = Services::get('submission')->get($submissionId);
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
	
		// sort by number of submissions (sets key to 0, 1, 2, 3, ...)
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
			$frequencyOfNumberOfSubmission[$freq]['value'] = $freq;          // frequency value, e.g. worked on 22 submissions
			$frequencyOfNumberOfSubmission[$freq]['count']++;	             // how often does this frequency value occur
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

	// remove users who do not exist anymore (nicht mehr nötig? merge scheint auch die assignments zu entfernen)
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
			$hallOfFameDAO = new HallOfFameDAO;	
			$keys = array_keys($achievements);
			$start = sizeof($achievements)-1;
			for ($i=$start; $i>=0; $i--) { 
				$userId = $achievements[$keys[$i]]['user_id'];	
				if (!$hallOfFameDAO->getUserSetting($userId,"HallOfFame")=='true') {
					unset($achievements[$keys[$i]]);
				}
			}
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

<?php

/**
 * @file plugins/generic/hallOfFame/HallOfFameDAO.inc.php
 *
 * Copyright (c) 2015 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HallOfFameDAO
 *
 ALTER TABLE table_name
  RENAME TO new_table_name;
 
 */

class HallOfFameDAO extends DAO {

    function __construct() {
		parent::__construct();
	}

	function getUserGroupIdByName($user_group_name,$context_id) {

		$result = $this->retrieve(
			'SELECT b.user_group_id, s.setting_value FROM user_groups b LEFT JOIN user_group_settings s ON
			b.user_group_id=s.user_group_id WHERE s.setting_name="name" AND s.setting_value="'.$user_group_name.'"
			AND b.context_id='.$context_id
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['user_group_id'],'int');
		}	
	}

	function getPublicationDate($submission_id) {
		$result = $this->retrieve(
			'SELECT date_published from publications where submission_id = '.$submission_id
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['date_published'],'string');
		}			
	}

	function getPublicationDates($context_id) {

		$result = $this->retrieve(
			'select submission_id, date_published, status from publications 
			where submission_id in (select submission_id from submissions where context_id='.$context_id.'
			and status=3)'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$publicationDates = null;
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				//$tmp = $this->convertFromDB($row['user_id'],null);
				$publicationDates[$this->convertFromDB($row['submission_id'],null)] = $this->convertFromDB($row['date_published'],null);
				$result->MoveNext();
			}
			$result->Close();
			return $publicationDates;
		}
		$result->Close();
		return null;	
	}	
	
	function getPDFPublicationDates($context_id) {

		$result = $this->retrieve(
			'select pf.submission_id, pd.date from 
			 publication_dates as pd left join publication_formats as pf on pd.publication_format_id=pf.publication_format_id 
			 left join publication_format_settings as pfs on pfs.publication_format_id=pf.publication_format_id 
			 where pfs.setting_name="name" and left(pfs.setting_value,3)="PDF" and 
			 pf.submission_id in (select submission_id from publications where status=3) and 
			 pf.submission_id in (select submission_id from submissions where context_id='.$context_id.')'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$tmp = null;
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				//$tmp = $this->convertFromDB($row['user_id'],null);
				$tmp[$this->convertFromDB($row['submission_id'],null)] = $this->convertFromDB($row['date'],null);
				$result->MoveNext();
			}
			$result->Close();
			return $tmp;
		}
		$result->Close();
		return null;	
	}	
	
	function getPublicationId($submissionId) {
		
		$result = $this->retrieve('select publication_id from publications where submission_id='.$submissionId);
		
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['publication_id'],null);
		}		
	}

	// get all user-submission tuples for one user group, only get those submissions that are in the catalog (date_published not null)		
	function getAchievements($user_group_id) {

		//$result = $this->retrieve('select user_id,submission_id from stage_assignments where user_group_id='.$userGroup.' and submission_id IN (select submission_id from published_submissions WHERE date_published IS NOT NULL)');

		$result = $this->retrieve('select user_id, submission_id from stage_assignments where user_group_id='.$user_group_id.' and submission_id in (select submission_id from publications where status=3)');
//		$result = $this->retrieve('select user_id, submission_id from stage_assignments where user_group_id='.$user_group_id.' and submission_id in (25,216)');
		
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$i=0;
			$submissions = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$submissions[$i]['user_id'] = $this->convertFromDB($row['user_id'],null);
				$submissions[$i]['submission_id'] = $this->convertFromDB($row['submission_id'],null);
				$i++;
				$result->MoveNext();
			}
			$result->Close();
			return $submissions;
		}	
	}
	
	function getUserSetting($user_id,$setting_name) {
		$result = $this->retrieve(
			"SELECT setting_value FROM langsci_user_settings
				WHERE setting_name='".$setting_name."' AND user_id =" . $user_id			
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['setting_value'],null);
		}
	}	
	

}

?>

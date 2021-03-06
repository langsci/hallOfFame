
<?php

/**
 * @file plugins/generic/hallOfFame/LangsciCommonDAO.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LangsciCommonDAO
 *
 */

class LangsciCommonDAO extends DAO {
	/**
	 * Constructor
	 */
	function LangsciCommonDAO() {
		parent::DAO();
	}

	function existsTable($table) {
		$result = $this->retrieve(
			"SHOW TABLES LIKE '".$table."'"
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return false;
		} else {
			$result->Close();
			return true;
		}
	}

	function getUserSetting($user_id,$setting_name) {
		$result = $this->retrieve(
			"SELECT setting_value FROM langsci_website_settings
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

	function getContextBySubmissionId($submissionId) {

		$result = $this->retrieve(
			'select context_id from submissions where submission_id='.$submissionId);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['context_id']);
		}	

	}


}

?>


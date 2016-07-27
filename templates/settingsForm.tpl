{**
 * @file plugins/generic/hallOfFame/templates/settingsForm.tpl
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}

<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#hallOfFameSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="hallOfFameSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">

	{fbvFormArea id="hallOfFameSettingsForm" class="border" title="plugins.generic.hallOfFame.settings.title"}

		{** What user groups to include **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.userGroupsIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.userGroups"
						id="langsci_hallOfFame_userGroups" value=$langsci_hallOfFame_userGroups
						maxlength="200" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** path **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.pathIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.path"
						id="langsci_hallOfFame_path" value=$langsci_hallOfFame_path
						maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** date **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.startCountingIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.startCounting"
						id="langsci_hallOfFame_startCounting" value=$langsci_hallOfFame_startCounting
						maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** percentile ranks of medals **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.percentileRanksIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.percentileRanks"
						id="langsci_hallOfFame_percentileRanks" value=$langsci_hallOfFame_percentileRanks
						maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}


		{** number of people listed in the medal count (count=0 -> no tab **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.medalCountIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.medalCount"
						id="langsci_hallOfFame_medalCount" value=$langsci_hallOfFame_medalCount maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** recent date **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.recentDateIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.recentDate" id="langsci_hallOfFame_recentDate" value=$langsci_hallOfFame_recentDate maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** series star **}

		{fbvFormSection}
			<p class="pkp_help">{translate key="plugins.generic.hallOfFame.form.minNumberOfSeriesIntro"}</p>
			{fbvElement type="text" label="plugins.generic.hallOfFame.form.minNumberOfSeries" id="langsci_hallOfFame_minNumberOfSeries" value=$langsci_hallOfFame_minNumberOfSeries maxlength="40" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{** public profile **}

		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="langsci_hallOfFame_linksToPublicProfile" value="1" checked=$langsci_hallOfFame_linksToPublicProfile label="plugins.generic.hallOfFame.form.linksToPublicProfile"}
		{/fbvFormSection}

		{** unified style sheet **}

		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="langsci_hallOfFame_unifiedStyleSheetForLinguistics" value="1" checked=$langsci_hallOfFame_unifiedStyleSheetForLinguistics label="plugins.generic.hallOfFame.form.unifiedStyleSheetForLinguistics"}
		{/fbvFormSection}

		{** commentators **}

		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="langsci_hallOfFame_includeCommentators" value="1" checked=$langsci_hallOfFame_includeCommentators label="plugins.generic.hallOfFame.form.includeCommentators"}
		{/fbvFormSection}

		{fbvFormButtons submitText="common.save"}

	{/fbvFormArea}
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>


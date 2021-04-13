{**
 * @file plugins/generic/hallOfFame/templates/settingsForm.tpl
 *
 * Copyright (c) 2016-2021 Language Science Press
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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

		{fbvFormButtons submitText="common.save"}

	{/fbvFormArea}
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>


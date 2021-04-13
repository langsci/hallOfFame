{**
 * plugins/generic/hallOfFame/templates/hallOfFame.tpl
 *
 * Copyright (c) 2016-2021 Languages Sciene Press
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 *}
 
{include file="frontend/components/header.tpl" pageTitleTranslated="$title"}

<script>
	document.addEventListener("DOMContentLoaded", function() {
		document.getElementById("proofreader").style.display = "block";	
		document.getElementById("typesetter").style.display = "none";
		document.getElementById("medalcount").style.display = "none";
		document.getElementById("tab-proofreader").style.backgroundColor = "#eee";		
	});

	function openPage(pageName) {
	  var i, tabcontent;
	  tabcontent = document.getElementsByClassName("tabcontent");
	  for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	  }
	  document.getElementById(pageName).style.display = "block";	  
	  
	  tablink = document.getElementsByClassName("tablink");
	  for (i = 0; i < tablink.length; i++) {
		tablink[i].style.backgroundColor = "#fff";
	  }
	  var str1 = "tab-"; 
	  document.getElementById(str1.concat(pageName)).style.backgroundColor = "#eee";  
	}
</script>


<link rel="stylesheet" href="{$baseUrl}/plugins/generic/hallOfFame/css/hallOfFame.css" type="text/css" />

<div id="hallOfFame">

	<h2 class="title">{translate key="plugins.generic.hallOfFame.title"}</h2>

	<p class="intro">{translate key="plugins.generic.hallOfFame.intro"}</p>

	<button class="tablink" id="tab-proofreader" onclick="openPage('proofreader')">{$proofreader.userGroupName}</button>
	<button class="tablink" id="tab-typesetter" onclick="openPage('typesetter')">{$typesetter.userGroupName}</button>
	<button class="tablink" id="tab-medalcount" onclick="openPage('medalcount')">Medal count</button>

	<div id="proofreader" class="tabcontent">
	
		{foreach from=$proofreader.userData item=medalUserData}
		
			{counter print=false assign=medal}	
			{if $medal==1 and $medalUserData.user|@count>0}
				<h2 class="goldTitle tooltip">
					<span class="headerText">Gold {$proofreader.userGroupName}s</span>
					<span class="tooltip">Gold {$proofreader.userGroupName} have statistically worked on more books than {math equation="x-y" x=100 y=$percentileRankGold} percent of all {$proofreader.userGroupName}s.</span>
				</h2>
			{/if}
			{if $medal==2 and $medalUserData.user|@count>0}
				<h2 class="silverTitle tooltip">
					<span class="headerText">Silver {$proofreader.userGroupName}s</span>
					<span class="tooltip">Gold {$proofreader.userGroupName} have statistically worked on more books than {math equation="x-y" x=100 y=$percentileRankSilver} percent of all {$proofreader.userGroupName}s.</span>					
				</h2>
			{/if}
			{if $medal==3 and $medalUserData.user|@count>0}
				<h2 class="bronzeTitle tooltip">
					<span class="headerText">Bronze {$proofreader.userGroupName}s</span>
					<span class="tooltip">Bronze {$proofreader.userGroupName}s have worked on at least one book.					
				</h2>
			{/if}			
		
			{foreach from=$medalUserData item=users}
			{foreach from=$users item=user}
				{assign var="barWidth" value=$user.numberOfSubmissions*300/$proofreader.maxAchievements}
				<button class="accordion">
					<div class="triangle"></div>					
					<div class="contributor-name" style="width:{math equation="x*y+z" x=$maxNameLength y=12 z=0}px" >{$user.fullName}</div>
											
					<div class="star tooltip">
					{if $user.maxSeriesUser}
						<img src='{$baseUrl}/{$imageDirectory}/series.png'>
						<span class="tooltip">Most versatile: {$user.fullName|strip_unsafe_html} has worked for {$proofreader.maxSeries} different series as {$proofreader.userGroupName|lower}.</span>
					{/if}
					</div>
					<div class="star tooltip">
					{if $user.recentMaxAchievementUser}
						<img src='{$baseUrl}/{$imageDirectory}/recent.png'>
						<span class="tooltip">Most active current {$proofreader.userGroupName|lower}: In the last {$settingRecency} months, {$user.fullName|strip_unsafe_html} has worked on {$proofreader.maxRecentAchievements} book{if $proofreader.maxRecentAchievements>1}s{/if} as {$proofreader.userGroupName|lower}.</span>						
					{/if}
					</div>
					<div class="star tooltip">
					{if !$user.maxSeriesUser}
						<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
					{/if}					
					{if !$user.recentMaxAchievementUser}
						<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
					{/if}			
					</div>
									
					<div class="colorBar tooltip" style="width:{$barWidth}px;">{if $barWidth>=33}{$user.numberOfSubmissions}/{$user.rankPercentile}{else}&nbsp{/if}
						<span class="tooltip">{$user.fullName|strip_unsafe_html} has worked on {$user.numberOfSubmissions} book{if $user.numberOfSubmissions>1}s{/if} and is thus statistically more active than {$user.rankPercentile}% of the {$proofreader.userGroupName|lower}s.</span>
					</div>		

					{if $barWidth<33}
						<div class="smallBarCaption">
							&nbsp&nbsp{$user.numberOfSubmissions}/{$user.rankPercentile}
						</div>
					{/if}
				</button>				
								
				<div class="panel">
					<ol>					
						{foreach from=$user.submissions item=submission}
							<li class="bibList">
								{$submission.name}
								<a class="linkToBookPage" href="{$submission.path}">&rarr;</a>
							</li>
						{/foreach}
					</ol>
				</div>			
			{/foreach}
			{/foreach}
		{/foreach}
	</div>
	
	<div id="typesetter" class="tabcontent">
	
		{foreach from=$typesetter.userData item=medalUserData}
		
			{counter print=false assign=medal}	
			{if $medal==4 and $medalUserData.user|@count>0}
				<h2 class="goldTitle tooltip">
					<span class="headerText">Gold {$typesetter.userGroupName}s</span>
					<span class="tooltip">Gold {$typesetter.userGroupName}s have statistically worked on more books than {math equation="x-y" x=100 y=$percentileRankGold} percent of all {$typesetter.userGroupName|lower}s.</span>
				</h2>
			{/if}
			{if $medal==5 and $medalUserData.user|@count>0}
				<h2 class="silverTitle tooltip">
					<span class="headerText">Silver {$typesetter.userGroupName}s</span>
					<span class="tooltip">Silver {$typesetter.userGroupName}s have statistically worked on more books than {math equation="x-y" x=100 y=$percentileRankSilver} percent of all {$typesetter.userGroupName|lower}s.</span>					
				</h2>
			{/if}
			{if $medal==6 and $medalUserData.user|@count>0}
				<h2 class="bronzeTitle tooltip">
					<span class="headerText">Bronze {$typesetter.userGroupName}s</span>
					<span class="tooltip">Bronze {$typesetter.userGroupName}s have worked on at least one book.
				</h2>
			{/if}			
		
			{foreach from=$medalUserData item=users}
			{foreach from=$users item=user}
				{assign var="barWidth" value=$user.numberOfSubmissions*300/$typesetter.maxAchievements}
				<button class="accordion">
					<div class="triangle"></div>					
					<div class="contributor-name" style="width:{math equation="x*y+z" x=$maxNameLength y=12 z=0}px" >{$user.fullName}</div>
				
					<div class="star tooltip">					
					{if $user.maxSeriesUser}
						<img src='{$baseUrl}/{$imageDirectory}/series.png'>
						<span class="tooltip">Most versatile: {$user.fullName|strip_unsafe_html} has worked for {$typesetter.maxSeries} different series as {$typesetter.userGroupName|lower}.</span>
					{/if}
					</div>
					<div class="star tooltip">
					{if $user.recentMaxAchievementUser}
						<img src='{$baseUrl}/{$imageDirectory}/recent.png'>
						<span class="tooltip">Most active current {$typesetter.userGroupName|lower}: In the last {$settingRecency} months, {$user.fullName|strip_unsafe_html} has worked on {$typesetter.maxRecentAchievements} book{if $typesetter.maxRecentAchievements>1}s{/if} as {$typesetter.userGroupName|lower}.</span>
					{/if}
					</div>
					<div class="star tooltip">
					{if !$user.maxSeriesUser}
						<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
					{/if}					
					{if !$user.recentMaxAchievementUser}
						<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
					{/if}
					</div>
				
					
					<div class="colorBar tooltip" style="width:{$barWidth}px;">{if $barWidth>=33}{$user.numberOfSubmissions}/{$user.rankPercentile}{else}&nbsp{/if}
						<span class="tooltip">{$user.fullName|strip_unsafe_html} has worked on {$user.numberOfSubmissions} book{if $user.numberOfSubmissions>1}s{/if} and is thus statistically more active than {$user.rankPercentile}% of the {$typesetter.userGroupName|lower}s.</span>
					</div>		

					{if $barWidth<33}
						<div class="smallBarCaption">
							&nbsp&nbsp{$user.numberOfSubmissions}/{$user.rankPercentile}
						</div>
					{/if}
				</button>				
								
				<div class="panel">
					<ol>					
						{foreach from=$user.submissions item=submission}
							<li class="bibList">
								{$submission.name}
								<a class="linkToBookPage" href="{$submission.path}">&rarr;</a>
							</li>
						{/foreach}
					</ol>
				</div>			
			{/foreach}
			{/foreach}
		{/foreach}
		
	</div>

	<div id="medalcount" class="tabcontent">
		<ul>
		{foreach from=$medalCount item=user}
			<li>
				<div class="rank">{$user.rank}.</div> 
				<div class="medalCount">
					{foreach from=$user.type item=achievementType key=medal}
						{foreach from=$achievementType key=k item=i}
							<div class="tooltip">
								<img style="float:left" src='{$baseUrl}/{$imageDirectory}/{$medal}.png'>
								<span class="tooltipsmall tooltip">{if $medal=="gold"}Gold {$k}{elseif $medal=="silver"}Silver {$k}{elseif $medal=="bronze"}Bronze {$k}{elseif $medal=="recent"}Most active {$k} at present{elseif $medal=="series"}Most versatile {$k}{/if}</span>
							</div>						
						{/foreach}											
					{/foreach}
				</div>
				{$user.name}
			</li>
		{/foreach}
		</ul>
	</div>

</div>

<script>
	var acc = document.getElementsByClassName("accordion");
	var i;
	for (i = 0; i < acc.length; i++) {
	  acc[i].addEventListener("click", function() {
		this.classList.toggle("active");
		var panel = this.nextElementSibling;
		if (panel.style.display === "block") {
		  panel.style.display = "none";
		} else {
		  panel.style.display = "block";
		}
	  });	  
	}
</script>

{include file="frontend/components/footer.tpl"}


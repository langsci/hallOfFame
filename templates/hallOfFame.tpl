{**
 * templates/user/profile.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * User profile tabset.
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

<style>

	button {
		text-align:left;
	}
	
	.tablink {
	  float: left;
	  border: none;
	  outline: none;
	  cursor: pointer;
	  padding: 14px 16px;
	  width: 25%;
	  border-top: none;
	  border-left:none;
	  border-right:none;
	  border-bottom: 2px solid #eee;
	  border-top:  2px solid #eee;
	  background-color: white;
	}

	.tabcontent {
	  padding: 20px 20px;
	  height: 100%;
	}
	
	.accordion {
	  cursor: pointer;
	  padding: 18px;
	  width: 100%;
	  border: none;
	  text-align: left;
	  outline: none;
	  transition: 0.8s;
	  background-color: white;
	}

	.active, .accordion:hover {

	}

	.panel {
	  padding: 0 18px;
	  display: none;
	  overflow: hidden;
	}
	
.triangle {
    border-color: white white white #005680;;
    border-style: solid;
    border-width: 5px 10px 5px 10px;
    height: 0px;
    width: 0px;
	float:left
}

.active .triangle {
    border-color: #005680 white white white;
    border-width: 10px 5px 10px 5px;
}

.contributor-name {
    margin-top: -3px;
	float:left;
}

.active .contributor-name {
    padding-left: 10px;
	float:left;
}
.colorBar {
	float:left;
	margin-top: -3px;
	background-color: #ffe56b;
	padding-left: 10px;
}

.tooltip {
  position: relative;
  display: inline-block;
  
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 320px;
  background-color:#eee;
  border-bottom: 1px solid #999; 
  border-radius: 3px;
  padding: 3px;

  /* Position the tooltip */
  position: absolute;
  z-index: 1;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}

.smallBarCaption {
	margin-top:-3px;
}

.star {
	float:left;
	margin-top:-8px;
	padding-right: 5px;
}
	
</style>

<div id="hallOfFame">

	<h2 class="title">{translate key="plugins.generic.hallOfFame.title"}</h2>

	<p class="intro">{translate key="plugins.generic.hallOfFame.intro"}</p>
	



	<button class="tablink" id="tab-proofreader" onclick="openPage('proofreader')">Proofreader</button>
	<button class="tablink" id="tab-typesetter" onclick="openPage('typesetter')">Typesetter</button>
	<button class="tablink" id="tab-medalcount" onclick="openPage('medalcount')">Medal count</button>

	<div id="proofreader" class="tabcontent">
	
		{foreach from=$proofreader.userData item=medalUserData}
		
			{counter print=false assign=medal}	
			{if $medal==1 and $medalUserData.user|@count>0}
				<h2 class="goldTitle tooltip">
					<span class="headerText">Gold Proofreaders</span>
				</h2>
			{/if}
			{if $medal==2 and $medalUserData.user|@count>0}
				<h2 class="silverTitle tooltip">
					<span class="headerText">Silver Proofreaders</span>
				</h2>
			{/if}
			{if $medal==3 and $medalUserData.user|@count>0}
				<h2 class="bronzeTitle tooltip">
					<span class="headerText">Bronze Proofreaders</span>
				</h2>
			{/if}			
			
			{foreach from=$medalUserData item=users}
			{foreach from=$users item=user}
				{assign var="barWidth" value=$user.numberOfSubmissions*300/$proofreader.maxAchievements}
				<button class="accordion">
					<div class="triangle"></div>					
					<div class="contributor-name" style="width:{math equation="x*y+z" x=$maxNameLength y=12 z=40}px" >{$user.fullName}</div>
					
					
									
										<div class="star tooltip">
										{if $user.maxSeriesUser}
											<img src='{$baseUrl}/{$imageDirectory}/series.png'>
											<span class="tooltip">Most versatile: {$user.fullName|strip_unsafe_html} has worked for {$userGroup.maxSeries} different series as {$userGroup.userGroupName|lower}.</span>
										{else}
										<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
										{/if}
										</div>							
									
									
										<div class="star tooltip">
										{if $user.recentMaxAchievementUser}
											<img src='{$baseUrl}/{$imageDirectory}/recent.png'>
											<span class="tooltip">Most active current {$userGroup.userGroupName|lower}: In the last {$settingRecency} months, {$user.fullName|strip_unsafe_html} has worked on {$userGroup.maxRecentAchievements} book{if $userGroup.maxRecentAchievements>1}s{/if} as {$userGroup.userGroupName|lower}.</span>
										{else}
										<img src='{$baseUrl}/{$imageDirectory}/empty.png'>
										{/if}
										</div>
									
							

									
					<div class="colorBar tooltip" style="width:{$barWidth}px;">{if $barWidth>=33}{$user.numberOfSubmissions}/{$user.rankPercentile}{else}&nbsp{/if}
						<span class="tooltiptext">{$user.fullName|strip_unsafe_html} has worked on {$user.numberOfSubmissions} book{if $user.numberOfSubmissions>1}s{/if} and is thus statistically more active than {$user.rankPercentile}% of the {$proofreader.userGroupName|lower}s.</span>
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
	  <h3>Typesetter</h3>
	  <p>Typesetter</p>
	</div>

	<div id="medalcount" class="tabcontent">
	  <h3>Medal count</h3>
	  <p>Medal count</p>
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


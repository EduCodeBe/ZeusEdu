<div class="container">
	
<h3>Absences de {$detailsEleve.nom} {$detailsEleve.prenom} {$detailsEleve.classe}</h3>
{if $listePresences|count == 0}
	<p>Aucune absence</p>
{else}
<div class="table-responsive">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th colspan="{$listePeriodes|count}" style="text-align:center">Périodes</th>
			</tr>
		</thead>
		<tr>
			<th>Date</th>
			{foreach from=$listePeriodes key=noPeriode item=bornes}
				<th><strong>{$noPeriode}<br></strong>{$bornes.debut} / {$bornes.fin}</th>
			{/foreach}
		</tr>
		{foreach from=$listePresences key=date item=presence}
			<tr>
				<td>{$date}</td>
				{foreach from=$listePeriodes key=noPeriode item=bornes}
					{if ($listePresences.$date.$noPeriode.statut != '')}
						{assign var=statut value=$listePresences.$date.$noPeriode.statut}
						{else}
						{assign var=statut value='indetermine'}
					{/if}
					{if $statut != 'indetermine'}
						{assign var=laPeriode value=$listePresences.$date.$noPeriode}
						{assign var=titre value=$laPeriode.educ|cat:' ['|cat:$laPeriode.quand|cat:' à '|cat:$laPeriode.heure|cat:']'}			
						{assign var=parent value=$laPeriode.parent|cat:'<br>'|cat:$laPeriode.media}
					{else}
						{assign var=parent value=Null}
						{assign var=titre value='Présences non prises'}
					{/if}
					<td class="pop {$statut}"
						data-content="{$parent}"
						data-html="true"
						data-container="body"
						data-original-title="{$titre}"
						data-placement="top"
						>
						<img src="images/{$statut}.png" alt="{$statut}">
					</td>	
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
{include file='legendeAbsences.html'}

{/if}

<form name="fake" style="display:none">
	{* bidouille pour assurer le fonctionnement du sélecteur avec autocomplete *}
</form>
</div>  <!-- container -->


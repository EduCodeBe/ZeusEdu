{assign var="ancre" value=$matricule}
<h2 title="cours {$intituleCours.coursGrp}">Bulletin {$bulletin} - {$intituleCours.statut} {$intituleCours.annee}
	{if $intituleCours.nomCours} {$intituleCours.nomCours}
	{else}
	{$intituleCours.libelle} {$intituleCours.nbheures}h -> {$listeClasses|@implode:', '}
	{/if}</h2>
<form name="encodage" id="encodage" action="index.php" method="POST" autocomplete="off" >
<input type="submit" name="submit" value="Enregistrer tout" class="noprint enregistrer" id="enregistrer">
<input type="reset" name="annuler" id="annuler" value="Annuler">

<input type="hidden" name="action" value="gestEncodageBulletins">
<input type="hidden" name="mode" value="enregistrer">
<input type="hidden" name="bulletin" value="{$bulletin}">
<input type="hidden" name="coursGrp" value="{$coursGrp}">
<input type="hidden" name="matricule" id="matricule" value="{$matricule}">
<input type="hidden" name="tri" value="{$tri}">
<p id="ouvrirTout" class="fauxBouton noprint" title="Déplier ou replier les Remarques et les Situations pour tous les élèves"></p>
{assign var="tabIndexForm" value="1"}
{assign var="tabIndexCert" value="500"}
{assign var="tabIndexAutres" value="1000"}

<select name="selectEleve" id="selectEleve">
	<option value=''>Sélectionner un élève</option>
	{foreach from=$listeEleves key=matricule item=unEleve}
	<option value="{$matricule}" id="{$matricule}" class="select">{$unEleve.nom} {$unEleve.prenom}</option>
	{/foreach}
</select>

{foreach from=$listeEleves key=matricule item=unEleve}
<div id="el{$matricule}" class="eleve">
	<div class="blocGaucheBulletin photo" style="text-align:center">
	<div style="text-align:right">
	{if isset($listeElevesSuivPrec.$matricule.prev) && ($listeElevesSuivPrec.$matricule.prev != Null)}
		<a href="#el{$listeElevesSuivPrec.$matricule.prev}">
			<img src="images/up.png" alt="^" title="Précédent"></a>
	{/if}
	{if isset($listeElevesSuivPrec.$matricule.next) && ($listeElevesSuivPrec.$matricule.next != Null)}
		<a href="#el{$listeElevesSuivPrec.$matricule.next}">
			<img src="images/down.png" alt="^" title="Suivant"></a>
	{/if}
	</div>
	<p><strong>{$unEleve.nom} {$unEleve.prenom}</strong></p>
	<img class="photoEleve" src="../photos/{$unEleve.photo}.jpg" width="100px" alt="{$matricule}" title="{$unEleve.nom} {$unEleve.prenom} {$matricule}">
	<p><strong>Classe: {$unEleve.classe}</strong></p>

	<input type="submit" name="submit" value="Enregistrer tout" id="{$matricule}"
	title="Enregistre l'ensemble des modifications de la page" class="noprint enregistrer"><span></span>
	
	</div>
	
	<div class="blocDroitBulletin">
	{assign var=formExiste value=(isset($ponderations.$coursGrp.all.$bulletin.form) && ($ponderations.$coursGrp.all.$bulletin.form != '')) ||
				(isset($ponderations.$coursGrp.$matricule.$bulletin.form) && ($ponderations.$coursGrp.$matricule.$bulletin.form != ''))}
	{assign var=certExiste value=(isset($ponderations.$coursGrp.all.$bulletin.cert) && ($ponderations.$coursGrp.all.$bulletin.cert != '')) ||
				(isset($ponderations.$coursGrp.$matricule.$bulletin.cert) && ($ponderations.$coursGrp.$matricule.$bulletin.cert != ''))}
	<table class="tableauBull">
		<tr>
			<th>Compétences</th>

			{if $formExiste}
				<th>TJ /
				<span class="ponderation">
				{$ponderations.$coursGrp.$matricule.$bulletin.form|default:$ponderations.$coursGrp.all.$bulletin.form}
				</span></th>
			<th>Max</th>{/if}
			{if $certExiste}
				<th>Cert /
				<span class="ponderation">
				{$ponderations.$coursGrp.$matricule.$bulletin.cert|default:$ponderations.$coursGrp.all.$bulletin.cert}
				</span></th>
			<th>Max</th>{/if}

		</tr>
	{assign var=cotes value=$listeCotes.$matricule.$coursGrp|default:Null}
	{assign var=blocage value=$listeVerrous.$matricule|default:Null}
	
	{foreach from=$listeCompetences key=cours item=lesCompetences}
		{foreach from=$lesCompetences key=idComp item=uneCompetence}
			<tr{if isset($tableErreurs.$matricule.$idComp)} class="erreurEncodage"{/if}>
				<td style="text-align:right" title="comp_{$idComp}"> {$uneCompetence.libelle}</td>
				
				{if $formExiste}
					{* Il y a, au moins, une pondération pour le "Formatif" durant cette période *}
					<td style="width:6em; text-align:center" 
					{if $cotes.$idComp.form.echec}class="echecEncodage"{/if}>
					<input tabIndex="{$tabIndexForm}" type="text" {if ($blocage.$coursGrp > 0)}readonly="readonly"{/if} 
					name="cote-eleve_{$matricule}-comp_{$idComp}-form" 
						value="{$cotes.$idComp.form.cote}" maxlength="5" size="2" class="cote"></td>
						
					{* Le max de Formatif pour cette compétence *}
					<td style="width:6em; text-align:center">
					<input tabIndex="{$tabIndexForm+1}" type="text" {if ($blocage.$coursGrp > 0)}readonly="readonly"{/if} 
					name="cote-eleve_{$matricule}-comp_{$idComp}-maxForm" 
						value="{$cotes.$idComp.form.maxForm}" maxlength="4" size="2" class="maxForm-comp_{$idComp} cote">
						{if !($blocage.$coursGrp)}<img src="images/flcBas.png" alt="flc" class="report noprint">{/if}</td>
					{assign var="tabIndexForm" value=$tabIndexForm+2}
				{/if}
				
				{if $certExiste}
					{* Il y a, au moins, une pondération générale pour le "Certificatif" durant cette période *}
					<td style="width:8em; text-align:center"
					{if $cotes.$idComp.cert.echec}class="echecEncodage"{/if}>
					<input tabIndex="{$tabIndexCert}" type="text" {if ($blocage.$coursGrp > 0)}readonly="readonly"{/if} 
					name="cote-eleve_{$matricule}-comp_{$idComp}-cert" 
						value="{$cotes.$idComp.cert.cote}" maxlength="5" size="2" class="cote"></td>
		
					{* Le max de Certificatif pour cette compétence *}
					<td style="width:6em; text-align:center">
					<input tabIndex="{$tabIndexCert+1}" type="text" {if ($blocage.$coursGrp > 0)}readonly="readonly"{/if} 
					name="cote-eleve_{$matricule}-comp_{$idComp}-maxCert" 
						value="{$cotes.$idComp.cert.maxCert}" maxlength="3" size="2" class="maxCert-comp_{$idComp} cote">
						{if !($blocage.$coursGrp)}<img src="images/flcBas.png" alt="flc" class="report noprint">{/if}</td>
					{assign var="tabIndexCert" value=$tabIndexCert+2}
				{/if}
			</tr>
		{/foreach}
	{/foreach}
		{assign var="totaux" value=$listeSommesFormCert.$matricule|default:Null}
		<tr>
			<th>Totaux</th>

			{if $formExiste}
				<td class="totaux">
					<input type="text" id="totalForm-{$matricule}" maxlength="3" size="3" disabled="disabled" 
					value="{$totaux.totalForm}">
				</td>
				<td class="totaux">
					<input type="text" id="maxCert-{$matricule}" maxlength="3" size="3" disabled="disabled" 
					value="{$totaux.maxForm}">
					{if !($blocage.$coursGrp)}<img src="images/flcBasDummy.png" alt="O">{/if}
				</td>
			{/if}
			{if $certExiste}
				<td class="totaux">
					<input type="text" id="totalCert-{$matricule}" maxlength="3" size="3" disabled="disabled" value="{$totaux.totalCert}">
				</td>
				<td class="totaux">
					<input type="text" id="maxCert-{$matricule}" maxlength="3" size="3" disabled="disabled" value="{$totaux.maxCert}">
					{if !($blocage.$coursGrp)}<img src="images/flcBasDummy.png" alt="O">{/if}
				</td>
			{/if}
			
		</tr>
	</table>
	<span class="tooltip" style="float:right">
	<span class="infoSup">Mentions admises</span>
	<div class="tip">
	Mentions neutres: <strong>{$COTEABS}</strong>
	</div>
	</span>

	<!-- blocGaucheBulletin -->
	{if $listeAttitudes}<div class="blocRemarque">{/if}
		<h3>Remarque pour la période {$bulletin}</h3>
		<textarea{if isset($blocage.$coursGrp) && ($blocage.$coursGrp > 0)} readonly="readonly"{/if} class="remarque" rows="8" 
			cols="{if isset($listeAttitudes)}50{else}80{/if}" 
			name="commentaire-eleve_{$matricule}"
			tabIndex="{$tabIndexAutres}">{$listeCommentaires.$matricule.$bulletin|default:Null}</textarea>
	{if $listeAttitudes}</div>{/if}

	{if $listeAttitudes}
	<div class="blocAttitudes">
	<h3>Attitudes</h3>
	{assign var="attitudes" value=$listeAttitudes.$matricule}
	<table class="tableauBull attitudes">
		<tr>
			<th>Attitude</th>
			<th><span class="clickNE">Non Évalué</span> / <span class="clickA">Acquis</span> / <span class="clickNA">Non Acquis</span></th>
		</tr>
		<tr>
			<td {if $attitudes[1] == 'N'} class="echec"{/if}>Respect des autres</td>
			<td>
			<span class="nonEvalue">NE</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att1" 
				value="NE" {if $attitudes[1] == 'NE'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+1}"> | 
			<span class="acquis">A</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att1" 
				value="A" {if $attitudes[1] == 'A'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+1}"> | 
			<span class="nonAcquis">NA</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att1" 
				value="N" {if $attitudes[1] == 'N'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+1}">
			</td>
		</tr>
		<tr>
			<td {if $attitudes[2] == 'N'} class="echec"{/if}>Respect des consignes</td>
			<td>
			<span class="nonEvalue">NE</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att2" 
				value="NE" {if $attitudes[2] == 'NE'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+2}"> | 
			<span class="acquis">A</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att2" 
				value="A" {if $attitudes[2] == 'A'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+2}"> | 
			<span class="nonAcquis">NA</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att2" 
				value="N" {if $attitudes[2] == 'N'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+2}">
			</td>
		</tr>
		<tr>
			<td {if $attitudes[3] == 'N'} class="echec"{/if}>Volonté de progresser</td>
			<td>
			<span class="nonEvalue">NE</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att3" 
				value="NE" {if $attitudes[3] == 'NE'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+3}"> | 
			<span class="acquis">A</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att3" 
				value="A" {if $attitudes[3] == 'A'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+3}"> | 
			<span class="nonAcquis">NA</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att3" 
				value="N" {if $attitudes[3] == 'N'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+3}">
			</td>
		</tr>
		<tr>
			<td {if $attitudes[4] == 'N'} class="echec"{/if}>Ordre et soin</td>
			<td>
			<span class="nonEvalue">NE</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att4" 
				value="NE" {if $attitudes[1] == 'NE'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+4}"> | 
			<span class="acquis">A</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att4" 
				value="A" {if $attitudes[4] == 'A'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+4}"> | 
			<span class="nonAcquis">NA</span> <input type="radio" {if ($blocage.$coursGrp > 0)}disabled="disabled"{/if} 
				name="attitudes-eleve_{$matricule}-att4" 
				value="N" {if $attitudes[4] == 'N'}checked="checked"{/if} class="radioAcquis"
				tabIndex="{$tabIndexAutres+4}">
			</td>
		</tr>
	</table>

	{assign var="tabIndexAutres" value=$tabIndexAutres+5}
	</div>
	{/if}

	{* ---------------------------------------------------------------------- *}
	{* tableau récapitulatif période, situation, étoile, crochets, délibé     *}
	{* ---------------------------------------------------------------------- *}
	{assign var="cotes" value=$listeGlobalPeriodePondere.$matricule.$coursGrp|default:Null}
	{assign var="bullPrec" value=$bulletin-1}
	<table class="tableauBull situationActuelle" style="width:100%">
		<tr>
			<th width="20%">Sit. Préc.</th>
			<th width="20%">Période</th>
			<th width="20%">Sit. (bull. <strong>{$bulletin}</strong>)</th>
			<th width="20%">Choix de situation
			</th>
			<th width="20%">Délibé</th>
		</tr>
		<tr>
			{* ------------------------------------------------------ *}
			{* situation au bulletin précédent ---------------------- *}
			{* ------------------------------------------------------ *}
			<td width="15%">
			{if isset($situationsPrecedentes.$matricule.$coursGrp.maxSit) &&  ($situationsPrecedentes.$matricule.$coursGrp.maxSit!= Null)}
				{assign var=sit value=$situationsPrecedentes.$matricule.$coursGrp.sit}
				{assign var=max value=$situationsPrecedentes.$matricule.$coursGrp.maxSit}
			<div class="fraction">
				<div class="num">{$sit|default:'&nbsp;'}</div>
				<div class="den">{$max|default:'&nbsp;'}</div>
			</div> 
			{assign var=pourcent value= 100 * $sit / $max}
			<span class="micro">= {$pourcent|string_format:"%.0f"} %</span>
			{/if}
			</td>
			
			{* ------------------------------------------------------ *}
			{* cotes de période, Formatif et/ou Certificatif ---------*}
			{* ------------------------------------------------------ *}
			<td width="30%">
				{if is_numeric($cotes.form.cote)}
				<div class="lblFraction">TJ</div>
				<div class="fraction">
				<div class="num">{$cotes.form.cote}</div>
				<div class="den">{$cotes.form.max}</div>
				</div>
				{/if}
				{if is_numeric($cotes.cert.cote)}
				<div class="lblFraction">Cert.</div>
				<div class="fraction">
				<div class="num">{$cotes.cert.cote}</div>
				<div class="den">{$cotes.cert.max}</div>
				</div>
				{/if}
			</td>
			
			{* -------------------------------------------------------*}
			{* nouvelle cote de situation y compris en % -------------*}
			{* ------------------------------------------------------ *}
			<td width="15%">
				{if isset($listeSituations.$matricule.$coursGrp.$bulletin)}
					{if isset($listeSituations.$matricule.$coursGrp.$bulletin.sit) && is_numeric($listeSituations.$matricule.$coursGrp.$bulletin.sit)}
						{if $listeSituations.$matricule.$coursGrp.$bulletin.max neq 0}
						<div class="fraction">
							<div class="num">{$listeSituations.$matricule.$coursGrp.$bulletin.sit}</div>
							<div class="den">{$listeSituations.$matricule.$coursGrp.$bulletin.max}</div>
						</div> 
						<span class="micro">= {$listeSituations.$matricule.$coursGrp.$bulletin.pourcent}%</span>
						{/if}
					{/if}
				{else}
				&nbsp;
				{/if}
			</td>
			
			{* Faut-il traiter le cas d'une période avec délibération? *}
			{if in_array($bulletin,$PERIODESDELIBES)}
			
			{* ------------------------------------------------------ *}
			{*  Choix de la cote de délibé     ---------------------- *}
			{* ------------------------------------------------------ *}
			<td style="width:20%; text-align:center">
				{if !($blocage.$coursGrp)}

				{* champs destinés à retenir si les cotes sont entre [], magiques, étoilées ou ² (réussite deuxième année du degré) *}
				{if isset($listeSituations.$matricule.$coursGrp.$bulletin)}
					{assign var=attribut value=$listeSituations.$matricule.$coursGrp.$bulletin.attribut}
				{else}
					{assign var=attribut value=Null}
				{/if}
				{* ----->>>> attribut de la cote de délibé: * ² [] ... --------*}
				<input type="hidden" name="attribut-eleve_{$matricule}" 
					id="attribut-eleve_{$matricule}" value="{$attribut}"
					{if ($blocage.$coursGrp > 0)}disabled="disabled"{/if}>

				{* ------------------------------------------------------ *}
				{* cote entre crochets? ----------------------------------*}
				{* Deux boutons: avec ou sans crochets -------------------*}
				{* ------------------------------------------------------ *}

					{if isset($listeSituations.$matricule.$coursGrp.$bulletin)}
						<input style="font-size:8pt" type="button" name="btnHook-eleve_{$matricule}" 
							tabIndex="{$tabIndexAutres+1}"
							class="hook" value="[{$listeSituations.$matricule.$coursGrp.$bulletin.pourcent|default:''} %]"
							{if ($blocage.$coursGrp > 0)}disabled="disabled"{/if}>
						<input style="font-size:8pt" type="button" name="btnNohook-eleve_{$matricule}" 
							tabIndex="{$tabIndexAutres+2}"
							class="nohook" value="{$listeSituations.$matricule.$coursGrp.$bulletin.pourcent|default:''} %"
							{if ($blocage.$coursGrp > 0)}disabled="disabled"{/if}>
						{if ($listeSituations.$matricule.$coursGrp.$bulletin.sitDelibe == Null)}
							<img src="images/led.gif" alt="!!" title="Sélectionnez une cote de délibération">{/if}
						{else}
							-
						{/if}  {*  /cote entre crochets  *}
					{else}
						&nbsp;
					{/if}

				{* ------------------------------------------------------ *}
				{* ces boutons n'apparaîssent qu'à la dernière période    *}
				{* Quand le bulletin est le dernier de l'année            *}
				{* ------------------------------------------------------ *}
				{if $bulletin == $nbBulletins}
				
					{* cote étoilée? -----------------------------------------*}
					{* Un bouton à cliquer si la situation le permet----------*}
					{if isset($listeSituations.$matricule.$coursGrp.$bulletin) && ($listeSommesFormCert.$matricule.pourcentCert > $listeSituations.$matricule.$coursGrp.$bulletin.pourcent)}
					<input style="font-size:8pt" type="button" name="btnStar-eleve_{$matricule}" 
						tabIndex="{$tabIndexAutres+3}"
						class="star" value="{$listeSommesFormCert.$matricule.pourcentCert} % *"
						{if ($blocage.$coursGrp > 0)}disabled="disabled"{/if}
						>
					{/if}
					
					{* baguette magique? -----------------------------------------*}
					<button type="button" name="magic-eleve_{$matricule}" class="magic" 
						{if ($blocage.$coursGrp > 0)}disabled="disabled"{/if}
						><img width="16" src="images/magic.png" alt="/"></button>
					
					
					{* cote réussite du degré? ------------------------------------*}
					{* Un bouton à cliquer si l'élève est en échec pour le degré---*}
					{* et s'il y a une cote de première année du degré             *}
					{if isset($listeSituations.$matricule.$coursGrp.$bulletin)&& ($listeSituations.$matricule.$coursGrp.$bulletin.pourcent < 50) 
							&& ($sitDeuxiemes.$coursGrp.$matricule.sit2)}
					<div class="cote1erDegre">
					{* attribution de la cote 50% en cas de réussite en deuxième; la cote de deuxième se trouve dans sit2 *}
					{* la cote de deuxième exclusivement est affichée pour information *}
					
						{$sitDeuxiemes.$coursGrp.$matricule.sit2}% en 2<sup>e</sup>
						{if $sitDeuxiemes.$coursGrp.$matricule.sit2 >= 50}
						=>
						{assign var=reussite50 value=50}
							<input style="font-size:8pt" type="button" name="btnDegre-eleve_{$matricule}"
								tabIndex="{$tabIndexAutres+4}"
								class="degre" value="{$reussite50} %"
								{if ($blocage.$coursGrp > 0)}disable="disabled"{/if}
								>
						{/if}
					</div>
					{/if}
				{/if}
			</td>
			
			{* ------------------------------------------------------- *}
			{* situation de délibé de l'élève -------------------------*}
			{* Une fois dans un texte à l'écran -----------------------*}
			{* Une fois dans un champ text caché (pour POST)-----------*}
			{* ------------------------------------------------------- *}
			<td style="width:20%; text-align:center"{if isset($tableErreurs.$matricule['sitDelibe'])} class="erreurEncodage"{/if}>
			{if isset($listeSituations.$matricule.$coursGrp.$bulletin)}
				{assign var="sitDelibe" value=$listeSituations.$matricule.$coursGrp.$bulletin.sitDelibe|default:Null}
				{else}
				{assign var="sitDelibe" value=Null}
			{/if}
			{if isset($listeSituations.$matricule.$coursGrp.$bulletin)}
			{assign var="symbole" value=$listeSituations.$matricule.$coursGrp.$bulletin.symbole}
			<strong id="situationFinale_{$matricule}">
				{if $listeSituations.$matricule.$coursGrp.$bulletin.attribut == 'hook'}
				[{$sitDelibe}]%
				{else}
				{$sitDelibe}<sup>{$symbole}</sup>%{/if}
			{else}
			&nbsp;
			{/if}
			</strong>
			<input type="text" maxlength="4" size="3" name="situation-eleve_{$matricule}" 
				id="situation-eleve_{$matricule}" value="{$sitDelibe}" class="sitDelibe" style="display:none">
				
				{* balayette pour effacer la cote de délibé *}
				{if $sitDelibe != ''}
					<img src="images/balai.png" alt="X" title="Effacer la cote de délibération" id="bal_{$matricule}" class="balayette">
				{/if}
			</td>
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		</tr>
	</table>
	{assign var="tabIndexAutres" value=$tabIndexAutres+5}
	
	{* -------------------------------------------------------------- *}
	<p class="ouvrir" style="clear:both">Remarques des autres périodes</p>
	<ul class="commentaires" style="display:none">
	{section name=annee start=1 loop=$nbBulletins+1}
    {assign var="periode" value=$smarty.section.annee.index}
		<li>{$periode} => {$listeCommentaires.$matricule.$periode|default:Null}
	{/section}
	</ul>

	<p class="ouvrir" style="clear:both">Situations</p>
		<ul class="situations" style="display:none">
		{section name=annee start=1 loop=$nbBulletins+1}
			{assign var="periode" value=$smarty.section.annee.index}
			{assign var="situation" value=$listeSituations.$matricule.$coursGrp.$periode|default:Null}
				<li>{$periode} => {if $situation.sit}<strong>{$situation.sit}/{$situation.max}</strong> soit {$situation.pourcent}%{/if}</li>
		{/section}
		</ul>
	</div>
</div>
<div style="clear:both; padding: 1em 0;"></div>
{assign var="elevePrecedent_ID" value=$matricule}
{/foreach}

</form>
<script type="text/javascript">
	
var show = "Cliquer pour voir";
var hide = "Cliquer pour cacher";
var showAll = "Déplier Remarques et Situations";
var hideAll = "Replier Remarques et Situations";
var hiddenAll = true;
var A = "Acquis";
var NA = "Non Acquis";
var report = "Report de la cote";
var modifie = false;
var desactive = "Désactivé: modification en cours. Enregistrez ou Annulez.";
var confirmationCopie = "Voulez-vous vraiment recopier ce maximum pour les autres élèves du même cours?\nAttention, les valeurs existantes seront écrasées.";
var confirmationReset = "Êtes-vous sûr(e) de vouloir annuler?\nToutes les informations modifiées depuis le dernier enregistrement seront perdues.\nCliquez sur 'OK' si vous êtes sûr(e).";
var noAccess = "Attention|Les cotes et mentions de ce bulletin ne sont plus modifiables.<br>Contactez l'administrateur ou le/la titulaire.";
var confirmationBeforeUnload = "Vous allez perdre toutes les modifications. Annulez pour rester sur la page.";
var erreursEncodage = "Vos données contenaient une ou plusieurs erreurs.\nVoyez les lignes surlignées en rouge.";
var refraichir = "Enregistrez pour rafraîchir le calcul";
var coteArbitraire = "Attention! Vous allez attribuer une cote arbitraire.\nCette fonction ne doit être utilisée que pour des circonstances exceptionnelles.\nVeuillez confirmer.";
var toutesAttitudes = "Cliquez pour attribuer en groupe"

$(document).ready(function(){

	$("input").tabEnter();
	
	$().UItoTop({ easingType: 'easeOutQuart' });
	
	$(".ouvrir").prepend("[+]").next().hide();
	$(".ouvrir").css("cursor","pointer").attr("title",show);
	$("#ouvrirTout").css("cursor","pointer");

	$(".radioAcquis").each(function (numero){
		if ($(this).val() == "N" && $(this).attr("checked"))
			$(this).parent().addClass("echecEncodage");
	})
	
	$(".cote, .remarque, .radioAcquis").each(function(numero){
		var element = $(".cote, .remarque, .radioAcquis").eq(numero);
		if (element.attr("readonly") || element.attr("disabled"))
			element.parent().parent().attr("title",noAccess);
	})
	
	$(".ouvrir").click(function(){
		$(this).next().toggle("fast");
		var texte = $(this).text();
		if ($(this).text().substring(1,2) == '+') {
			$(this).text(texte.replace('+','-'));
			$(this).attr("title",hide)
			}
			else {
				$(this).text(texte.replace('-','+'));
				$(this).attr("title",show);
			}
		})
	
	$(".report").attr("title",report).css("cursor","pointer");
	
	$(".report").click(function(){
		var max = $(this).prev().val();
		var prevClass = $(this).prev().attr("class");
		// élimination du nom de class 'cote' dans le nom de class
		prevClass = prevClass.substring(0,prevClass.lastIndexOf(' cote'));
		var listeInputs = $("."+prevClass);
		if (confirm(confirmationCopie)) {
			listeInputs.val(max);
			modification();
			}
		});
	
	// le copier/coller provoque aussi  une "modification"
	$("input, textarea").bind('paste', function(){
		modification()
	});
	
	$("#ouvrirTout").html(function(){
		texte = hiddenAll?showAll:hideAll;
		$(this).html(texte);
	});
	
	$("#ouvrirTout").click(function(){
		$(".ouvrir").click();
		hiddenAll = !(hiddenAll);
		var texte = hiddenAll?showAll:hideAll;
		$(this).html(texte);
	})
	
	function modification () {
		if (!(modifie)) {
			modifie = true;
			$(".enregistrer, #annuler").show();
			$("#bulletin").attr("disabled","disabled").attr("title",desactive);
			$("#coursGrp").attr("disabled","disabled").attr("title",desactive);
			$("#envoi").hide();
			$(".totaux input").css("color","white");
			window.onbeforeunload = function(){
				return confirm (confirmationBeforeUnload);
			};
			}
		}

	$("input, textarea").keyup(function(e){
		var readonly = $(this).attr("readonly");
		if (!(readonly)) {
		var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
		if ((key > 31) || (key == 8)) {
			modification();
			}
		}
	})
	
	$(".cote").keyup(function(e){
		var readonly = $(this).attr("readonly");
		if (!(readonly)) {
			var eleve = $(this).attr("name").split('-');
			var matricule = eleve[0].split("_")[1];
			var type = eleve[4];
		}
	})
	
	$(".radioAcquis").click(function(){
		if ($(this).val()=="N")
			$(this).parent().addClass("echecEncodage");
			else $(this).parent().removeClass("echecEncodage");
		modification();
		})
	
	$(".enregistrer, #annuler").hide();
	
	$("#annuler").click(function(){
		if (confirm(confirmationReset)) {
			this.form.reset();
			$(".acquis").each(function(numero){
				var checked = $(this).next().attr("checked");
				if (checked)
					$(this).parent().removeClass("echecEncodage");
					else $(this).parent().addClass("echecEncodage")
				});
			$("#bulletin").attr("disabled", false);
			$("#coursGrp").attr("disabled", false);
			$(".totaux input").css("color","black");
			modifie = false;
			$(".enregistrer, #annuler").hide();
			window.onbeforeunload = function(){};
			return false
		}
		})
	
	$(".enregistrer").click(function(){
		$(this).val("Un moment").addClass("patienter");
		$.blockUI();
		$("#wait").show();
		var ancre = $(this).attr("id");
		$("#matricule").val(ancre);
		window.onbeforeunload = function(){};
	})

	$(".hook, .nohook").click(function(){
		modification();
		var cote = $(this).val().replace(/[\[\]²%\* ]+/g,'');
		// Retrouver le matricule dans Ex: "btnHook-eleve_5042"
		var matricule = $(this).attr("name").split('-')[1].split("_")[1];
		
		// cacher le champ Input (éventuellement utilisé par la baguette magique)
		$("#situation-eleve_"+matricule).hide();
		// attribution d'une valeur affichée
		if ($(this).hasClass('hook')) 
			$("#situationFinale_"+matricule).html('['+cote+']%').show();
			else $("#situationFinale_"+matricule).html(cote+'%').show();
		// attribution d'une valeur au champ input situation-eleve pour$_POST
		$("#situation-eleve_"+matricule).val(cote);
		// indicateur d'attribut de la situation de délibé
		if ($(this).hasClass('hook')) 
			$("#attribut-eleve_"+matricule).val('hook');
			else $("#attribut-eleve_"+matricule).val('');
		})
	
	$(".star").click(function(){
		modification();
		var cote = $(this).val().replace(/[\[\]²%\* ]+/g,'');
		var matricule = $(this).attr("name").split('-')[1].split("_")[1];

		// cacher le champ Input (pas de baguette magique)
		$("#situation-eleve_"+matricule).hide();
		// attribution d'une valeur affichée et affichage du texte
		$("#situationFinale_"+matricule).html(cote+'*%').show();
		
		// attribution d'une valeur au champ input situation-eleve
		$("#situation-eleve_"+matricule).val(cote);
		// indicateur d'attribut de la situation de délibé
		$("#attribut-eleve_"+matricule).val('star');
	})
	
	$(".degre").click(function(){
		modification();
		var cote = $(this).val().replace(/[\[\]²%\* ]+/g,'');
		var matricule = $(this).attr("name").split('-')[1].split("_")[1];
		
		// cacher le champ Input (pas de baguette magique)
		$("#situation-eleve_"+matricule).hide();
		// attribution d'une valeur affichée
		$("#situationFinale_"+matricule).html(cote+'²%').show();
		
		// attribution d'une valeur au champ input situation-eleve
		$("#situation-eleve_"+matricule).val(cote);
		$("#attribut-eleve_"+matricule).val('degre');
	})

	
	$(".magic").click(function(){
		if (confirm(coteArbitraire)) {
			modification();
			// var cote = $(this).val().replace(/[\[\]²%\* ]+/g,'');
			var matricule = $(this).attr("name").split('-')[1].split("_")[1];

			// montrer le champ input
			$("#situation-eleve_"+matricule).css('display','block');
			// attribution d'une valeur affichée et affichage du texte
			$("#situationFinale_"+matricule).hide();
			
			$("#attribut-eleve_"+matricule).val('magique');
		}
	})
	
	$(".balayette").click(function(){
		modification();
		var matricule = parseInt($(this).attr("id").substr(4,10));
		$("#situationFinale_"+matricule).text('').hide();
		$("#attribut-eleve_"+matricule).val('');
		$("#situation-eleve_"+matricule).val('');
		})

		
	function goToByScroll(matricule){
     	$('html,body').animate({
			scrollTop: $("#"+matricule).offset().top-100
			},
			'slow'
			);
		}

	$("#selectEleve").change(function(){
		var matricule = $(this).val();
		goToByScroll("el"+matricule);
		})
	
	//$(".remarque").focus(function(){
	//	var center = $(window).height()/2;
	//	var top = $(this).offset().top ;
	//	if (top > center){
	//		$(window).scrollTop(top-center);
	//	}
	//});
	
	$(".clickNE, .clickNA, .clickA").attr('title',toutesAttitudes);

	$(".clickNE").click(function(){
		$(this).parent().parent().nextAll().find('.nonEvalue').next('input').trigger('click')
		})
	$(".clickNA").click(function(){
		$(this).parent().parent().nextAll().find('.nonAcquis').next('input').trigger('click')
		})
	$(".clickA").click(function(){
		$(this).parent().parent().nextAll().find('.acquis').next('input').trigger('click')
		})

	{if (isset($ancre))}
		goToByScroll("el{$ancre}")
	{/if}

});

{if $tableErreurs} alert(erreursEncodage){/if}
</script>

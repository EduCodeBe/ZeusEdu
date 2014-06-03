{* Permet de sélectionner un niveau d'étude puis un cours parmi ceux qui sont donnés à ce niveau*}
{* demande une $listeNiveaux; la liste des cours est chargée en arrière-plan en fonction du niveau sélectionné *}

<div id="selecteur" class="noprint" style="clear:both">
	<form name="formSelecteur" id="formSelecteur" method="POST" action="index.php">
	<select name="niveau" id="niveau">
		<option value="">Niveau</option>
		{foreach from=$listeNiveaux item=unNiveau}
			<option value="{$unNiveau}"{if isset($niveau) && ($unNiveau == $niveau)}selected{/if}>{$unNiveau}</option>
		{/foreach}
	</select>
	<span id="choixCours">
	{if $listeNiveaux}
		{include file="listeCoursComp.tpl"}
	{/if}
	</span>
	<input type="submit" value="OK" name="OK" id="envoi">
	<input type="hidden" name="action" value="{$action}">
	<input type="hidden" name="mode" value="{$mode}">
	<input type="hidden" name="etape" value="show">
	</form>
</div>

<script type="text/javascript">
{literal}
$(document).ready (function() {

	$("#formSelecteur").submit(function(){
		if ($("#cours").val() != "") {
			$("#wait").show();
			$("#corpsPage").hide();
			}
			else return false;
	})

	$("#niveau").change(function(){
		var niveau = $(this).val();
		$.post("inc/listeCours.inc.php",
			{'niveau': niveau},
				function (resultat){
					$("#choixCours").html(resultat)
				}
			)
	});
})
{/literal}
</script>

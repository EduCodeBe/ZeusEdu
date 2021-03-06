<div class="modal fade noprint" id="modalDelete" tabindex="-1" role="dialog" aria-labelled-by="labelModal" aria-hidden="true">

	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">
					Effacement d'une annonce
				</h4>
			</div>
			<!-- modal-header -->
			<div class="modal-body">

				<p>Objet: <strong id="spanDelObjet">{$notification.objet}</strong></p>
				<p>Date de début: <strong id="spanDelDatedebut">{$notification.dateDebut}</strong></p>
				<p>Date de fin: <strong id="spanDelDatefin">{$notification.dateFin}</strong></p>
				<p>Destinataire: <strong id="spanDelDestinataire">{$destinataire}</strong></p>
				<div id='modalDelPjFiles'>
					{include file="notification/modal/pjFiles.tpl"}
				</div>
				<p>Confirmez la suppression définitive de cette annonce</p>

				<div class="btn-group btn-group-sm pull-right">
					<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
					<button type="button" id="modalDelIdBtn" data-notifid="{$notifId}" data-type="{$type}" class="btn btn-danger">Supprimer cette annonce</button>
				</div>
				<div class="clearfix"></div>

			</div>
			<!-- modal-body -->
		</div>
		<!-- modal-content -->
	</div>
	<!-- modal-dialog -->

</div>
<!-- modal -->

<script type="text/javascript">
    {literal}
    $(document).ready(function(){
        $.gritter.add({
            title: "Accès à la base de données",
            text: $("#attention").html(),
            image: '../images/info.png',
            sticky: true
        })
        $("#attention").hide();        
        })    
    {/literal}
</script>
<div id="attention">
<p>Nombre d'enregistrement(s) modifié(s): <strong>{$resultats.ajouts}</strong></p>
<p>Nombre d'enregistrement(s) en échec: <strong>{$resultats.erreurs}</strong></p>
</div>
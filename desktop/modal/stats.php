<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<br/>
<a class="btn btn-success pull-right" id="bt_saveConfiguration"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Informations}}</a></li>
</ul>

<div class="tab-content" id="div_configuration">
	<div role="tabpanel" class="tab-pane active" id="eqlogictab">
		<br/>
		<table class="table table-bordered tablesorter">
			<thead>
				<tr>
					<th>{{Equipement}}</th>
					<th>{{Plugin}}</th>
					<th>{{Information}}</th>
					<th>{{Trigger}}</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

</div>


<?php include_file('desktop', 'mitsubishi', 'js', 'mitsubishi');?>

<script>
</script>

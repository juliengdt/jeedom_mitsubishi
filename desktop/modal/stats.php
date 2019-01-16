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

<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>


<?php include_file('desktop', 'mitsubishi', 'js', 'mitsubishi');?>

<script>

// Create the chart
Highcharts.chart('container', {
  chart: {
    type: 'column'
  },
  title: {
    text: 'Statistiques de la veille'
  },
  xAxis: {
    type: 'category'
  },
  yAxis: {
    title: {
      text: 'kWh'
    }

  },
  legend: {
    enabled: false
  },
  plotOptions: {
    series: {
      borderWidth: 0,
      dataLabels: {
        enabled: true,
        format: '{point.y:.1f}kWh'
      }
    }
  },

  tooltip: {
    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
  },

  "series": [
    {
      "name": "Energie",
      "colorByPoint": true,
      "data": [
				<?php
				$eqLogic = mitsubishi::byLogicalId();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'HotWater');
				$HotWater = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'Heating');
				$Heating = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'ProducedHotWater');
				$ProducedHotWater = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'ProducedHeating');
				$ProducedHeating = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId($eqLogic->getId(),'CoP');
				$CoP = $cmd->execCmd();

echo '
				{
					"name": "Eau Chaude énergie consommée",
					"y": ' . $HotWater ',
				},
				{
					"name": "Eau Chaude énergie produite",
					"y": ' . $ProducedHotWater . ',
				},
				{
					"name": "Chauffage énergie consommée",
					"y": ' . $Heating . ',
				},
				{
					"name": "Chauffage énergie produite",
					"y": ' . $ProducedHeating . ',
				}';

				?>

      ]
    }
  ]
});
</script>

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

<div id="containerPlot" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
<br />
<div id="container360" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>


<?php include_file('desktop', 'mitsubishi', 'js', 'mitsubishi');?>

<script>

// Create the chart
Highcharts.chart('containerPlot', {
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
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'HotWater');
				$HotWater = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'Heating');
				$Heating = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ProducedHotWater');
				$ProducedHotWater = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ProducedHeating');
				$ProducedHeating = $cmd->execCmd();
				$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'CoP');
				$CoP = $cmd->execCmd();

echo '
				{
					"name": "Eau Chaude énergie consommée",
					"y": ' . $HotWater . ',
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


// Build the chart
Highcharts.chart('container360', {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: 'pie'
  },
  title: {
    text: 'Répartition du fonctionnement de la veille'
  },
  tooltip: {
    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: false
      },
      showInLegend: true
    }
  },
  series: [{
	name: 'Brands',
	colorByPoint: true,
	data: [

		<?php
		$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ModeStop');
		$ModeStop = $cmd->execCmd();
		$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ModeHotWater');
		$ModeHotWater = $cmd->execCmd();
		$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ModeHeating');
		$ModeHeating = $cmd->execCmd();
		$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'LegionellaPrevention');
		$LegionellaPrevention = $cmd->execCmd();
		$cmd = mitsubishiCmd::byEqLogicIdAndLogicalId(init(id),'ModePowerOff');
		$ModePowerOff = $cmd->execCmd();

		echo "{
      name: 'Arrêt',
      y: " . $ModeStop . "
    }, {
      name: 'Eteint',
      y: " . $ModePowerOff . "
    }, {
      name: 'Eau Chaude',
      y: " . $ModeHotWater . "
    }, {
      name: 'Chauffage',
      y: " . $ModeHeating . "
    }, {
      name: 'Choc Thermique',
      y: " . $LegionellaPrevention . "
    }";

			?>
		]
  }]
});
</script>

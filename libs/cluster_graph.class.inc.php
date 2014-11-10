<?php
require_once 'jpgraph.php';
require_once 'jpgraph_bar.php';
require_once 'jpgraph_pie.php';
require_once 'jpgraph_pie3d.php';
require_once 'jpgraph_line.php';

if( !function_exists('imageantialias') ) {
    function imageantialias( $image, $enabled ) {
        return false;
    }
}
class cluster_graph {

	public static function bar_graph($input_data,$xaxis,$yaxis,$title = "") {
                $data_legend;

                if (count($input_data) > 0) {
                        foreach($input_data as $row) {
                                $datax[] = $row[$xaxis];
                        }
                }
                else {
                        $datax[] = 0;
                }

                $graph = new Graph(900,600,'auto');

                $graph->SetMargin(60,20,20,80);

                $graph->SetMarginColor('blue');
		$graph->SetScale("textlin");	
                $graph->SetShadow();
                $graph->yaxis->scale->SetGrace(10);
                $graph->yaxis->HideFirstTicklabel();
                $graph->title->Set($title);
                $graph->title->SetColor("#000000");
                $graph->SetFrame(false,'#ffffff');
                $graph->xaxis->SetTickLabels($datax);
                $graph->xaxis->SetLabelAngle('55');
                $bplot = self::bar_plot($input_data,$yaxis);
                $graph->Add($bplot);
                $graph->Stroke();
	 }


	public static function accumulated_bar_plot($input_data,$xaxis,$yaxis,
				$title = "",$legend = array()) {
                $data_legend;
		$plots = array();
                if (count($input_data) > 0) {
			//Xaxis
			for ($i=0;$i<count($input_data[0]);$i++) {
				$datax[] = $input_data[0][$i][$xaxis];
			}
			for ($i=0;$i<count($input_data);$i++) {
				array_push($plots,self::bar_plot($input_data[$i],$yaxis,$legend[$i]));
                        }
                }
                else {
                        $datax[] = 0;
                }
                $graph = new Graph(900,600,'auto');
                $graph->SetMargin(60,20,20,80);
                $graph->SetMarginColor('blue');
                $graph->SetScale("textlin");
                $graph->SetShadow();
                $graph->yaxis->scale->SetGrace(10);
                $graph->yaxis->HideFirstTicklabel();
                $graph->title->Set($title);
                $graph->title->SetColor("#000000");
                $graph->SetFrame(false,'#ffffff');
                $graph->xaxis->SetTickLabels($datax);
                $graph->xaxis->SetLabelAngle('55');
		$gbplot = new AccBarPlot($plots);

		//Legend
		if (count($legend)) {
                	$graph->legend->SetPos(0.1,0.95,"left","bottom");
	                $graph->legend->SetLayout("LEGEND_VERT");

		}
		$graph->Add($gbplot);
                $graph->Stroke();


	}
	public static function pie_graph($input_data,$title = "") {
	        $data_legend;
		$data;
        	if (count($input_data) > 0) {
                	$count = 0;
	                foreach($input_data as $row) {
        	                $data_legend[] = $row['legend'];
                	        $data[] = $row['value'];
                        	$count++;
	                }
        	}
	        else{
        	        $data[0] = 1;
                	$data_legend[0] = "None";


        	}

	        $graph = new PieGraph(600,300,"auto");
	        $graph->title->Set($title);
	        $graph->title->SetColor("#000000");
        	$p1 = new PiePlot3d($data);
	        $p1->SetAngle(85);
        	$p1->SetSize(0.35);
	        $p1->SetCenter(0.3,0.5);
        	$p1->SetLegends($data_legend);
        	$graph->legend->SetPos(0.6,0.2,"left","top");
	        $graph->legend->SetLayout("LEGEND_VERT");
	        $graph->Add($p1);
        	$graph->Stroke();
	}


	private static function bar_plot($input_data,$yaxis,$legend = "") {
		if (count($input_data) > 0) {
                        foreach($input_data as $row) {
                                $datay[] = $row[$yaxis];
                        }
                }
                else {
                        $datay[] = 0;
                }
		$bplot = new BarPlot($datay);
		$bplot->SetAlign("center");
		if ($legend != "") {
			$bplot->SetLegend($legend);
		}
		return $bplot;


	}
}
?>

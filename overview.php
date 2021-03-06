<?php
session_start();
include("values.php");

if (isset($_GET["pw"])) {
    $pw             = htmlspecialchars($_GET["pw"]);
    $_SESSION['pw'] = $pw;
}
if (!isset($_SESSION['pw'])) {
    echo 'NO PASSWORD';
    return;
}
$games      = array();
$teams      = array();
$ergebnisse = array();
$spieltage  = array();
$AllPlayer  = array();
$AllPlayer_Result_div  = array();
$result_div=array();
$AveragePlayer = array();
$AllGames_AllTipps = array();
$draw = 0;
$myID;
$myName;
$currentDateTime = new DateTime('now');
$currentDateTime = date_format($currentDateTime, 'Y-m-d H:i:s');
try {
    
    include("connection.php");
    
    $link->exec('SET CHARACTER SET utf8');
    
    // ----------------- ALL GAMES --------------
    $handle = $link->prepare('select * from Spiele limit 500');
    $handle->execute();
    
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $games[$row->ID]     = array(
            $row->HEIM_ID,
            $row->AUS_ID,
            $row->ERG_ID,
            $row->DATETIME
        );
        $datetime            = $row->DATETIME;
        $date                = date('Y-m-d', strtotime($datetime));
        $spieltage[$row->ID] = $date;
    }
    // ----------------- TEAMS --------------
    $handle = $link->prepare('select * from Mannschaften limit 500');
    //$handle->bindValue(1, 5, PDO::PARAM_INT);
    $handle->execute();
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $teams[$row->ID] = array(
            $row->NAME,
            $row->IMAGE
        );
    }
    // ----------------- Ergebnisse --------------
    $handle = $link->prepare('select * from Ergebnis limit 500');
    //$handle->bindValue(1, 5, PDO::PARAM_INT);
    $handle->execute();
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $ergebnisse[$row->ID] = array(
            $row->ToreA,
            $row->ToreB,
            $row->Extra
        );
    }


    //-----------------------Ergebnis-Verteilung--------------- 
    //$result_div['3-4'] = 2;

    foreach ($ergebnisse as $erg) {
    	if($erg[0]>=$erg[1]){
    		if(isset($result_div[$erg[0].'-'.$erg[1]])){
    			$result_div[$erg[0].'-'.$erg[1]] ++;
	    	}else{
	    		$result_div[$erg[0].'-'.$erg[1]] = 1;
	    	}
    	}
    	else {
			if(isset($result_div[$erg[1].'-'.$erg[0]])){
    			$result_div[$erg[1].'-'.$erg[0]] ++;
	    	}else{
	    		$result_div[$erg[1].'-'.$erg[0]] = 1;
	    	}
    	}  	
    }
    $result_div['0-0'] --;
    ksort($result_div);


    // ----------------- Spieltage --------------
    $spieltage = array_unique($spieltage);
    // ----------------- AllPlayer --------------
    $handle    = $link->prepare('select * from Spieler limit 100');
    $handle->execute();
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $AllPlayer[$row->ID] = $row->NAME;
    }
    // ----------------- User --------------
    $handle = $link->prepare('select * from Spieler Where PWD = ? limit 1');
    $handle->bindValue(1, $_SESSION["pw"], PDO::PARAM_INT);
    $handle->execute();
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $myID   = $row->ID;
        $myName = $row->NAME;
    }
    if ($result == null) {
        echo 'WRONG PASSWORD';
        return;
    }
    // ----------------- Alle Tipps --------------
    $handle = $link->prepare('select * from Tipps limit 2000');
    $handle->execute();
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $AllTipps[$row->ID] = array(
            $row->SpielID,
            $row->SpielerID,
            $row->Tipp1,
            $row->Tipp2
        );
    }

    //----------------------- Tipp-Kreativität-------------------

    for ($j=1; $j < sizeof($AllPlayer)+1; $j++) { 
    	$ctr = 0;
    	$used = array();
	    foreach ($AllTipps as $tipp) {
    		if($j == $tipp[1] && !in_array($tipp[2].'-'.$tipp[3], $used)){
    			$ctr++;
    			array_push($used, $tipp[2].'-'.$tipp[3]);
    		}
	    }
	    $AllPlayer_Result_div[$j]=$ctr;
	}

    // ----------------- Meine Tipps --------------
    $handle = $link->prepare('select * from Tipps WHERE SpielerID = ? limit 2000');
    $handle->bindValue(1, $myID, PDO::PARAM_INT);
    $handle->execute();
    
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    foreach ($result as $row) {
        $myTipps[$row->SpielID] = array(
            $row->Tipp1,
            $row->Tipp2
        );
    }

    // Graphs
	// ----------------- Alle Tipps von allen bisherigen Spielen pro Spieler--------------
	// [ Spielnummer ] := a[Spielernummer]= tippID
    for ($i=1; $i < sizeof($games)+1; $i++) { 

		if($games[$i][3]<$currentDateTime && $ergebnisse[$games[$i][2]][2]!=0){
	    	for ($k=1; $k < sizeof($AllPlayer)+1; $k++) { 

	    		if(!isset($AllGames_AllTipps[$i])){
	    			$AllGames_AllTipps[$i] = array();
	    		}

	    		foreach ($AllTipps as $tip) {
	    			if($tip[1]==$k && $tip[0]==$i){
	    		 		//$AllGames_AllTipps[$i][$k] = $tip;
						$AllGames_AllTipps[$i][$k] = getPoints($tip,$ergebnisse,$games,$k,$i);
	    		 	}
	    		 	else if(empty($AllGames_AllTipps[$i][$k])){
	    		 		$AllGames_AllTipps[$i][$k] = 0;
	    		 	}
	    		}
	    	}
	    }
    }

	$AllGames_AllTipps_Added=array();
    // Adding Points
    for ($n=1; $n < sizeof($AllGames_AllTipps)+1; $n++) { 
    	for ($m=1; $m < sizeof($AllGames_AllTipps[$n])+1; $m++) { 
    		if($n>1){
    			$AllGames_AllTipps_Added[$n][$m] = $AllGames_AllTipps[$n][$m] + $AllGames_AllTipps_Added[$n-1][$m] ;
    		}	
    		else{
    			$AllGames_AllTipps_Added[$n][$m] = $AllGames_AllTipps[$n][$m];
    		}
    	}
    }

    $arraySorted = array();
    // Remember the position
    $AllGames_AllTipps_Added_Position = array();
    for ($n=1; $n < sizeof($AllGames_AllTipps_Added)+1; $n++) { 
    	for ($m=1; $m < sizeof($AllGames_AllTipps_Added[$n])+1; $m++) { 
    		$AllGames_AllTipps_Added_Position[$n][$m] = getPosition($AllGames_AllTipps_Added,$n,$m);
    	}
    }
    if(sizeof($AllGames_AllTipps_Added_Position)>0){
    	$draw = 1;
    }
}
catch (\PDOException $ex) {
    echo ($ex);
}
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="SK">
    <link rel="shortcut icon" href="images/ball.ico">
    <title><?php echo $appname;?></title>
    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="assets/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
	    $('.nav-sidebar>li>a').click(function() {
    		$('.nav>li').removeClass();
    		$(this).parent().addClass('active');
    		return true;
    	});

	    // tooltip
	    $('span').tooltip({
			    selector: "[data-toggle=tooltip]",
			    container: "body"
		    });
	    });
    </script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    
    <script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
	    var data = google.visualization.arrayToDataTable([	    	
	    ['Match', <?php for ($p=1; $p < sizeof($AllPlayer)+1; $p++) { 
	    	echo '\''.$AllPlayer[$p].'\',';
	    }
	     echo '],';

	    for ($i=1; $i<sizeOf($AllGames_AllTipps_Added)+1;$i++) {
	    	echo '['.$i.',';
			for ($k=1; $k<sizeOf($AllGames_AllTipps_Added[$i])+1;$k++) {
				if(($AllGames_AllTipps_Added[$i][$k])!=null)
					echo $AllGames_AllTipps_Added[$i][$k].',';
				else echo '0,';
			}
			echo ']';
			if($i != sizeOf($AllGames_AllTipps_Added)){
	            echo ',';
	        }
	    }
	    echo ']);';
		?>

	    
	    var options = {
	    title: 'Punkte-Verlauf',
	    hAxis: {title: 'Spiele',  titleTextStyle: {color: 'red'}}
	    };
	   

	    //---------------------------

	    var data2 = google.visualization.arrayToDataTable([	    	
	    ['Match', <?php for ($p=1; $p < sizeof($AllPlayer)+1; $p++) { 
	    	echo '\''.$AllPlayer[$p].'\',';
	    }
	     echo '],';

	    for ($i=1; $i<sizeOf($AllGames_AllTipps_Added_Position)+1;$i++) {
	    	echo '['.$i.',';
			for ($k=1; $k<sizeOf($AllGames_AllTipps_Added_Position[$i])+1;$k++) {
				if(($AllGames_AllTipps_Added_Position[$i][$k])!=null)
					echo $AllGames_AllTipps_Added_Position[$i][$k].',';
				else echo '0,';
			}
			echo ']';
			if($i != sizeOf($AllGames_AllTipps_Added_Position)){
	            echo ',';
	        }
	    }
	    echo ']);';
		?>

	    
	    var options2 = {
	    title: 'Platzierungs-Verlauf',
	    hAxis: {title: 'Spiele',  titleTextStyle: {color: 'red'}},
		dataOpacity: '0.9',
		vAxis: { direction:'-1', maxValue:'10', minValue:'1',viewWindow: {min:'1'}}
		};

		//---------------------------

	    var data3 = google.visualization.arrayToDataTable([	    	
	    ['Result', <?php for ($p=1; $p < sizeof($AllPlayer)+1; $p++) { 
	    	echo '\''.$AllPlayer[$p].'\',';
	    }
	     echo '],';
		 echo '[ 0,';
	    for ($i=1; $i<sizeOf($AllPlayer_Result_div)+1;$i++) {
	    	
			if($AllPlayer_Result_div[$i]!=null){
					echo $AllPlayer_Result_div[$i].',';
			}
				
	    }
	    echo ']';
	    echo ']);';
		?>

	    
	    var options3 = {
	      title: 'Tipp Kreativität',
	      hAxis:{textPosition:'none'},
		};

		//---------------------------

	    var data4 = google.visualization.arrayToDataTable([	 
	    ['Name', 'result'],

	    <?php foreach ($result_div as $value=>$res) {
	    	echo '[ \''.$value.'\','.$res.'],';		   
	    }
	    
	    echo ']);';

		?>

	    
	    var options4 = {
	      title: 'Ergebnis-Vielfalt',
		};

		
<?php 
	if($draw){
		echo ' var chart = new google.visualization.LineChart(document.getElementById(\'chart_div\'));
	    chart.draw(data, options);';
		echo 'var chart2 = new google.visualization.LineChart(document.getElementById(\'chart_div2\'));
	    chart2.draw(data2, options2);';
	    echo 'var chart3 = new google.visualization.ColumnChart(document.getElementById(\'chart_div3\'));
	    chart3.draw(data3, options3);';
	    echo 'var chart4 = new google.visualization.PieChart(document.getElementById(\'chart_div4\'));
	    chart4.draw(data4, options4);';

	}

?>

	    
	  }
	    
    </script>
  </head>
  <body data-spy="scroll" data-target=".sidebar">
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $appname;?></a>
        </div>
        <div class="navbar-collapse myNavbar collapse ">
          <ul class="nav navbar-nav ">
            <li><a href="index.php">

              <?php 
              echo $name1;

              if(isset($myTipps) && sizeof($myTipps)>0){
             
                $diff = (sizeof($games) - sizeof($myTipps));
                if ($diff>0){
                  echo '<span style="margin-left:10px;background-color:#428bca;" class="badge label-info pull-right">'.$diff.'</span>';
                }
              }
              else{
                echo '<span style="margin-left:10px;background-color:#428bca;" class="badge label-info pull-right">'.sizeof($games).'</span>';
              }

              ?>
              </a></li>
            <li class="active" ><a href="overview.php"><?php echo $name2;?></a></li>
            <li><span class="nameHighlight"><?php echo $myName;?></span></li>
          </ul>
        </div>
      </div>
    </div>
    <div style="margin-top:30px"></div>
    <div class="row" id="content">
      <!---------------------CONTENT---------------------------------------------->
      <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
          <li class="active"><a href="#Tabelle">Tabelle</a></li>
          <li><a href="#Punkte">Punkte-Verlauf</a></li>
          <li><a href="#Platz">Platzierungs-Verlauf</a></li>
          <li><a href="#Kreativ">Tipp-Kreativität</a></li>
          <li><a href="#Vielfalt">Ergebnis-Vielfalt</a></li>
          <li><a href="#Uber">Übersicht</a></li>
          
        </ul>
        
      </div>
      <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
      <div style="margin-bottom:30px">
    	Nach <?php echo sizeof($ergebnisse)-1?> von 64 Spielen
    	<div class="progress">
	  		<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo (sizeof($ergebnisse)-1)/64*100?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo (sizeof($ergebnisse)-1)/64*100?>%;">
	    	<?php echo ceil((sizeof($ergebnisse)-1)/64*100)?>%
	  		</div>
		</div>
      </div>
        <h3 id="Tabelle" class="sub-header">Tabelle</h3>
        <table class="table table-hover table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th><span class="TableToolTip"> <a data-html="true" data-toggle="tooltip" data-placement="top" data-original-title="Richtige Tendenz">RT</a></span></th>
              <th><span class="TableToolTip"> <a data-html="true" data-toggle="tooltip" data-placement="top" data-original-title="Richtige Tendenz und Tordifferenz">RD</a></span></th>
              <th><span class="TableToolTip"> <a data-html="true" data-toggle="tooltip" data-placement="top" data-original-title="Richtiges Ergebnis">RE</a></span></th>
              <th>Punkte</th>
            </tr>
          </thead>
          <tbody>
            <?php
			//Data for the first array
            if(isset($AllTipps) && sizeof($AllTipps)>0){
              for ($k=1; $k < sizeof($AllPlayer)+1; $k++) {

                $mResults = getCounter($AllTipps,$ergebnisse,$games,$k);
                $arraySorted[$k-1] = array($AllPlayer[$k],$mResults[0],$mResults[1],$mResults[2],($mResults[0]*1+$mResults[1]*2+$mResults[2]*3));
            }
              echo '<tr>';
              usort($arraySorted, "cmp");

              for ($k=1; $k < sizeof($AllPlayer)+1; $k++) {
                echo'<td>'.$k.'</td><td>';
	            echo '<p>'.$arraySorted[$k-1][0].'</p>';	
                echo '</td><td>'.$arraySorted[$k-1][1].'</td><td>'.$arraySorted[$k-1][2].'</td><td>'.$arraySorted[$k-1][3].'</td><td>'.$arraySorted[$k-1][4].'</td></tr>';
              }
            }
            ?>
          </tbody>
        </table>
      </br>
        <h3  id="Punkte" class="sub-header">Punkte-Verlauf</h3>
        <div id="chart_div" style="height:500px"></div>
        </br>
        <h3  id="Platz" class="sub-header">Platzierungs-Verlauf</h3>
        <div id="chart_div2" style="height:500px"></div>
        </br>
        <h3  id="Kreativ" class="sub-header">Tipp-Kreativität</h3>
        <div id="chart_div3" style="height:500px"></div>
        </br>
        <h3  id="Vielfalt" class="sub-header">Ergebnis-Vielfalt</h3>
        <div id="chart_div4" style="height:600px"></div>
        </br>
        <h3  id="Uber" class="sub-header">Übersicht</h3>
        <table class="table table-hover table-bordered">
          <thead>
            <tr>
              <th>Spiel</th>
              <th>Erg.</th>
              <?php
              for ($i=1; $i < sizeof($AllPlayer)+1; $i++) {
	              echo '<th>';
		          echo '<p>'.$AllPlayer[$i];'</p>';	
	              echo '</th>';
	              }
              ?>
              <!-- <th>&#216;</th> -->
            </tr>
          </thead>
          <tbody>
            <?php
            for ($i=1; $i < sizeof($games)+1; $i++) {
            	$numTips=0;
            	$avTip1=0;
            	$avTip2=0;
            echo '<tr>';
              echo '<td>'.$teams[$games[$i][0]][0].'-'.$teams[$games[$i][1]][0].'</td>';
              if($ergebnisse[$games[$i][2]][2]!= 0 ){
            	  echo '<td>'.$ergebnisse[$games[$i][2]][0].' : '.$ergebnisse[$games[$i][2]][1];

            	if($ergebnisse[$games[$i][2]][2]!= 0 && $ergebnisse[$games[$i][2]][2]!= 1){
              		echo ' '.$ergebnisse[$games[$i][2]][2];
              	}
              	echo '</td>';
              }
              else{
              	echo '<td>-:-</td>';
              }
              for ($k=1; $k < sizeof($AllPlayer)+1; $k++) {
              
              echo '<td>';
                $cont = '<span class="glyphicon glyphicon-remove"></span>';

                if(isset($AllTipps) && sizeof($AllTipps)>0){
	                foreach ($AllTipps as $tip) {

	                  if($tip[0] == $i && $tip[1] == $k){

	                  	if($currentDateTime<$games[$i][3] ){
	                  		$cont = '<span class="glyphicon glyphicon-ok"></span>';
	                  	}
	              		else{
	                  		$cont = $tip[2].' : '.$tip[3];
	                  		$numTips+=1;
	                  		$avTip1 += $tip[2];
	                  		$avTip2 += $tip[3];

	                  		if($ergebnisse[$games[$i][2]][2]!= 0 && $ergebnisse[$games[$i][2]][0] == $tip[2] && $ergebnisse[$games[$i][2]][1]== $tip[3]){
			                	$cont = '<span style="color:blue;">'.$cont.'</span>';
			                }
	              		}
	              	}
            	}
            }
        	echo $cont;   	  
              echo '</td>';
              }
           //    if($numTips!=0){
           // 	   echo '<td>'.floor(floor($avTip1*10/$numTips)/10).' : '.floor(floor($avTip2*10/$numTips)/10).'</td>';
          	// }else{
          	// 	echo '<td>-:-</td>';
          	// }
            echo '</tr>';
            }
        
            ?>
          </tbody>
        </table>
        </br>
      </div>
      <!---------------------CONTENT - END---------------------------------------------->
    </div>
  </div>
  <!-- Bootstrap core JavaScript
  ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
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
$myTipps    = array();
$AllTipps   = array();
$myID;
$myName;

$currentDateTime = new DateTime('now');
$currentDateTime = date_format($currentDateTime, 'Y-m-d H:i:s');




try {
    // Create a new connection.
    // You'll probably want to replace hostname with localhost in the first parameter.
    // Note how we declare the charset to be utf8mb4.  This alerts the connection that we'll be passing UTF-8 data.  This may not be required depending on your configuration, but it'll save you headaches down the road if you're trying to store Unicode strings in your database.  See "Gotchas".
    // The PDO options we pass do the following:
    // \PDO::ATTR_ERRMODE enables exceptions for errors.  This is optional but can be handy.
    // \PDO::ATTR_PERSISTENT disables persistent connections, which can cause concurrency issues in certain cases.  See "Gotchas".
    
    include("connection.php");
    // $link = new \PDO(   'mysql:host=HOSTNAME;dbname=DBNAME;charset=utf8mb4', 
    //                        'USERNAME', 'PASSWORD', 
    //                        array(
    //                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
    //                            \PDO::ATTR_PERSISTENT => false
    //                        )
    //                    );
    
    $link->exec('SET CHARACTER SET utf8');
    
    // ----------------- ALL GAMES --------------
    
    $handle = $link->prepare('select * from Spiele limit 500');
    
    // PHP bug: if you don't specify PDO::PARAM_INT, PDO may enclose the argument in quotes.  This can mess up some MySQL queries that don't expect integers to be quoted.
    // See: https://bugs.php.net/bug.php?id=44639
    // If you're not sure whether the value you're passing is an integer, use the is_int() function.
    // $handle->bindValue(1, 100, PDO::PARAM_INT);
    // $handle->bindValue(2, 'Bilbo Baggins');
    //$handle->bindValue(1, 5, PDO::PARAM_INT);
    
    $handle->execute();
    
    // Using the fetchAll() method might be too resource-heavy if you're selecting a truly massive amount of rows.
    // If that's the case, you can use the fetch() method and loop through each result row one by one.
    // You can also return arrays and other things instead of objects.  See the PDO documentation for details.
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
    $handle->execute();
    
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    
    foreach ($result as $row) {
        $ergebnisse[$row->ID] = array(
            $row->ToreA,
            $row->ToreB,
            $row->Extra
        );
    }
    
    // ----------------- Spieltage --------------
    $spieltage = array_unique($spieltage);
    
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
    
    // ----------------- Tipps speichern --------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        foreach ($_POST as $key => $value) {
            if ($value != "") {
                
                //Valid bet-time
 				$handle = $link->prepare('Select * FROM Spiele WHERE ID = ?');
                $handle->bindValue(1, substr($key, 0, -1), PDO::PARAM_INT);
                $handle->execute();
                
                $result = $handle->fetchAll(\PDO::FETCH_OBJ);

				foreach ($result as $row) {
				        if($row->DATETIME < $currentDateTime){
				        	echo 'It\'s to late!. Please reload this site. ';
				        	return;
				        }
				}                


                //Does the game exists?
                $handle = $link->prepare('Select * FROM Tipps WHERE SpielerID = ? AND SpielID = ?');
                $handle->bindValue(1, $myID, PDO::PARAM_INT);
                $handle->bindValue(2, substr($key, 0, -1), PDO::PARAM_INT);
                $handle->execute();
                
                $result = $handle->fetchAll(\PDO::FETCH_OBJ);
                
                if (count($result) == 0) {
                    
                    // INSERT NEW
                    if (substr($key, -1) == "A") {
                        $handle = $link->prepare('INSERT INTO Tipps (ID,SpielerID, SpielID,Tipp1) VALUES (null,?,?,?)');
                    } else {
                        $handle = $link->prepare('INSERT INTO Tipps (ID,SpielerID, SpielID,Tipp2) VALUES (null,?,?,?)');
                    }
                    $handle->bindValue(1, $myID, PDO::PARAM_INT);
                    $handle->bindValue(2, substr($key, 0, -1), PDO::PARAM_INT);
                    $handle->bindValue(3, $value, PDO::PARAM_INT);
                    $handle->execute();
                } else {
                    // UPDATE
                    if (substr($key, -1) == "A") {
                        $handle = $link->prepare('UPDATE Tipps SET Tipp1=? WHERE SpielerID=? AND SpielID=?');
                    } else {
                        $handle = $link->prepare('UPDATE Tipps SET Tipp2=? WHERE SpielerID=? AND SpielID=?');
                    }
                    $handle->bindValue(1, $value, PDO::PARAM_INT);
                    $handle->bindValue(2, $myID, PDO::PARAM_INT);
                    $handle->bindValue(3, substr($key, 0, -1), PDO::PARAM_INT);
                    $handle->execute();
                    
                }
            }
        }
    }
    
    // ----------------- Meine Tipps --------------    
    
    $handle = $link->prepare('select * from Tipps WHERE SpielerID = ? limit 500');
    $handle->bindValue(1, $myID, PDO::PARAM_INT);
    
    $handle->execute();
    
    $result = $handle->fetchAll(\PDO::FETCH_OBJ);
    
    foreach ($result as $row) {
        $myTipps[$row->SpielID] = array(
            $row->Tipp1,
            $row->Tipp2
        );
    }
    
    
    
    // ----------------- Alle Tipps --------------
    $handle = $link->prepare('select * from Tipps limit 500');
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
            	
            	var d = new Date();
            	var month = d.getMonth()+1;
				var day = d.getDate();
            	var output = d.getFullYear() + '-' + ((''+month).length<2 ? '0' : '') + month + '-' + ((''+day).length<2 ? '0' : '') + day + ' '
            	$('html,body').animate({scrollTop: $("#"+output).offset().top},'slow');
                
                $('.nav-sidebar>li>a').click(function() {
                    $('.nav>li').removeClass();
                    $(this).parent().addClass('active');
                    return true;
                });
                
                  // tooltip
                $('div').tooltip({
                    selector: "[data-toggle=tooltip]",
                    container: "body"
                });
            
            });
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
                        <li class="active">
                            <a href="index.php">
                            <?php 
                                echo $name1;
                                
                                $diff = (sizeof($games) - sizeof($myTipps));
                                if ($diff>0){
                                    echo '<span style="margin-left:10px;background-color:#428bca;" class="badge label-info pull-right">'.$diff.'</span>';
                                }
                        
                                ?>
                            </a>
                        </li>
                        <li><a href="overview.php"><?php echo $name2;?></a></li>
                        <li><span class="nameHighlight"><?php echo $myName;?></span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div style="margin-top:50px"></div>
        <div class="row" id="content">
            <!---------------------CONTENT---------------------------------------------->
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <?php
                        $used=0;
                        foreach ($spieltage as $value) {
                            if($used==0){
                                $used=1;
                                echo '<li class="active"><a href="#'.$value.'">'.$value.'</a></li>';
                            } 
                            else {
                            echo '<li><a href="#'.$value.'">'.$value.'</a></li>';
                            }
                        }
                        ?>
                </ul>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <form action="index.php" method="post">
                    <button style="right:20px;top:60px; position:fixed;" type="submit" class="btn btn-sm btn-primary">Speichern</button>
                    <?php
                        foreach ($spieltage as $value) {
                          echo '<h3 id="'.$value.'" class="sub-header">'.$value.'</h3>';
                        
                        
                           for ($i=1; $i < sizeof($games)+1; $i++) { 
                        
                              $game = $games[$i];
                            
                            // korrekter Spieltag
                              $mDate = date('Y-m-d', strtotime($game[3]));
                              $mTime = date('G:i', strtotime($game[3]));
                        
                              if($mDate == $value){
                              	echo '</br>';
                                echo' <div class="resultcenter"><span class="results"></br>';
                                echo   $mTime.' Uhr';
                                echo' </span></div>';
                        
                                echo '<div class="line" >';
                        
                                #echo '<span style="float:left">'.$mTime.' Uhr</span>';
                                echo '<div class="left"><h4><image src="images/'.$teams[$game[0]][1].'"> </image> '.$teams[$game[0]][0].'</h4></div>';
                                echo '<div class="center"><h4>-</h4></div>';
                                echo '<div class="right"><h4>'.$teams[$game[1]][0].' <image src="images/'.$teams[$game[1]][1].'"> </image>';
                                echo' </span></h4></div>';
                        
                        
                                echo' <div class="resultcenter"><span class="results">';
                        
                                if($currentDateTime<$game[3]){
                                  echo '- : -'; 
                                  $dis = "";
                                } 
                                else{ 
                                  $dis = "disabled";

                                  if($ergebnisse[$game[2]][2] != 0 ){
                                  	echo $ergebnisse[$game[2]][0].' : '.$ergebnisse[$game[2]][1];
                                  }else{
                                  	echo '- : -'; 
                                  }

                                }
                        
                                if($ergebnisse[$game[2]][2] != 0  && $ergebnisse[$game[2]][2] != 1){
                                  echo ' '.$ergebnisse[$game[2]][2].' ';
                                }
                        
                                echo' </span></div>';
                        
                        
                                echo '<div class="left"><h5>Dein Tipp <input '.$dis.' name="'.$i.'A" maxlength="2" type="text" value="';
                        
                                if(isset($myTipps[$i]) && isset($myTipps[$i][0])){
                                  echo $myTipps[$i][0];
                                }
                                echo '">';
                        
                                echo'</input></h5></div>';
                                echo '<div class="center"><h5>:</h5></div>';
                                echo '<div class="right"><h5><input '.$dis.' name="'.$i.'B" maxlength="2" type="text" value="';
                        
                                if(isset($myTipps[$i]) && isset($myTipps[$i][1])){
                                  echo $myTipps[$i][1];
                                }
                                echo '">';
                                echo'</input> <span class="points">Punkte: ';
                        
                                if($currentDateTime<$game[3] || $ergebnisse[$game[2]][2] == 0){
                                  echo '-';
                                } else{
                                  echo getCounterSingle($AllTipps,$ergebnisse,$games,$myID,$i);
                                }
                        
                                echo '</span>';
                                echo '</h5></div>';
                                echo' <div class="resultcenter"><span class="results"></br></span></div>';
                                echo '</div>';
                        
                            }       
                          }
                        
                        }
                        ?>
                </form>
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
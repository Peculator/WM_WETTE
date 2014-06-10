<?php 

$appname = "WM-Wette 2014";
$name1 = "Tipps";
$name2 = "Tabelle";

function getCounter($tipps,$ergebnisse,$spiele,$playerID){
	$value=array();
	$rt=0;$rd=0;$re=0;

	foreach ($tipps as $tipp) {
		for ($i=1; $i < sizeof($spiele)+1; $i++) { 
				
			if($tipp[1]==$playerID && $tipp[0]==$i && $ergebnisse[$spiele[$i][2]][2]!=0 && $ergebnisse[$spiele[$i][2]][2]!=1){
				

				//Ergebnis
				if($tipp[2] == $ergebnisse[$spiele[$i][2]][0] && $tipp[3] == $ergebnisse[$spiele[$i][2]][1]){
					$re++;
				}
				//Tendenz + Differenz
				elseif($tipp[2]- $tipp[3] == $ergebnisse[$spiele[$i][2]][0]-$ergebnisse[$spiele[$i][2]][1]){
					$rd ++;
				}
				//Tendenz
				elseif($tipp[2]> $tipp[3] && $ergebnisse[$spiele[$i][2]][0]>$ergebnisse[$spiele[$i][2]][1] ||
					$tipp[2]<$tipp[3] && $ergebnisse[$spiele[$i][2]][0]<$ergebnisse[$spiele[$i][2]][1]){
					$rt++;
				}
			}
		}
	}

	$value[0]=$rt;$value[1]=$rd;$value[2]=$re;

	return $value;
}

function getCounterSingle($tipps,$ergebnisse,$spiele,$playerID,$gameIndex){
	$result = 0;
	if(sizeof($tipps)==0)return 0;

	foreach ($tipps as $tipp) {
		for ($i=0; $i < sizeof($spiele); $i++) { 

			if($tipp[1]==$playerID && $tipp[0]==$i && $i==$gameIndex){
			
				
				//Ergebnis
				if($tipp[2] == $ergebnisse[$spiele[$i][2]][0] && $tipp[3] == $ergebnisse[$spiele[$i][2]][1]){
					$result=3;
				}
				//Tendenz + Differenz
				elseif($tipp[2]- $tipp[3] == $ergebnisse[$spiele[$i][2]][0]-$ergebnisse[$spiele[$i][2]][1]){
					$result=2;
				}
				//Tendenz
				elseif($tipp[2]> $tipp[3] && $ergebnisse[$spiele[$i][2]][0]>$ergebnisse[$spiele[$i][2]][1] ||
					$tipp[2]<$tipp[3] && $ergebnisse[$spiele[$i][2]][0]<$ergebnisse[$spiele[$i][2]][1]){
					$result=1;
				}
			}
		}
	}

	return $result;

}

function cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return ($a[4] < $b[4]) ? -1 : 1;
}

?>
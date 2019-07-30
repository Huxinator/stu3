<?php
	$data[details] = "Klasse L - Basisklasse Wald";

	$bonusdata = array(BONUS_AENER,BONUS_LFOOD,BONUS_LFOOD,BONUS_HABI);
	
	$data[sizew] = 7;
	$data[sizeh] = 5;

	$hasground = 0;
	
	$data[basefield] = 101;
	$odata[basefield] = 900;
	$udata[basefield] = 801;

	$phases = 0;
	$ophases = 0;
	$uphases = 0;
		
		
	// config
	
	$wasser  = rand(8,10);
	$berge   = rand(4,6);
	$sumpf   = rand(3,5);
	$bäume   = rand(10,14);
	
	$ufels   = rand(8,12);
	
	
	// Surface Phases





	$phase[$phases][mode] = "equatorial";
	$phase[$phases][description] = "Sümpfe";
	$phase[$phases][num] = $sumpf;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "121");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = 0;
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 15;	
	$phases++;

	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Wasserflächen";
	$phase[$phases][num] = $wasser;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "201");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array(121);
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 8;	
	$phases++;
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Berge";
	$phase[$phases][num] = $berge;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "701");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "201");
	$phase[$phases][noadjacentlimit] = 1;	
	$phase[$phases][fragmentation] = 10;	
	$phases++;
	
	$phase[$phases][mode] = "normal";
	$phase[$phases][description] = "Bäume";
	$phase[$phases][num] = $bäume;
	$phase[$phases][from] = array("0" => "101");
	$phase[$phases][to]   = array("0" => "111");
	$phase[$phases][adjacent] = 0;
	$phase[$phases][noadjacent] = array("0" => "401");
	$phase[$phases][noadjacentlimit] = 0;	
	$phase[$phases][fragmentation] = 12;	
	$phases++;
	

	// Orbit Phases
	/*
	$ophase[$ophases][mode] = "lower orbit";
	$ophase[$ophases][description] = "Lower Orbit";
	$ophase[$ophases][num] = 10;
	$ophase[$ophases][from] = array("0" => "100");
	$ophase[$ophases][to]   = array("0" => "120");
	$ophase[$ophases][adjacent] = 0;
	$ophase[$phases][noadjacent] = 0;
	$ophase[$ophases][noadjacentlimit] = 0;	
	$ophase[$ophases][fragmentation] = 2;	
	$ophases++;
	*/
	
	// Underground Phases
	
	$uphase[$uphases][mode] = "normal";
	$uphase[$uphases][description] = "Untergrundfels";
	$uphase[$uphases][num] = $ufels;
	$uphase[$uphases][from] = array("0" => "801");
	$uphase[$uphases][to]   = array("0" => "802");
	$uphase[$uphases][adjacent] = 0;
	$uphase[$uphases][noadjacent] = 0;
	$uphase[$uphases][noadjacentlimit] = 0;	
	$uphase[$uphases][fragmentation] = 10;	
	$uphases++;	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
?>
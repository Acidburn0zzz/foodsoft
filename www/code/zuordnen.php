<?php
//error_reporting(E_ALL); // alle Fehler anzeigen
//all pwd empty: update `bestellgruppen` set passwort = '352DeJsgtxG.6'

function drop_basar($bestellid){
	$sql = "DELETE bestellzuordnung.* FROM bestellzuordnung inner
	join gruppenbestellungen on (gruppenbestellungen.id =
	gruppenbestellung_id) WHERE art = 2 AND gesamtbestellung_id = ".$bestellid." AND bestellguppen_id = ".mysql_escape_string(sql_basar_id());
	//echo $sql."<br>";
	mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Basarbestellungen nicht aus DB löschen..",mysql_error());
}
function sql_basar_id(){
	    $sql = "SELECT id FROM bestellgruppen
	    		WHERE name = \"_basar\"";
	    //echo $sql."<br>";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Basar-ID nich aus DB laden..",mysql_error());
	    if(mysql_num_rows($result)!=1) 
		error(__LINE__,__FILE__,"Kein Eintrag für Glasrueckgabe" );
	    $row = mysql_fetch_array($result);
	    return $row['id'];


}

function getGlassID(){
	    $sql = "SELECT id FROM produkte
	    		WHERE name = \"glasrueckgabe\"";
	    //echo $sql."<br>";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Glas-Produkt-ID nich aus DB laden..",mysql_error());
	    if(mysql_num_rows($result)!=1) 
		error(__LINE__,__FILE__,"Kein Eintrag für Glasrueckgabe" );
	    $row = mysql_fetch_array($result);
	    return $row['id'];


}
function sql_create_gruppenbestellung($gruppe, $bestell_id){
	    //Gruppenbestellung erzeugen
	    $sql = "INSERT INTO gruppenbestellungen
	    		(bestellguppen_id, gesamtbestellung_id)
			VALUES (".$gruppe.", ".$bestell_id.")";
	    //echo $sql."<br>";
	    mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
	    //Id Auslesen und zurückgeben
	    $sql = "SELECT last_insert_id() as id;";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
	    $id = mysql_fetch_array($result);
	    return($id['id']);
	
}
function sql_basar2group($gruppe, $produkt, $menge){

	    //Bestell-ID bestimmen
	    $sql = "SELECT * FROM (".select_basar().") as basar WHERE produkt_id = ".mysql_escape_string($produkt);
	    //echo $sql."<br>";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Basar nich aus DB laden..",mysql_error());
	    $row = mysql_fetch_array($result);
	    $bestell_id = $row['gesamtbestellung_id'];

	    //Gruppenbestellung ID raussuchen
	    $sql = "SELECT id FROM gruppenbestellungen
	    		WHERE gesamtbestellung_id = ".$bestell_id.
			" AND bestellguppen_id = ".$gruppe;

	    //echo $sql."<br>";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());

	    //Evtl. fehlende Gruppenbestellung erzeugen
	    if(mysql_num_rows($result)==0){
	    	sql_create_gruppenbestellung($gruppe, $bestell_id);
	    	$result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
	    }
	    
	    $row = mysql_fetch_array($result);

	    $sql2 = "INSERT INTO bestellzuordnung
	    		(produkt_id, gruppenbestellung_id, menge, art)
			VALUES (".$produkt.", ".$row['id'].", $menge, 2)";
	    //echo $sql2."<br>";
	    mysql_query($sql2) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
}
function kontostand($gruppen_id){
	    //Bestellt
	    $query = "SELECT summe FROM (".select_bestellsumme().")as bestellsumme WHERE bestellguppen_id = ".mysql_escape_string($gruppen_id);
	    //echo "<p>".$query."</p>";
	    $result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Produktdaten nich aus DB laden..",mysql_error());
	    $row = mysql_fetch_array($result);
	    $summe = $row['summe'];
	    //Sonstige Transaktionen
	    $query = "SELECT sum( summe ) as summe
			FROM `gruppen_transaktion`
			WHERE gruppen_id =".mysql_escape_string($gruppen_id);
	    //echo "<p>".$query."</p>";
	    $result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Produktdaten nich aus DB laden..",mysql_error());
	    $row = mysql_fetch_array($result);
	    $summe += $row['summe'];

	    return $summe;

}
function select_verteilmengen_preise(){
	return "select `gruppenbestellungen`.`bestellguppen_id` AS `bestellguppen_id`,`gesamtbestellungen`.`id` AS `bestell_id`,`gesamtbestellungen`.`name` AS `name`,`bestellzuordnung`.`produkt_id` AS `produkt_id`,`bestellzuordnung`.`menge` AS `menge`,`produktpreise`.`preis` AS `preis`,`gesamtbestellungen`.`bestellende` AS `bestellende` from ((((`bestellzuordnung` join `gruppenbestellungen` on((`bestellzuordnung`.`gruppenbestellung_id` = `gruppenbestellungen`.`id`))) join `bestellvorschlaege` on(((`bestellzuordnung`.`produkt_id` = `bestellvorschlaege`.`produkt_id`) and (`gruppenbestellungen`.`gesamtbestellung_id` = `bestellvorschlaege`.`gesamtbestellung_id`)))) join `produktpreise` on((`bestellvorschlaege`.`produktpreise_id` = `produktpreise`.`id`))) join `gesamtbestellungen` on((`gesamtbestellungen`.`id` = `gruppenbestellungen`.`gesamtbestellung_id`))) where (`bestellzuordnung`.`art` = 2) order by `gesamtbestellungen`.`bestellende`
";
}
function select_verteilmengen(){
	return "select `verteilmengen_preise`.`bestell_id` AS
	`bestell_id`,`verteilmengen_preise`.`produkt_id` AS
	`produkt_id`,sum(`verteilmengen_preise`.`menge`) AS `menge`
	from (".select_verteilmengen_preise().") as `verteilmengen_preise` group by `verteilmengen_preise`.`bestell_id`,`verteilmengen_preise`.`produkt_id`";
}
function select_bestellkosten(){
	return "select `verteilmengen_preise`.`bestellguppen_id` AS
	`bestellguppen_id`,`verteilmengen_preise`.`bestell_id` AS
	`bestell_id`,`verteilmengen_preise`.`name` AS
	`name`,sum((`verteilmengen_preise`.`menge` *
	`verteilmengen_preise`.`preis`)) AS
	`gesamtpreis`,`verteilmengen_preise`.`bestellende` AS
	`bestellende` from (".select_verteilmengen_preise().") as `verteilmengen_preise` group by `verteilmengen_preise`.`bestellguppen_id`,`verteilmengen_preise`.`bestell_id`,`verteilmengen_preise`.`name`,`verteilmengen_preise`.`bestellende`";
}
function select_bestellsumme(){
	return "select bestellkosten.bestellguppen_id
	,sum(bestellkosten.gesamtpreis) AS summe from
	(".select_bestellkosten().") as`bestellkosten` group by `bestellkosten`.`bestellguppen_id`
";
	/*
	`bestellsumme` AS select `bestellkosten`.`bestellguppen_id` AS `bestellguppen_id`,sum(`bestellkosten`.`gesamtpreis`) AS `summe` from `bestellkosten` group by `bestellkosten`.`bestellguppen_id`
	`bestellkosten` AS select `verteilmengen_preise`.`bestellguppen_id` AS `bestellguppen_id`,`verteilmengen_preise`.`bestell_id` AS `bestell_id`,`verteilmengen_preise`.`name` AS `name`,sum((`verteilmengen_preise`.`menge` * `verteilmengen_preise`.`preis`)) AS `gesamtpreis`,`verteilmengen_preise`.`bestellende` AS `bestellende` from `verteilmengen_preise` group by `verteilmengen_preise`.`bestellguppen_id`,`verteilmengen_preise`.`bestell_id`,`verteilmengen_preise`.`name`,`verteilmengen_preise`.`bestellende`
	`verteilmengen_preise` AS select `gruppenbestellungen`.`bestellguppen_id` AS `bestellguppen_id`,`gesamtbestellungen`.`id` AS `bestell_id`,`gesamtbestellungen`.`name` AS `name`,`bestellzuordnung`.`produkt_id` AS `produkt_id`,`bestellzuordnung`.`menge` AS `menge`,`produktpreise`.`preis` AS `preis`,`gesamtbestellungen`.`bestellende` AS `bestellende` from ((((`bestellzuordnung` join `gruppenbestellungen` on((`bestellzuordnung`.`gruppenbestellung_id` = `gruppenbestellungen`.`id`))) join `bestellvorschlaege` on(((`bestellzuordnung`.`produkt_id` = `bestellvorschlaege`.`produkt_id`) and (`gruppenbestellungen`.`gesamtbestellung_id` = `bestellvorschlaege`.`gesamtbestellung_id`)))) join `produktpreise` on((`bestellvorschlaege`.`produktpreise_id` = `produktpreise`.`id`))) join `gesamtbestellungen` on((`gesamtbestellungen`.`id` = `gruppenbestellungen`.`gesamtbestellung_id`))) where (`bestellzuordnung`.`art` = 2) order by `gesamtbestellungen`.`bestellende`
	`verteilmengen` AS select `verteilmengen_preise`.`bestell_id` AS `bestell_id`,`verteilmengen_preise`.`produkt_id` AS `produkt_id`,sum(`verteilmengen_preise`.`menge`) AS `menge` from `verteilmengen_preise` group by `verteilmengen_preise`.`bestell_id`,`verteilmengen_preise`.`produkt_id`
	*/
}
function sql_gesamtpreise($gruppe_id){
            $query = "SELECT gesamtbestellungen.name, sum(menge * preis) AS gesamtpreis, 
	    				DATE_FORMAT(bestellende,'%d.%m.%Y  <br> <font size=1>(%T)</font>') as datum
				FROM  bestellzuordnung 
				INNER JOIN gruppenbestellungen ON ( gruppenbestellung_id = gruppenbestellungen.id )
				INNER JOIN bestellvorschlaege
				USING ( gesamtbestellung_id, produkt_id )
				INNER JOIN produktpreise ON ( produktpreise_id = produktpreise.id ) 
				INNER JOIN gesamtbestellungen ON (gesamtbestellungen.id = gruppenbestellungen.gesamtbestellung_id)
				WHERE art =2 and bestellguppen_id = '".mysql_escape_string($gruppe_id)."'
				GROUP BY gesamtbestellungen.name
				    ORDER BY bestellende;";

//	    echo "<p>".$query."</p>";
	    $result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Produktdaten nich aus DB laden..",mysql_error());

	    return $result;

}


function sql_bestellprodukte($bestell_id){
            $query = "SELECT *, produkte.name as produkt_name, produktgruppen.name as produktgruppen_name FROM produkte INNER JOIN
	                            bestellvorschlaege ON (produkte.id=bestellvorschlaege.produkt_id)
				    INNER JOIN produktpreise 
				    ON (bestellvorschlaege.produktpreise_id=produktpreise.id)
				    INNER JOIN produktgruppen
				    ON (produktgruppen.id=produkte.produktgruppen_id)
				    WHERE bestellvorschlaege.gesamtbestellung_id='".mysql_escape_string($bestell_id)."'
				    ORDER BY produktgruppen_id, produkte.name;";

	    //echo "<p>".$query."</p>";
	    $result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Produktdaten nich aus DB laden..",mysql_error());

	    return $result;
}

function sql_produktpreise2($produkt_id){
	$query = "SELECT * FROM produktpreise 
		  WHERE produkt_id=".mysql_escape_string($produkt_id);
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Gebindegroessen nich aus DB laden..",mysql_error());
	//echo "<p>".$query."</p>";
	return $result;
}
function sql_produktpreise($produkt_id, $bestellstart, $bestellende){
	$query = "SELECT gebindegroesse,preis FROM produktpreise 
		  WHERE zeitstart <= '".mysql_escape_string($bestellstart)."' 
		        AND (ISNULL(zeitende) OR zeitende >= '".mysql_escape_string($bestellende)."')
			AND produkt_id=".mysql_escape_string($produkt_id)."
			ORDER BY gebindegroesse DESC;";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Gebindegroessen nich aus DB laden..",mysql_error());
	//echo "<p>".$query."</p>";
	return $result;
}
function sql_verteilmengen($bestell_id, $produkt_id, $gruppen_id){
	$result = sql_bestellmengen($bestell_id, $produkt_id,2, $gruppen_id);
	if(mysql_num_rows($result)!=1) 
		error(__LINE__,__FILE__,"Nicht genau ein Eintrag für Verteilmenge" );
	$row = mysql_fetch_array($result);
	return $row['menge'];
	
}
function sql_bestellmengen($bestell_id, $produkt_id, $art, $gruppen_id=false,$sortByDate=true){
	$query = "SELECT  *, gruppenbestellungen.id as gruppenbest_id,
	bestellzuordnung.id as bestellzuordnung_id
	FROM gruppenbestellungen INNER JOIN bestellzuordnung 
	ON (bestellzuordnung.gruppenbestellung_id = gruppenbestellungen.id)
	WHERE gruppenbestellungen.gesamtbestellung_id = ".mysql_escape_string($bestell_id)." 
	AND bestellzuordnung.produkt_id = ".mysql_escape_string($produkt_id);
	if($gruppen_id!==false){
		$query = $query." AND gruppenbestellungen.bestellguppen_id = ".mysql_escape_string($gruppen_id);
	}
	if($art!==false){
		$query = $query." AND bestellzuordnung.art=".$art;
	}
	if($sortByDate){
		$query = $query." ORDER BY bestellzuordnung.zeitpunkt;";
	}else{
		$query = $query." ORDER BY gruppenbestellung_id, art;";
	}
	//echo "<p>".$query."</p>";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Bestellmengen nich aus DB laden..",mysql_error());
	return $result;
}
function sql_gruppenname($gruppen_id){
	$query="SELECT name 
		FROM bestellgruppen 
		WHERE id = ".mysql_escape_string($gruppen_id); 
	//echo "<p>".$query."</p>";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Bestellgruppendaten nich aus DB laden..",mysql_error());
	$row=mysql_fetch_array($result);
	return $row['name'];
}
function sql_gruppen($bestell_id=FALSE){
        if($bestell_id==FALSE){
		$query="SELECT * FROM bestellgruppen";
	} else {
	    $query="SELECT bestellgruppen.id, bestellgruppen.name 
		FROM bestellgruppen INNER JOIN gruppenbestellungen 
		ON (gruppenbestellungen.bestellguppen_id = bestellgruppen.id)
		WHERE gruppenbestellungen.gesamtbestellung_id = ".mysql_escape_string($bestell_id); 
	}
	//echo "<p>".$query."</p>";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Bestellgruppendaten nich aus DB laden..",mysql_error());
	return $result;
	
}

function sql_bestellungen(){
	 $query = "SELECT * FROM gesamtbestellungen WHERE NOW() > bestellende ORDER BY bestellende DESC";
         $result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Bestellgruppendaten nich aus DB laden..",mysql_error());
	 return $result;
}

function bestell_zeile($bestell_id){
	$all = sql_bestellungen();
	while ($row = mysql_fetch_array($all)) 
		if($row['id']==$bestell_id) return $row;
}
function nichtGeliefert($bestell_id, $produkt_id){
    $sql = "UPDATE bestellzuordnung INNER JOIN gruppenbestellungen 
	    ON gruppenbestellung_id = gruppenbestellungen.id 
	    SET menge =0 
	    WHERE art=2 
	    AND produkt_id = ".$produkt_id." 
	    AND gesamtbestellung_id = ".$bestell_id.";";
    mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Verteilmengen nicht ändern..",mysql_error());
    //echo $sql;
    $sql = "UPDATE bestellvorschlaege
    	    SET liefermenge = 0 
	    WHERE produkt_id = ".$produkt_id."
	    AND gesamtbestellung_id = ".$bestell_id;
    //mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Verteilmengen nicht ändern..",mysql_error());
    //echo $sql;
}
function writeLiefermenge_sql($bestell_id){
	$query = "SELECT produkt_id, sum(menge) as s FROM gruppenbestellungen  
		  INNER JOIN bestellzuordnung ON
		  	(gruppenbestellungen.id = gruppenbestellung_id)
		  WHERE art = 2 
		  AND gesamtbestellung_id = ".$bestell_id." 
		  GROUP BY produkt_id";
	//echo $query."<br>";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Liefermengen nicht in DB schreiben...",mysql_error());
  	while ($produkt_row = mysql_fetch_array($result)){
		$sql2 = "UPDATE bestellvorschlaege SET bestellmenge = "
		        .$produkt_row['s'].", liefermenge = ".
		        $produkt_row['s']." WHERE gesamtbestellung_id = ".
			$bestell_id." AND produkt_id = ".$produkt_row['produkt_id'];
		echo $sql2."<br>";
		mysql_query($sql2) or error(__LINE__,__FILE__,"Konnte Liefermengen nicht in DB schreiben...",mysql_error());
	}

}
function sql_basar(){
   $sql = "SELECT * FROM (".select_basar().") as basar";
   //echo $sql."<br>";
   $result =  mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
   return $result;

}
function select_basar(){
   return "SELECT produkte.name, bestellvorschlaege.produkt_id,
bestellvorschlaege.gesamtbestellung_id,
bestellvorschlaege.produktpreise_id,  (bestellvorschlaege.liefermenge - sum(bestellzuordnung.menge)) as basar FROM 
`bestellzuordnung` 
JOIN `gruppenbestellungen` ON ( `bestellzuordnung`.`gruppenbestellung_id` = `gruppenbestellungen`.`id` ) 
JOIN `bestellvorschlaege` ON (  `bestellzuordnung`.`produkt_id` = `bestellvorschlaege`.`produkt_id` AND `gruppenbestellungen`.`gesamtbestellung_id` = `bestellvorschlaege`.`gesamtbestellung_id` )
JOIN `produktpreise` ON ( `bestellvorschlaege`.`produktpreise_id` = `produktpreise`.`id` ) 
JOIN `gesamtbestellungen` ON ( `gesamtbestellungen`.`id` = `gruppenbestellungen`.`gesamtbestellung_id` ) 
JOIN `produkte` ON ( bestellzuordnung.`produkt_id` = `produkte`.`id` ) 
WHERE `bestellzuordnung`.`art` =2 
GROUP BY gesamtbestellungen.id , bestellzuordnung.`produkt_id`
HAVING ( `basar` <>0) " ;


 /*  
   "select
   produkte.name,bestellvorschlaege.produkt_id,bestellvorschlaege.gesamtbestellung_id,(bestellvorschlaege.liefermenge
   - verteilmengen.menge) AS basar,bestellvorschlaege.produktpreise_id
   from (((".select_verteilmengen().") as `verteilmengen` join `bestellvorschlaege` on(((`verteilmengen`.`bestell_id` = `bestellvorschlaege`.`gesamtbestellung_id`) and (`bestellvorschlaege`.`produkt_id` = `verteilmengen`.`produkt_id`)))) join `produkte` on((`verteilmengen`.`produkt_id` = `produkte`.`id`))) having (`basar` <> 0) ";
   */
}
function from_basar(){
   return "((`verteilmengen` join `bestellvorschlaege` on(((`verteilmengen`.`bestell_id` = `bestellvorschlaege`.`gesamtbestellung_id`) and (`bestellvorschlaege`.`produkt_id` = `verteilmengen`.`produkt_id`)))) join `produkte` on((`verteilmengen`.`produkt_id` = `produkte`.`id`)))";
   /*
   VIEW `basar` AS select `produkte`.`name` AS `name`,`bestellvorschlaege`.`produkt_id` AS `produkt_id`,`bestellvorschlaege`.`gesamtbestellung_id` AS `gesamtbestellung_id`,(`bestellvorschlaege`.`liefermenge` - `verteilmengen`.`menge`) AS `basar`,`bestellvorschlaege`.`produktpreise_id` AS `produktpreise_id` from ((`verteilmengen` join `bestellvorschlaege` on(((`verteilmengen`.`bestell_id` = `bestellvorschlaege`.`gesamtbestellung_id`) and (`bestellvorschlaege`.`produkt_id` = `verteilmengen`.`produkt_id`)))) join `produkte` on((`verteilmengen`.`produkt_id` = `produkte`.`id`))) having (`basar` <> 0)
   */
}
function zusaetzlicheBestellung($produkt_id, $bestell_id, $menge ){
   $sql ="SELECT * FROM bestellvorschlaege 
   		WHERE produkt_id = ".mysql_escape_string($produkt_id)." 
   		AND gesamtbestellung_id = ".mysql_escape_string($bestell_id) ;
   //echo $sql."<br>";
   $result2 =  mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
   if (mysql_num_rows($result2) == 1){
   	$sql = "UPDATE 	bestellvorschlaege set liefermenge = ".$menge." 
		WHERE produkt_id = ".mysql_escape_string($produkt_id)." 
   		AND gesamtbestellung_id = ".mysql_escape_string($bestell_id) ;
   //echo $sql."<br>";
   mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());

   }else {

   $sql ="SELECT *, produktpreise.id as preis_id FROM produktpreise, gesamtbestellungen 
   				WHERE produkt_id = ".mysql_escape_string($produkt_id)." 
   				AND gesamtbestellungen.id = ".mysql_escape_string($bestell_id)." 
				AND (zeitende >= bestellende OR ISNULL(zeitende))
				AND zeitstart <= bestellstart;" ;
   //echo $sql."<br>";
   $result2 =  mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
   if (mysql_num_rows($result2) > 1){
	    echo error(__LINE__,__FILE__,"Mehr als ein Preis");
	    return;
	 } else {
	    $preis_row = mysql_fetch_array($result2);
	    $sql = "INSERT INTO bestellvorschlaege 
	              (produkt_id, gesamtbestellung_id, produktpreise_id, liefermenge)
	            VALUES (".$produkt_id.",".
		    $bestell_id.",".
		    $preis_row['preis_id'].",".
		    $menge.")";
	    //echo $sql."<br>";
	    mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
	}
	}
	    //Dummy Eintrag in bestellzuordnung
	    $sql = "SELECT id FROM gruppenbestellungen
	    		WHERE gesamtbestellung_id = ".$bestell_id;
	    //echo $sql."<br>";
	    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
	    $row = mysql_fetch_array($result);
	    $sql2 = "INSERT INTO bestellzuordnung
	    		(produkt_id, gruppenbestellung_id, menge, art)
			VALUES (".$produkt_id.", ".$row['id'].", 0, 2)";
	    //echo $sql2."<br>";
	    mysql_query($sql2) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());

}
function getProduzentBestellID($bestell_id){
    $sql="SELECT DISTINCT lieferanten_id FROM bestellvorschlaege 
		INNER JOIN produkte ON (produkt_id = produkte.id)
		WHERE gesamtbestellung_id = ".$bestell_id;
    //echo $sql."<br>";
    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Preise nich aus DB laden..",mysql_error());
    if (mysql_num_rows($result) > 1)
	    echo error(__LINE__,__FILE__,"Mehr als ein Lieferant fuer Bestellung ".$bestell_id);
	 else {
	    $row = mysql_fetch_array($result);
	    return $row['lieferanten_id'];

	 }
}
function getProdukt($produkt_id){
   $sql = "SELECT * FROM produkte WHERE id = ".$produkt_id;
    //echo $sql."<br>";
    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Produkte nich aus DB laden..",mysql_error());
    return mysql_fetch_array($result);
}
function getProdukteVonLieferant($lieferant_id){
   $sql = "SELECT * FROM produkte WHERE lieferanten_id = ".$lieferant_id;
    //echo $sql."<br>";
    $result = mysql_query($sql) or error(__LINE__,__FILE__,"Konnte Produkte nich aus DB laden..",mysql_error());
    return $result;
}
function writeVerteilmengen_sql($gruppenMengeInGebinde, $gruppenbestellung_id, $produkt_id){
	if($gruppenMengeInGebinde > 0){
		$query = "INSERT INTO  bestellzuordnung (menge, produkt_id, gruppenbestellung_id, art) 
			  VALUES (".$gruppenMengeInGebinde.
			 ", ".$produkt_id.
			 ", ".$gruppenbestellung_id.", 2);";
		echo $query."<br>";
		mysql_query($query) or error(__LINE__,__FILE__,"Konnte Verteilmengen nicht in DB schreiben...",mysql_error());
	}
}

function changeLieferpreis_sql($preis_id, $produkt_id, $bestellung_id){
	$query = "UPDATE bestellvorschlaege 
		  SET produktpreise_id = ".mysql_escape_string($preis_id)."
		  WHERE produkt_id = ".mysql_escape_string($produkt_id)."
		  AND gesamtbestellung_id = ".mysql_escape_string($bestellung_id).";";
	//echo $query."<br>";
	mysql_query($query) or error(__LINE__,__FILE__,"Konnte Liefermengen nicht in DB ändern...",mysql_error());
}
function changeLiefermengen_sql($menge, $produkt_id, $bestellung_id){
	$query = "UPDATE bestellvorschlaege 
		  SET liefermenge = ".mysql_escape_string($menge)."
		  WHERE produkt_id = ".mysql_escape_string($produkt_id)."
		  AND gesamtbestellung_id = ".mysql_escape_string($bestellung_id).";";
	//echo $query."<br>";
	mysql_query($query) or error(__LINE__,__FILE__,"Konnte Liefermengen nicht in DB ändern...",mysql_error());
}
function changeVerteilmengen_sql($menge, $gruppen_id, $produkt_id, $bestellung_id){
	$query = "UPDATE bestellzuordnung 
		  INNER JOIN gruppenbestellungen ON (gruppenbestellungen.id = gruppenbestellung_id) 
		  SET menge = ".mysql_escape_string($menge)."
		  WHERE art = 2 
		  AND produkt_id = ".mysql_escape_string($produkt_id)."
		  AND bestellguppen_id = ".mysql_escape_string($gruppen_id)."
		  AND gesamtbestellung_id = ".mysql_escape_string($bestellung_id).";";
	//echo $query."<br>";
	mysql_query($query) or error(__LINE__,__FILE__,"Konnte Verteilmengen nicht in DB ändern...",mysql_error());
}
function check_bereitsVerteilt($bestell_id){
	$query = "SELECT  *, gruppenbestellungen.id as gruppenbest_id,
	bestellzuordnung.id as bestellzuordnung_id 
	FROM gruppenbestellungen INNER JOIN bestellzuordnung 
	ON (bestellzuordnung.gruppenbestellung_id = gruppenbestellungen.id)
	WHERE gruppenbestellungen.gesamtbestellung_id = ".mysql_escape_string($bestell_id)." 
	AND bestellzuordnung.art=2 ;";
	//echo "<p>".$query."</p>";
	$result = mysql_query($query) or error(__LINE__,__FILE__,"Konnte Bestellmengen nich aus DB laden..",mysql_error());
	if(mysql_num_rows($result)==0) return false;
	return true;
}

function verteilmengenZuweisen($bestell_id){
  // nichts tun, wenn keine Bestellung ausgewählt
  if($bestell_id==""){
  	echo "<h2>Bitte Bestellung auswählen!!</h2>";
	return;
  }
  // Gleich aussteigen, wenn zuweisung bereits erfolgt
  if(check_bereitsVerteilt($bestell_id)) return;

  //row_gesamtbestellung einlesen aus Datenbank
  //benötigt für Bestellstart und Ende
  $row_gesamtbestellung = bestell_zeile($bestell_id);

  $gruppen = sql_gruppen($bestell_id);
  while ($gruppe_row = mysql_fetch_array($gruppen)){
     //Diese loops sind noch nicht sauber verschachtelt.
     //Eigentlich könnte man sich die Gruppenmengen in Array merken
     //und damit weiterrechnen. Dazu sind aber im Moment zuviele
     //Variablen da, die ich nicht verstehe.
     $gruppen_id = $gruppe_row['id'];
     //echo "Bearbeite Gruppe (".$gruppen_id.") ".$gruppe_row['name'];
     // Produkte auslesen & Tabelle erstellen...
     $result = sql_bestellprodukte($bestell_id);
				    

	$produkt_counter = 0;
	$bestellungDurchfuehren = true;   
			 
	while ($produkt_row = mysql_fetch_array($result)) {

	   unset($gebindegroessen);
	   unset($gebindepreis);
			 
	    // Gebindegroessen und Preise des Produktes auslesen...
	    $preise = sql_produktpreise($produkt_row['produkt_id'],
	    				$row_gesamtbestellung['bestellstart'],
	    				$row_gesamtbestellung['bestellende']);
	    $i = 0;
	    while ($row = mysql_fetch_array($preise)) {
		   $gebindegroessen[$i]=$row['gebindegroesse'];
	 	   $gebindepreis[$i]=$row['preis'];
	 	   $i++;

	    }			 


	    // Bestellmengenzähler setzen
	    $gesamtBestellmengeFest[$produkt_row['produkt_id']] = 0;
   	    $gesamtBestellmengeToleranz[$produkt_row['produkt_id']] = 0;
	    $gruppenBestellmengeFest[$produkt_row['produkt_id']] = 0;
	    $gruppenBestellmengeToleranz[$produkt_row['produkt_id']] = 0;
	    $gruppenBestellmengeFestInBerstellung[$produkt_row['produkt_id']] = 0;
	    $gruppenBestellmengeToleranzInBerstellung[$produkt_row['produkt_id']] = 0;
	    unset($gruppenBestellintervallUntereGrenze);
	    unset($gruppenBestellintervallObereGrenze);
	    unset($bestellintervallId);
					
					
	    // Hier werden die aktuellen festen Bestellmengen ausgelesen...
	    $bestellmengen = sql_bestellmengen($bestell_id,
	    $produkt_row['produkt_id'],0);
	    $intervallgrenzen_counter = 0;								
	    while ($einzelbestellung_row = mysql_fetch_array($bestellmengen)) {
		if ($einzelbestellung_row['bestellguppen_id'] == $gruppen_id) {
		    $gruppenbestellung_id = $einzelbestellung_row['gruppenbest_id'];
		    $ug = $gruppenBestellintervallUntereGrenze[$produkt_row['produkt_id']][$intervallgrenzen_counter] = $gesamtBestellmengeFest[$produkt_row['produkt_id']] + 1;
		    $og = $gruppenBestellintervallObereGrenze[$produkt_row['produkt_id']][$intervallgrenzen_counter] = $gesamtBestellmengeFest[$produkt_row['produkt_id']] + $einzelbestellung_row['menge'];
		    $bestellintervallId[$produkt_row['produkt_id']][$intervallgrenzen_counter] = $einzelbestellung_row['bestellzuordnung_id'];
								
		    $intervallgrenzen_counter++;
		    $gruppenBestellmengeFest[$produkt_row['produkt_id']] += $einzelbestellung_row['menge'];
		}
		$gesamtBestellmengeFest[$produkt_row['produkt_id']] += $einzelbestellung_row['menge'];
	   }
					
	   $gesamteBestellmengeAnfang = $gesamtBestellmengeFest[$produkt_row['produkt_id']];

	   unset($toleranzenNachGruppen);
	   // Hier werden die aktuellen toleranz Bestellmengen ausgelesen...
	   $bestellmengen = sql_bestellmengen($bestell_id, $produkt_row['produkt_id'],1);
	   $toleranzBestellungId = -1;
	   while ($einzelbestellung_row = mysql_fetch_array($bestellmengen)) {						
	 	if ($einzelbestellung_row['bestellguppen_id'] == $gruppen_id) {
		    $gruppenBestellmengeToleranz[$produkt_row['produkt_id']] += $einzelbestellung_row['menge'];
		    $toleranzBestellungId =  $einzelbestellung_row['bestellzuordnung_id'];
		}
		$gesamtBestellmengeToleranz[$produkt_row['produkt_id']] += $einzelbestellung_row['menge'];
						 
		// für jede Gruppe getrennt die Toleranzmengen ablegen
	 	$bestellgruppen_id = $einzelbestellung_row['bestellguppen_id'];
		if (!isset($toleranzenNachGruppen[$bestellgruppen_id])) $toleranzenNachGruppen[$bestellgruppen_id] = 0;
		$toleranzenNachGruppen[$bestellgruppen_id] += $einzelbestellung_row['menge'];
						 
	  }
					
	  if (isset($toleranzenNachGruppen)) ksort($toleranzenNachGruppen);
					
	  // jetzt die Gebindeaufteilung berechnen
	  unset($gruppenMengeInGebinde);
	  unset($festeGebindeaufteilung);
				
	  $rest_menge = $gesamtBestellmengeFest[$produkt_row['produkt_id']]; 
	  $gesamtMengeBestellt = 0;
	  $gruppeGesamtMengeInGebinden = 0;
 	  for ($i=0; $i < count($gebindegroessen); $i++) {
	      $festeGebindeaufteilung[$i] = floor($rest_menge / $gebindegroessen[$i]);
	      $rest_menge = $rest_menge % $gebindegroessen[$i];
					 
	      // berechne: wieviel  hat die aktuelle Gruppe in diesem Gebinde
	      $gebindeAnfang = $gesamtMengeBestellt + 1;
	      $gesamtMengeBestellt += $festeGebindeaufteilung[$i] * $gebindegroessen[$i];
					 
	      $gruppenMengeInGebinde[$i] = 0;
					 
	      if ($festeGebindeaufteilung[$i] > 0 && isset($gruppenBestellintervallUntereGrenze[$produkt_row['produkt_id']])) {
		   for ($j=0; $j < count($gruppenBestellintervallUntereGrenze[$produkt_row['produkt_id']]); $j++) {
			$ug = $gruppenBestellintervallUntereGrenze[$produkt_row['produkt_id']][$j];
			$og = $gruppenBestellintervallObereGrenze[$produkt_row['produkt_id']][$j];
			$gebindeEnde = $gesamtMengeBestellt;

			if ($ug >= $gebindeAnfang && $ug <= $gebindeEnde) {  // untere Grenze des Bestellintervalls im aktuellen Gebinde...
			     if ($og >= $gebindeAnfang && $og <= $gebindeEnde)   { // und die obere Grenze auch dann...
				$gruppenMengeInGebinde[$i] += 1 + $og - $ug;
			     } else {   // und die obere Grenze nicht, dann ...
				$gruppenMengeInGebinde[$i] += 1 + $gebindeEnde - $ug;    // alles bis zum Intervallende
			     }
			} else if ($og >= $gebindeAnfang && $og <= $gebindeEnde) {  // die obere Grenze des Bestellintervalls im aktuellen Gebinde, und die untere nicht, dann...
				$gruppenMengeInGebinde[$i] += 1 + $og - $gebindeAnfang;    // alles ab Intervallanfang bis obere Grenze
			} else if ($ug < $gebindeAnfang && $og > $gebindeEnde) { //die untere Grenze des Bestellintervalls unterhalb und die obere oberhalb des aktuellen Gebindes, dann..
			 	$gruppenMengeInGebinde[$i] += 1 + $gebindeEnde - $gebindeAnfang;    // das gesamte Gebinde
			}
		   }
	      }
	      $gruppeGesamtMengeInGebinden += $gruppenMengeInGebinde[$i];
	  }
				
	  // versuche offenes Gebinde mit Toleranzmengen zu füllen							
	  $gruppenToleranzInGebinde     = 0;
	  $toleranzGebNr = -1;
		
	  if ($rest_menge != 0) {
		$fuellmenge = $gebindegroessen[count($gebindegroessen)-1] - $rest_menge;
		if (isset($toleranzenNachGruppen) && $fuellmenge <= $gesamtBestellmengeToleranz[$produkt_row['produkt_id']]) {
			//echo "<p>toleranzenNachGruppen: ".$toleranzenNachGruppen."</p>";
			//echo "<p>isset(toleranzenNachGruppen): ";
			//if(isset($toleranzenNachGruppen)) echo "true";
			//else echo "false";
			//echo "</p>";
			reset($toleranzenNachGruppen);
			do {
			    while (!(list($key, $value) = each($toleranzenNachGruppen))) reset($toleranzenNachGruppen);   // neue Wete auslesen und ggf. wieder am Anfang des Arrays starten

			    if ($value > 0) { 
				$toleranzenNachGruppen[$key] --;
				$fuellmenge--;
				if ($key == $gruppen_id) $gruppenToleranzInGebinde++;
			    }
										
										
			} while($fuellmenge > 0);
								 
			// das "toleranzgefüllte" Gebinde anzeigen
			$toleranzGebNr = count($festeGebindeaufteilung)-1;
								 
			$festeGebindeaufteilung[count($festeGebindeaufteilung)-1]++;
			$gruppenMengeInGebinde[$toleranzGebNr] += $gruppenBestellmengeFest[$produkt_row['produkt_id']]  - $gruppeGesamtMengeInGebinden;
			$gruppenMengeInGebinde[$toleranzGebNr] += $gruppenToleranzInGebinde;
			$gruppeGesamtMengeInGebinden = $gruppenBestellmengeFest[$produkt_row['produkt_id']];
			$toleranzFuellung = count($gebindegroessen) -1;
								 
			// Gebindeaufteillung an Toleranzfüllung anpassen...
			$anzInAktGeb = $festeGebindeaufteilung[$toleranzGebNr] * $gebindegroessen[$toleranzGebNr];											 

			for ($i = count($gebindegroessen)-2; $i >= 0 ; $i--)
			if (($anzInAktGeb % $gebindegroessen[$i]) == 0) {
			   	$gruppenMengeInGebinde[$i] += $gruppenMengeInGebinde[$toleranzGebNr];
				$gruppenMengeInGebinde[$toleranzGebNr] = 0;
				$festeGebindeaufteilung[$i] += floor($anzInAktGeb / $gebindegroessen[$i]);
				$festeGebindeaufteilung[$toleranzGebNr] = 0;
				$toleranzGebNr = $i;
				$anzInAktGeb = $festeGebindeaufteilung[$toleranzGebNr] * $gebindegroessen[$toleranzGebNr];
			}
								 
		}
	  }

	$gruppenToleranzNichtInGebinde =
	$gruppenBestellmengeToleranz[$produkt_row['produkt_id']] - $gruppenToleranzInGebinde;
	$gruppeGesamtMengeNichtInGebinden =
	$gruppenBestellmengeFest[$produkt_row['produkt_id']]  - $gruppeGesamtMengeInGebinden;
	
	//Hier können Verteilmengen geschrieben werden
	writeVerteilmengen_sql($gruppeGesamtMengeInGebinden, $gruppenbestellung_id, $produkt_row['produkt_id']);
     }
  }
  	writeLiefermenge_sql($bestell_id);
	drop_basar($bestell_id);
}

?>
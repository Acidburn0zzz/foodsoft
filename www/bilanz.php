<?php
  //
  // bilanz.php
  //

  error_reporting(E_ALL);

  if( ! $angemeldet ) {
    exit( "<div class='warn'>Bitte erst <a href='index.php'>Anmelden...</a></div>");
  } 

  ?> <h1>Bilanz - <blink>Achtung, in Arbeit: Werte stimmen nicht!</blink></h1> <?

  // aktiva berechnen:
  //

  if( ! isset( $inventur_datum ) )
    $inventur_datum = "(keine)";
  if( ! isset( $inventur_pfandwert ) )
    $inventur_pfandwert = 0.0;


  $basar_wert = 0.0;
  $basar = sql_basar();

  while( $row = mysql_fetch_array( $basar ) ) {
    // print_r( $row );
    $basar_wert += $row['basar'] * $row['preis'];
  }

  $basar_2 = kontostand( $basar_id );

  $abschreibung = kontostand( $muell_id );

  $result = doSQL( "
    SELECT sum( summe ) as summe
    FROM gruppen_transaktion
    WHERE (type=0) and (kontoauszugs_nr<=0)
  " );
  $row = mysql_fetch_array( $result );
  $gruppen_einzahlungen_ungebucht = $row['summe'];
  

  $erster_posten = 1;
  function rubrik( $name ) {
    global $erster_posten;
    echo "
      <tr class='rubrik'>
        <th colspan='2'>$name</th>
      </tr>
    ";
    $erster_posten = 1;
  }
  function posten( $name, $wert ) {
    global $erster_posten, $seitensumme;
    $class = ( $wert < 0 ? 'rednumber' : 'number' );
    printf( "
      <tr class='%s'>
        <td>%s:</td>
        <td class='$class'>%.2lf</td>
      </tr>
      "
    , $erster_posten ? 'ersterposten' : 'posten'
    , $name, $wert
    );
    $erster_posten = 0;
    $seitensumme += $wert;
  }

  echo "
    <table width='100%'>
      <colgroup>
        <col width='*'><col width='*'>
      </colgroup>
      <tr><th> Aktiva </th><th> Passiva </th></tr>
      <tr>
        <td>

        <table class='inner' width='100%'>
  ";


  $seitensumme = 0;

  rubrik( "Bankguthaben" );
    $kontosalden = sql_bankkonto_salden();
    while( $konto = mysql_fetch_array( $kontosalden ) ) {
      posten( "
        <a href=\"javascript:neuesfenster('index.php?window=konto&konto_id={$konto['konto_id']}','konto');\"
        >Konto {$konto['name']}</a>"
      , $konto['saldo']
      );
    }
    
    posten( "Ungebuchte Einzahlungen", $gruppen_einzahlungen_ungebucht );

  rubrik( "Umlaufvermögen" );
    posten( "Warenbestand Basar", $basar_wert );
    posten( "Bestand Pfandverpackungen", $inventur_pfandwert );

  rubrik( "Forderungen" );
    posten( "Forderungen an Gruppen", forderungen_gruppen_summe() );


  $aktiva = $seitensumme;


  //
  // ab hier passiva:
  //
  echo "
      </table>
      </td><td>

      <table class='inner' width='100%'>
  ";

  $seitensumme = 0;
  

  rubrik( "Einlagen der Gruppen" );
    posten( "Sockeleinlagen", sockel_gruppen_summe() );
    posten( "Kontoguthaben", guthaben_gruppen_summe() );

  $verbindlichkeiten = sql_verbindlichkeiten_lieferanten();
  rubrik( "Verbindlichkeiten" );
    while( $vkeit = mysql_fetch_array( $verbindlichkeiten ) ) {
      posten( $vkeit['name'], $vkeit['soll'] );
    }

  $passiva = $seitensumme;

  $bilanzverlust = $aktiva - $passiva;
  $passiva += $bilanzverlust;

  rubrik( "Bilanzausgleich" );
    posten( ( $bilanzverlust > 0 ) ? "Bilanzüberschuss" : "Bilanzverlust", $bilanzverlust );

  echo "
        </table>
        </td>
      </tr>
  ";

  printf ("
      <tr class='summe'>
        <td class='number'>%.2lf</td>
        <td class='number'>%.2lf</td>
      </tr>
    "
  , $aktiva
  , $passiva
  );

  echo "</table>";

?>

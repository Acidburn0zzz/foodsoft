<?php
//
// verteilung.php
//
// zeigt verteilung eines oder aller produkte einer bestellung auf die gruppen an
// und erlaubt aenderung der verteilmengen.

//error_reporting(E_ALL); // alle Fehler anzeigen

assert( $angemeldet ) or exit();
need_http_var('bestell_id','u',true);
get_http_var('produkt_id','u',0, true);

$status = getState( $bestell_id );

$editable = ( $status == STATUS_VERTEILT and hat_dienst(1,3,4,5) and ! $readonly );
$ro_tag = ( $editable ? '' : 'readonly' );

nur_fuer_dienst(1,3,4,5);

setWikiHelpTopic( "foodsoft:verteilung" );

if( $produkt_id ) {
  ?> <h1>Produktverteilung</h1> <?
} else {
  ?> <h1>Verteilliste</h1> <?
}
bestellung_overview( sql_bestellung( $bestell_id ) );


// aktionen verarbeiten; hier: liefer/verteilmengen aendern:
//
get_http_var( 'action', 'w', '' );
$editable or $action = '';
switch( $action ) {
  case 'update_distribution':
    update_distribution( $bestell_id, $produkt_id );
    break;
}

function update_distribution( $bestell_id, $produkt_id ) {
  $produkte = sql_bestellprodukte( $bestell_id, 0, $produkt_id );

  while( $produkt = mysql_fetch_array( $produkte ) ) {
    preisdatenSetzen( & $produkt );
    $produkt_id = $produkt['produkt_id'];
    $verteilmult = $produkt['kan_verteilmult'];
    $verteileinheit = $produkt['kan_verteileinheit'];
    $preis = $produkt['preis'];
    $liefermenge = $produkt['liefermenge'] * $verteilmult;

    $feldname = "liefermenge_{$bestell_id}_{$produkt_id}";
    global $$feldname;
    if( get_http_var( $feldname, 'f' ) ) {
      $liefermenge_form = $$feldname;
      if( $liefermenge != $liefermenge_form ) {
        changeLiefermengen_sql( $liefermenge_form / $verteilmult, $produkt_id, $bestell_id );
      }
    }

    $gruppen = sql_beteiligte_bestellgruppen( $bestell_id, $produkt_id );
    while( $gruppe = mysql_fetch_array( $gruppen ) ) {
      $gruppen_id = $gruppe['id'];
      $mengen = sql_select_single_row( select_bestellprodukte( $bestell_id, $gruppen_id, $produkt_id ), true );
      if( $mengen ) {
        $toleranzmenge = $mengen['toleranzbestellmenge'] * $verteilmult;
        $festmenge = $mengen['gesamtbestellmenge'] * $verteilmult - $toleranzmenge;
        $verteilmenge = $mengen['verteilmenge'] * $verteilmult;
      } else {
        $toleranzmenge = 0;
        $festmenge = 0;
        $verteilmenge = 0;
      }
      $feldname = "menge_{$bestell_id}_{$produkt_id}_{$gruppen_id}";
      global $$feldname;
      if( get_http_var( $feldname, 'f' ) ) {
        $menge_form = $$feldname;
        if( $verteilmenge != $menge_form ) {
          changeVerteilmengen_sql( $menge_form / $verteilmult, $gruppen_id, $produkt_id, $bestell_id );
        }
      }
    }
  }
}

open_div('bigskip');

if( ! $ro_tag ) {
  open_form('','','', array('action' => 'update_distribution') );
  floating_submission_button();
}

open_table('list');
  distribution_tabellenkopf(); 

  $produkte = sql_bestellprodukte( $bestell_id, 0, $produkt_id );

  while( $produkt = mysql_fetch_array( $produkte ) ) {
    if( ( $produkt['liefermenge'] < 0.5 ) and ( $produkt['verteilmenge'] < 0.5 ) )
      continue;
    $produkt_id = $produkt['produkt_id'];

    distribution_produktdaten( $bestell_id, $produkt_id );
    distribution_view( $bestell_id, $produkt_id, ! $ro_tag );
    open_tr();
      open_td( '', "colspan='6'", '&nbsp;' );
  }

close_table();

if( ! $ro_tag ) {
  floating_submission_button( 'reminder' );
  close_form();
}

close_div();

?>

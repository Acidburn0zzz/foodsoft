<?PHP

assert($angemeldet) or exit();
$editable = ! $readonly;
 
get_http_var( 'meinkonto', 'u', 0, true );

if( $meinkonto ) {
  setWikiHelpTopic( 'foodsoft:MeinKonto' );
  $gruppen_id = $login_gruppen_id;
  $self_fields['gruppen_id'] = $gruppen_id;
  $gruppen_name = sql_gruppenname( $gruppen_id );
  ?> <h1>Mein Konto: Kontoausz&uuml;ge von Gruppe <? echo $gruppen_name; ?></h1> <?

  if( ! $readonly ) {

    open_fieldset( 'small_form', '', 'Überweisung eintragen', 'off' );
      open_form( '', "action=einzahlung" );
        open_table('layout');
          form_row_betrag( 'Ich habe heute ' ); echo ' Euro fuer unsere Gruppe '. gruppe_view( $login_gruppen_id ); submission_button( 'überwiesen' );
        close_table();
      close_form();
    close_fieldset();
    medskip();

    open_div('alert', "style='padding:1ex 0ex 1ex 0ex;'" );
      open_fieldset( 'small_form', '', 'Spende an die Foodcoop', 'off' );
        open_form( '', "action=spende" );
          open_table('layout');
            form_row_gruppe( 'Unsere Gruppe', false, $gruppen_id );
            form_row_betrag( 'spendet der Foodcoop' ); echo " Euro!";
            form_row_text( 'Anmerkungen:', 'notiz', 60, 'Spende zum Schuldenabbau' );
            qquad();
            submission_button( 'Speichern' );
          close_table();
        close_form();
      close_fieldset();
    close_div();
    medskip();

    get_http_var( 'action', 'w', '' );
    $editable or $action = '';
    switch( $action ) {
      case 'einzahlung':
        need_http_var( 'betrag', 'f' );
        sql_gruppen_transaktion( 0, $login_gruppen_id, $betrag, "Einzahlung" );
        break;
      case 'spende':
        need_http_var( 'betrag', 'f' );
        if( $betrag <= 0 )
          break;
        get_http_var( 'notiz', 'H', 'Spende' );
        sql_doppelte_transaktion(
          array( 'konto_id' => -1, 'gruppen_id' => sql_muell_id(), 'transaktionsart' => TRANSAKTION_TYP_SPENDE )
        , array( 'konto_id' => -1, 'gruppen_id' => $gruppen_id, 'transaktionsart' => TRANSAKTION_TYP_SPENDE )
        , $betrag
        , $mysqlheute
        , $notiz
        , true
        );
        open_javascript( "alert( 'Spende ist eingegangen, vielen Dank!' );" );
        break;
    }
  }

} else { // kontoblatt-anzeige fuer dienste
  nur_fuer_dienst(4,5);
  setWikiHelpTopic( 'foodsoft:kontoblatt' );
  get_http_var( 'gruppen_id', 'u', 0, true );
  ?> <h1>Kontoblatt</h1> <?

  if( ! $readonly ) {
    get_http_var( 'action', 'w', '' );
    switch( $action ) {
      case 'finish_transaction':
        action_finish_transaction();
        break;
      case 'buchung_gruppe_sonderausgabe':
        action_buchung_gruppe_sonderausgabe();
        break;
      case 'buchung_gruppe_bank':
        action_buchung_gruppe_bank();
        break;
      case 'buchung_gruppe_lieferant':
        action_buchung_gruppe_lieferant();
        break;
      case 'umbuchung_gruppe_gruppe':
        action_buchung_gruppe_gruppe();
        break;
      case 'buchung_gruppe_anfangsguthaben':
        action_buchung_gruppe_anfangsguthaben();
        break;
    }
  }

  open_table( 'menu' );
      open_th( '', "colspan='2'", 'Optionen' );
    open_tr();
      open_td('', '', 'Gruppe:' );
      open_td();
        open_select( 'gruppen_id', true );
          echo optionen_gruppen( $gruppen_id );
        close_select();
  close_table();
  medskip();

  if( ! $gruppen_id )
    return;

  $gruppen_name = sql_gruppenname( $gruppen_id );

  if( ! $readonly ) {
    open_fieldset( 'small_form', '', 'Transaktionen', 'off' );

      ?> <h4>Art der Transaktion:</h4> <?

      alternatives_radio( array(
        'gruppe_bank_form' => array( 'Einzahlung oder Auszahlung'
                                   , 'Einzahlung auf oder Auszahlung von Bankkonto der Foodcoop' )
      , 'gruppe_gruppe_form' => array( 'Transfer an andere Gruppe'
                                   , 'überweisung auf ein anderes Gruppenkonto' )
      , 'gruppe_lieferant_form' => array( 'Zahlung von Gruppe an Lieferant'
                                   , 'überweisung von Gruppe an Lieferant' )
      , 'sonderausgabe_gruppe_form' => array( 'Sonderausgabe durch Gruppe'
                                        , 'Sonderausgabe durch Gruppe (z.B. Geschenkkauf)' )
      , 'anfangsguthaben_gruppe_form' => array( 'Erfassung Anfangsguthaben Gruppe'
                                        , 'Anfangsguthaben (bei Umstellung auf Foodsoft) erfassen' )
      ) );

      open_div( 'nodisplay', "id='gruppe_bank_form'" );
        formular_buchung_gruppe_bank();
      close_div();

      open_div( 'nodisplay', "id='gruppe_gruppe_form'" );
        formular_buchung_gruppe_gruppe();
      close_div();

      open_div( 'nodisplay', "id='gruppe_lieferant_form'" );
        formular_buchung_gruppe_lieferant();
      close_div();

      open_div( 'nodisplay', "id='sonderausgabe_gruppe_form'" );
        formular_buchung_gruppe_sonderausgabe();
      close_div();

      open_div( 'nodisplay', "id='anfangsguthaben_gruppe_form'" );
        formular_buchung_gruppe_anfangsguthaben();
      close_div();

    close_fieldset();
    medskip();
  }
}

$kontostand = kontostand($gruppen_id);
$pfandkontostand = pfandkontostand($gruppen_id);

// aktuelle Gruppendaten laden
$bestellgruppen_row = sql_gruppendaten( $gruppen_id );
	
// wieviele Kontenbewegungen werden ab wo angezeigt...
if (isset($HTTP_GET_VARS['start_pos'])) $start_pos = $HTTP_GET_VARS['start_pos']; else $start_pos = 0;
//Funktioniert erstmal mit der Mischung aus Automatischer Berechung und manuellen Eintr�gen nicht
//FIXME: vielleicht ggf. start/enddatum waehlbar machen? oder immer ganze jahre?
$size          = 2000;
	 
	
$cols = 9;
open_table('list');
    open_th( '', '', 'Typ' );
    open_th( '', '', 'Valuta' );
    open_th( '', '', 'Buchung' );
    open_th( '', '', 'Informationen' );
    open_th( '', '', 'Pfand Kauf' );
    open_th( 'bottom', '', 'Rückgabe' );
    open_th( '', '', 'Pfandkonto' );
    open_th( '', '', 'Waren' );
    open_th( '', '', 'Buchung' );
    open_th( '', '', 'Kontostand' );
  open_tr( 'summe' );
    open_td( 'right', "colspan='6'", 'Kontostand:' );
    open_td( 'number', '', price_view( $pfandkontostand ) );
    open_td();
    open_td();
    open_td( 'number', '', price_view( $kontostand ) );

  $konto_result = sql_get_group_transactions( $gruppen_id, 0 );
  $num_rows = count($result);

  $vert_result = sql_bestellungen_soll_gruppe($gruppen_id);
  $summe = $kontostand;
  $pfandsumme = $pfandkontostand;
  $konto_row = current($konto_result);
  $vert_row = current($vert_result);
  while( $vert_row or $konto_row ) {
    open_tr();

    //Mische Eintr�ge aus Kontobewegungen und Verteilzuordnung zusammen
    if( ( $vert_row ? $vert_row['valuta_kan'] : '0' ) > ( $konto_row ? $konto_row['valuta_kan'] : '0' ) ) {

      $pfand_leer_soll = $vert_row['pfand_leer_brutto_soll'];
      $pfand_voll_soll = $vert_row['pfand_voll_brutto_soll'];
      $pfand_soll = $pfand_leer_soll + $pfand_voll_soll;
      $waren_soll = $vert_row['waren_brutto_soll'];
      $soll = $pfand_soll + $waren_soll;
      $have_pfand = false;

      open_td('bold', '', 'Bestellung' );
      open_td('', '', $vert_row['valuta_trad'] );
      open_td('', '', $vert_row['lieferdatum_trad'] );
      open_td('', '', 'Bestellung '. fc_link( 'lieferschein', array(
        'class' => 'href', 'text' => $vert_row['name'], 'title' => 'zum Lieferschein...'
      , 'bestell_id' => $vert_row['gesamtbestellung_id'] , 'gruppen_id' => $gruppen_id
      , 'spalten' => ( PR_COL_NAME | PR_COL_BESTELLMENGE | PR_COL_VPREIS | PR_COL_LIEFERMENGE | PR_COL_ENDSUMME )
      ) ) );
      open_td( 'number' );
        if( abs( $pfand_voll_soll ) > 0.005 ) {
          echo price_view( $pfand_voll_soll );
          $have_pfand = true;
        }
      open_td( 'number' );
        if( abs( $pfand_leer_soll ) > 0.005 ) {
          echo price_view( $pfand_leer_soll );
          $have_pfand = true;
        }
      open_td( 'number', '', $have_pfand ? price_view( $pfandsumme ) : '' );
      open_td( 'number', '', price_view( $waren_soll ) );
      open_td( 'number bold', '', price_view( $soll ) );
      open_td( 'number', '', price_view( $summe ) );

      $summe -= $soll;
      $pfandsumme -= $pfand_soll;
      $vert_row = next($vert_result);
    } else {
      $k_id = $konto_row['konterbuchung_id'];
      open_td( 'bold' );
        if( $k_id >= 0 ) {
          $text = ( $konto_row['summe'] > 0 ? 'Einzahlung' : 'Auszahlung' );
        } else {
          $text = 'Verrechnung';
        }
        echo $k_id ? fc_link( 'edit_buchung', "class=href,transaktion_id={$konto_row['id']},text=$text" ) : $text;
      open_td('', '', $konto_row['valuta_trad'] );
       open_td( '', '', $konto_row['date'] ."<div class='small'>{$konto_row['dienst_name']}</div>" );
      open_td();
        open_div( '', '', $konto_row['notiz'] );
        if( $k_id ) {
          buchung_kurzinfo( $k_id );
        } else {
          if( $meinkonto ) {
            div_msg( 'alert', 'noch nich verbucht' );
          } else {
            form_finish_transaction( $konto_row['id'] );
          }
        }
        open_td();
        open_td();
        open_td();
        open_td();
        open_td( 'number bold', '', price_view( $konto_row['summe'] ) );
        open_td( 'number', '', price_view( $summe ) );

      $summe -= $konto_row['summe'];
      $konto_row = next($konto_result);
    }
  }
  open_tr( 'summe' );
    open_td( 'right', "colspan='6'", 'Startsaldo:' );
    open_td( 'number', '', price_view( $pfandsumme ) );
    open_td();
    open_td();
    open_td( 'number', '', price_view( $summe ) );

close_table();

if( 0 ) { // noch ausser betrieb
  ?>
	 <form name="skip" action="showGroupTransaktions.php">
	    <input type="hidden" name="gruppen_id" value="<?PHP echo $gruppen_id; ?>">
			<input type="hidden" name="gruppen_pwd" value="<?PHP echo $gruppen_pwd; ?>">
			<input type="hidden" name="start_pos" value="<?PHP echo $start_pos; ?>">
			<?PHP 
			   $downButtonScript = "";
			   if ($start_pos > 0 && $start_pos > $size)
				    $downButtonScript="document.forms['skip'].start_pos.value=".($start_pos-$size).";";
				 else if ($start_pos > 0)
				    $downButtonScript="document.forms['skip'].start_pos.value=0;";
						
				 if ($downButtonScript != "")
				    echo "<input type=button value='<' onClick=\"".$downButtonScript." ;document.forms['skip'].submit();\">";

			   $upButtonScript = "";
			   if ($num_rows == $size)
				    $upButtonScript="document.forms['skip'].start_pos.value=".($start_pos+$size).";";

				 if ($upButtonScript != "") echo "<input type=button value='>' onClick=\"".$upButtonScript.";document.forms['skip'].submit()\"";
			?>
	 </form>
   <?
}

?>

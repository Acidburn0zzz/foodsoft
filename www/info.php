<?PHP
   // �bergebene Variablen einlesen..
   if (isset($HTTP_GET_VARS['info_pwd'])) $info_pwd = $HTTP_GET_VARS['info_pwd'];       // Passwort f�r den Bereich
	 
	 // Passwort pr�fen...
	 $pwd_ok = ($info_pwd == $real_info_pwd);
?>



<h2>FoodCoop Kreuzberg-Neuk�lln</h2>

  <?PHP
	 
	    // Wenn kein Passwort f�r die Bestellgruppen-Admin angegeben wurde, dann abfragen...
			if (!isset($info_pwd) || !$pwd_ok) {
	?>
				 <form action="index.php">
				    <input type="hidden" name="area" value="info">
				    <b>Bitte Zungangspasswort angeben:</b><br>
						<input type="password" size="12" name="info_pwd"><input type="submit" value="ok">						
				 </form>
	<?PHP
			} else	{
  ?>

<h3>=> Gruppen�bersicht <=</h3>
<BR>
<img src="adrliste.jpg">

<?PHP
    }
?>

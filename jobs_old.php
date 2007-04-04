<?PHP
   // �bergebene Variablen einlesen...
   if (isset($HTTP_GET_VARS['jobcenter_pwd'])) $jobcenter_pwd = $HTTP_GET_VARS['jobcenter_pwd'];       // Passwort f�r den Bereich
	 
	 // Passwort pr�fen...
	 $pwd_ok = ($jobcenter_pwd == $real_jobcenter_pwd);
?>



<h2>FoodCoop Kreuzberg-Neuk�lln</h2>

  <?PHP
	 
	    // Wenn kein Passwort f�r die Bestellgruppen-Admin angegeben wurde, dann abfragen...
			if (!isset($jobcenter_pwd) || !$pwd_ok) {
	?>
				 <form action="index.php">
				    <input type="hidden" name="area" value="jobs">
				    <b>Bitte Zungangspasswort angeben:</b><br>
						<input type="password" size="12" name="jobcenter_pwd"><input type="submit" value="ok">						
				 </form>
	<?PHP
			} else	{
  ?>

<h3>=> Jobcenter <=</h3>
<br>
Und hier der vorl�ufige "Jobcenter". Bisher mu� ich euch noch per Hand eintragen... Deswegen einfach eine <b>Mail an <a href="mailto:job_center@gmx.de">job_center@gmx.de</a></b> mit dem Job den ihr
�bernehmt.<br>
Um euch untereinander abzusprechen, kann man die Namen in der Liste anklicken und sieht dann ggf. Kommentare und die Mail-Adressen der anderen!
<br><br>
<table border="1">
   <tr bgcolor="#BBBBBB">
	    <td rowspan="2" valign="bottom">Datum</td>
			<td colspan="2">Freitags</td>
			<td>Montag</td>
      <td>Dienstags</td>		
      <td>Mittwochs</td>
			<td colspan="2">Donnerstags</td>
	 </tr>
   <tr bgcolor="#DDDDDD">
		 <td>Palette Terra</td>
		 <td>Palette GEPA</td>
		 <td>Bestellgruppe GEPA</td>
		 <td>Bestellgruppe Terra</td>
		 <td>Annahme GEPA</td>
		 <td>Annahme</td>
		 <td>Abgabe</td>
	 </tr>
	 <tr>
	    <td valign="top" bgcolor="#DDDDDD">11. - 18.06.</td>
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n benjamin.meichsner[�t]gmx.de');" class="smalfont">Benni</a><br>
				 <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n kathrin[�t]dresdener27.de');" class="smalfont">Kathrin</a>
			</td>
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n hobitz[�t]gmx.de');" class="smalfont">Henning</a><br>
				 Felix			
			</td>
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n driemel[�t]inf.fu-berlin.de');" class="smalfont">Anne</a><br>
				 <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n f.bertsch[�t]jpberlin.de');" class="smalfont">Bertsch</a>			
			</td>
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n jaentzdunkel2[�t]aol.com');" class="smalfont">Jens</a><br>
                           <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n JennyRust[�t]gmx.de');" class="smalfont">Jenny</a><br>
				 <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n f.bertsch[�t]jpberlin.de');" class="smalfont">Bertsch</a>
			</td>	
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n p_night[�t]gmx.net');" class="smalfont">Philipp</a>
			</td>				
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n Lemmie52[�t]aol.com');" class="smalfont">Mareike</a><br>
				 <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n piepa[�t]gmx.net');" class="smalfont">Anton</a>
			</td>	
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n p_night[�t]gmx.net');" class="smalfont">Philipp</a>
			</td>
	 </tr>
	 <tr>
	    <td valign="top" bgcolor="#DDDDDD">19. - 26.06.</td>
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n benjamin.meichsner[�t]gmx.de');" class="smalfont">Benni</a><br>
				 <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n kathrin[�t]dresdener27.de');" class="smalfont">Kathrin</a>
			</td>
			   
			</td>
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]			
			</td>
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]					
			</td>
			<td class="smalfont" align="left" valign="top" >
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n nils@hardern.de');" class="smalfont">Nils</a>
			   <span style="color:#CC0000"><br>wer hilft ?</span>
			</td>			
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]			
			</td>		
			<td class="smalfont" align="left" valign="top">
			   <span style="color:#CC0000"> </span>
			   <a href="javascript:alert('Kommentar:\n - \n\n Mail:\n fernbedienung[�t]fernsehsofa.de');" class="smalfont">Max</a>
			</td>	
			<td class="smalfont" align="left" valign="top">
			   <a href="javascript:alert('Kommentar:\n von 14:30 bis 17:00 Uhr \n\n Mail:\n ms_sophie22[�t]hotmail.com');" class="smalfont">Sophie</a> <br> wer noch ?
			</td>
	 </tr>	 
	 	 <tr>
	    <td valign="top" bgcolor="#DDDDDD">26.06. - 02.07.</td>
			<td class="smalfont" align="left" valign="top">
			   -
			</td>
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]			
			</td>
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]					
			</td>
			<td class="smalfont" align="left" valign="top">
			   -
			</td>			
			<td class="smalfont" align="left" valign="top">
			   [nicht gebraucht]					
			</td>		
			<td class="smalfont" align="left" valign="top">
			   -
			</td>	
			<td class="smalfont" align="left" valign="top">
			   -
			</td>
	 </tr>	 
</table>

<?PHP
    }
?>

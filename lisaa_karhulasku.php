  <!doctype html>

<?php
$rows = null;

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta
if (isset($_POST['karhulasku']))
{

   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan

   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);

   $laskuExistQuery = "select Numero from LASKU where Numero=".$_POST['karhulasku'].";";
   
   // Yhteys saatiin
   if ($conn_id)
   {
      // Suoritetaan kysely
      $rows = execQuery($conn_id, $laskuExistQuery);
      
      if (is_null($rows[0]["numero"]))
      {
        echo 'Sisäinen virhe, pyydettyä laskua ei löytynyt';
      }

      // Suljetaan yhteys. Ei välttämättä pakollinen, koska
      // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
      pg_close($conn_id);
   }
   else
   {
       echo 'Tietokantaan ei saada yhteyttä';
   }
}
?>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>TiKaSu Harkkatyö</title>
  <meta name="description" content="Tietokantojen suunnittelu, harkkatyö">

  <link rel="stylesheet" href="css/styles.css?v=1.0">
<style>
td{
  padding: 2%;
}
ul{
  padding: 5%;
}
p{
  padding: 5%;
}
table{
  width: 100%;
}

</style>
</head>

<body>
  <?php
  // PHP code goes here
  ?>

  <h1>Laskujen hallintajärjestelmä</h1>
  <table>
    <tr>
      <td>
<?php


if (isset($_POST['karhulasku']))
{
echo '<h2>Lisää karhulasku</h2> 

<p>
<form method="post" action="lisaa_karhulasku.php" name="poistalaskukylla">

<label for="viitenr">Viitenumero:</label>
<input type="number" name="viitenr" value="00000">
<label for="erapaiva">Eräpäivä:</label>
<input type="date" name="erapaiva" value="1.1.2010">
<label for="viivastysmaksu">Viivästysmaksu:</label>
<input type="number" name="viivastysmaksu" value="00000">
<input type="hidden" name="laskunro" value="'.$rows[0]["numero"].'">

<input type="submit" name="lisaakarhulasku" value="Lisää karhulasku">
</form><input type="button" value="Takaisin" onclick="window.history.back()" /></p>';


}

?>
      </td>
    </tr>
  </table>
</body>
</html>

<?php
if (isset($_POST['lisaakarhulasku']))
{
    
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan
   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);
   
   // Yhteys saatiin
   if ($conn_id)
   {
      //Alkuperäisen laskun tietoja karhulaskua varten
      $laskuQuery = "select Summa, Mainoskampanja, Tilinumero from LASKU where Numero=".$_POST['laskunro'].";";
      $laskuntiedot = execQuery($conn_id, $laskuQuery);
      
      if(!empty($laskuntiedot))
      {
      //Luo karhulasku ja palauta sen Numero
      $insertKarhulaskuQuery = "insert into LASKU (Viitenumero, Summa, Viivastysmaksu, Erapaiva, Aktiivinen, Mainoskampanja, Karhulasku, Tilinumero)
      values ('".htmlspecialchars($_POST['viitenr'])."','".$laskuntiedot[0]["summa"]."',".$_POST['viivastysmaksu'].",'".$_POST['erapaiva']."','Aktiivinen',".$laskuntiedot[0]["mainoskampanja"].", null,'".$laskuntiedot[0]["tilinumero"]."')
      returning Numero;";
      $ret = execModify($conn_id, $insertKarhulaskuQuery, "insertquery_name");
      $retrow = pg_fetch_row($ret); 
      $karhulaskuNumero = $retrow[0];
      
      //Lisää karhulaskuviite laskuun
      $modifyLaskuQuery = "update LASKU set Karhulasku=".$karhulaskuNumero." where LASKU.Numero=".$_POST['laskunro'].";";
      $result = execModify($conn_id, $modifyLaskuQuery, "updatequery_name");
      
      // Suljetaan yhteys. Ei välttämättä pakollinen, koska
      // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
      pg_close($conn_id);
      
      //Takaisin laskutukseen
      header('Location:laskutus.php');
      exit;
      }
      else
      {
        echo 'Sisäinen virhe, pyydettyä laskua ei löytynyt';
      }
      
   }
   else
   {
       echo 'Tietokantaan ei saada yhteyttä';
   }
}
?>

<?php
/** Suorittaa kyselyn.
 *  @param $conn_id Kahva kyselyn tietokantaan.
 *  @param $query Kysely.
 *  @return Palauttaa kyselyn palauttamat rivit (assosiatiivisessa) taulukossa.
 */
function execQuery($conn_id, $query)
{

   if ($conn_id)
   {
      // Aloitetaan transaktio
      pg_query($conn_id, "BEGIN");

      // Asetetaan tapahtuma pelkäksi lukutapahtumaksi
      pg_query($conn_id, "SET TRANSACTION READ ONLY");

      // Asetetaan eristyvyystaso
      pg_query($conn_id, "SET TRANSACTION ISOLATION LEVEL READ COMMITTED");

      // Valmistellaan kysely ja annetaan sille jokin nimi
      $result = pg_prepare($conn_id, "query_name", $query);

	  //Virhe
	  if(!$result)
	  {
		echo pg_last_error($conn_id );
		return null;
	  }

	  // Ajetaan tietyllä nimellä oleva valmisteltu kysely.
	  // Parametrisoidulle kyselylle voi antaa parametrit taulukossa.
	  $result = pg_execute($conn_id, "query_name", array() );

      // Onnistuessa commitoidaan ja haetaan tulokset
      if ($result)
      {
         // Haetaan kyselyn tulokset
		 pg_query($conn_id, "COMMIT");

         return fetchAll($result);
      }
      elseif (!$result)
      {
         // Virheen sattuessa rollback
         pg_query($conn_id, "ROLLBACK");

         // Tulostetaan mikä meni vikaan
         echo '<h3>Kysely epäonnistui</h3>';
         echo pg_last_error($conn_id );
      }

   }

   return null;
}

/** Suorittaa kyselyn.
 *  @param $conn_id Kahva kyselyn tietokantaan.
 *  @param $query Kysely.
 *  @param $name Kyselyn nimi joka on uniikki jokaiselle connectionille.
 *  @return Palauttaa kyselyn palauttamat rivit (assosiatiivisessa) taulukossa.
 */
function execModify($conn_id, $query, $name)
{

   if ($conn_id)
   {
      // Aloitetaan transaktio
      pg_query($conn_id, "BEGIN");

      // Valmistellaan kysely ja annetaan sille jokin nimi
      $result = pg_prepare($conn_id, $name, $query);

	  //Virhe
	  if(!$result)
	  {
		echo pg_last_error($conn_id );
		return null;
	  }

	  // Ajetaan tietyllä nimellä oleva valmisteltu kysely.
	  // Parametrisoidulle kyselylle voi antaa parametrit taulukossa.
	  $result = pg_execute($conn_id, $name, array() );

      // Onnistuessa commitoidaan ja haetaan tulokset
      if ($result)
      {
         // Haetaan kyselyn tulokset
		 pg_query($conn_id, "COMMIT");

         return $result;
      }
      elseif (!$result)
      {
         // Virheen sattuessa rollback
         pg_query($conn_id, "ROLLBACK");

         // Tulostetaan mikä meni vikaan
         echo '<h3>Kysely epäonnistui</h3>';
         echo pg_last_error($conn_id );
      }

   }

   return null;
}

/** Palauttaa koko tuloksen assosiatiivisessa taulukossa.
 *  @param $result Kahva tulosjoukkoon.
 *  @return Kyselyn tulos taulussa (array).
 */
function fetchAll($result = 0)
{
   $rows = array ();

   if ($result)
   {
      $i = 0;

	  // Voidaan hakea myös tavallisena taulukkona.
      //while ($row = pg_fetch_array($result))
	  while ($row = pg_fetch_assoc($result))
      {
	    $rows[$i] = $row;
         ++$i;
      }

   }

   return $rows;
}

?>



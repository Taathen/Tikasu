<!doctype html>

<?php 
$rows = null;
// Yhdistetään laskut mainoskampanjoihin ja mainoskampanjat mainostajiin. Haetaan laskun numero ja siihen liittyvä mainostaja.
// Tulokset järjestetään laskun numeron mukaisesti ja tulokset rajataan kymmeneen.
$queryStart = "select LASKU.Numero as LaskunNumero, MAINOSTAJA.Nimi as Mainostaja from LASKU
               inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
               inner join MAINOSTAJA on MAINOSKAMPANJA.Mainostaja=Mainostaja.Vat_tunnus";
$queryOrder = " order by LaskunNumero";
$query = "select LASKU.Numero as LaskunNumero, MAINOSTAJA.Nimi as Mainostaja from LASKU
          inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
          inner join MAINOSTAJA on MAINOSKAMPANJA.Mainostaja=Mainostaja.Vat_tunnus
          order by LaskunNumero";
$billSelected = false;


// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja
// lähetetty tekstialue ei ole tyhjä.
if (isset($_POST['execquery']))
{
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan
   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);

  $query = $queryStart;
  if(isset($_POST['numberLimit'])){
    $numberLimit = $_POST['numberLimit'];
    $query .= " where LASKU.Numero$numberLimit";
    if(isset($_POST['nameLimit']) && $_POST['nameLimit'] != ""){
      $nameLimit = $_POST['nameLimit'];
      $query .= " and MAINOSTAJA.Nimi='$nameLimit'";
    }
  } else if(isset($_POST['nameLimit'])){
    $nameLimit = $_POST['nameLimit'];
    $query .= " where MAINOSTAJA.Nimi='$nameLimit'";
  }
  $query .= $queryOrder;
  if(isset($_POST['limitTo']) && $_POST['limitTo'] != ""){
    $limitTo = $_POST['limitTo'];
    $query .= " limit $limitTo";
  }

   // Yhteys saatiin
   if ($conn_id)
   {
      // Suoritetaan kysely
      $rows = execQuery($conn_id, $query);

      // Suljetaan yhteys. Ei välttämättä pakollinen, koska
      // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
      pg_close($conn_id);
   }
   else
   {
      echo 'Tietokantaan ei saada yhteyttä';
   }
}

if (isset($_POST['execdetailquery']))
{
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan
   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);
   $selected = $_POST['execdetailquery'];
   $laskuQuery = "select * from LASKU 
                  inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
                  where LASKU.Numero=$selected;";
   
   if(isset($_POST['query'])){
     $query = $_POST['query'];
   }

   // Yhteys saatiin
   if ($conn_id)
   {
      // Suoritetaan kysely
      // Hae uudelleen laskut listaa varten
      $rows = execQuery($conn_id, $query, "rows");
      // Hae laskun tiedot
      $lasku = execQuery($conn_id, $laskuQuery, "lasku");
      // Hae laskun mainostajan tiedot
      $laskunMainostajanTunnus = $lasku[0]['mainostaja'];
      $mainostajaQuery = "select * from MAINOSTAJA
                          inner join MAINOSTAJANYHDYSHENKILO on MAINOSTAJANYHDYSHENKILO.Mainostaja=MAINOSTAJA.Vat_tunnus
                          inner join HENKILO on MAINOSTAJANYHDYSHENKILO.Henkilo=HENKILO.Email
                          inner join OSOITE on HENKILO.osoite=OSOITE.Osoite
                          where MAINOSTAJA.Vat_tunnus='$laskunMainostajanTunnus';";
      $mainostaja = execQuery($conn_id, $mainostajaQuery, "mainostaja");
      // Hae laskun mainosmyyjän tiedot
      $mainosmyyjanTunnus = $mainostaja[0]['mainosmyyja'];
      $mainosmyyjaQuery = "select * from MAINOSMYYJA
                           inner join HENKILO on MAINOSMYYJA.Henkilo=HENKILO.Email
                           inner join OSOITE on HENKILO.osoite=OSOITE.Osoite
                           where MAINOSMYYJA.Kayttaja='$mainosmyyjanTunnus';";
      $mainosmyyja = execQuery($conn_id, $mainosmyyjaQuery, "mainosmyyja");
      // Hae laskun mainosten tiedot
      $mainoskampanjanId = $lasku[0]['mainoskampanja'];
      $mainosQuery = "select * from MAINOS where MAINOS.Mainoskampanja=$mainoskampanjanId;";
      $mainokset = execQuery($conn_id, $mainosQuery, "mainokset");
      $mainoskampanjaQuery = "select * from MAINOSKAMPANJA where MainoskampanjaId=$mainoskampanjanId;";
      $mainoskampanja = execQuery($conn_id, $mainoskampanjaQuery, "mainoskampanja");
      $mainosSummaQuery = "select SUM(hinta) as summa from MAINOS where MAINOS.Mainoskampanja=$mainoskampanjanId;";
      $mainosSumma = execQuery($conn_id, $mainosSummaQuery, "mainostenSumma");

      $billSelected = true;

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

  <style>
    ul{
      padding: 5%;
    }
    p{
      padding: 5%;
    }
    th{
      text-align: left;
      padding-left: 1%;
    }
    td{
      padding-left: 1%;
    }
    .laskunTiedot{
      padding-top: 1%;
    }
  </style>
</head>

<body>
  <a href="index.html">Etusivulle</a>
  <h1>Laskujen hallintajärjestelmä</h1>
  <table>
    <tr>
      <form method="post" action="laskutus.php" name="queryform">
        <label for="numberLimit">Näytä Laskut joiden numero täyttää seuraavan ehdon: </label><input type="text" id="numberLimit" name="numberLimit" value=">=0"><br>
        <label for="nameLimit">Näytä vain laskut, joiden saaja on seuraava (Jätä tyhjäksi jos et halua rajata saajan mukaan): </label><input type="text" id="nameLimit" name="nameLimit" value=""><br>
        <label for="limitTo">Rajaa näytettävien laskujen määrää (Jätä tyhjäksi jos haluat kaikki laskut näkyviin): </label><input type="text" id="limitTo" name="limitTo" value=""><br>
        <input type="submit" name="execquery" value="Hae mainoskampanjat">
      </form>
    </tr>
    <tr>
      <?php
        if (is_array($rows))
        {
          echo '<table width="800">';

          $headers = array_keys($rows[0]);

          // Taulun otsakkeet
          echo '<tr>';
          foreach ($headers as $header)
          {
            echo '<th>'.$header.'</th>';
          }
          echo '</tr>';

          // Tulostetaan rivit
          foreach ($rows as $row)
          {
            echo '<tr>';
            echo '<td><form method="post" action="laskutus.php" name="queryform"><input type="hidden" name="query" value="'.$query.'"><input type="submit" name="execdetailquery" value="'.$row[$headers[0]].'"></form></td>';
            echo '<td>'.$row[$headers[1]].'<td>';
            echo '<td><form method="post" action="poista_lasku.php" name="poistalasku"><button type="submit" name="poistalasku" value="'.$row[$headers[0]].'">Poista lasku</button></form><td>';
            echo '<td><form method="post" action="lisaa_karhulasku.php" name="karhulasku"><button type="submit" name="karhulasku" value="'.$row[$headers[0]].'">Lisää karhulasku</button></form><td>';
            echo '</tr>';
          }

          echo '</table>';
        }
      ?>
    </tr>
    <tr>
      <?php
        // Laskun tarkemmat tiedot tänne
        if ($billSelected == true)
        {
          echo '<div style="padding-top: 1%;padding-left: 1%;">';
          // Muodosta laskun viesti
          date_default_timezone_set("Europe/Helsinki");
          $date = date("d.m.Y");
          $billMessage = "LASKU ".$date."\n=======================\n\nMainoskampanjan myyjä:\n";

          // Mainoskampanjan myyjä
          $billMessage .= $mainosmyyja[0]['nimi']."\n";
          $billMessage .= $mainosmyyja[0]['osoite']."\n";
          $billMessage .= $mainosmyyja[0]['postinumero']."\n";
        
          // Mainoskampanjan tilaaja
          $billMessage .= "\nMainoskampanjan tilaaja:\n";
          $billMessage .= $mainostaja[0]['nimi']."\n";
          $billMessage .= $mainostaja[0]['osoite']."\n";
          $billMessage .= $mainostaja[0]['postinumero']."\n";
        
          // Laskutettava mainoskampanja
          $billMessage .= "\nLaskutettava mainoskampanja:\n---------------------\n";
          $billMessage .= $lasku[0]['nimi']." ".$lasku[0]['alkupvm']."-".$lasku[0]['loppupvm']."\n";
          $billMessage .= "Määrärahat: ".$lasku[0]['maararahat']."\n";

          // Esitetyt mainokset
          $billMessage .= "\nEsitetyt mainokset:\n---------------------\n";
          foreach ($mainokset as $mainosRow){
            $billMessage .= "\nMainos ".$mainosRow['mainosid'].": ".$mainosRow['nimi']."\n";
            $billMessage .= "Kesto: ".$mainosRow['kesto']."\n";
            $billMessage .= "Hinta: ".$mainosRow['hinta']."\n";
          }
          $billMessage .= "Yhteensä: ".$mainosSumma[0]['summa']."\n";

          $billMessage .= "\nTilinumero: ".$lasku[0]['tilinumero']."\n";
          $billMessage .= "Eräpäivä: ".$lasku[0]['erapaiva']."\n";
          $billMessage .= "Viitenumero: ".$lasku[0]['viitenumero']."\n";
          $billMessage .= "Loppusumma: ".$lasku[0]['summa']."\n";
        
          $billMessage = wordwrap($billMessage, 70);

          echo str_replace("\n", "<br>", $billMessage);
          
          if($mainoskampanja[0]['aktiivinen'] == "Aktiivinen"){
            echo '<br>';
            echo "Mainoskampanja käynnissä: Laskua ei voida lähettää";
          } else {
            echo '<form method="post" action="laskutus.php" name="queryform"><input type="hidden" name="query" value="'.$query.'">';
            echo '<input type="submit" name="execquery" value="Lähetä lasku">';
            echo '</form>';
            // Seuraavalla komennolla sähköposti lähetettäisiin
            //mail($mainostaja[0]['email'], "MAINOSKAMPANJA LASKU", $billMessage);
          }
          echo '</div>';
        }
      ?>
    </tr>
  </table>
</body>
</html>


<?php 
/** Suorittaa kyselyn.
 *  @param $conn_id Kahva kyselyn tietokantaan.
 *  @param $query Kysely.
 *  @return Palauttaa kyselyn palauttamat rivit (assosiatiivisessa) taulukossa.
 */
function execQuery($conn_id, $query, $name="query_name")
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

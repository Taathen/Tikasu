<!doctype html>

<?php
$rows = null;
// Yhdistetään laskut mainoskampanjoihin ja mainoskampanjat mainostajiin. Haetaan laskun numero ja siihen liittyvä mainostaja.
// Tulokset järjestetään laskun numeron mukaisesti
$queryStart = "select LASKU.Numero as LaskunNumero, MAINOSTAJA.Nimi as Mainostaja from LASKU
               inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
               inner join MAINOSTAJA on MAINOSKAMPANJA.Mainostaja=Mainostaja.Vat_tunnus";
$queryOrder = " order by LaskunNumero";
$query = "select LASKU.Numero as LaskunNumero, MAINOSTAJA.Nimi as Mainostaja from LASKU
          inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
          inner join MAINOSTAJA on MAINOSKAMPANJA.Mainostaja=Mainostaja.Vat_tunnus
          order by LaskunNumero";

$details = null;

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
  } else {
    $nameLimit = $_POST['nameLimit'];
    $query .= "where MAINOSTAJA.Nimi='$nameLimit'";
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
      echo 'Tietokantaan ei saada yhteyttä?';
   }
}

if (isset($_POST['execdetailquery']))
{
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan
   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);
   $selected = $_POST['execdetailquery'];
   $detailQuery = "select * from LASKU
                   inner join MAINOSKAMPANJA on LASKU.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
                   inner join MAINOSTAJA on MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus
                   where LASKU.Numero=$selected;";

   if(isset($_POST['query'])){
     $query = $_POST['query'];
   }

   // Yhteys saatiin
   if ($conn_id)
   {
      // Suoritetaan kysely
      $rows = execQuery($conn_id, $query, "rows");
      $details = execQuery($conn_id, $detailQuery, "details");

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
  <title>TiKaSu Harkkatyö?</title>
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
  </style>
</head>

<body>
  <h1>Laskujen hallintajärjestelmä</h1>
  <table>
    <tr>
      <form method="post" action="laskutus.php" name="queryform">
        <label for="numberLimit">Näytä Laskut joiden numero täyttää seuraavan ehdon: </label><input type="text" id="numberLimit" name="numberLimit" value=">=0"><br>
        <label for="nameLimit">Näytä vain laskut, joiden saaja on seuraava (Jätä tyhjäksi jos et halua rajata saajan mukaan): </label><input type="text" id="nameLimit" name="nameLimit" value=""><br>
        <label for="limitTo">Rajaa näytettävien laskujen määrää (Jätä tyhjäksi jos haluat kaikki laskut näkyviin): </label><input type="text" id="limitTo" name="limitTo" value=""><br>
        <input type="submit" name="execquery" value="Mainoskampanjat">
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
            echo '</tr>';
          }

          echo '</table>';
        }
      ?>
    </tr>
    <tr>
      <tr>
        <th>Valitun Laskun tiedot</th>
      </tr>
      <?php
        // Laskun tarkemmat tiedot tä?nne
        if (is_array($details))
        {
          echo '<table width="1200">';

          $detailHeaders = array_keys($details[0]);

          // Taulun otsakkeet
          echo '<tr>';
          foreach ($detailHeaders as $detailHeader)
          {
            echo '<th>'.$detailHeader.'</th>';
          }
          echo '</tr>';

          // Tulostetaan rivit
          foreach ($details as $detailRow)
          {
            echo '<tr>';
            foreach ($detailRow as $detailColumn)
            {
              echo '<td>'.$detailColumn.'</td>';
            }
            echo '</tr>';
          }
          echo '</table>';
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
      echo '<h3>Kysely epä?onnistui</h3>';
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

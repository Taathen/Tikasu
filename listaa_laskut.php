<!doctype html>

<?php
$rows = null;
$queryList = "select * from LASKU;";

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja
// lähetetty tekstialue ei ole tyhjä.

   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan

   // Kysely jolla saadaan laskettua mainoksista muodostunut kokonaishinta
   $sumQuery = "
   select SUM(mainos.hinta)::money::numeric::float8 as price
  from kuuntelu, mainos, mainoskampanja
  where kuuntelu.Mainos=mainos.MainosId
  and mainos.Mainoskampanja = mainoskampanja.MainoskampanjaId
group by mainos.hinta;
";

   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);

   // Yhteys saatiin
   if ($conn_id)
   {

     if (isset($_POST['lisaalasku']))
     {
     // kysellään mainoskampanjan loppusumma
      $sum = execQuery($conn_id, $sumQuery, "sumQuery_name");
      $finalSum = 0;

      foreach ($sum as $row)
      {
         $finalSum = $row['price'];
      }

      // validate time
      $query = "
      INSERT INTO LASKU (Viitenumero, Summa, Viivastysmaksu, Erapaiva, Aktiivinen, Mainoskampanja, Karhulasku)
      VALUES (".htmlspecialchars($_POST['viitenr']).", ".$finalSum.", 0, '". $_POST['erapaiva'] . "', 'Aktiivinen', " .htmlspecialchars($_POST['mainoskampanja']). ", null);
      ";
      // suoritetaan insert
      $result = execInsert($conn_id, $query);
      }

      // haetaan kaikki laskut
      $rows = execQuery($conn_id, $queryList, "listQuery_name");

      // Suljetaan yhteys. Ei välttämättä pakollinen, koska
      // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
      pg_close($conn_id);
   }
   else
   {
       echo 'Tietokantaan ei saada yhteyttä';
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

  <h1>Mainosten hallintajärjestelmä</h1>
  <table>
    <tr>
      <td>
          <ul>
<li>Linkkejä</li>
<li>Linkkejä</li>
          </ul>
      </td>
      <td>
<h2>Kaikki laskut</h2>
<?php
if (is_array($rows))
{
   echo '<p>
         <table width="300">';

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
      foreach ($row as $column)
      {
         echo '<td>'.$column.'</td>';
      }
      echo '<td><form method="post" action="muokkaa_laskua.php" name="laskuform"><input type="hidden" name="numero" value="' .$row['numero']. '"><input type="submit" name="laskuform" value="Muokkaa laskua"></form></td> </tr>';
   }

   echo '</table>
         </p>';
}
?>
      </td>
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
function execQuery($conn_id, $query, $name)
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

/** Suorittaa kyselyn.
 *  @param $conn_id Kahva kyselyn tietokantaan.
 *  @param $query Kysely.
 *  @return Palauttaa kyselyn palauttamat rivit (assosiatiivisessa) taulukossa.
 */
function execInsert($conn_id, $query)
{

   if ($conn_id)
   {
      // Aloitetaan transaktio
      pg_query($conn_id, "BEGIN");

      // Valmistellaan kysely ja annetaan sille jokin nimi
      $result = pg_prepare($conn_id, "insert_name", $query);

	  //Virhe
	  if(!$result)
	  {
		echo pg_last_error($conn_id );
		return null;
	  }

	  // Ajetaan tietyllä nimellä oleva valmisteltu kysely.
	  // Parametrisoidulle kyselylle voi antaa parametrit taulukossa.
	  $result = pg_execute($conn_id, "insert_name", array() );

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

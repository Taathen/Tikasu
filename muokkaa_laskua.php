<!doctype html>

<?php
$rows = null;

// handle form ajax calls
if(isset($_POST['erapaiva'])){
  $updateQuery = "update LASKU set Erapaiva='" .$_POST['erapaiva']. "' where Numero=" .$_POST['numero'].";";
  $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
  $conn_id = pg_connect($conn_string);

  // Yhteys saatiin
  if ($conn_id)
  {
     // Suoritetaan kysely
     $rows = execUpdate($conn_id, $updateQuery, "erapaiva");

     // Suljetaan yhteys. Ei välttämättä pakollinen, koska
     // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
     pg_close($conn_id);
  }
  else
  {
      echo 'Tietokantaan ei saada yhteyttä';
  }
}
if(isset($_POST['summa'])){
  $updateQuery = "update LASKU set Summa=" .$_POST['summa']. " where Numero=" .$_POST['numero'].";";
  $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
  $conn_id = pg_connect($conn_string);

  // Yhteys saatiin
  if ($conn_id)
  {
     // Suoritetaan kysely
     $rows = execUpdate($conn_id, $updateQuery, "summa");

     // Suljetaan yhteys. Ei välttämättä pakollinen, koska
     // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
     pg_close($conn_id);
  }
  else
  {
      echo 'Tietokantaan ei saada yhteyttä';
  }
}
if(isset($_POST['aktiivinen'])){
  $updateQuery = "update LASKU set Aktiivinen='" .$_POST['aktiivinen']. "' where Numero=" .$_POST['numero'].";";
  $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
  $conn_id = pg_connect($conn_string);

  // Yhteys saatiin
  if ($conn_id)
  {
     // Suoritetaan kysely
     $rows = execUpdate($conn_id, $updateQuery, "aktiivinen");

     // Suljetaan yhteys. Ei välttämättä pakollinen, koska
     // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
     pg_close($conn_id);
  }
  else
  {
      echo 'Tietokantaan ei saada yhteyttä';
  }
}
if(isset($_POST['viivastys'])){
  $updateQuery = "update LASKU set Viivastysmaksu=" .$_POST['viivastys']. " where Numero=" .$_POST['numero'].";";
  $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
  $conn_id = pg_connect($conn_string);

  // Yhteys saatiin
  if ($conn_id)
  {
     // Suoritetaan kysely
     $rows = execUpdate($conn_id, $updateQuery, "viivastys");

     // Suljetaan yhteys. Ei välttämättä pakollinen, koska
     // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
     pg_close($conn_id);
  }
  else
  {
      echo 'Tietokantaan ei saada yhteyttä';
  }
}

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja
// ja että tänne tultiin laskulistasta
if (isset($_POST['laskuform']))
{
$query = "select erapaiva, summa::money::numeric::float8, viivastysmaksu::money::numeric::float8, aktiivinen, numero from LASKU where numero =". $_POST['numero'].";";
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan

   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);

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
?>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>TiKaSu Harkkatyö</title>
  <meta name="description" content="Tietokantojen suunnittelu, harkkatyö">

  <link rel="stylesheet" href="css/styles.css?v=1.0">
  <script type="text/javascript" src="ajax.js"></script>
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
<?php
foreach ($rows as $row)
{
   echo '<tr>';

echo '
<h2>Muokkaa laskua ' .$row['numero']. '</h2>
<p>
<form method="post" action="listaa_laskut.php" name="lisaalasku">
<input type="hidden" value="'.$row['numero'].'" name="numero">

<label for="erapaiva">Eräpäivä:</label>
<input type="date" name="erapaiva" value="' .$row['erapaiva']. '" onchange="muutaErapaiva()">
<label for="summa">Summa:</label>
<input type="text" name="summa" value="' .$row['summa']. '" onchange="muutaSumma()">
<label for="viivastys">Viivästysmaksu:</label>
<input type="number" name="viivastys" value="' .$row['viivastysmaksu']. '" onchange="muutaViivastys()">
<label for="aktiivinen">Aktiivinen:</label>

<select id="aktiivinen" name="aktiivinen" onchange="muutaAktiivinen()">';
if ($row['aktiivinen'] == 'Aktiivinen'){
  echo '  <option value="Aktiivinen" selected>Aktiivinen</option>
    <option value="Passiivinen" >Ei-aktiivinen</option>';
  }
  else {
    echo '  <option value="akt">Aktiivinen</option>
      <option value="ei" selected>Ei-aktiivinen</option>';
  }
echo '
</select>
';
}
echo '
</form>
</p>
';
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

function execUpdate($conn_id, $query, $queryname)
{

   if ($conn_id)
   {
      // Aloitetaan transaktio
      pg_query($conn_id, "BEGIN");

      // Valmistellaan kysely ja annetaan sille jokin nimi
      $result = pg_prepare($conn_id, $queryname, $query);

	  //Virhe
	  if(!$result)
	  {
		echo pg_last_error($conn_id );
		return null;
	  }

	  // Ajetaan tietyllä nimellä oleva valmisteltu kysely.
	  // Parametrisoidulle kyselylle voi antaa parametrit taulukossa.
	  $result = pg_execute($conn_id, $queryname, array() );

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

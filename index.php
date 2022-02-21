<!doctype html>

<?php 
$rows = null;
$query = "select * from MAINOSKAMPANJA limit 10;";

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja
// lähetetty tekstialue ei ole tyhjä.
if (isset($_POST['execquery']))
{

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
<li><form method="post" action="index.php" name="queryform"><input type="submit" name="execquery" value="Mainoskampanjat"></form></li>
<li>Linkkejä</li>
<li>Linkkejä</li>
          </ul>
      </td>
      <td>
<p>Tähän avautuu tietokannan kyselyn sisällöt!</p>
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
      echo '</tr>';
   }

   echo '</table>
         </p>';
}
?>
      </td>
    </tr>
    <tr>
      <td colspan="2">Tänne jotain ohjeita ehkä?</td>
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


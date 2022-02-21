<!doctype html>

<?php 
$rows = null;
$validSearch = null;
$mainoksenNimi = null;
$headerArray = array("esityspaiva","esitysaika","sukupuoli","ika","maa","kaupunki","nimi","esittaja","genre");

$queryStart = "select";
$query = "select
            kuuntelu.esityspaiva,
            kuuntelu.esitysaika,
            kuuntelija.sukupuoli,
            kuuntelija.ika,
            kuuntelija.maa,
            kuuntelija.kaupunki,
            teos.nimi,
            esittaja.nimi as esittaja,
            teoksengenre.genre
            from
            MAINOSTAJA
            inner join MAINOSKAMPANJA on MAINOSTAJA.Vat_tunnus = MAINOSKAMPANJA.Mainostaja
            inner join mainos on mainoskampanja.mainoskampanjaid = mainos.mainoskampanja
            inner join kuuntelu on mainos.mainosid = kuuntelu.mainos
            inner join kuuntelija on kuuntelu.kuuntelija = kuuntelija.kayttaja
            inner join soittolista on kuuntelija.kayttaja = soittolista.kuuntelija
            inner join soittolistanteos on soittolista.soittolistaid = soittolistanteos.soittolista
            inner join teos on soittolistanteos.teos = teos.teosid
            inner join teoksentekija on teos.teosid = teoksentekija.teos
            inner join esittaja on teoksentekija.esittaja = esittaja.esittajaid
            inner join teoksengenre on teos.teosid = teoksengenre.teos";

$details = null;

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja
// lähetetty tekstialue ei ole tyhjä.
if (isset($_POST['getData']))
{
  if (!isset($_POST['date']) && !isset($_POST['time']) && !isset($_POST['gender']) && !isset($_POST['age']) && !isset($_POST['country']) && !isset($_POST['city']) && !isset($_POST['song']) && !isset($_POST['artist']) && !isset($_POST['genre'])) {
    echo '<h3>Valitse vähintään yksi kategoria</h3>';
    $validSearch = null;
  } else {
    
    $query = $queryStart;

    if(isset($_POST['date'])) {
        $query .= " kuuntelu.esityspaiva,";
    }
    if(isset($_POST['time'])) {
        $query .= " kuuntelu.esitysaika,"; 
    }
    if(isset($_POST['gender'])) {
        $query .= " kuuntelija.sukupuoli,"; 
    }
    if(isset($_POST['age'])) {
        $query .= " kuuntelija.ika,"; 
    }
    if(isset($_POST['country'])) {
        $query .= " kuuntelija.maa,"; 
    }
    if(isset($_POST['city'])) {
        $query .= " kuuntelija.kaupunki,"; 
    }
    if(isset($_POST['song'])) {
        $query .= " teos.nimi,"; 
    }
    if(isset($_POST['artist'])) {
        $query .= " esittaja.nimi as esittaja,"; 
    }
    if(isset($_POST['genre'])) {
        $query .= " teoksengenre.genre,"; 
    }

    $mainoksenNimi = $_POST['mainokset'];
    $query = substr($query, 0, -1);
    $query .= " from MAINOSTAJA
    inner join MAINOSKAMPANJA on MAINOSTAJA.Vat_tunnus = MAINOSKAMPANJA.Mainostaja
    inner join mainos on mainoskampanja.mainoskampanjaid = mainos.mainoskampanja
    inner join kuuntelu on mainos.mainosid = kuuntelu.mainos
    inner join kuuntelija on kuuntelu.kuuntelija = kuuntelija.kayttaja
    inner join soittolista on kuuntelija.kayttaja = soittolista.kuuntelija
    inner join soittolistanteos on soittolista.soittolistaid = soittolistanteos.soittolista
    inner join teos on soittolistanteos.teos = teos.teosid
    inner join teoksentekija on teos.teosid = teoksentekija.teos
    inner join esittaja on teoksentekija.esittaja = esittaja.esittajaid
    inner join teoksengenre on teos.teosid = teoksengenre.teos
    where mainos.nimi='{$mainoksenNimi}'";
 
   // Otetaan yhteys tietokantaan ja tallennetaan kahva $conn_id muuttujaan
   $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
   $conn_id = pg_connect($conn_string);

   // Yhteys saatiin
   if ($conn_id)
   {
      // Suoritetaan kysely
      $rows = execQuery($conn_id, $query);
			$validSearch = true;
      // Suljetaan yhteys. Ei välttämättä pakollinen, koska
      // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
      pg_close($conn_id);
   }
   else
   {
      echo 'Tietokantaan ei saada yhteyttä';
   }
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
  </style>
</head>

<body>
<a href="index.html">Etusivulle</a>

  <h1>Mainosesitysraportti</h1>
  <table>
    <tr>
        <form method="post" action="mainosesitysraportti.php" name="queryform">
          <label>Valitse mainos:</label>
          <select id="mainokset" name="mainokset">
        <?php
            $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
            $conn_id = pg_connect($conn_string);
         
           $query = "select nimi from mainos";

            // Yhteys saatiin
            if ($conn_id)
            {
               // Suoritetaan kysely
               $tmp = execQuery($conn_id, $query);
         
               // Suljetaan yhteys. Ei välttämättä pakollinen, koska
               // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
               pg_close($conn_id);
            }
            else
            {
               echo 'Tietokantaan ei saada yhteyttä';
            }

            $headerss = array_keys($tmp[0]);

            foreach ($tmp as $tmp2)
            {
                echo '<option value="' .$tmp2[$headerss[0]]. '">' . $tmp2[$headerss[0]] . '</option>';
            }

        ?>
          </select><br>
            Esityspäivä: <input type="checkbox" name="date" value="pdate"><br>
            Esitysaika: <input type="checkbox" name="time" value="ptime"><br>
            Kuuntelijan sukupuoli: <input type="checkbox" name="gender" value="gender"><br>
            Kuuntelijan ikä: <input type="checkbox" name="age" value="age"><br>
            Kuuntelijan maa: <input type="checkbox" name="country" value="country"><br>
            Kuuntelijan paikkakunta: <input type="checkbox" name="city" value="city"><br>
            Soitettu kappale: <input type="checkbox" name="song" value="song"><br>
            Kappaleen esittäjä: <input type="checkbox" name="artist" value="artist"><br>
            Kappaleen genre: <input type="checkbox" name="genre" value="genre"><br>
            <input type="submit" name="getData" value="Hae mainokset">
        </form><br><br>
    </tr>
    <tr>
      <?php
        if (is_array($rows) && !empty($rows))
        {
          $conn_string = "host=localhost port=5432 dbname=tikasu user=admin password=LFG";
            $conn_id = pg_connect($conn_string);
         
           $query = "select
                      mainos.nimi as mainos,
                      mainostaja.nimi as mainostaja,
                      mainoskampanja.nimi as kampanja
                    from
                      mainos
                      inner join mainoskampanja on mainos.mainoskampanja = mainoskampanja.mainoskampanjaid
                      inner join mainostaja on mainoskampanja.mainostaja = mainostaja.vat_tunnus
                      where mainos.nimi='{$mainoksenNimi}'
                    ";

            // Yhteys saatiin
            if ($conn_id)
            {
               // Suoritetaan kysely
               $tmp = execQuery($conn_id, $query);
         
               // Suljetaan yhteys. Ei välttämättä pakollinen, koska
               // yhteys suljetaan automaattisesti skriptin suorituksen jälkeen.
               pg_close($conn_id);
            }
            else
            {
               echo 'Tietokantaan ei saada yhteyttä';
            }

            $headerss = array_keys($tmp[0]);

          // ei toimi ilman tata wtf
            foreach ($tmp as $tmp2)
            {
            }
          
          echo 'MAINOSESITYSRAPORTTI<br>';
      		echo '================<br><br>';
     			echo 'Mainostaja: '.$tmp2[$headerss[1]].'<br>';
      		echo 'Mainoskampanja: '.$tmp2[$headerss[2]].'<br>';
      		echo 'Mainos: '.$tmp2[$headerss[0]].'<br>';
      		echo '--------------------------------------<br>';
          
          echo '<table border="1">';

          $headers = array_keys($rows[0]);

          // Taulun otsakkeet
          echo '<tr>';
          foreach ($headerArray as $header)
          {
            echo '<th width="120">'.$header.'</th>';
          }
          echo '</tr>';

          // Tulostetaan rivit
          foreach ($rows as $row)
          {
            echo '<tr>';
            if(array_key_exists($headerArray[0],$row)) {
              echo "<td>".$row['esityspaiva'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[1],$row)) {
              echo '<td>'.$row['esitysaika'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[2],$row)) {
              echo '<td>'.$row['sukupuoli'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[3],$row)) {
              echo '<td>'.$row['ika'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[4],$row)) {
              echo '<td>'.$row['maa'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[5],$row)) {
              echo '<td>'.$row['kaupunki'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[6],$row)) {
              echo '<td>'.$row['nimi'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[7],$row)) {
              echo '<td>'.$row['esittaja'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            if(array_key_exists($headerArray[8],$row)) {
              echo '<td>'.$row['genre'].'</td>';
            } else {
              echo '<td>'."-".'</td>';
            }
            echo '</tr>';
          }

          echo '</table>';
          echo '<form target="_blank" action="http://tie-tkannat-5.it.tuni.fi/emailsent.html" method="post">';
            echo '<input type="submit" value="Lähetä raportti">';
            echo '</form>';
        } else if (isset($_POST['getData']) && $validSearch) {
          echo '<br>Mainos ei ole aktiivisessa mainoskampanjassa';
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


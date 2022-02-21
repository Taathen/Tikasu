<?php

echo '<html>
      <body>';

$rows = null;
$query = "";

// Tarkastetaan, että lomake on lähetetty painamalla submit-painiketta ja 
// lähetetty tekstialue ei ole tyhjä.
if (isset($_POST['execquery']) && !empty ($_POST['query']))
{
   // Syötteen tarkastaminen voi olla aiheellista!
   $query = $_POST['query'];

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


// Tulostetaan sivu

echo '<h2>SQL-Konsoli</h2>';

echo '<p><form action="konsoli.php" name="queryform" method="post">
         <textarea name="query" rows="6" cols="60">'.$query.'</textarea><br> <br>
        <input type="submit" name="execquery" value="Suorita">        
    </form></p>';
    
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

echo '</body>
      </html>';
      
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

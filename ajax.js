function muutaErapaiva(){
  var era = document.getElementsByName("erapaiva")[0].value;
  var numero = document.getElementsByName("numero")[0].value;
  var http = new XMLHttpRequest();
  var url = 'muokkaa_laskua.php';
  var params = 'erapaiva=' + era + '&numero=' + numero + '&laskuform=laskuform';
  http.open('POST', url, true);
  //Send the proper header information along with the request
  http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  http.onreadystatechange = function() {//Call a function when the state changes.
      if(http.readyState == 4 && http.status == 200) {
          console.log(http.responseText);
      }
  }
  http.send(params);
}

function muutaSumma(){
  var summa = document.getElementsByName("summa")[0].value;
  var numero = document.getElementsByName("numero")[0].value;
  var http = new XMLHttpRequest();
  var url = 'muokkaa_laskua.php';
  var params = 'summa=' + summa + '&numero=' + numero + '&laskuform=laskuform';
  http.open('POST', url, true);
  //Send the proper header information along with the request
  http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  http.onreadystatechange = function() {//Call a function when the state changes.
      if(http.readyState == 4 && http.status == 200) {
          console.log(http.responseText);
      }
  }
  http.send(params);
}

function muutaAktiivinen(){
  var aktiivinen = document.getElementsByName("aktiivinen")[0].value;
  var numero = document.getElementsByName("numero")[0].value;
  var http = new XMLHttpRequest();
  var url = 'muokkaa_laskua.php';
  var params = 'aktiivinen=' + aktiivinen + '&numero=' + numero + '&laskuform=laskuform';
  http.open('POST', url, true);
  //Send the proper header information along with the request
  http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  http.onreadystatechange = function() {//Call a function when the state changes.
      if(http.readyState == 4 && http.status == 200) {
          console.log(http.responseText);
      }
  }
  http.send(params);
}

function muutaViivastys(){
  var viivastys = document.getElementsByName("viivastys")[0].value;
  var numero = document.getElementsByName("numero")[0].value;
  var http = new XMLHttpRequest();
  var url = 'muokkaa_laskua.php';
  var params = 'viivastys=' + viivastys + '&numero=' + numero + '&laskuform=laskuform';
  http.open('POST', url, true);
  //Send the proper header information along with the request
  http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  http.onreadystatechange = function() {//Call a function when the state changes.
      if(http.readyState == 4 && http.status == 200) {
          console.log(http.responseText);
      }
  }
  http.send(params);
}

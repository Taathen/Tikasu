--ryhmät oikeuksien hallintaan
CREATE GROUP Taloussihteeri;
CREATE GROUP Mainosmyyja;

--käyttäjän luonti olisi esim.
--CREATE ROLE Talous_sihteeri123 LOGIN PASSWORD '1234';
--ALTER GROUP Taloussihteeri ADD USER Talous_sihteeri123;

--annetaan Mainosmyyjille nyt suhteellisen vapaat kädet
GRANT ALL ON OSOITE TO GROUP Mainosmyyja;
GRANT ALL ON HENKILO TO GROUP Mainosmyyja;
GRANT ALL ON MAA TO GROUP Mainosmyyja;
GRANT ALL ON KAUPUNKI TO GROUP Mainosmyyja;
GRANT ALL ON MAINOSTAJA TO GROUP Mainosmyyja;
GRANT ALL ON MAINOSTAJANYHDYSHENKILO TO GROUP Mainosmyyja;
GRANT SELECT ON KUUNTELIJA TO GROUP Mainosmyyja;
GRANT ALL ON MAINOSKAMPANJA TO GROUP Mainosmyyja;
GRANT SELECT ON HINNASTO TO GROUP Mainosmyyja;
GRANT ALL ON MAINOS TO GROUP Mainosmyyja;
GRANT ALL ON LASKU TO GROUP Mainosmyyja;
GRANT ALL ON PROFIILI TO GROUP Mainosmyyja;
GRANT ALL ON PROFIILINKAUPUNKI TO GROUP Mainosmyyja;
GRANT ALL ON GENRE TO GROUP Mainosmyyja;
GRANT ALL ON PROFIILINGENRE TO GROUP Mainosmyyja;
GRANT ALL ON KOKOELMA TO GROUP Mainosmyyja;
GRANT ALL ON TEOS TO GROUP Mainosmyyja;
GRANT ALL ON PROFIILINTEOS TO GROUP Mainosmyyja;
GRANT ALL ON ESITTAJA TO GROUP Mainosmyyja;
GRANT ALL ON PROFIILINESITTAJA TO GROUP Mainosmyyja;
GRANT ALL ON TEOKSENGENRE TO GROUP Mainosmyyja;
GRANT ALL ON ROOLI TO GROUP Mainosmyyja;
GRANT ALL ON TEOKSENTEKIJA TO GROUP Mainosmyyja;
GRANT ALL ON MUSIIKKIKAPPALE TO GROUP Mainosmyyja;
GRANT ALL ON KUUNTELU TO GROUP Mainosmyyja;
GRANT ALL ON SOITTOLISTA TO GROUP Mainosmyyja;
GRANT ALL ON SOITTOLISTANTEOS TO GROUP Mainosmyyja;
GRANT ALL ON KOKOELMANTEOS TO GROUP Mainosmyyja;

--Taloussihteeri voi tarvita mainosten/mainostajien nimiä ja osoitteita
GRANT SELECT ON MAINOSKAMPANJA TO GROUP Taloussihteeri;
GRANT SELECT ON MAINOSTAJA TO GROUP Taloussihteeri;
GRANT SELECT ON MAINOS TO GROUP Taloussihteeri;
GRANT SELECT ON HENKILO TO GROUP Taloussihteeri;
GRANT SELECT ON OSOITE TO GROUP Taloussihteeri;
GRANT SELECT ON KAUPUNKI TO GROUP Taloussihteeri;
GRANT SELECT ON MAA TO GROUP Taloussihteeri;
--hinnaston käsittelyoikeus
GRANT ALL ON HINNASTO TO GROUP Taloussihteeri;
--laskujen käsittelyyn myös muokkausoikeudet
GRANT ALL ON LASKU TO GROUP Taloussihteeri;





--mainostiedot kuukausiraporttiin/laskutukseen
--SELECT * FROM RaporttiMainostiedot WHERE Mainostaja= AND Alkupvm <= AND Loppupvm >= 
--mainostajan mainoskampanjat (nimi) ja mainokset (nimi,lähetysajat(profiileista),pituus,kuuntelukerrat,hinta)
CREATE OR REPLACE VIEW RaporttiMainostiedot AS
SELECT Mainostaja,Alkupvm,Loppupvm,Mainoskampanja,Mainos,Alkuaika,Loppuaika,Kesto,Kuuntelut,Hinta FROM
    (SELECT MAINOSTAJA.Nimi AS Mainostaja, 
           MAINOSKAMPANJA.Alkupvm,
           MAINOSKAMPANJA.Loppupvm,
           MAINOSKAMPANJA.Nimi AS Mainoskampanja, 
           MAINOS.Nimi AS Mainos,
           PROFIILI.Alkuaika,
           PROFIILI.Loppuaika,
           MAINOS.Kesto,
           MAINOS.Hinta,
           MAINOS.MainosId
    FROM MAINOSTAJA, MAINOSKAMPANJA, MAINOS, PROFIILI, KUUNTELU
    WHERE MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus 
          AND MAINOS.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
          AND (PROFIILI.Mainos=MAINOS.MainosId OR PROFIILI.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId))
          t1
INNER JOIN 
    (SELECT MAINOS.MainosId AS MainosId2,Count(KUUNTELU) AS Kuuntelut
    FROM MAINOSTAJA, MAINOSKAMPANJA, MAINOS, PROFIILI, KUUNTELU
    WHERE MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus 
          AND MAINOS.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
          AND (PROFIILI.Mainos=MAINOS.MainosId OR PROFIILI.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId)
          AND KUUNTELU.Mainos=MAINOS.MainosId AND KUUNTELU.Esityspaiva BETWEEN MAINOSKAMPANJA.Alkupvm AND MAINOSKAMPANJA.Loppupvm
    GROUP BY MAINOS.MainosId) t2
     ON t1.MainosId=t2.MainosId2;
    
    
   
    
--näkymä on vain tietojen näyttämistä varten eli select-lupa on tarpeeksi
--kuukausiraporttia käyttää Mainosmyyja tai Taloussihteeri
GRANT SELECT ON RaporttiMainostiedot TO GROUP Taloussihteeri;
GRANT SELECT ON RaporttiMainostiedot TO GROUP Mainosmyyja;





--SELECT * FROM Mainosesitysraportti WHERE Mainostaja= AND Mainos=
--mainoksen esityspäivä, esitysaika, kuuntelijan sukupuoli, ikä, maa, kaupunki sekä soitettu kappale, esittäjä ja genre.
CREATE OR REPLACE VIEW Mainosesitysraportti AS
    SELECT MAINOSTAJA.Nimi AS Mainostaja,
           MAINOS.Nimi AS Mainos,
           MAINOSKAMPANJA.Nimi AS Mainoskampanja,
           KUUNTELU.Esityspaiva,
           KUUNTELU.Esitysaika,
           KUUNTELIJA.Sukupuoli,
           KUUNTELIJA.Ika,
           KAUPUNKI.Nimi AS Kaupunki,
           MAA.Nimi AS Maa,
           TEOS.Nimi AS Teos,
           ESITTAJA.Nimi AS Esittaja,
           GENRE.Nimi AS Genre
    FROM MAINOSTAJA, MAINOS, MAINOSKAMPANJA, KUUNTELU, KUUNTELIJA, KAUPUNKI, MAA, PROFIILI, PROFIILINTEOS, TEOS, PROFIILINESITTAJA, ESITTAJA, PROFIILINGENRE, GENRE
    WHERE MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus
          AND MAINOS.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId
          AND KUUNTELU.Mainos=MAINOS.MainosId
          AND KUUNTELU.Kuuntelija=KUUNTELIJA.Kayttaja
          AND KAUPUNKI.Nimi=KUUNTELIJA.Kaupunki
          AND MAA.Nimi=KAUPUNKI.Maa
          AND (PROFIILI.Mainos=MAINOS.MainosId OR PROFIILI.Mainoskampanja=MAINOSKAMPANJA.MainoskampanjaId)
          AND PROFIILINTEOS.Profiili=PROFIILI.ProfiiliId
          AND TEOS.TeosId=PROFIILINTEOS.Teos
          AND PROFIILINESITTAJA.Profiili=PROFIILI.ProfiiliId
          AND ESITTAJA.EsittajaId=PROFIILINESITTAJA.Esittaja
          AND PROFIILINGENRE.Profiili=PROFIILI.ProfiiliId
          AND GENRE.Nimi=PROFIILINGENRE.Genre;
    
--näkymä on vain tietojen näyttämistä varten eli select-lupa on tarpeeksi
GRANT SELECT ON Mainosesitysraportti TO GROUP Taloussihteeri;
GRANT SELECT ON Mainosesitysraportti TO GROUP Mainosmyyja;




--myyjän ja tilaajan yleiset tiedot, voidaan käyttää laskun laadinnan apuna
CREATE OR REPLACE VIEW Laskutus AS
    SELECT Mainoskampanja,Mainosmyyja,MyyjaOsoite,MyyjaPostinumero,Yhteyshenkilo,Mainostaja,MainostajaOsoite,MainostajaPostinumero FROM
    (SELECT MAINOSKAMPANJA.Nimi AS Mainoskampanja,
           HENKILO.Nimi AS Mainosmyyja,
           OSOITE.Osoite AS MyyjaOsoite,
           OSOITE.Postinumero AS MyyjaPostinumero
    FROM MAINOSKAMPANJA,MAINOSTAJA,HENKILO,OSOITE
    WHERE MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus
          AND MAINOSTAJA.Mainosmyyja=HENKILO.Kayttajatunnus
          AND HENKILO.Osoite=OSOITE.Osoite) t1
    INNER JOIN
    (SELECT MAINOSKAMPANJA.Nimi AS Mainoskampanja2,
           HENKILO.Nimi AS Yhteyshenkilo,
           MAINOSTAJA.Nimi AS Mainostaja,
           OSOITE.Osoite AS MainostajaOsoite,
           OSOITE.Postinumero AS MainostajaPostinumero
    FROM MAINOSKAMPANJA,HENKILO,MAINOSTAJA,MAINOSTAJANYHDYSHENKILO,OSOITE
    WHERE MAINOSKAMPANJA.Mainostaja=MAINOSTAJA.Vat_tunnus
          AND MAINOSTAJA.Vat_tunnus=MAINOSTAJANYHDYSHENKILO.Mainostaja
          AND MAINOSTAJANYHDYSHENKILO.Henkilo=HENKILO.Email
          AND HENKILO.Osoite=OSOITE.Osoite) t2
    ON t1.Mainoskampanja=t2.Mainoskampanja2;
    

--Taloussihteeri voi lukea tiedot tästä näkymästä
GRANT SELECT ON Laskutus TO GROUP Taloussihteeri;
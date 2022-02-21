
-- Luodaan relaatioissa käytettävät enumit
CREATE TYPE sukupuoli AS ENUM ('Mies','Nainen');
CREATE TYPE aktiivinen AS ENUM ('Aktiivinen', 'Passiivinen');

-- Uniikki käyttäjätunnus ja salasana tallennetaan merkkijonoina.
-- Salasanan kanssa käyttävän applikaation tulee olla tarkkana, että käytetään vain
-- hashattyja ja suolattuja salasanoja.
CREATE TABLE KAYTTAJA(
     Kayttajatunnus VARCHAR(40) PRIMARY KEY,
     Salasana VARCHAR(40) NOT NULL
);

-- Osoite ja postinumero tallennetaan merkkijonoina, joissa tarpeeksi merkkejä
-- monen valtion tarpeisiin Osoite sisältää kadun sekä numeron ja on uniikki.
CREATE TABLE OSOITE(
     Osoite VARCHAR(60) PRIMARY KEY,
     Postinumero VARCHAR(10) NOT NULL
);

-- Henkilön sähköposti on uniikki ja toimii avaimena. Henkilöön liittyy käyttäjä sekä osoite.
-- Poistettaessa käyttäjä, halutaan myös henkilötietojen poistuvan GDPR takia.
CREATE TABLE HENKILO(
     Email VARCHAR(40) PRIMARY KEY,
     Nimi VARCHAR(40) NOT NULL,
     Puhelinnumero VARCHAR(21) NOT NULL,
     Kayttajatunnus VARCHAR(40) NOT NULL REFERENCES KAYTTAJA(Kayttajatunnus) ON DELETE CASCADE ON UPDATE CASCADE,
     Osoite VARCHAR(60) NOT NULL REFERENCES OSOITE(Osoite) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE MAA(
    Nimi VARCHAR(50) PRIMARY KEY
);

-- Kaupungilla on nimi sekä maa, johon se liittyy. Mikäli maa poistetaan, kaupunki jää.
-- Kaupungin avain on yhdistelmä nimestä ja maasta jossa se sijaitsee uniikkiuden varmistamiseksi.
CREATE TABLE KAUPUNKI(
     Nimi VARCHAR(50),
     Maa VARCHAR(50) REFERENCES MAA(Nimi) ON DELETE SET NULL ON UPDATE CASCADE,
     PRIMARY KEY(Nimi, Maa)
);

-- Poistettaessa käyttäjä, halutaan myös henkilötietojen poistuvan GDPR takia.
CREATE TABLE TALOUSSIHTEERI(
  Kayttaja VARCHAR(40) PRIMARY KEY REFERENCES KAYTTAJA(Kayttajatunnus) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Poistettaessa käyttäjä, halutaan myös henkilötietojen poistuvan GDPR takia.
CREATE TABLE MAINOSMYYJA(
  Kayttaja VARCHAR(40) PRIMARY KEY REFERENCES KAYTTAJA(Kayttajatunnus) ON DELETE CASCADE ON UPDATE CASCADE,
  Henkilo VARCHAR(40) REFERENCES HENKILO(Email) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Yksilöllinen VAT-tunnus avain. Mainostajaan liittyy Mainosmyyja. Poistettaessa mainosmyyjä,
-- Mainostajan tiedot jäävät arvolla null, jotta asiakastietojen säilyvyys varmistetaan.
CREATE TABLE MAINOSTAJA(
  Vat_tunnus VARCHAR(40) PRIMARY KEY,
  Nimi VARCHAR(40) NOT NULL,
  Mainosmyyja VARCHAR(40) REFERENCES MAINOSMYYJA(Kayttaja) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Poistettaessa henkilö, halutaan myös yhdyshenkilön poistuvan GDPR takia.
-- Mainostajaa poistettaessa mainostajayhdyshenkilöäkään ei tarvita, joten se poistetaan.
CREATE TABLE MAINOSTAJANYHDYSHENKILO(
  Mainostaja VARCHAR(40) PRIMARY KEY REFERENCES MAINOSTAJA(Vat_tunnus) ON DELETE CASCADE ON UPDATE CASCADE,
  Henkilo VARCHAR(40) REFERENCES HENKILO(Email) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Erikoistettu versio käyttäjästä: mikäli käyttäjä poistuu, poistuu kuuntelijankin tiedot (GDPR)
--
CREATE TABLE KUUNTELIJA(
  Kayttaja VARCHAR(40) PRIMARY KEY REFERENCES KAYTTAJA(Kayttajatunnus) ON DELETE CASCADE ON UPDATE CASCADE,
  Ika INTEGER,
  Sukupuoli sukupuoli,
  Kaupunki VARCHAR(40),
  Maa VARCHAR(40),
  FOREIGN KEY(Kaupunki, Maa) REFERENCES KAUPUNKI(Nimi, Maa) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Jokaisella kampanjalla on keinotekoinen id niiden kertaluontoisuuden vuoksi. Nimi ei ole
-- uniikki ja on oletuksena "Uusi mainoskampanja".
-- Poistettaessa mainostaja on tarpeellista poistaa myös mainostajaan liittyvät mainoskampanjat.
CREATE TABLE MAINOSKAMPANJA(
  MainoskampanjaId SERIAL PRIMARY KEY,
  AlkuPvm DATE NOT NULL,
  LoppuPvm DATE NOT NULL,
  Maararahat MONEY NOT NULL,
  Nimi VARCHAR(40) NOT NULL DEFAULT 'Uusi mainoskampanja',
  Aktiivinen aktiivinen,
  JaljellaRahaa MONEY,
  Mainostaja VARCHAR(40) REFERENCES MAINOSTAJA(Vat_tunnus) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Taulu, jonka mukaan eri aikavälien hinnat voidaan määritellä
CREATE TABLE HINNASTO(
  AlkuPvm DATE,
  LoppuPvm DATE,
  Sekuntihinta MONEY NOT NULL,
  PRIMARY KEY(AlkuPvm, LoppuPvm)
);

-- Mainoskampanjan poistuessa on luonnollista poistaa myös siihen liittyvät mainokset.
-- Mainos linkittyy Hinnastoon alku - ja loppupäivämäärällä. Hinnasto on pysyvä ja jota ei saa poistaa.
CREATE TABLE MAINOS(
  MainosId SERIAL PRIMARY KEY,
  Mainoskampanja INTEGER REFERENCES MAINOSKAMPANJA(MainoskampanjaId) ON DELETE CASCADE ON UPDATE CASCADE,
  Nimi VARCHAR(40) DEFAULT 'Uusi mainos',
  Kesto TIME NOT NULL,
  Kuvaus  VARCHAR(200),
  Jingle VARCHAR(40) NOT NULL,
  Hinta Money NOT NULL,
  HinnastoAlku DATE,
  HinnastoLoppu DATE,
  FOREIGN KEY(HinnastoAlku, HinnastoLoppu) REFERENCES HINNASTO(Alkupvm, Loppupvm) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Laskun kertaluontoisuuden vuoksi laskun uniikki id on keinotekoinen. Lasku liittyy mainoskampanjaan.
-- Mikäli se poistetaan, on tarpeen poistaa myös lasku. Laskuun voi liittyä karhulasku (voi olla null!)
CREATE TABLE LASKU(
  Numero SERIAL PRIMARY KEY,
  Viitenumero VARCHAR(40) NOT NULL,
  Summa MONEY NOT NULL,
  Viivastysmaksu MONEY NOT NULL,
  Erapaiva DATE NOT NULL,
  Aktiivinen aktiivinen NOT NULL,
  Mainoskampanja INTEGER REFERENCES  MAINOSKAMPANJA(MainoskampanjaId) ON DELETE CASCADE ON UPDATE CASCADE,
  Karhulasku INTEGER REFERENCES LASKU(Numero) ON DELETE CASCADE ON UPDATE CASCADE,
  Tilinumero VARCHAR(18) NOT NULL
);

-- Kuuntelijoiden profilointiin tehty taulu. Keinotekoinen id kertaluontoisuuden vuoksi.
-- Profiili liittyy kampaanjaan tai mainokseen. Mikäli Kampanja tai mainos poistetaan,
-- on luonnollista poistaa siihen liittyvä profiilikin käyttämättömänä.
CREATE TABLE PROFIILI(
  ProfiiliId SERIAL PRIMARY KEY,
  Alkuaika TIME NOT NULL,
  Loppuaika TIME NOT NULL,
  Alaikaraja INTEGER,
  Ylaikaraja INTEGER,
  Kohdesukupuoli sukupuoli,
  Mainoskampanja INTEGER REFERENCES  MAINOSKAMPANJA(MainoskampanjaId) ON DELETE CASCADE ON UPDATE CASCADE,
  Mainos INTEGER REFERENCES MAINOS(MainosId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE PROFIILINKAUPUNKI(
  Profiili INTEGER PRIMARY KEY REFERENCES Profiili(ProfiiliId) ON DELETE RESTRICT ON UPDATE CASCADE,
  Kaupunki VARCHAR(40),
  Maa VARCHAR(40),
  FOREIGN KEY(Kaupunki, Maa) REFERENCES KAUPUNKI(Nimi, Maa) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE GENRE(
  Nimi VARCHAR(50) PRIMARY KEY
);

CREATE TABLE PROFIILINGENRE(
  Profiili INTEGER PRIMARY KEY REFERENCES Profiili(ProfiiliId) ON DELETE RESTRICT ON UPDATE CASCADE,
  Genre VARCHAR(40) REFERENCES Genre(Nimi) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE KOKOELMA(
  KokoelmaId SERIAL PRIMARY KEY,
  Kesto TIME NOT NULL
);

-- Teokset liittyvät kokoelmiin.
CREATE TABLE TEOS(
  TeosId SERIAL PRIMARY KEY,
  Nimi VARCHAR(100) NOT NULL,
  Julkaisuvuosi INTEGER,
  Kokoelma INTEGER REFERENCES KOKOELMA(KokoelmaId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE PROFIILINTEOS(
  Profiili INTEGER PRIMARY KEY REFERENCES Profiili(ProfiiliId) ON DELETE RESTRICT ON UPDATE CASCADE,
  Teos INTEGER REFERENCES Teos(TeosId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE ESITTAJA(
  EsittajaId SERIAL PRIMARY KEY,
  Nimi VARCHAR(40) NOT NULL
);

CREATE TABLE PROFIILINESITTAJA(
  Profiili INTEGER PRIMARY KEY REFERENCES Profiili(ProfiiliId) ON DELETE RESTRICT ON UPDATE CASCADE,
  Esittaja INTEGER REFERENCES Esittaja(EsittajaId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE TEOKSENGENRE(
  Genre VARCHAR(40) PRIMARY KEY REFERENCES GENRE(Nimi) ON DELETE RESTRICT ON UPDATE CASCADE,
  Teos INTEGER REFERENCES TEOS(TeosId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE ROOLI(
  RoolinNimi VARCHAR(40) PRIMARY KEY
);

CREATE TABLE TEOKSENTEKIJA(
  Teos INTEGER PRIMARY KEY REFERENCES TEOS(TeosId) ON DELETE CASCADE ON UPDATE CASCADE,
  Esittaja INTEGER REFERENCES Esittaja(EsittajaId) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Yksilöllinen PUID on avain. Kappaleella on oltava sekä kesto että äänitiedosto.
-- Kappale liittyy teokseen; mikäli teos poistetaan on luonnollista poistaa myös kappale.
CREATE TABLE MUSIIKKIKAPPALE(
  PUID VARCHAR(40) PRIMARY KEY,
  Kesto TIME NOT NULL,
  Aanitiedosto VARCHAR(40) NOT NULL,
  Teos INTEGER REFERENCES TEOS(TeosId) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Mainoskuuntelua kuvaava taulu. Kertaluontoisuuden vuoksi keinotekoinen id.
-- Kuuntelu liittyy Mainokseen ja Kuuntelijaan. Poistaessa mainos on luonnollista poistetaan myös siihen liittyvät kuuntelut
CREATE TABLE KUUNTELU(
  KuunteluId SERIAL PRIMARY KEY,
  Esitysaika TIMESTAMP NOT NULL,
  Esityspaiva DATE NOT NULL,
  Mainos INTEGER REFERENCES MAINOS(MainosId) ON DELETE CASCADE ON UPDATE CASCADE,
  Kuuntelija VARCHAR(40) REFERENCES KUUNTELIJA(Kayttaja) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Soittolistan kertaluontoisuuden vuoksi keinotekoinen id.
-- Soittolista on kuuntelijakohtainen, jotenkin kuuntelijan poistuessa poistetaan myös hänen soittolistansa.
CREATE TABLE SOITTOLISTA(
  SoittolistaId SERIAL PRIMARY KEY,
  Nimi VARCHAR(40) NOT NULL,
  Kuuntelija VARCHAR(40) REFERENCES KUUNTELIJA(Kayttaja) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE SOITTOLISTANTEOS(
  Soittolista INTEGER PRIMARY KEY REFERENCES SOITTOLISTA(SoittolistaId) ON DELETE CASCADE ON UPDATE CASCADE,
  Teos INTEGER REFERENCES TEOS(TeosId) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE KOKOELMANTEOS(
  Kokoelma INTEGER PRIMARY KEY REFERENCES Kokoelma(KokoelmaId) ON DELETE CASCADE ON UPDATE CASCADE,
  Jarjestysnumero INTEGER,
  Teos INTEGER REFERENCES TEOS(TeosId) ON DELETE CASCADE ON UPDATE CASCADE
);


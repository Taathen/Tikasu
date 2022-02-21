

INSERT INTO KAYTTAJA (Kayttajatunnus, Salasana)
VALUES ('tunnus', 'salasanahashattyna');

INSERT INTO MAINOSMYYJA (Kayttaja)
VALUES ('tunnus');

INSERT INTO OSOITE (Osoite, Postinumero)
VALUES ('Katukatu 2 b 10', '00789');

INSERT INTO KAUPUNKI (Nimi, Maa)
VALUES ('Lahti', 'Venäjä');

INSERT INTO MAA (Nimi)
VALUES ('Venäjä');

INSERT INTO HENKILO (Email, Nimi, Puhelinnumero, Kayttajatunnus, Osoite)
VALUES ('sahkoposti@jee.fi', 'Jaakko Ahven', '050659082', 'tunnus', 'Katukatu 2 b 10');

INSERT INTO KAYTTAJA (Kayttajatunnus, Salasana)
VALUES ('tunnus2', 'salasanahashattyna');

INSERT INTO MAINOSMYYJA (Kayttaja)
VALUES ('tunnus2');

INSERT INTO KAYTTAJA (Kayttajatunnus, Salasana)
VALUES ('tunnus3', 'salasanahashattyna');

INSERT INTO MAINOSMYYJA (Kayttaja, Henkilo)
VALUES ('tunnus3', 'sahkoposti@jee.fi');

INSERT INTO MAINOSTAJA (Vat_tunnus, Nimi, Mainosmyyja)
VALUES ('FI 99999999', 'Sauvon Kalafirma', 'tunnus');

INSERT INTO MAINOSTAJA (Vat_tunnus, Nimi, Mainosmyyja)
VALUES ('FI 99999998', 'Porin Kalafirma', 'tunnus2');

INSERT INTO MAINOSTAJA (Vat_tunnus, Nimi, Mainosmyyja)
VALUES ('FI 99999997', 'Tampereen Kalafirma', 'tunnus3');

-- HINNASTO

INSERT INTO HINNASTO (AlkuPvm, LoppuPvm, Sekuntihinta)
VALUES ('1800-12-01', '2100-12-24', 1.2);


----- KAMPANJA

INSERT INTO MAINOSKAMPANJA (AlkuPvm, LoppuPvm, Maararahat, Nimi, Aktiivinen, JaljellaRahaa, Mainostaja)
VALUES ('2019-12-01', '2020-12-24', 10000, 'Kampanja1', 'Aktiivinen', 8000, 'FI 99999997');


INSERT INTO MAINOSKAMPANJA (AlkuPvm, LoppuPvm, Maararahat, Nimi, Aktiivinen, JaljellaRahaa, Mainostaja)
VALUES ('2019-12-01', '2019-12-25', 10000, 'Kampanja2', 'Passiivinen', 8000, 'FI 99999998');

INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (1, 'Mainos1', '12:01:01', 'Mainoksen 1 kuvaus', 'jingle1.ogg', 200, '1800-12-01', '2100-12-24');

INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (1, 'Mainos2', '09:01:01', 'Mainoksen 2 kuvaus', 'jingle2.ogg', 200, '1800-12-01', '2100-12-24');

INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (1, 'Mainos3', '15:01:01', 'Mainoksen 3 kuvaus', 'jingle3.ogg', 200, '1800-12-01', '2100-12-24');


INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (2, 'Mainos4', '12:01:01', 'Mainoksen 4 kuvaus', 'jingle4.ogg', 200, '1800-12-01', '2100-12-24');

INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (2, 'Mainos5', '09:01:01', 'Mainoksen 5 kuvaus', 'jingle5.ogg', 200, '1800-12-01', '2100-12-24');

INSERT INTO MAINOS (Mainoskampanja, Nimi, Kesto, Kuvaus, Jingle, Hinta, HinnastoAlku, HinnastoLoppu)
VALUES (2, 'Mainos6', '15:01:01', 'Mainoksen 6 kuvaus', 'jingle6.ogg', 200, '1800-12-01', '2100-12-24');


---- laskut

INSERT INTO LASKU (Viitenumero, Summa, Viivastysmaksu, Erapaiva, Aktiivinen, Mainoskampanja, Karhulasku, Tilinumero)
VALUES ('172376427408', 5000, 20, '2020-12-06', 'Aktiivinen', 2, null, 'FI9284862429427777');

INSERT INTO LASKU (Viitenumero, Summa, Viivastysmaksu, Erapaiva, Aktiivinen, Mainoskampanja, Karhulasku, Tilinumero)
VALUES ('172376427409', 5000, 0, '2020-12-01', 'Aktiivinen', 1, (select Numero from LASKU where Viitenumero='172376427408'), 'FI9284862429427779');


---- Teokset ja genret

INSERT INTO TEOS (Nimi, Julkaisuvuosi, Kokoelma)
VALUES ('Teos', 1991, null);

INSERT INTO GENRE (Nimi)
VALUES ('Suomiräp');

INSERT INTO ESITTAJA (Nimi)
VALUES ('Cheeck');

INSERT INTO KUUNTELIJA (Kayttaja, Ika, Sukupuoli, Kaupunki, Maa)
VALUES ('tunnus', 20, 'Nainen', 'Lahti', 'Venäjä');

INSERT INTO SOITTOLISTA (Nimi, Kuuntelija)
VALUES ('Suomiräp-soittolista', 'tunnus');

INSERT INTO SOITTOLISTANTEOS (Soittolista, Teos)
VALUES (3, 1);

INSERT INTO TEOKSENTEKIJA (Teos, Esittaja)
VALUES (1, 1);

INSERT INTO TEOKSENGENRE (Teos, Genre)
VALUES (1, 'Suomiräp');

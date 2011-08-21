<?php

  // Return langs
  return array(
  
    // General
    'invalid email address' => 'Sähköpostiosoite ei ole kelvollinen',
   
    // Company validation errors
    'company name required' => 'Yrityksen / organisaation nimi vaaditaan',
    'company homepage invalid' => 'Kotisivun www-osoite on virheellinen',
    
    // User validation errors
    'username value required' => 'Käyttäjätunnus vaaditaan',
    'username must be unique' => 'Pahoittelut, mutta valittu käyttäjätunnus on jo otettu',
    'email value is required' => 'Sähköpostiosoite vaaditaan',
    'email address must be unique' => 'Pahoittelut, mutta valittu sähköpostiosoite on jo otettu',
    'company value required' => 'Käyttäjän täytyy olla osa yritystä / organisaatiota',
    'password value required' => 'Salasana vaaditaan',
    'passwords dont match' => 'Salasanat eivät täsmää',
    'old password required' => 'Vanha salasana vaaditaan',
    'invalid old password' => 'Vanha salasana ei ole kelvollinen',
    'users must belong to a company' => 'Käyttäjän luomiseksi kontaktien täytyy kuulua yritykseen',
    'contact linked to user' => 'Kontakti on linkitetty käyttäjään {0}',
  
  	// Password validation errors
  	'password invalid min length' => 'Salasanan pituus täytyy olla vähintään {0} merkkiä',
  	'password invalid numbers' => 'Salasanassa täytyy olla vähintään {0} numeerista merkkiä',
  	'password invalid uppercase' => 'Salasanassa täytyy olla vähintään {0} isoa kirjainta',
  	'password invalid metacharacters' => 'Salasanassa täytyy olla vähintään {0} erityismerkkiä',
  	'password exists history' => 'Salasanaa on käytetty yhtenä viimeisestä kymmenestä salasanasta',
  	'password invalid difference' => 'Salasanasi täytyy erota vähintään 3 merkillä viimeisestä 10 salasanasta',
  	'password expired' => 'Salasanasi on vanhentunut',
  	'password invalid' => 'Salasanasi ei ole enää voimassa',
    
    // Avatar
    'invalid upload type' => 'Virheellinen tiedostotyyppi.  Sallittuja tyyppejä ovat {0}',
    'invalid upload dimensions' => 'Virheellinen kuvaresoluutio.  Sallittu koko on {0}x{1} pikseliä',
    'invalid upload size' => 'Virheellinen kuvakoko.  Maksimikoko on {0}',
    'invalid upload failed to move' => 'Ladatun tiedoston siirtäminen epäonnistui',
    
    // Registration form
    'terms of services not accepted' => 'Luodaksesi käyttäjätilin sinun täytyy lukea ja hyväksyä käyttöehtomme',
    
    // Init company website
    'failed to load company website' => 'Nettisivun lataus epäonnistui.  Omistajayritystä ei löydy',
    'failed to load project' => 'Aktiivisen työtilan lataus epäonnistui',
    
    // Login form
    'username value missing' => 'Anna käyttäjätunnuksesi',
    'password value missing' => 'Anna salasanasi',
    'invalid login data' => 'Sisäänkirjautumisesi epäonnistui.  Tarkistaisitko sisäänkirjautumistietosi ja yritä uudestaan',
    
    // Add project form
    'project name required' => 'Työtilan nimi vaaditaan',
    'project name unique' => 'Työtilan nimen täytyy olla ainutkertainen',
    
    // Add message form
    'message title required' => 'Otsikko vaaditaan',
    'message title unique' => 'Otsikon täytyy olla ainutkertainen tässä työtilassa',
    'message text required' => 'Teksti vaaditaan',
    
    // Add comment form
    'comment text required' => 'Kommentin teksti vaaditaan',
    
    // Add milestone form
    'milestone name required' => 'Välitavoitteen nimi vaaditaan',
    'milestone due date required' => 'Välitavoitteen määräpäivä vaaditaan',
    
    // Add task list
    'task list name required' => 'Tehtävän nimi vaaditaan',
    'task list name unique' => 'Tehtävän nimen täytyy olla ainutkertainen tässä työtilassa',
    'task title required' => 'Tehtävän otsikko vaaditaan',
  
    // Add task
    'task text required' => 'Tehtävän teksti vaaditaan',
	'repeat x times must be a valid number between 1 and 1000' => 'Toista X kertaa täytyy olla kelvollinen numero väliltä 1 ja 1000.',
	'repeat period must be a valid number between 1 and 1000' => 'Toistumisjakson täytyy olla kelvollinen numero väliltä 1 ja 1000.',
  	'to repeat by start date you must specify task start date' => 'Toistaaksesi alkamispäivän sinun täytyy määritellä tehtävän alkamispäivä',
	'to repeat by due date you must specify task due date' => 'Toistaaksesi määräpäivän sinun täytyy määritellä tehtävän määräpäivä',
	'task cannot be instantiated more times' => 'Tehtävää ei voida toteuttaa useampia kertoja, tämä on viimeinen toisto.',
	
    // Add event
    'event subject required' => 'Tapahtuman aihe vaaditaan',
    'event description maxlength' => 'Kuvauksen täytyy olla alle 3000 merkkiä',
    'event subject maxlength' => 'Aiheen täytyy olla alle 100 merkkiä',
    
    // Add project form
    'form name required' => 'Lomakkeen nimi vaaditaan',
    'form name unique' => 'Lomakkeen nimen täytyy olla ainutkertainen',
    'form success message required' => 'Valmistumismerkintä (muistiinpano) vaaditaan',
    'form action required' => 'Lomakkeen toiminto vaaditaan',
    'project form select message' => 'Valitse muistiinpano',
    'project form select task lists' => 'Valitse tehtävä',
    
    // Submit project form
    'form content required' => 'Lisää sisältöä tekstikenttään',
    
    // Validate project folder
    'folder name required' => 'Kansion nimi vaaditaan',
    'folder name unique' => 'Kansion nimen täytyy olla ainutkertainen tässä työtilassa',
    
    // Validate add / edit file form
    'folder id required' => 'Valitse kansio',
    'filename required' => 'Tiedostonimi vaaditaan',
  	'weblink required' => 'Nettilinkin www-osoite vaaditaan',
    
    // File revisions (internal)
    'file revision file_id required' => 'Version täytyy olla kytkettynä tiedostoon',
    'file revision filename required' => 'Tiedostonimi vaaditaan',
    'file revision type_string required' => 'Tuntematon tiedostotyyppi',
    'file revision comment required' => 'Version kommentti vaaditaan',
    
    // Test mail settings
    'test mail recipient required' => 'Vastaanottajan osoite vaaditaan',
    'test mail recipient invalid format' => 'Virheellinen vastaanottajan osoiteformaatti',
    'test mail message required' => 'Sähköpostiviesti vaaditaan',
    
    // Mass mailer
    'massmailer subject required' => 'Viestin otsikko vaaditaan',
    'massmailer message required' => 'Viestin vartalo vaaditaan',
    'massmailer select recepients' => 'Valitse käyttäjät, jotka tulevat vastaanottamaan tämän sähköpostin',
    
  	//Email module
  	'mail account name required' => 'Sähköpostitilin nimi vaaditaan',
  	'mail account id required' => 'Sähköpostitunnus vaaditaan',
  	'mail account server required' => 'Sähköpostipalvelimen osoite vaaditaan',
  	'mail account password required' => 'Salasana vaaditaan',
	'send mail error' => 'Virhe lähetettäessä sähköpostia.  Mahdollisesti väärät SMTP asetukset.',
    'email address already exists' => 'Tämä sähköpostiosoite on jo käytössä.',
  
  	'session expired error' => 'Istunto vanhentui käyttäjän epäaktiivisuuden vuoksi.  Kirjaudu uudestaan sisään',
  	'unimplemented type' => 'Toteuttamaton tyyppi',
  	'unimplemented action' => 'Toteuttamaton toiminto',
  
  	'workspace own parent error' => 'Tämä työtila ei voi olla sen oma ylemmän tason työtila',
  	'task own parent error' => 'Tämä tehtävä ei voi olla sen oma ylemmän tason tehtävä',
  	'task child of child error' => 'Tämä tehtävä ei voi olla yksi sen alemman tason tehtävistä',
  
  	'chart title required' => 'Kaavion otsikko vaaditaan.',
  	'chart title unique' => 'Kaavion otsikon täytyy olla ainutkertainen.',
    'must choose at least one workspace error' => 'Sinun täytyy valita vähintään yksi työtila minne laitat objektin.',
    
    
    'user has contact' => 'Järjestelmässä on olemassa kontakti, johon tämä käyttäjä on jo osoitettu',
    
    'maximum number of users reached error' => 'Maksimimäärä käyttäjiä saavutettu',
	'maximum number of users exceeded error' => 'Maksimimäärä käyttäjiä on ylitetty.  Tämä sovellus ei toimi enää, ennen kuin asia on korjattu.',
	'maximum disk space reached' => 'Levytilasi on täynnä.  Poista joitakin objekteja ennen kuin yrität lisätä uusia, tai ota yhteyttä käyttötukeen.',
    'name must be unique' => 'Pahoittelut, mutta valittu nimi on jo otettu',
  	'not implemented' => 'Ei toteutettu',
  	'return code' => 'Paluukoodi: {0}',
  	'task filter criteria not recognised' => 'Tehtäväsuodattimen ehtoa \'{0}\' ei tunnistettu',
  	'mail account dnx' => 'Sähköpostitiliä ei ole olemassa',
    'error document checked out by another user' => 'Toinen käyttäjä on varannut käyttöön tämän dokumentin.',
  	//Custom properties
  	'custom property value required' => '{0} vaaditaan',
  	'value must be numeric' => 'Arvojen täytyy olla lukumääräisiä kentälle {0}',
  	'values cannot be empty' => 'Arvot eivät saa olla tyhjiä kentälle {0}',
  
  	//Reports
  	'report name required' => 'Raportin nimi vaaditaan',
  	'report object type required' => 'Raportin objektityyppi vaaditaan',

  	'error assign task user dnx' => 'Yritit osoittaa käyttäjälle, jota ei ole olemassa',
	'error assign task permissions user' => 'Sinulla ei ole käyttöoikeuksia osoittaa tehtävää tälle käyttäjälle',
	'error assign task company dnx' => 'Yritit osoittaa yritykselle, jota ei ole olemassa',
	'error assign task permissions company' => 'Sinulla ei ole käyttöoikeuksia osoittaa tehtävää tälle yritykselle',
  	'account already being checked' => 'Tili on jo merkitty.',
  	'no files to compress' => 'Ei tiedostoja pakattavaksi',
  
  	//Subscribers
  	
  	'cant modify subscribers' => 'Ilmoitusten tilaajia ei voi muokata',
  	'this object must belong to a ws to modify its subscribers' => 'Tämän objektin täytyy kuulua työtilaan, jotta sen ilmoitusten tilaajia voisi muokata.',

  	'mailAccount dnx' => 'Sähköpostitiliä ei ole olemassa',
  	'error add contact from user' => 'Käyttäjästä ei voitu lisätä kontaktia.',
  	'zip not supported' => 'Palvelin ei tue ZIP-pakettitiedostojen käsittelyä',
  	'no tag specified' => 'Tagia ei ole määritelty',
  
    'no mailAccount error' => 'Toiminto ei ole saatavana.  Et ole lisännyt sähköpostitiliä.',
  ); // array

?>

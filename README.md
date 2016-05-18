nl.sp.accesscontrol
===================

Deze extensie regelt een aantal zaken om CiviCRM beter af te schermen voor SP-afdelingsgebruikers:

- Aanpassingen in ACLs contacten en groepen (obv data uit geostelsel) afdelingen kunnen alleen hun eigen contacten zien en hun eigen groepen
- Nieuwe permissie voor view all contacts en edit all contacts zodat we ook voor landelijke gebruikers alle contacten inzichtelijk blijven maar we wel op groepen kunnen filteren
- landelijke gebruikers en administrators zien geen afdelingsgroepen
- Aparte Drupal-permissies voor het tonen van specifieke tabbladen (Tags, Wijzigingenlogboek, ...)
- Restrictie op tabblad activiteiten bij een contact. Alleen de activiteiten die 'met' het contact zijn worden getoond. En niet de activiteiten die toegewezen, dan wel toegevoegd door het contact.(Zie ook issue [#470](https://redmine.sp.nl/issues/470))
- Mogelijk maken van bewerken van custom velden zónder 'edit all contacts' of het contact zelf te kunnen bewerken
- Tonen van links naar webforms op relevante plaatsen (deze moeten nog wel handmatig worden geimporteerd/aangemaakt)
- Standaard-afzendadressen niet tonen in CiviMail, en afdelings/persoonlijke adressen juist wel
- Permissie om een test e-mail naar een group te versturen. 
- Permissie om message templates te mogen bijwerken vanuit het scherm van een nieuwe mailing, e-mail of pdf activiteit
- Overschrijft CRM_Mailing_BAO_Mailing voor een fijnere Access control op Mailings. Standaard zijn ook alle mailings zichtbaar waarvan de groep verwijderd is. (Zie ook issue [#468](https://redmine.sp.nl/issues/468))
- API Permissie voor aanmaken activiteiten
- Instellen filter op activiteiten en aanmaken juiste activiteistypes
- Nieuwe permissie om te bepalen of je toegang hebt to de custom zoekopdracht inclusief/exclusief zoeken

De extensies nl.sp.geostelsel en org.civicoop.postcodenl moeten zijn geïnstalleerd.

Rapporten
---------

Deze extensie bevat ook een rapport waarin alle afdelingsgebruikers getoond worden en tot welke groep van contacten ze toegang hebben.

Evenementenbeheer voor afdelingsgebruikers
------------------------------------------

Deze module implementeert ook functionaliteit zodat lokale afdelingsgebruikers
ook evenementen voor hun afdeling, regio of provincie kunnen toevoegen.

Als een lokale afdelingsgebruiker een evenement aanmaakt dan is het veld Afdeling/Regio/Provincie
verplicht om in te vullen.

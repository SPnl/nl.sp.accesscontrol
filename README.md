nl.sp.accesscontrol
===================

Deze extensie regelt een aantal zaken om CiviCRM beter af te schermen voor SP-afdelingsgebruikers:

- Aanpassingen in ACLs contacten en groepen (obv data uit geostelsel)
- Aparte Drupal-permissies voor het tonen van specifieke tabbladen (Tags, Wijzigingenlogboek, ...)
- Restrictie op tabblad activiteiten bij een contact. Alleen de activiteiten die 'met' het contact zijn worden getoond. En niet de activiteiten die toegewezen, dan wel toegevoegd door het contact.(Zie ook issue [#470](https://redmine.sp.nl/issues/470))
- Mogelijk maken van bewerken van custom velden zónder 'edit all contacts' of het contact zelf te kunnen bewerken
- Tonen van links naar webforms op relevante plaatsen (deze moeten nog wel handmatig worden geimporteerd/aangemaakt)
- Standaard-afzendadressen niet tonen in CiviMail, en afdelings/persoonlijke adressen juist wel
- Permissie om message templates te mogen bijwerken vanuit het scherm van een nieuwe mailing, e-mail of pdf activiteit
- Overschrijft CRM_Mailing_BAO_Mailing voor een fijnere Access control op Mailings. Standaard zijn ook alle mailings zichtbaar waarvan de groep verwijderd is. (Zie ook issue [#468](https://redmine.sp.nl/issues/468))
- API Permissie voor aanmaken activiteiten
- Instellen filter op activiteiten en aanmaken juiste activiteistypes

De extensies nl.sp.geostelsel en org.civicoop.postcodenl moeten zijn geïnstalleerd.

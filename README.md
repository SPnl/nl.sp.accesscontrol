nl.sp.accesscontrol
===================

Deze extensie regelt een aantal zaken om CiviCRM beter af te schermen voor SP-afdelingsgebruikers:

- Aanpassingen in ACLs contacten en groepen (obv data uit geostelsel)
- Aparte Drupal-permissies voor het tonen van specifieke tabbladen (Tags, Wijzigingenlogboek)
- Standaard-afzendadressen niet tonen in CiviMail, en afdelings/persoonlijke adressen juist wel
- Permissie om message templates te mogen bijwerken vanuit het scherm van een nieuwe mailing, e-mail of pdf activiteit
- Mogelijk maken van bewerken van custom velden zónder 'edit all contacts' of het contact zelf te kunnen bewerken
- Tonen van links naar webforms op relevante plaatsen
- Overschrijft CRM_Mailing_BAO_Mailing voor een fijnere Access control op Mailings. Standaard zijn ook alle mailings zichtbaar waarvan de groep verwijderd is. (Zie ook issue [#468](https://redmine.sp.nl/issues/468))
- TODO later: standaard juiste ACLs voor actuele custom fields aanmaken + activiteitstypes + tabs in juiste volgorde

De extensies nl.sp.geostelsel en org.civicoop.postcodenl moeten zijn geïnstalleerd.

<?php
return array(
  	'backup process desc' => 'Ein Backup speichert den momentanen Zustand der gesamten Anwendung in einem komprimierten Verzeichnis. Damit kann man einfach ein Backup der Feng Office-Installation anfertigen.<br /><br />Das Erzeugen eines Backups von Datenbank und Dateisystem kann länger als ein paar Sekunden dauern, weshalb der Backup-Vorgang aus drei Schritten besteht:<br />1. Backup-Prozess starten,<br />2. Backup-Paket herunterladen.<br />3. Optional kann ein bestehendes Backup-Paket manuell gelöscht werden, wodurch es nicht mehr zur Verfügung steht.<br /> ',
  	'backup config warning' => 'Warnung: Die Verzeichnisse „config“ und „tmp“ werden nicht gesichert!',
  	'start backup' => 'Backup-Prozess starten',
  	'start backup desc' => 'Das Starten eines Backup-Prozesses löscht automatisch das vorherige Backup-Paket und erzeugt ein neues.',
  	'download backup' => 'Backup-Paket herunterladen',
  	'download backup desc' => 'Vor dem Herunterladen eines neuen Backup-Paketes muss dieses zuerst erzeugt werden!',
  	'delete backup' => 'Backup-Paket löschen',
  	'delete backup desc' => 'Löscht das letzte Backup-Paket, wodurch es nicht mehr zum Herunterladen zur Verfügung steht. Das Löschen nach dem Herunterladen wird empfohlen.',
  	'backup' => 'Backup',
  	'backup menu' => 'Backup-Menü',
  	'last backup' => 'Das letzte Backup wurde erzeugt am',
  	'no backups' => 'Es stehen keine Backups zum Herunterladen zur Verfügung!',
  	'cron event name cron_backup' => 'Feng Office sichern',
  	'cron event desc cron_backup' => 'Diese Zeitsteuerung sichert Feng Office regelmäßig. Der Administrator wird Sicherungen über den Administrationsbereich herunterladen können. Die Sicherungen werden als Zip-Datei im Verzeichnis „tmp/backup“ gespeichert.',
	'error db backup' => 'Fehler bei der Erstellung des Datenbank-Backups! Überprüfen Sie die Variable „MYSQLDUMP_COMMAND“!',
  	'backup command failed' => 'Backup-Befehl fehlgeschlagen! Überprüfen Sie die Variable „MYSQLDUMP_COMMAND“!',
  	'success db backup' => 'Backup wurde erfolgreich erstellt!',
	'error create backup folder' => 'Fehler bei der Erstellung des Backup-Verzeichnisses! Das Backup kann nicht fertiggestellt werden!',
	'error delete backup' => 'Fehler beim Löschen des Datenbank-Backups!',
	'success delete backup' => 'Das Backup wurde erfolgreich gelöscht!',
	'return code' => 'Return-Code: {0}',

	'success install backup plugin' => 'Backup-Plugin erfolgreich installiert',
	'error install backup plugin' => 'Fehler beim Installieren des Backup-Plugins: {0}',
);
?>
#!/usr/bin/env php
<?php

if (php_sapi_name() !== "cli"){
    exit(1);
}

if ($argc < 2){
    echo "Error: Usage: $argv[0] password\n";
    exit(2);
}

// Acc�s bdd
$host = "localhost";
$login = 'root';
$pass = $argv[1];
$port = "3306";

// Base � exclure de la sauvegarde
$no = array("information_schema","mysql","performance_schema");

// Connexion  � la base
$connex = mysqli_connect($host.":".$port,$login,$pass);

// S�lection des bases � sauvegarder
$sql = "SHOW DATABASES";
$res = mysqli_query($connex,$sql);

// Cr�ation du r�pertoire /data/dump/ si il n'existe pas
if(is_dir("/var/dump") === false) exec("mkdir '/var/dump'",$tab);

while($row = mysqli_fetch_array($res)) {
	// On regarde si la base courante n'est pas dans la liste de celle � sauvegarder
	if(in_array($row[0],$no) === false) {
		// Cr�ation du r�pertoire /data/dump/$row[0] si il n'existe pas
		if(is_dir("/var/dump/".$row[0]) === false) exec("mkdir '/var/dump/'".$row[0],$tab);
		
		$tab = array();
		// S�lection des tables de la base
		$sql = "SHOW TABLES FROM ".$row[0];
		$res2 = mysqli_query($connex,$sql);
		
		while($row2 = mysqli_fetch_array($res2)) {
			// Dump de la table $row2[0]
			$exe = "/usr/bin/mysqldump --force --opt -h $host -u $login --password=$pass -P $port -f --no-create-db --databases ".$row[0]." --tables ".$row2[0]." > /var/dump/".$row[0]."/".$row2[0].".sql";
			exec($exe,$tab);
		}
		// Dump des proc�dures, fonctions, �venements et triggers de la base $row[0]
		$exe = "/usr/bin/mysqldump --force --opt -h $host -u $login --password=$pass -P $port -f --routines --events --triggers  --no-create-info --no-data --no-create-db --skip-opt --databases ".$row[0]." > /var/dump/".$row[0]."/events_routines_triggers.sql";
		exec($exe,$tab);			
	}
}

//$archive = date("dmy").".bdd.tar.gz";
$archive = date("dmy").".bdd.tar.bz2";
//$archive = "yan.bdd.tar.bz2";

// Suppression du backup de la veille
//exec("rm /var/sauvegardes/ftp/*.bdd.tar.gz",$tab);
exec("rm /var/sauvegardes/ftp/*.bdd.tar.bz2",$tab);

// Compressions des fichiers (.sql) g�n�r�s
exec("tar -cjf /var/sauvegardes/bdd/".$archive." /var/dump & > /dev/null",$tab);
//exec("tar -cjf /var/sauvegardes/ftp/".$archive." /var/dump & > /dev/null",$tab);

// Suppression des fichiers (.sql) g�n�r�s
exec("rm -R /var/dump/*",$tab);

// Copie de l'archive pour r�cup�ration ftp
exec("cp /var/sauvegardes/bdd/".$archive." /var/sauvegardes/ftp/.",$tab);

// Cr�ation du r�pertoire /data/ftp/scripts/ si il n'existe pas
if(is_dir("/var/sauvegardes/ftp/scripts") === false) exec("mkdir '/var/sauvegardes/ftp/scripts'",$tab);

// Copie des scripts pour r�cup�ration ftp
exec("cp -R /var/scripts/* /var/sauvegardes/ftp/scripts",$tab);

// Cr�ation du r�pertoire /data/ftp/etc/ si il n'existe pas
if(is_dir("/var/sauvegardes/ftp/config/") === false) exec("mkdir '/var/sauvegardes/ftp/config/'",$tab);

// Copie des fichiers de config (php, mysql, apache) pour r�cup�ration ftp
exec("cp -R /etc/php/7.0/apache2/php.ini /var/sauvegardes/ftp/config/.",$tab);
exec("cp -R /etc/apache2/apache2.conf /var/sauvegardes/ftp/config/.",$tab);
exec("cp -R /etc/mysql/my.cnf /var/sauvegardes/ftp/config/.",$tab);
exec("cp -R /etc/apache2/sites-enabled/ /var/sauvegardes/ftp/config/.",$tab);

// Cr�ation du r�pertoire /data/ftp/crontab/ si il n'existe pas
//if(is_dir("/data/ftp/crontab/") === false) exec("mkdir '/data/ftp/crontab/'",$tab);

// Copie des fichiers crontab pour r�cup�ration ftp
//exec("cp -R /var/cron/tabs/ /data/ftp/crontab/.",$tab);

// Modification des droits du r�pertoire /data/ftp/
exec("chmod -R 775 /var/sauvegardes/ftp/",$tab);
exec("chown -R it-siweb:it-siweb /var/sauvegardes/ftp/",$tab);
?>

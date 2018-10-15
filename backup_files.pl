#!/usr/bin/perl -w

use strict;

my $time=`/bin/date +"\%Hh\%M"`;
chomp $time;
print "Backup files- $time - debut\n";

my $backup = "files";
my $rep = "/var/sauvegardes/$backup";
my $save= "jours";

if(! -d "/var/sauvegardes" ){
    print "/var/sauvegardes n'existe pas\n";
	exit;
}
if(! -d "$rep" ){
	mkdir "$rep";
}

#pour avoir la date
my $date=`/bin/date +"\%d\%m\%y"`;
my $jour = `/bin/date +"\%A"`;
my $mois = `/bin/date +"\%d"`;
chomp $date;
chomp $jour;
chomp $mois;

#pour choisir le type de sauvegarde :jour,semaine,mois
if($mois == "01"){
    $save= "mois";
	`rm  $rep/mois/*`;
    sauver($rep,$date,$save);
    `rm  $rep/semaines/*`;
}
elsif($jour eq "Sunday"){
    $save= "semaines";
    sauver($rep,$date,$save);
    `rm  $rep/jours/*`;
}
else{
    sauver($rep,$date,$save);
}

#fin
my $time2=`/bin/date +"\%Hh\%M"`;
chomp $time2;

#Fonction de sauvegarde
sub sauver
{
    my ($rep,$date,$emplacement) = @_;
	print "sauver($rep,$date,$save)\n";

	if(! -d "$rep/$emplacement" ){
    	mkdir "$rep/$emplacement";
	}
	if( -d "$rep/$date" ){
       `rm -r $rep/$date`; 
    }	
    `mkdir $rep/$date`;

	#copie de www
	`rsync -e ssh -avz --exclude-from=/var/sauvegardes/excludes.txt /var/www/ $rep/$date/www/`;
	#compression 
    `tar --exclude='printwait' --exclude='log_*.txt*' -cvzf $rep/$emplacement/$date.$backup.tar.gz $rep/$date & > /dev/null`;
	#supression rep temp
    `rm -r $rep/$date`;
}


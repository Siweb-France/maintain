#!/usr/bin/perl -w

use strict;

clean("/var/sauvegardes/bdd/","7");
clean("/var/sauvegardes/files/jours/","7");

sub clean
{
	my ($file,$ref) = @_;
	my @liste = `ls $file`;
	
	foreach my $uneligne(@liste){
		chomp $uneligne;
    	if(-d "$file/$uneligne"){
    		clean("$file/$uneligne",$ref);
    	}
    	my $age = -M "$file/$uneligne";
    	if ($age > $ref){
        	print "suppr: $file/$uneligne` \n";
            `/bin/rm -rf $file/$uneligne`;
        }
    	print "$file/$uneligne : $age\n"; 	
    }
}

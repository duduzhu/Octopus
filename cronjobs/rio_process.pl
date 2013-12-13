#!/usr/bin/perl use strict;
print "<html><head><title>GSM Lab RIO Report</title></head></body>\n";
print "<h1>LastUpdate: ".`date`."</h1>\n";
print "<table border="1" ><thead><th>BSC</th><th>LAC-CI</th><th>OMC-R</th><th>Attenu</th></thead>\n";
my @power_list=@ARGV;
foreach my $power_file (@power_list)
{
    open(POWER_INFILE, "$power_file") or die "open $power_file failed";
    <POWER_INFILE>;
    my $power_col=<POWER_INFILE>;
    if(defined $power_col) {
	# try to locate the columns that matter
        my @power_col_arry = split(";", $power_col);
        my $atten=0;
        my $powerid=0;
        for(my $power_counter=0; $power_counter < @power_col_arry; $power_counter++)
        {
            if($power_col_arry[$power_counter] eq "RnlPowerControlInstanceIdentifier") {
                $powerid = $power_counter;
                }
            if($power_col_arry[$power_counter] eq "BsTxPwrAttenuation") {
                $atten = $power_counter;
                }
        }
        my %result_300;            
        while (<POWER_INFILE>) {
            chomp;
            my @power_a = split(";");
            $result_300{$power_a[$powerid]}=$power_a[$atten];
        }
        my $omcr = $power_file;
	$omcr =~ s/\.power//;
        my $bsc_file = "$omcr.bsc";
        open(BSC_INFILE, "$bsc_file") || die "open $bsc_file failed \n";
        <BSC_INFILE>;
        my $bsc_col=<BSC_INFILE>;
        my @bsc_col_arry = split(";", $bsc_col);        
        my $bsc_id_col =0;
        my $bsc_userlabel_col =0;
        for(my $bsc_counter=0; $bsc_counter < @bsc_col_arry; $bsc_counter++){
            if($bsc_col_arry[$bsc_counter] eq "RnlAlcatelBSCInstanceIdentifier") {
                $bsc_id_col = $bsc_counter;
                }        
            if($bsc_col_arry[$bsc_counter] eq "UserLabel") {
                $bsc_userlabel_col = $bsc_counter;
                }        
        }
        my %bscid_userlabel;
        while (<BSC_INFILE>) {
            chomp;
            my @bsc_a = split(";");
            $bscid_userlabel{$bsc_a[$bsc_id_col]} = $bsc_a[$bsc_userlabel_col];
        }
        my $cell_file = "$omcr.cell";
        open(CELL_INFILE, "$cell_file") or die;
        <CELL_INFILE>;
        my $cell_col=<CELL_INFILE>;
        my @cell_col_arry = split(";", $cell_col);

        my $cgi=0;
        my $cellid=0;
        my $bcch=0;
        my $bsic=0;
        my $state=0;
        my $sector=0;

        for(my $cell_counter=0; $cell_counter < @cell_col_arry; $cell_counter++){
            if($cell_col_arry[$cell_counter] eq "CellInstanceIdentifier") {
                $cellid = $cell_counter;
                }
            if($cell_col_arry[$cell_counter] eq "BCCHFrequency") {
                $bcch = $cell_counter;
                }
            if($cell_col_arry[$cell_counter] eq "BsIdentityCode") {
                $bsic = $cell_counter;
                }
            if($cell_col_arry[$cell_counter] eq "CellGlobalIdentity") {
                $cgi = $cell_counter;
                }
            if($cell_col_arry[$cell_counter] eq "AdministrativeState") {
                $state = $cell_counter;
                }
            if($cell_col_arry[$cell_counter] eq "RnlSupportingSector") {
                $sector = $cell_counter;
                }
        }        
        while (<CELL_INFILE>) {
            chomp;
            my @cell_a = split(";");
            if(exists $result_300{$cell_a[$cellid]}) {
                my $bsc_id_pos = index($cell_a[$sector],"bsc");
                my $btsRdn_pos = index($cell_a[$sector],"btsRdn");
                my $bsc_id = substr($cell_a[$sector],$bsc_id_pos+4,$btsRdn_pos-$bsc_id_pos-6);

		my $ATT = $result_300{$cell_a[$cellid]};
		if($ATT < 300)
		{
		 my $BSC = $bscid_userlabel{$bsc_id};
		 if(!($BSC =~ /LOAD|BSC20D/)){
		    $cell_a[$cgi]  =~ /lac (\d+)}.*ci (\d+)}/;
		    my $LAC=$1;
		    my $CI=$2;
		    print "<tr><td>$BSC</td><td>$LAC-$CI</td><td>$omcr</td><td>$ATT</td></tr>\n";
		 }
		}
                #print CELL_OUTFILE "$cell_a[$cellid];$cell_a[$bcch];$cell_a[$bsic];$cell_a[$cgi];$result_300{$cell_a[$cellid]};$cell_a[$state];$bscid_userlabel{$bsc_id}\n";

            }
        }
        close POWER_INFILE;
        close BSC_INFILE;
        close CELL_INFILE;
    }
}
print "</table></body></html>";
exit;


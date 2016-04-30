<?php
include('./config.php');
$email = $_POST['f_email'];
list($userName, $mailDomain) = explode("@", $email); 
if(!emailLookup($mailDomain, "MX"))
  die("Invalid email address");
$request = "INSERT INTO `$tablename` (`f_email`) VALUES ('$email')";
$con=mysqli_connect("$host","$user","$password","$db");
if(!mysqli_query($con, $request))
	echo(mysqli_error($con));
else
	echo "Address added to mail list.";
	
	
function emailLookup($hostName, $recType = '') { 
  if(!empty($hostName)) { 
    if( $recType == '' ) $recType = "MX"; 
    exec("nslookup -type=$recType $hostName", $result); 
    foreach ($result as $line) { 

      if(preg_match("/$hostName/i",$line)) { 
        return true; 
      } 

    } 
    return false; 
  } 
  return false; 
}
?>
					
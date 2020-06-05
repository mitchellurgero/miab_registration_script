<?php
//single file registration script!
session_start();
//requires
require("simple-php-captcha.php");

//uncomment to Disable registration.
//die("<br><br><center>Registration is currently disabled due to closed beta testing. Please try again in a couple days! :D</center><br><center><p>Right now, beta testing is invite only.</p></center>");

//Config (You really only need to change domain, m, & p. But anything in the config array can be changed how you want :)
$config = array(
	"domain"	=> "domain.com",
	"m"			=> escapeshellcmd("adminuser@domain.com"),
	"p"			=> escapeshellcmd("adminuserpassword"),
	"captcha" => array( //Configure Captcha settings for registration.
        'min_length' => 8,
        'max_length' => 10,
        'backgrounds' => array('backgrounds/45-degree-fabric.png','backgrounds/45-degree-fabric.png','backgrounds/45-degree-fabric.png','backgrounds/45-degree-fabric.png'),
        'fonts' => array('fonts/times_new_yorker.ttf'),
        'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
        'min_font_size' => 15,
        'max_font_size' => 20,
        'color' => '#666',
        'angle_min' => 0,
        'angle_max' => 20,
        'shadow' => true,
        'shadow_color' => '#fff',
        'shadow_offset_x' => -1,
        'shadow_offset_y' => 1
    	)
	);
//Check for POST
head();
if(isset($_SESSION['email']) && isset($_POST['username'])){
	//body();
	$_SESSION['message'] = '<p style="color:DarkRed">You already created an email. Get outta here!</p>';
	
}
if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['captcha'])){
	//Process a registration.
	if($_POST['captcha'] == $_SESSION['captcha']['code']){
		if(!isset($_SESSION['email'])){
			$ret = register($_POST['username'], $_POST['password']);
			if($ret === true){
				$_SESSION['message'] = '<text><p style="color:green">E-mail '.$_POST['username'].'@'.$config['domain'].' successfully created! You may login to that email using the following settings:</p>
					<br>
					Server: <b>'.$config['domain'].'</b><br>
					Username: <b>'.$_POST['username'].'@'.$config['domain'].'</b><br>
					Password: Duh?<br>
					Type: <b>IMAP/SMTP</b><br>
					Ports: <b>993/587</b><br>
					SSL/TLS: <b>Yes</b><br>
					</text>';
					$_SESSION['email'] = $username."@".$config['domain'];
			} else {
				$_SESSION['message'] = '<text style="color:darkred">There was an error creating the email, please try again or contact the systems administrator!<br>'.$ret.'</text>';
			}	
		}
		
	} else {
		$_SESSION['message'] = '<text style="color:darkred">The captcha was invalid, please try again!</text>';
	}

}
bodyLogin();
function head(){
	global $config;
	echo '';
	?>
<html>
	<head>
		<title><?php echo $config['domain']; ?> Registration</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<style>
		.frame {
		  display: block;
		  width: 100vw;
		  height: 80vh;
		  max-width: 100%;
		  margin: 0;
		  padding: 0;
		  border: 0 none;
		  box-sizing: border-box;
		}
		</style>
	</head>

	<?php
}

function bodyLogin(){
	global $config;
	$_SESSION['captcha'] = simple_php_captcha($config['captcha']);
	echo '';
	?>
	<body>
	<div class="container">
		<div class="page-header">
	  		<h1><?php echo $config['domain']; ?> Registration Form</h1>
		</div>
	<div class="row">
		<div class="col-md-6">
			<iframe src="eula.html" class="frame"></iframe><br />
		</div>
		<div class="col-md-6">
			<form action="<?php echo __FILE__; ?>" method="POST">
				<div class="form-group">
			    <label for="user">Username:</label>
			    <div class="row">
				    <div class="col-md-6">
				    	<input type="username" class="form-control" id="username" name="username">
				    </div>
				    <div class="col-md-6">
				    	<p>@<?php echo $config['domain']; ?></p>
				    </div>
			    </div>
			  </div>
			  <div class="form-group">
			    <label for="pass">Password:</label>
			    <input type="password" class="form-control" id="password" name="password">
			  </div>
			  	<div class="form-group">
			  		<label for="cap">Captcha Request:</label><br />
			  		<img src="<?php echo $_SESSION['captcha']['image_src']; ?>">&nbsp;&nbsp;<input type="text" name="captcha" id="captcha" rows="12">
			  	</div>
			  <small>By clicking "Submit" you are agreeing to the Terms and Conditions found to the left of this form.</small><button type="submit" class="btn btn-primary pull-right">Submit</button>
			</form><br>
			<?php
			if(isset($_SESSION['message']) && $_SESSION['message'] !== ""){
				echo $_SESSION['message'];
				$_SESSION['message'] = "";
			}
			?>
		</div>
	</div>
	</div>
	</body>
</html>
	<?php
}
function register($username, $password){
	global $config;
	//first make sure this shit is safe.
	$username = clean($username);
	$password = escapeshellcmd($password);
	$return = shell_exec('curl -X POST -k --insecure --user '.$config['m'].':'.$config['p'].' -d "email='.$username.'@'.$config['domain'].'" -d "password='.$password.'" https://localhost/admin/mail/users/add');
	$bool = false;
	if(strpos($return, 'mail user added') !== false){
		$bool = true;
	} else if(strpos($return, 'User already exists.') !== false){
		$bool = "User account already exists, please use a different name.";
	} else {
		$bool = false;
	}
	return $bool;
}
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
?>

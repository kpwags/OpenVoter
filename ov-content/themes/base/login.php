<?php
if (isset($_REQUEST['redirecturl'])) {
	$previous_page = $_GET['redirecturl'];
} elseif (!isset($_SERVER['HTTP_REFERER']) || strripos($_SERVER['HTTP_REFERER'], "register") || strripos($_SERVER['HTTP_REFERER'], "login")) {
	$previous_page = "/";
} else {
	$previous_page = $_SERVER['HTTP_REFERER'];
}

if ($ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /");
	exit();
}

$is_mobile = $ovContent->IsMobileBrowser();
if ($is_mobile && MOBILEEXISTS) {
	header("Location: /m/login");
	exit();
}

include (get_head());
?>
<title>Sign in to <?php echo $ovSettings->Title(); ?></title>
</head>
<body>
<?php
include ('category-bar-hidden.php');
include (get_header());
?>
<div class="login-form">
	<h1>Sign in to <?php echo $ovSettings->Title(); ?></h1>
	<form action="/php/user_login.php" method="post">
		<?php
			if (isset($_GET['error'])) {
				if ($_GET['error'] == 1) { $message = "Invalid Username or Password"; }
				if ($_GET['error'] == 2) { $message = "Sorry but your account has been suspended"; }
				if ($_GET['error'] == 3) { $message = "Sorry but your account has been banned"; }
				if ($_GET['error'] == 4) { $message = "Sorry but your account has been banned"; }
				
		?>
				<div class="error-box"><?php echo $message; ?></div>
		<?php
			}
		?>

		<div class="field">
			<input type="text" size="35" id="username" name="username" placeholder="Username or E-mail Address" />
		</div>
		<div class="field">
			<input type="password" size="35" maxlength="20" id="password" name="password" placeholder="Password" /><span class="forgot-password"><a href="/recover-password" title="Forget your password?">Forget your password?</a></span>
		</div>
		<div class="field">
			<input type="checkbox" class="textbox_22" id="remember" name="remember" value="yes" />&nbsp;&nbsp;<label for="remember">Remember Login</label>
		</div>
		<div class="field">
			<input type="hidden" value="<?php echo $previous_page; ?>" name="previous_page" />
			<input type="submit" id="submit" name="submit" value="Log In" class="normal-button" />
		</div>
	</form>

	<div class="register">
		Need an account? <a href="/sign-up" title="Sign up for an account">Sign up here!</a>
	</div>
</div>
<?php
include (get_footer());
?>
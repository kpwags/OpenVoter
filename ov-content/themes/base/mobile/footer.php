<div id="footer-menu">
	<ul>
		<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
			<li><a href="/php/mobile/logout.php">logout</a></li>
		<?php } else { ?>
			<li><a href="/m/login">login</a></li>
		<?php } ?>
		<li>
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<a href="/m/users/<?php echo strtolower($ovUserSecurity->LoggedInUsername()); ?>">profile</a>
			<?php } ?>
		</li>
	</ul>
</div>
<div id="footer">&copy; Copyright 2011 <?php echo $ovSettings->Title(); ?></div>
</body>
</html>
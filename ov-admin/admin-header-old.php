<div id="admin_header">
	<div class="content">
		<div class="header-logo">
				<a href="/ov-admin" title="Admin Console" class="page_title"><?php echo $ovSettings->Title(); ?> Admin Console</a>
				<a href="/" title="<?php echo htmlspecialchars($ovSettings->Title()); ?>" class="header_link">&lt;&lt; Back to site</a>
		</div>
		<div class="header-admin-info">
			<?php if ($ovAdminSecurity->IsAdminLoggedIn()) { ?>
				<span class="bold">Welcome <?php echo $ovAdminSecurity->AdminName(); ?></span>
				&nbsp;&nbsp;<a href="/ov-admin/profile" title="Your Profile" class="header_link">Your Profile</a>&nbsp;&nbsp;
				<a href="/ov-admin/logout.php" title="Logout" class="header_link">Log Out</a>
			<?php } else { ?>
				&nbsp;
			<?php } ?>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
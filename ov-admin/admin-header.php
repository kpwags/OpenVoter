<div id="header">
	<div class="logo">
		<a href="/ov-admin"><img src="/ov-admin/img/openvoter-logo.png" alt="openvoter" /></a>
	</div>
	<?php if ($ovAdminSecurity->IsAdminLoggedIn()) { ?>
		<div class="admin-user">
			<span class="name"><?php echo $ovAdminSecurity->AdminName(); ?></span>
			<span class="arrow"><img src="/ov-admin/img/arrow.png" alt="openvoter" /></span>
		</div>
	<?php } ?>
	<div class="clearfix"></div>
</div>
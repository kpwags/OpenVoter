<?php 
	$alert_count = $ovAlerting->GetAlertCount(); 
?>

<?php if ($ovAdminSecurity->IsAdminLoggedIn()) { ?>
<div id="admin-bar">
	<div class="content">
		<ul>
			<li><a href="/ov-admin">Admin Console</a></li>
			<li><a href="/ov-admin/reports">Reports</a></li>
			<li><a href="/ov-admin/feedback">Feedback</a></li>
		</ul>
	</div>
</div>
<?php } ?>


<!-- USER BAR -->
<div id="user-bar">
	<div class="content">
		<ul class="main-list">
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<?php
					// USER IS LOGGED IN
					$loggedInUser = new ovoUser(false, $ovUserSecurity->LoggedInUsername());
					$user_lists = $ovList->GetUserLists();
				?>

				<li class="top-item"><a href="/submit" title="Share New Link" class="qtooltip-bottom non-alert">Share New Link</a></li>
				
				<li class="top-item lists-item">
					<a onclick="showListsDropdown()" title="Lists" class="non-alert"><img src="/<?php echo get_theme_directory(); ?>img/lists.png" alt="Lists" />Lists</a>
					<div class="lists-dropdown">
						<ul>
							<li><a href="/friend-activity" title="Friends">Friends</a></li>
							<?php 
								if ($user_lists) { 
									foreach ($user_lists as $list) { 
							?>
										<li>
											<a href="/lists/<?php echo strtolower($ovUserSecurity->LoggedInUsername()); ?>/<?php echo $list['unique_name']; ?>" title="<?php echo htmlspecialchars($list['name']); ?>">
												<?php echo htmlspecialchars($list['name']); ?>
											</a>
										</li>
							<?php 	} 
								}
							?>
							<li>
								<a href="/manage-lists" title="Manage Lists">
									<img src="/<?php echo get_theme_directory(); ?>img/icons/settings.png" alt="Settings" />Manage Lists
								</a>
							</li>
						</ul>
					</div>
				</li>
				
				<?php if ($alert_count > 0) { ?>
					<li class="top-item"><a href="/notifications" title="<?php echo $alert_count; ?> New Notifications" class="qtooltip-bottom notification-active"><?php echo $alert_count; ?></a></li>
				<?php } else { ?>
					<li class="top-item"><a href="/notifications" title="Notifications" class="notification">0</a></li>
				<?php } ?>
				
				<li class="top-item last-item user-item" onclick="window.location = '/users/<?php echo strtolower($ovUserSecurity->LoggedInUsername()); ?>'">					
					<img src="<?php echo $loggedInUser->Avatar(); ?>" alt="<?php echo $ovUserSecurity->LoggedInUsername(); ?>" width="32" height="32" /><?php echo $ovUserSecurity->LoggedInUsername(); ?>
					<div class="user-dropdown">
						<div class="user-dropdown-avatar"><img src="<?php echo $loggedInUser->Avatar(); ?>" alt="<?php echo $ovUserSecurity->LoggedInUsername(); ?>" width="64" height="64" /></div>
						<div class="user-dropdown-info">
							<div class="username"><?php echo $loggedInUser->Username(); ?></div>
							<div class="karma"><?php echo $loggedInUser->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
						</div>
						<div class="clearfix"></div>	
						<ul>
							<li><a href="/users/<?php echo strtolower($ovUserSecurity->LoggedInUsername()); ?>" title="<?php echo $ovUserSecurity->LoggedInUsername(); ?>">Profile</a></li>
							<li><a href="/settings" title="Account Settings">Account Settings</a></li>
							<li><a href="/logout" title="Log Out">Log Out</a></li>
						</ul>
					</div>
				</li>
			<?php } else { ?>
				<!-- USER IS NOT LOGGED IN -->
				<li class="top-item"><a href="/login" title="Login to <?php echo $ovSettings->Title(); ?>" class="qtooltip-bottom non-alert">Log In</a></li>
				<li class="top-item last-item"><a href="/sign-up" title="Sign Up for <?php echo $ovSettings->Title(); ?>" class="qtooltip-bottom non-alert">Sign Up</a></li>
			<?php } ?>
		</ul>
	</div>
</div>

<!-- HEADER LOGO AND SEARCH -->
<div id="header">
	<div class="content">
		<div class="header-logo">
			<a href="/" title="<?php echo htmlspecialchars($ovSettings->Title()); ?>">
				<img src="/<?php echo get_theme_directory(); ?>img/openvoter-logo.jpg" alt="<?php echo htmlspecialchars($ovSettings->Title()); ?>" />
			</a>
		</div>
		<div class="header-search">
			<div class='search-form'>
				<form action="/php/prepare_search.php" method="post" class="header-search-form">
					<input type="text" size="26" name="keywords" id="keywords" placeholder="Search <?php echo $ovSettings->Title(); ?>" />
					<button type="submit" name="submit"></button>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>

<div class="container">
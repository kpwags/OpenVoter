<?php
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /m/login?redirecturl=/m/notifications");
	exit();
}



$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

$type = "home";
if (isset($_GET['type'])) {
	$type = $_GET['type'];
}

include (get_mobile_head());
?>
<title>Notifications | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
</head>
<body>
	<?php
	include (get_mobile_header());
	?>
	
	<?php
		$alert_category_count = $ovAlerting->GetAlertCategoryCounts();
	?>
	<input type="hidden" id="share-alert-count" value="<?php echo $alert_category_count['share_alert_count']; ?>"/>
	<input type="hidden" id="comment-alert-count" value="<?php echo $alert_category_count['comment_alert_count']; ?>"/>
	<input type="hidden" id="follower-alert-count" value="<?php echo $alert_category_count['follower_alert_count']; ?>"/>
	<input type="hidden" id="favorite-alert-count" value="<?php echo $alert_category_count['favorite_alert_count']; ?>"/>
	<input type="hidden" id="site-title-text" value="<?php echo $ovSettings->Title(); ?>" />
	
	<?php if ($type == "comments") { ?>
		<ul class="notifications-list">
			<li class="title-item">New Comments</li>
		<?php
			$alerts = $ovAlerting->GetCommentAlerts();
			if ($alerts) {
				foreach ($alerts as $alert) {
		?>
					
					<li onclick="navigateTo('/php/mobile/process-alert.php?id=<?php echo $alert['id']; ?>&amp;type=comment&amp;url=/m<?php echo urlencode($alert['submission']); ?>')">
						<div class="notification-text">
							<div class="notification-normal-text"><?php echo $alert['username']; ?> has posted a new comment on</div>
							<?php echo $alert['title']; ?>
						</div>
						<a class="arrow"></a>
						<div class="clearfix">
					</li>
		<?php
				}
			} else {
		?>
			<li><div class="notification-normal-text">No new comments</div></li>
		<?php 
			}
		?>
		</ul>
		<?php if ($alerts) { ?>
			<div class="mark-all-read"><button class="normal-button button-full-width" onclick="markAllAlertsRead('comments')">Mark All Read</button></div>
		<?php } ?>
	<?php } elseif ($type == "shares") { ?>
		<ul class="notifications-list">
			<li class="title-item">Shares</li>
		<?php
			$alerts = $ovAlerting->GetShareAlerts();
			if ($alerts) {
				foreach ($alerts as $alert) {
		?>					
					<li onclick="navigateTo('/php/mobile/process-alert.php?id=<?php echo $alert['id']; ?>&amp;type=share&amp;url=/m<?php echo urlencode($alert['submission']); ?>')">
						<div class="notification-text">
							<div class="notification-normal-text"><?php echo $alert['username']; ?> has shared:</div>
							<?php echo $alert['title']; ?>
							<?php if ($alert['message'] != "") { ?>
								<div class="share-message">
									<?php echo htmlspecialchars($alert['message']); ?>
								</div>
							<?php } ?>
						</div>
						<a class="arrow"></a>
						<div class="clearfix"></div>
					</li>
		<?php
				}
			} else {
		?>
			<li><div class="notification-normal-text">No new shares</div></li>
		<?php 
			}
		?>
		</ul>
		<?php if ($alerts) { ?>
			<div class="mark-all-read"><button class="normal-button button-full-width" onclick="markAllAlertsRead('shares')">Mark All Read</button></div>
		<?php } ?>
	<?php } elseif ($type == "followers") { ?>
		<ul class="notifications-list">
			<li class="title-item">New Followers</li>
		<?php
			$alerts = $ovAlerting->GetFollowerAlerts();
			if ($alerts) {
				foreach ($alerts as $alert) {
		?>					
					<li onclick="navigateTo('/php/mobile/process-alert.php?id=<?php echo $alert['id']; ?>&amp;type=follower&amp;url=/m<?php echo urlencode("/users/" . strtolower($alert['username'])); ?>')">
						<div class="notification-text">
							<?php echo $alert['username']; ?> has started to follow you.
						</div>
						<a class="arrow"></a>
						<div class="clearfix"></div>
					</li>
		<?php
				}
			} else {
		?>
			<li><div class="notification-normal-text">No new followers</div></li>
		<?php 
			}
		?>
		</ul>
		<?php if ($alerts) { ?>
			<div class="mark-all-read"><button class="normal-button button-full-width" onclick="markAllAlertsRead('followers')">Mark All Read</button></div>
		<?php } ?>
	<?php } elseif ($type == "favorites") { ?>
		<ul class="notifications-list">
			<li class="title-item">New Favorites</li>
		<?php
			$alerts = $ovAlerting->GetFavoriteAlerts();
			if ($alerts) {
				foreach ($alerts as $alert) {
		?>					
					<li onclick="navigateTo('/php/mobile/process-alert.php?id=<?php echo $alert['id']; ?>&amp;type=favorite&amp;url=/m<?php echo urlencode($alert['submission']); ?>')">
						<div class="notification-text">
							<div class="notification-normal-text"><?php echo $alert['username']; ?> has marked your submission a favorite</div>
							<?php echo $alert['title']; ?>
						</div>
						<a class="arrow"></a>
						<div class="clearfix"></div>
					</li>
		<?php
				}
			} else {
		?>
			<li><div class="notification-normal-text">No new followers</div></li>
		<?php 
			}
		?>
		</ul>
		<?php if ($alerts) { ?>
			<div class="mark-all-read"><button class="normal-button button-full-width" onclick="markAllAlertsRead('favorites')">Mark All Read</button></div>
		<?php } ?>
	<?php } else { ?>
		
		<?php
			$alert_category_count = $ovAlerting->GetAlertCategoryCounts();
			$share_count_text = "";
			$comment_count_text = "";
			$follower_count_text = "";
			$favorite_count_text = "";

			if ($alert_category_count['share_alert_count'] > 0) {
				$share_count_text = "(" . $alert_category_count['share_alert_count'] . ")";
			}

			if ($alert_category_count['comment_alert_count'] > 0) {
				$comment_count_text = "(" . $alert_category_count['comment_alert_count'] . ")";
			}

			if ($alert_category_count['follower_alert_count'] > 0) {
				$follower_count_text = "(" . $alert_category_count['follower_alert_count'] . ")";
			}

			if ($alert_category_count['favorite_alert_count'] > 0) {
				$favorite_count_text = "(" . $alert_category_count['favorite_alert_count'] . ")";
			}
		?>
		<ul class="notifications-list">
			<li onclick="navigateTo('/m/notifications/shares')">
				<div class="notification-text">Shares <span id="all-shares-alerts"><?php echo $share_count_text; ?></span></div>
				<a class="arrow"></a>
				<div class="clearfix"></div>
			</li>
			<li onclick="navigateTo('/m/notifications/comments')">
				<div class="notification-text">Comments <span id="all-comments-alerts"><?php echo $comment_count_text; ?></span></div>
				<a class="arrow"></a>
				<div class="clearfix"></div>
			</li>
			<li onclick="navigateTo('/m/notifications/followers')">
				<div class="notification-text">Followers <span id="all-followers-alerts"><?php echo $follower_count_text; ?></span></div>
				<a class="arrow"></a>
				<div class="clearfix"></div>
			</li>
			<li onclick="navigateTo('/m/notifications/favorites')">
				<div class="notification-text">Favorites <span id="all-favorites-alerts"><?php echo $favorite_count_text; ?></span></div>
				<a class="arrow"></a>
				<div class="clearfix"></div>
			</li>
		</ul>
		<?php if ($alert_count > 0) { ?>
			<div class="mark-all-read"><button class="normal-button button-full-width" onclick="markAllAlertsRead('all')">Mark All Read</button></div>
		<?php } ?>
	<?php } ?>
	
<?php include(get_mobile_footer()); ?>
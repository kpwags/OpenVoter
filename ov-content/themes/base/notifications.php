<?php
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /login");
	exit();
}

$alert_type = $_GET['type'];

$alert_count = $ovAlerting->GetAlertCount();
$alert_category_count = $ovAlerting->GetAlertCategoryCounts();

if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
}

$share_count_text = "";
$comment_count_text = "";
$follower_count_text = "";
$favorite_count_text = "";

if ($alert_category_count['share_alert_count'] > 0) {
	$share_count_text = " (" . $alert_category_count['share_alert_count'] . ")";
}

if ($alert_category_count['comment_alert_count'] > 0) {
	$comment_count_text = " (" . $alert_category_count['comment_alert_count'] . ")";
}

if ($alert_category_count['follower_alert_count'] > 0) {
	$follower_count_text = " (" . $alert_category_count['follower_alert_count'] . ")";
}

if ($alert_category_count['favorite_alert_count'] > 0) {
	$favorite_count_text = " (" . $alert_category_count['favorite_alert_count'] . ")";
}

include (get_head());
?>
<title>Notifications<?php echo $alert_count_text; ?> | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
</head>
<body>
	<?php include ('category-bar-hidden.php'); ?>

	<?php
	include (get_header());
	?>

	<div class="side-list">
		<ul>
			<li <?php if ($alert_type == "shares") { ?> class="active-item"<?php } ?>><a href="/notifications/shares">Shares<span id="share-alert-count"><?php echo $share_count_text; ?></span></a></li>
			<li <?php if ($alert_type == "comments") { ?> class="active-item"<?php } ?>><a href="/notifications/comments">Comments<span id="comment-alert-count"><?php echo $comment_count_text; ?></a></li>
			<li <?php if ($alert_type == "followers") { ?> class="active-item"<?php } ?>><a href="/notifications/followers">Followers<span id="follower-alert-count"><?php echo $follower_count_text; ?></a></li>
			<li <?php if ($alert_type == "favorites") { ?> class="active-item"<?php } ?>><a href="/notifications/followers">Followers<span id="follower-alert-count"><?php echo $follower_count_text; ?></a></li>
		</ul>
	</div>

	<div class="list-page-main-content" id="notifications-area">
	
		<?php
			if ($alert_type == "comments") {
				$alerts = $ovAlerting->GetCommentAlerts();
				if ($alerts) {
					foreach ($alerts as $alert) {
		?>
						<div class="alert" id="alert-<?php echo $alert['id']; ?>">
							<div class="clear-alert alert-x qtooltip" onclick="MarkAlertRead('comments', '<?php echo $alert['id']; ?>')" title="Mark Read">
								<img src="/<?php echo get_theme_directory(); ?>img/alert-x.png" alt="" onmouseover="this.src='/<?php echo get_theme_directory() . "img/alert-x-hover.png" ?>'" onmouseout="this.src='/<?php echo get_theme_directory() . "img/alert-x.png" ?>'" />
							</div>
							<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>"><img src="<?php echo $alert['avatar']; ?>" alt="<?php echo htmlspecialchars($alert['username']); ?>" width="16" height="16" /></a>
							<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>"><?php echo $alert['username']; ?></a>&nbsp;has posted a new comment on&nbsp;
							<a href="/php/process_alert.php?id=<?php echo $alert['id']; ?>&amp;type=comment&amp;url=<?php echo urlencode($alert['submission']); ?>"><?php echo $alert['title']; ?></a>
						</div>
						<div class="clearfix"></div>
		<?php
					}
		?>
		
					<div class="all-alerts-read">
						<button onclick="MarkAllAlertsRead('comments')" class="normal-button">Mark All Comment Alerts Read</button>
					</div>
		<?php
				} else { // no new comment alerts
		?>
					<div>No New Comments</div>
		<?php
				}
			} elseif ($alert_type == "followers") {
				$alerts = $ovAlerting->GetFollowerAlerts();
				if ($alerts) {
					foreach ($alerts as $alert) {
		?>
						<div class="alert" id="alert-<?php echo $alert['id']; ?>">
							<div class="alert-content">
								<div class="alert-text">
									<a href="/php/process_alert.php?id=<?php echo $alert['id']; ?>&amp;type=follower&amp;url=<?php echo urlencode("/users/" . strtolower($alert['username'])); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>">
										<img src="<?php echo $alert['avatar']; ?>" alt="<?php echo htmlspecialchars($alert['username']); ?>" width="16" height="16" />
									</a>
									<a href="/php/process_alert.php?id=<?php echo $alert['id']; ?>&amp;type=follower&amp;url=<?php echo urlencode("/users/" . strtolower($alert['username'])); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>"><?php echo $alert['username']; ?></a>
									&nbsp;has started to follow you.
								</div>
								<div class="alert-action">
									<a onclick="MarkAlertRead('followers', '<?php echo $alert['id']; ?>')" class="alert-x qtooltip" title="Mark Read">
										<img src="/<?php echo get_theme_directory(); ?>img/alert-x.png" alt="" onmouseover="this.src='/<?php echo get_theme_directory() . "img/alert-x-hover.png" ?>'" onmouseout="this.src='/<?php echo get_theme_directory() . "img/alert-x.png" ?>'" />
									</a>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
		<?php
					}
		?>
		
					<div class="all-alerts-read">
						<button onclick="MarkAllAlertsRead('followers')" class="normal-button">Mark All Follower Alerts Read</button>
					</div>
		<?php
				} else { // no new follower alerts
		?>
					<div>No New Followers</div>
		<?php
				}
			} elseif ($alert_type == "favorites") {
				$alerts = $ovAlerting->GetFavoriteAlerts();
				if ($alerts) {
					foreach ($alerts as $alert) {
		?>
						<div class="alert" id="alert-<?php echo $alert['id']; ?>">
							<div class="alert-content">
								<div class="alert-text">
									<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>">
										<img src="<?php echo $alert['avatar']; ?>" alt="<?php echo htmlspecialchars($alert['username']); ?>" width="16" height="16" />
									</a>
									<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>"><?php echo $alert['username']; ?></a>
									&nbsp;has marked&nbsp;
									<a href="/php/process_alert.php?id=<?php echo $alert['id']; ?>&amp;type=favorite&amp;url=<?php echo urlencode($alert['submission']); ?>"><?php echo $alert['title']; ?></a>
									&nbsp;as a favorite.
								</div>
								<div class="alert-action">
									<a onclick="MarkAlertRead('favorites', '<?php echo $alert['id']; ?>')" class="alert-x qtooltip" title="Mark Read">
										<img src="/<?php echo get_theme_directory(); ?>img/alert-x.png" alt="" onmouseover="this.src='/<?php echo get_theme_directory() . "img/alert-x-hover.png" ?>'" onmouseout="this.src='/<?php echo get_theme_directory() . "img/alert-x.png" ?>'" />
									</a>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
		<?php
					}
		?>
		
					<div class="all-alerts-read">
						<button onclick="MarkAllAlertsRead('favorites')" class="normal-button">Mark All Favorites Alerts Read</button>
					</div>
		<?php
				} else { // no new favorite alerts
		?>
					<div>No New Favorites</div>
		<?php
				}
			} else { 
				$alerts = $ovAlerting->GetShareAlerts();
				if ($alerts) {
		?>
					<div class="margin_tb_20">To see any personalized messages along with the share, roll your mouse over the <img src="/<?php echo get_theme_directory(); ?>img/notes.png"  width="16" height="16" style="vertical-align:middle" />.
		<?php
					foreach ($alerts as $alert) {
		?>
						<div class="alert" id="alert-<?php echo $alert['id']; ?>">
							<div class="alert-content">
								<div class="alert-text">
									<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>">
										<img src="<?php echo $alert['avatar']; ?>" alt="<?php echo htmlspecialchars($alert['username']); ?>" width="16" height="16" />
									</a>
									<a href="/users/<?php echo strtolower($alert['username']); ?>" title="<?php echo htmlspecialchars($alert['username']); ?>"><?php echo $alert['username']; ?></a>
									&nbsp;has shared&nbsp;
									<a href="/php/process_alert.php?id=<?php echo $alert['id']; ?>&amp;type=share&amp;url=<?php echo urlencode($alert['submission']); ?>"><?php echo $alert['title']; ?></a>
									<?php if ($alert['message'] != "") { ?>
										<a class="share-message qtooltip" title="<?php echo htmlspecialchars($alert['message']); ?>"><img src="/<?php echo get_theme_directory(); ?>img/notes.png"  width="16" height="16" /></a>
									<?php } ?>
								</div>
								<div class="alert-action">
									<a onclick="MarkAlertRead('shares', '<?php echo $alert['id']; ?>')" class="alert-x qtooltip" title="Mark Read">
										<img src="/<?php echo get_theme_directory(); ?>img/alert-x.png" alt="" onmouseover="this.src='/<?php echo get_theme_directory() . "img/alert-x-hover.png" ?>'" onmouseout="this.src='/<?php echo get_theme_directory() . "img/alert-x.png" ?>'" />
									</a>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
		<?php
					}
		?>
		
					<div class="all-alerts-read">
						<button onclick="MarkAllAlertsRead('shares')" class="normal-button">Mark All Share Alerts Read</button>
					</div>
		<?php
				} else { // no new comment alerts
		?>
					<div>No New Comments</div>
		<?php
				}
			}
		?>

	</div>
	<div class="clearfix"></div>
	
<?php
include (get_footer());
?>
<?php
if (isset($_GET['name'])) {
	$username = $_GET['name'];
} else {
	header("Location: /error");
}

$page = "all";
if (isset($_GET['type'])) {
	$page = $_GET['type'];
}

$page_number = 1;
if (isset($_GET['p'])) {
	$page_number = $_GET['p'];
}

$ovoUser = new ovoUser(false, $username);

if (!$ovoUser || $ovoUser->ID() == "") {
	header("Location: /error");
}

$ovoUserSettings = new $ovUserSettings($ovoUser->ID());

$base_url = "/users/" . strtolower($ovoUser->Username());

$is_mobile = $ovContent->IsMobileBrowser();

if ($is_mobile && MOBILEEXISTS) {
	header("Location: /m/users/" . strtolower($ovoUser->Username()));
	exit();
}

switch($page)
{
	case "favorites":
		$page_title = $ovoUser->Username() . " - Favorites";
		break;
	case "submissions":
		$page_title = $ovoUser->Username() . " - Submissions";
		break;
	case "comments":
		$page_title = $ovoUser->Username() . " - Comments";
		break;
	case "likes":
		$page_title = $ovoUser->Username() . " - Likes";
		break;
	case "dislikes":
		$page_title = $ovoUser->Username() . " - Dislikes";
		break;
	case "friends":
		$page_title = $ovoUser->Username() . " - Friends";
		break;
	default:
		$page_title = $ovoUser->Username();
		break;
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

$page_title .= " | " . $ovSettings->Title() . $alert_count_text;

include (get_head());
?>
<title><?php echo $page_title; ?></title>
</head>
<body>
	<?php include ('category-bar-hidden.php'); ?>


	<?php
	include (get_header());
	?>

	<div class="user-sidebar">
		<div class="user-avatar">
			<a id="user_avatar_img" href="<?php echo $ovoUser->Avatar(); ?>" title="<?php echo htmlspecialchars($ovoUser->Username()); ?>">
				<img src="<?php echo $ovoUser->Avatar(); ?>" alt="<?php echo $ovoUser->Username(); ?>" class="avatar-image" />
			</a>
		</div>

		<div class="user-sidebar-friends">
			<h2>Followed by <?php echo $ovoUser->Username(); ?></h2>
			<ul class="sidebar-friends">
			<?php 
				$following = $ovoUser->GetRandomFollowing();
				if ($following) {
					foreach ($following as $f) {
						$ovoFollowing = new ovoUser(false, $f['username']);
			?>
						<li class="user-box-link" box-id="following-box-<?php echo $f['id']; ?>">
							<a href="/users/<?php echo strtolower($f['username']); ?>" title="<?php echo htmlspecialchars($f['username']); ?>"><img src="<?php echo $f['avatar']; ?>" alt=""/></a>
							<div class="user-box" id="following-box-<?php echo $f['id']; ?>">
								<div class="user-box-avatar">
									<img src="<?php echo $ovoFollowing->Avatar(); ?>" alt="" />
								</div>
								<div class="user-box-details">
									<div class="user-box-username"><?php echo $ovoFollowing->Username(); ?></div>
									<div class="user-box-karma"><?php echo $ovoFollowing->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
								</div>
								<div class="clearfix"></div>
							</div>
						</li>

			<?php
					}
				} else {
			?>
					<li><?php echo $ovoUser->Username(); ?> is not following anyone.</li>
			<?php
				}
			?>
			</ul>
			<div class="clearfix"></div>
		</div>

		<div class="user-sidebar-friends">
			<h2>Following <?php echo $ovoUser->Username(); ?></h2>
			<ul class="sidebar-friends">
			<?php 
				$following = $ovoUser->GetRandomFollowers();
				if ($following) {
					foreach ($following as $f) {
						$ovoFollowing = new ovoUser(false, $f['username']);
			?>
						<li class="user-box-link" box-id="follower-box-<?php echo $f['id']; ?>">
							<a href="/users/<?php echo strtolower($f['username']); ?>" title="<?php echo htmlspecialchars($f['username']); ?>"><img src="<?php echo $f['avatar']; ?>" alt="" /></a>
							<div class="user-box" id="follower-box-<?php echo $f['id']; ?>">
								<div class="user-box-avatar">
									<img src="<?php echo $ovoFollowing->Avatar(); ?>" alt="" />
								</div>
								<div class="user-box-details">
									<div class="user-box-username"><?php echo $ovoFollowing->Username(); ?></div>
									<div class="user-box-karma"><?php echo $ovoFollowing->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
								</div>
								<div class="clearfix"></div>
							</div>
						</li>
						
			<?php
					}
				} else {
			?>
					<li>No followers.</li>
			<?php
				}
			?>
			</ul>
			<div class="clearfix"></div>
		</div>

		<div class="user-stats">
			<?php $user_stats = $ovoUser->UserStats(); ?>
			<h2>Statistics</h2>
			<div class="join-date"><strong>Joined:</strong>&nbsp;&nbsp;&nbsp;<?php echo $user_stats['join_date']; ?></div>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="75%"><strong>Submissions:</strong></td>
					<td width="25%"><?php echo $user_stats['num_submissions']; ?></td>
				</tr>
				<tr>
					<td width="75%"><strong>Comments:</strong></td>
					<td width="25%"><?php echo $user_stats['num_comments']; ?></td>
				</tr>
				<tr>
					<td width="75%"><strong>Votes:</strong></td>
					<td width="25%"><?php echo $user_stats['num_votes']; ?></td>
				</tr>
			</table>
		</div>

		<div class="user-sidebar-section">
			<?php $user_lists = $ovList->GetUserLists($ovoUser->ID()); ?>
			<h2><?php echo $ovoUser->Username(); ?>'s Lists</h2>
			<?php
				if ($user_lists) {
			?>
				<ul>
			<?php
					foreach($user_lists as $l) {
						if (!$l['is_private'] || $ovoUser->ID() == $ovUserSecurity->LoggedInUserID()) {
			?>
						<li>
							<a href="/lists/<?php echo strtolower($ovoUser->Username() . "/" . strtolower($l['unique_name'])); ?>"><?php echo $l['name']; ?></a>
							<?php if ($l['is_private']) { ?>
								<img src="/<?php echo get_theme_directory(); ?>img/private-list.png" alt="" />
							<?php } ?>
						</li>
			<?php
						}
					}
			?>
				</ul>
			<?php
				} else {
			?>
					<div class="margin_tb_10">No Lists</div>
			<?php
				}
			?>
		</div>
	</div>
	
	<div class="user-profile">
		<div class="user-info">
			<?php 
				if ($ovoUser->ID() != $ovUserSecurity->LoggedInUserID()) { 
					// no need to display controls if the user is viewing their own profile
			?>

				<div class="user-controls">
					<a class="user-menu-button">
						<img src="/<?php echo get_theme_directory(); ?>img/user-menu.png" alt="" />
					</a>
					<div class="user-menu-dropdown">
						<ul>
							<li><a href="#report-form" onclick="ShowReportForm('user', '<?php echo $ovoUser->ID(); ?>')" class="fancybox-form-link">Report <?php echo $ovoUser->Username(); ?></a></li>
							<li>
								<?php
									if ($ovUser->IsBlocking($ovUserSecurity->LoggedInUserID(), $ovoUser->ID())) {
										// user is blocking
								?>
										<a onclick="UnblockUser('<?php echo $ovoUser->ID(); ?>', '<?php echo $ovoUser->Username(); ?>')" id="user-block-link">Unblock <?php echo $ovoUser->Username(); ?></a>
								<?php 
									} else {
										// user is not blocking
								?>
										<a onclick="BlockUser('<?php echo $ovoUser->ID(); ?>', '<?php echo $ovoUser->Username(); ?>')" id="user-block-link">Block <?php echo $ovoUser->Username(); ?></a>
								<?php 
									}
								?>
							</li>
							<li><a onclick="ShowAddToListForm('<?php echo $ovoUser->Username(); ?>')" href="#add-to-list-form" class="fancybox-form-link">Add <?php echo $ovoUser->Username(); ?> to list</a></li>
						</ul>
					</div>
					<?php 
						if ($ovUser->IsFollowing($ovoUser->ID())) {
							// user is following
					?>
							<a class="user-follow-button user-is-following" onclick="UnfollowUser('<?php echo $ovoUser->ID(); ?>', '<?php echo $ovoUser->Username(); ?>')" id="user-follow-link">Following</a>
					<?php 
						} else {
							// user is not following
					?>
							<a class="user-follow-button user-is-not-following" onclick="FollowUser('<?php echo $ovoUser->ID(); ?>', '<?php echo $ovoUser->Username(); ?>')" id="user-follow-link">Follow</a>
					<?php
						}
					?>
				</div>

			<?php } ?>

			<div class="user-profile-name"><?php echo $ovoUser->Username(); ?> 
				<a class="user-rss-link" href="/feeds.php?type=user&amp;id=<?php echo urlencode(strtolower($ovoUser->Username())); ?>">
					<img src="/img/feeds.png" alt="RSS" height="30" />
				</a>
			</div>
	
			<?php if ($ovSettings->UseKarmaSystem()) { ?>
				<div class="user-karma"><?php echo $ovoUser->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
			<?php } ?>

			<?php if ($ovoUser->Details() != "") { ?>
			<div class="user-bio">
				<?php echo $ovoUser->Details(); ?>
			</div>
			<?php } ?>

			<div class="additional-user-info">
				<?php if ($ovoUser->Location() != "") { ?>
					<?php echo $ovoUser->Location(); ?>
				<?php } ?>

				<?php if ($ovoUser->Website() != "") { ?>
					<?php if ($ovoUser->Location() != "") { ?>
						<img class="bullet" alt="" src="/<?php echo get_theme_directory(); ?>img/user-profile-bullet.png" />
					<?php } ?>
					<a href="<?php echo $ovoUser->Website(); ?>" title="<?php echo $ovoUser->Website(); ?>" target="_blank"><?php echo $ovoUser->Website(); ?></a>
				<?php } ?>

				<?php if ($ovoUser->TwitterUsername() != "") { ?>
					<?php if ($ovoUser->Website() != "" || $ovoUser->Location() != "") { ?>
						<img class="bullet" alt="" src="/<?php echo get_theme_directory(); ?>img/user-profile-bullet.png" />
					<?php } ?>
					<a href="http://www.twitter.com/<?php echo $ovoUser->TwitterUsername(); ?>" title="View <?php echo htmlspecialchars($ovoUser->Username()); ?> on Twitter" target="_blank">http://www.twitter.com/<?php echo $ovoUser->TwitterUsername(); ?></a>
				<?php } ?>
			</div>
		</div>

		<ul class="user-tab-menu">
			<li <?php if ($type == "all") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/all"; ?>">All</a></li>
			<li <?php if ($type == "submissions") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/submissions"; ?>">Submissions</a></li>
			<li <?php if ($type == "comments") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/comments"; ?>">Comments</a></li>
			
			<?php
				if ($ovoUserSettings->PubliclyDisplayLikes() || $ovoUser->ID() == $ovUserSecurity->LoggedInUserID())
				{
			?>
					<li <?php if ($type == "likes") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/likes"; ?>">Likes</a></li>
					<li <?php if ($type == "dislikes") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/dislikes"; ?>">Dislikes</a></li>
			<?php		
				}
			?>

			<li <?php if ($type == "favorites") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/favorites"; ?>">Favorites</a></li>
			<li <?php if ($type == "friends") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/friends"; ?>">Friends</a></li>
		</ul>

		<?php
			if ($type == "submissions") {
				// submissions
				$submissions = $ovoUser->GetSubmissions($page_number);
				$last_page = $submissions['last-page'];
				$submissions = $submissions['submissions'];
				
				if ($submissions) {
					foreach($submissions as $sub) {
						$ovoSubmission = new ovoSubmission($sub);
						$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
		?>
						<div class="user-activity">
							<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Submitted</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovoSubmission->SubmissionDate(); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							
							<div class="user-submission">
								<div class="user-submission-title">
									<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo htmlspecialchars($ovoSubmission->Title()); ?></a>
								</div>
								<div class="user-submission-summary">
									<?php echo $ovUtilities->FormatBody($ovoSubmission->Summary()); ?>
								</div>
								<div>
									<a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a>
								</div>
							</div>
						</div>
						<div class="submission-seperator"></div>
		<?php
					}
				
					if ($last_page > 1) {
						$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
					}
				} else {
					// no submissions
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't submitted anything yet, check back later!</div>
		<?php
				}
			} elseif ($type == "comments") {
				// comments
				$comments = $ovoUser->GetComments($page_number);
				$last_page = $comments['last-page'];
				$comments = $comments['comments'];

				if ($comments) {
					foreach ($comments as $comment) {
		?>
						<div class="user-activity">
							<div class="user-activity-line">
								<div class="user-activity-action">
									<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Commented On</strong>
								</div>
								<div class="user-activity-date">
									<?php echo $ovUtilities->CalculateTimeAgo($comment['date']); ?>
								</div>
							</div>
							<div class="clearfix"></div>

							<div class="user-submission-title">
								<a href="<?php echo $comment['submission_url']; ?>#comment<?php echo $comment['id']; ?>" title="<?php echo htmlspecialchars($comment['submission_title']); ?>"><?php echo htmlspecialchars($comment['submission_title']); ?></a>
							</div>

							<div class="user-comment-body">
								<?php echo $comment['body']; ?>
							</div>
						</div>
						<div class="submission-seperator"></div>
		<?php			
					}

					if ($last_page > 1) {
						$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
					}
				} else {
					// no comments
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't commented on anything yet, check back later!</div>
		<?php
				}
			} elseif ($type == "likes") {
				$submissions = $ovoUser->GetLikedSubmissions($page_number);
				$last_page = $submissions['last-page'];
				$submissions = $submissions['submissions'];

				if ($submissions && ($ovoUserSettings->PubliclyDisplayLikes() || $ovoUser->ID() == $ovUserSecurity->LoggedInUserID())) {
					foreach($submissions as $sub) {
						$ovoSubmission = new ovoSubmission($sub);
						$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
		?>
						<div class="user-activity">
							<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Liked</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovoSubmission->SubmissionDate(); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							
							<div class="user-submission">
								<div class="user-submission-title">
									<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo htmlspecialchars($ovoSubmission->Title()); ?></a>
								</div>
								<div class="user-submission-summary">
									<?php echo $ovUtilities->FormatBody($ovoSubmission->Summary()); ?>
								</div>
								<div>
									<a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a>
								</div>
							</div>
						</div>
						<div class="submission-seperator"></div>
		<?php
					}
					
					if ($last_page > 1) {
						$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
					}
				} else {
					// no likes
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't liked anything yet, check back later!</div>
		<?php
				}
			} elseif ($type == "dislikes") {
				$submissions = $ovoUser->GetDislikedSubmissions($page_number);
				$last_page = $submissions['last-page'];
				$submissions = $submissions['submissions'];
				
				if ($submissions && ($ovoUserSettings->PubliclyDisplayLikes() || $ovoUser->ID() == $ovUserSecurity->LoggedInUserID())) {
					foreach($submissions as $sub) {
						$ovoSubmission = new ovoSubmission($sub);
						$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
		?>
						<div class="user-activity">
							<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Disliked</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovoSubmission->SubmissionDate(); ?>
									</div>
								</div>
								<div class="clearfix"></div>
							
							<div class="user-submission">
								<div class="user-submission-title">
									<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo htmlspecialchars($ovoSubmission->Title()); ?></a>
								</div>
								<div class="user-submission-summary">
									<?php echo $ovUtilities->FormatBody($ovoSubmission->Summary()); ?>
								</div>
								<div>
									<a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a>
								</div>
							</div>
						</div>
						<div class="submission-seperator"></div>
		<?php 
					}

					if ($last_page > 1) {
						$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
					}
				} else {
					// no dislikes
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't disliked anything yet, check back later!</div>
		<?php
				}
			} elseif ($type == "favorites") {
				// favorites
				$favorites = $ovoUser->GetFavorites($page_number);
				$last_page = $favorites['last-page'];
				$favorites = $favorites['favorites'];
				
				if ($favorites) {
					foreach($favorites as $favorite) {
						if ($favorite['favorite_type'] == "comment") {
							$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']) . "#comment-" . $favorite['comment_id'];
		?>
							<div class="user-activity">
								<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Favorited a Comment</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovUtilities->CalculateTimeAgo($favorite['favorite_date']); ?>
									</div>
								</div>
								<div class="clearfix"></div>
									
								<div class="user-submission-title">
									<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($favorite['submission_title']); ?>"><?php echo htmlspecialchars($favorite['submission_title']); ?></a>
								</div>

								<div class="user-comment-body">
									<div class="comment-favorite-user">Posted by <a href="/users/<?php echo strtolower($favorite['comment_username']); ?>" title="<?php echo htmlspecialchars($favorite['comment_username']); ?>"><?php echo htmlspecialchars($favorite['comment_username']); ?></a></div>
									<?php echo $favorite['comment_body']; ?>
								</div>
							</div>
							<div class="submission-seperator"></div>
		<?php
						} elseif ($favorite['favorite_type'] == "submission") {
							$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']);
							$comment_count = $ovComment->GetCommentCount($favorite['submission_id']);
		?>
							<div class="user-activity">
								<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Favorited</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovUtilities->CalculateTimeAgo($favorite['favorite_date']); ?>
									</div>
								</div>
								<div class="clearfix"></div>

								<div class="user-submission">
									<div class="user-submission-title">
										<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($favorite['submission_title']); ?>"><?php echo $favorite['submission_title']; ?></a>
									</div>
									<div class="user-submission-summary">
										<?php echo $favorite['submission_summary']; ?>
									</div>
									<div>
										<a href="<?php echo $page_url; ?>#comments"><?php echo $comment_count; ?> Comments</a>
									</div>
								</div>
							</div>
							<div class="submission-seperator"></div>
		<?php
						} else {
		?>
							<div class="user-activity">Hmmm...error?</div>
		<?php
						}
					}

				} else {
					// no favorites
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't favorited anything yet, check back later!</div>
		<?php
				}
			} elseif ($type == "friends") {
				// friends and followers
				$followers = $ovoUser->GetFollowers();
				$following = $ovoUser->GetFollowing();
		?>
				<div class="user-friends-menu">
					<ul>
						<li><a id="menuFollowing" href="javascript:ToggleFriendsList('following')" class="active">People You're Following</a></li>
						<li><a id="menuFollowers" href="javascript:ToggleFriendsList('followers')">People Following You</a></li>
					</ul>
				</div>

				<div class="user-friends-list" id="userFollowing">
		<?php
					if ($following) {
						foreach ($following as $friend) {
		?>
							<div class="user-friend" id="user-following-<?php echo $friend["id"]; ?>">
								<div class="user-friend-avatar">
									<a href="/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>">
										<img src="<?php echo $friend["avatar"]; ?>" alt="" height="32" />
									</a>
								</div>
								<div class="user-friend-details">
									<div class="user-friend-username">
										<a href="/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>">
											<?php echo $friend["username"]; ?>
										</a>
									</div>
									<?php if ($ovUserSecurity->IsUserLoggedIn() && $ovUserSecurity->LoggedInUserID() == $ovoUser->ID()) { ?>
									<div class="user-friend-controls">
										<?php if ($ovoUser->IsUserFollowingYou($friend["id"])) { ?>
											<a class="confirm qtooltip" title="<?php echo htmlspecialchars($friend["username"]); ?> is Following You." style="cursor:normal !important"><img src="/<?php echo get_theme_directory(); ?>img/user-check.png" alt=""/></a>
										<?php } ?>
										<a onclick="UserFollowingUnfollowUser('<?php echo $friend["id"]; ?>')" class="red qtooltip" title="Unfollow <?php echo htmlspecialchars($friend["username"]); ?>" id="user-following-follow-link-<?php echo $friend["id"]; ?>"><img src="/<?php echo get_theme_directory(); ?>img/user-unfollow.png" alt=""  id="user-following-follow-image-<?php echo $friend["id"]; ?>" /></a>
										<a onclick="UserFollowingBlockUser('<?php echo $friend["id"]; ?>')" class="red qtooltip" title="Block <?php echo htmlspecialchars($friend["username"]); ?>"><img src="/<?php echo get_theme_directory(); ?>img/user-block.png" alt="" /></a>
									</div>
									<?php } ?>
									<div class="clearfix"></div>
									<div class="user-friend-bio"><?php echo $friend['details']; ?></div>
								</div>
							</div>
							<div class="friend-seperator"></div>
		<?php
						}
					} else {
						// not following anyone
		?>
						<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> isn't following anyone yet, check back later!</div>
		<?php
						
					}
		?>
				</div>
				<div class="user-friends-list" id="userFollowers" style="display:none">
		<?php
				if ($followers) {
					foreach ($followers as $friend) {
		?>
						<div class="user-friend" id="user-follower-<?php echo $friend["id"]; ?>">
							<div class="user-friend-avatar">
								<a href="/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>">
									<img src="<?php echo $friend["avatar"]; ?>" alt="" height="32" />
								</a>
							</div>
							<div class="user-friend-details">
								<div class="user-friend-username">
									<a href="/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>">
										<?php echo $friend["username"]; ?>
									</a>
								</div>
								<?php if ($ovUserSecurity->IsUserLoggedIn() && $ovUserSecurity->LoggedInUserID() == $ovoUser->ID()) { ?>
								<div class="user-friend-controls">
									<?php if ($ovoUser->IsUserFollowing($friend["id"])) { ?>
										<a onclick="UserFollowersUnfollowUser('<?php echo $friend["id"]; ?>')" class="red qtooltip" title="Unfollow <?php echo htmlspecialchars($friend["username"]); ?>" id="user-follower-follow-link-<?php echo $friend["id"]; ?>"><img src="/<?php echo get_theme_directory(); ?>img/user-unfollow.png" alt=""  id="user-follower-unfollow-image-<?php echo $friend["id"]; ?>" /><img src="/<?php echo get_theme_directory(); ?>img/user-follow.png" alt="" id="user-follower-follow-image-<?php echo $friend["id"]; ?>" style="display:none" /></a>
									<?php } else { ?>
										<a onclick="UserFollowersFollowUser('<?php echo $friend["id"]; ?>')" class="confirm qtooltip" title="Follow <?php echo htmlspecialchars($friend["username"]); ?>" id="user-follower-follow-link-<?php echo $friend["id"]; ?>"><img src="/<?php echo get_theme_directory(); ?>img/user-unfollow.png" alt=""  id="user-follower-unfollow-image-<?php echo $friend["id"]; ?>" style="display:none" /><img src="/<?php echo get_theme_directory(); ?>img/user-follow.png" alt="" id="user-follower-follow-image-<?php echo $friend["id"]; ?>" /></a>
									<?php } ?>
									<a onclick="UserFollowersBlockUser('<?php echo $friend["id"]; ?>')" class="red qtooltip" title="Block <?php echo htmlspecialchars($friend["username"]); ?>"><img src="/<?php echo get_theme_directory(); ?>img/user-block.png" alt="" /></a>
								</div>
								<?php } ?>
								<div class="clearfix"></div>
								<div class="user-friend-bio"><?php echo $friend['details']; ?></div>
							</div>
						</div>
						<div class="friend-seperator"></div>
		<?php
					}
				} else {
					// no one following user
		?>
					<div class="margin_tb_20">No one is following <?php echo $ovoUser->Username(); ?> yet, check back later!</div>
		<?php
				}
		?>
				</div>
		<?php
			} else {
				// get user recent activity
				if($ovoUserSettings->PubliclyDisplayLikes() || $ovoUser->ID() == $ovUserSecurity->LoggedInUserID()) {
					$activities = $ovoUser->GetRecentActivity($page_number);
				} else {
					$activities = $ovoUser->GetRecentActivityNoLikes($page_number);
				}

				$last_page = $activities['last-page'];
				$activities = $activities['activities'];
				
				if ($activities) {
					foreach($activities as $activity) {
		?>

					<?php 
						if ($activity['activity_type'] == "submission" || $activity['activity_type'] == "like" || $activity['activity_type'] == "dislike" || ($activity['activity_type'] == "favorite" && $activity["activity_sub_type"] == "submission") )
						{	// Since Submissions, likes, dislikes, and favorited submissions all display basically the same, use same template
							$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']);
							$comment_count = $ovComment->GetCommentCount($activity['submission_id']);
					?>		
							<div class="user-activity">
								<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>
										<?php 
											if ($activity['activity_type'] == "submission") {
												echo "Submitted";
											} elseif($activity['activity_type'] == "like") {
												echo "Liked";
											} elseif ($activity['activity_type'] == "dislike") {
												echo "Disliked";
											} elseif ($activity['activity_type'] == "favorite" && $activity["activity_sub_type"] == "submission") {
												echo "Favorited";
											}
										?>
										</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?>
									</div>

								</div>
								<div class="clearfix"></div>
								
								<div class="user-submission">
									<div class="user-submission-title">
										<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo $activity['submission_title']; ?></a>
									</div>
									<div class="user-submission-summary">
										<?php echo $activity['submission_summary']; ?>
									</div>
									<div>
										<a href="<?php echo $page_url; ?>#comments"><?php echo $comment_count; ?> Comments</a>
									</div>
								</div>
							</div>
							<div class="submission-seperator"></div>
					<?php 
						} elseif ($activity['activity_type'] == "comment") {
							// comment
							$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']) . "#comment-" . $activity['comment_id'];
					?>
							<div class="user-activity">
								<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Commented On</strong> 
									</div>
									<div class="user-activity-date">
										<?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?>
									</div>
								</div>
								<div class="clearfix"></div>

								<div class="user-submission-title">
									<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo $activity['submission_title']; ?></a>
								</div>

								<div class="user-comment-body">
									<?php echo $ovUtilities->ParseURL($ovUtilities->FormatBody($activity['comment_body'])); ?>
								</div>
							</div>
							<div class="submission-seperator"></div>
					<?php 
						} elseif (($activity['activity_type'] == "favorite" && $activity["activity_sub_type"] == "comment")) {
							// favorited comment
							$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']) . "#comment-" . $activity['comment_id'];
					?>
							<div class="user-activity">
								<div class="user-activity-line">
									<div class="user-activity-action">
										<?php echo $ovoUser->Username(); ?>&nbsp;<strong>Favorited a Comment</strong>
									</div>
									<div class="user-activity-date">
										<?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?>
									</div>
								</div>
								<div class="clearfix"></div>
									
								<div class="user-submission-title">
									<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo $activity['submission_title']; ?></a>
								</div>

								<div class="user-comment-body">
									<div class="comment-favorite-user">Posted by <a href="/users/<?php echo strtolower($activity['comment_username']); ?>" title="<?php echo htmlspecialchars($activity['comment_username']); ?>"><?php echo htmlspecialchars($activity['comment_username']); ?></a></div>
									<?php echo $ovUtilities->ParseURL($ovUtilities->FormatBody($activity['comment_body'])); ?>
								</div>
							</div>
							<div class="submission-seperator"></div>
					<?php 
						}
					?>

		<?php 
					}
					
					if ($last_page > 1) {
						$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
					}
				} else {
					// no recent activity
		?>
					<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't done anything yet, check back later!</div>
		<?php
				}
			} 
		?>	
	</div>


	<div id="report-form" class="modal_form" title="Report User" style="display:none">
		<div class="margin_tb_15">
			<label for="report-reason">Reason</label>
			<select id="report-reason" name="reason">
				<option value="Spammer">Spammer</option>
				<option value="Offensive">Offensive</option>
				<option value="Violation">Violates TOU</option>
				<option value="Other">Other</option>
			</select>
		</div>
		<div class="margin_tb_15">
			<label for="report-details">Details</label>
			<br/>
			<textarea id="report-details" name="details" style="height:100px;width:100%" class="limit255" charsleft="report-chars-left"></textarea>
			<div class="align_right" id="report-chars-left">255 characters remaining</div>
		</div>

		<input type="hidden" id="report-user-id" value="<?php echo $ovoUser->ID(); ?>" />

		<div class="align_right">
			<button onclick="SubmitReport('user')" class="normal-button">Submit Report</button>
		</div>
	</div>

	<div style="display:none">
		<div id="add-to-list-form">
			<a class="fancybox-close-button" onclick="closePopup()"></a>
			<div id="add-to-list-form-content">
				<div class="add-to-list-header">Add <span id="add-to-list-username"></span> To List</div>
				<div class="error-box" style="display:none" id="add-to-list-error"></div>
				<div id="list-choices">
				<?php 
					$user_lists = $ovList->GetUserLists($ovUserSecurity->LoggedInUserID()); 
					if ($user_lists) {
						foreach ($user_lists as $list) {
				?>
							<div class="list-checkbox">
								<input type="checkbox" name="list[]" value="<?php echo $list['id']; ?>" <?php if ($ovList->IsUserInList($list['id'], $ovoUser->ID())) { echo "checked"; } ?> /> <?php echo $list['name']; ?>
							</div>
				<?php
						}
					}
				?>
				</div>

				<input type="hidden" id="add-user-to-list-user-id" value="<?php echo $ovoUser->ID(); ?>" />

				<div class="list-checkbox"><button class="normal-button" onclick="AdjustListsForUser()">Save</button></div>
				
				<div class="list-checkbox"><a onclick="ToggleAddNewListForm()">Create New List</a></div>
				
				<div class="list-checkbox" id="new-list-form">
					<h6>Create new List</h6>
					<div class="error-box" style="display:none" id="user-add-list-error"></div>
					<div>
						<label for="list-name">Name</label><br/>
						<input type="text" id="list-name" />
					</div>
					<div>
						<input type="checkbox" name="list-private" id="list-private" value="yes" /> <label for="list-private">Make Private</label>
					</div>
					<div>
						<button onclick="AddNewList()" class="normal-button">Add</button>
						<button onclick="ToggleAddNewListForm()" class="cancel-button">Cancel</button>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
include (get_footer());
?>
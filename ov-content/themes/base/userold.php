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
	<?php
	include (get_header());
	?>

	<div class="user-sidebar">
		<div class="user-avatar">
			<a id="user_avatar_img" href="<?php echo $ovoUser->Avatar(); ?>" title="<?php echo $ovoUser->Username(); ?>">
				<img src="<?php echo $ovoUser->Avatar(); ?>" alt="<?php echo $ovoUser->Username(); ?>" class="avatar-image" />
			</a>
		</div>
	</div>
	
	<div class="user-profile">
		<div class="user-details">
			
				
			
			<div class="user-info">
				<h2 class="blue"><?php echo $ovoUser->Username(); ?></h2>
				<?php if ($ovoUser->Details() != "") { ?>
				<div class="user-bio">
					<?php echo $ovoUser->Details(); ?>
				</div>
				<?php } ?>
				
				<?php if ($ovoUser->Location() != "") { ?>
					<div class="user-location"><strong>Location:</strong> <?php echo $ovoUser->Location(); ?></div>
				<?php } ?>
				
				<?php if ($ovoUser->Website() != "") { ?>
					<div class="user-website"><a href="<?php echo $ovoUser->Website(); ?>" title="<?php echo $ovoUser->Website(); ?>" target="_blank"><?php echo $ovoUser->Website(); ?></a></div>
				<?php } ?>
			</div>
		</div>
		<div class="user-karma">
			<div class="karma-box">
				<div class="karma-points"><?php echo $ovoUser->KarmaPoints(); ?></div>
				<div class="karma-points-name"><?php echo $ovSettings->KarmaName(); ?></div>
			</div>
			<div class="user-stats">
				<?php $user_stats = $ovoUser->UserStats(); ?>
				<h1>Statistics</h1>
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
				</table>
			</div>
			<div class="user-rss-feed">
				<div class="rss-feed-button">
					<img src="/img/feeds.png" alt="RSS" height="16" />
					<a href="/feeds.php?type=user&amp;id=<?php echo urlencode(strtolower($ovoUser->Username())); ?>">User Feed</a>
				</div>
			</div>
		</div>
		
		<div class="clearfix"></div>
	</div>
	
	<ul class="tab-menu">
		<li <?php if ($type == "all") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/all"; ?>">All</a></li>
		<li <?php if ($type == "submissions") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/submissions"; ?>">Submissions</a></li>
		<li <?php if ($type == "comments") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/comments"; ?>">Comments</a></li>
		<li <?php if ($type == "likes") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/likes"; ?>">Likes</a></li>
		<li <?php if ($type == "dislikes") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/dislikes"; ?>">Dislikes</a></li>
		<li <?php if ($type == "favorites") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/favorites"; ?>">Favorites</a></li>
		<li <?php if ($type == "friends") { ?> class="active-tab"<?php } ?>><a href="<?php echo $base_url . "/friends"; ?>">Friends</a></li>
	</ul>
	
	<div class="main-content" style="margin-top:0">
	<?php
	switch($page)
	{
		case "submissions":
			$submissions = $ovoUser->GetSubmissions($page_number);
			$last_page = $submissions['last-page'];
			$submissions = $submissions['submissions'];
			
			if ($submissions) {
				foreach($submissions as $sub) {
					$ovoSubmission = new ovoSubmission($sub);
					$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
					
	?>
					<div class="user-submission">
						<div class="user-submission-score">
							<div class="user-submission-score-box"><?php echo $ovoSubmission->Score(); ?></div>
						</div>
						<div class="user-submission-details">
						<div class="user-submission-title">
							<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo $ovoSubmission->Title(); ?></a>
						</div>
						<div class="user-submission-date">
							Submitted <?php echo $ovoSubmission->SubmissionDate(); ?>
						</div>
						<div class="user-submission-links">
							<ul>
								<li><a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a></li>
							</ul>
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
			break;
		case "comments":
			$comments = $ovoUser->GetComments($page_number);
			$last_page = $comments['last-page'];
			$comments = $comments['comments'];
			
			if ($comments) {
				foreach($comments as $comment) {
	?>
					<div class="user-comment">
						<div class="user-comment-title">Commented on <a href="<?php echo $comment['submission_url']; ?>" title="<?php echo htmlspecialchars($comment['submission_title']); ?>"><?php echo htmlspecialchars($comment['submission_title']); ?></a></div>
						<div class="user-comment-text"><?php echo $comment['body']; ?></div>
						<div class="user-comment-posted-date">Posted <?php echo $comment['date']; ?></div>
					</div>
	<?php
				}
				
				if ($last_page > 1) {
					$ovUtilities->PrintPaginationRow("$base_url" . "/$type/", $page_number, $last_page);
				}
			} else {
				// no comments
	?>
				<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't commented yet, check back later!</div>
	<?php
			}
			break;
		case "likes":
			$submissions = $ovoUser->GetLikedSubmissions($page_number);
			$last_page = $submissions['last-page'];
			$submissions = $submissions['submissions'];
			
			if ($submissions) {
				foreach($submissions as $sub) {
					$ovoSubmission = new ovoSubmission($sub);
					$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
					
	?>
					<div class="user-submission">
						<div class="user-submission-score">
							<div class="user-submission-score-box"><?php echo $ovoSubmission->Score(); ?></div>
						</div>
						<div class="user-submission-details">
						<div class="user-submission-title">
							<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo $ovoSubmission->Title(); ?></a>
						</div>
						<div class="user-submission-date">
							Submitted <?php echo $ovoSubmission->SubmissionDate(); ?>
						</div>
						<div class="user-submission-links">
							<ul>
								<li><a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a></li>
							</ul>
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
			break;
		case "dislikes":
			$submissions = $ovoUser->GetDislikedSubmissions($page_number);
			$last_page = $submissions['last-page'];
			$submissions = $submissions['submissions'];
			
			if ($submissions) {
				foreach($submissions as $sub) {
					$ovoSubmission = new ovoSubmission($sub);
					$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
					
	?>
					<div class="user-submission">
						<div class="user-submission-score">
							<div class="user-submission-score-box"><?php echo $ovoSubmission->Score(); ?></div>
						</div>
						<div class="user-submission-details">
						<div class="user-submission-title">
							<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>"><?php echo $ovoSubmission->Title(); ?></a>
						</div>
						<div class="user-submission-date">
							Submitted <?php echo $ovoSubmission->SubmissionDate(); ?>
						</div>
						<div class="user-submission-links">
							<ul>
								<li><a href="<?php echo $ovoSubmission->PageURL(); ?>#comments"><?php echo $comment_count; ?> Comments</a></li>
							</ul>
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
			break;
		case "favorites":
			$favorites = $ovoUser->GetFavorites($page_number);
			$last_page = $favorites['last-page'];
			$favorites = $favorites['favorites'];
			
			if ($favorites) {
				foreach($favorites as $favorite) {
					if ($favorite['favorite_type'] == "comment") {
						$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']) . "#comment-" . $favorite['comment_id'];
	?>

						<div class="favorite">
							<div class="favorite-title">
								<a href="/users/<?php echo strtolower($favorite['comment_username']); ?>" title="<?php echo $favorite['comment_username']; ?>"><?php echo $favorite['comment_username']; ?>'s</a> 
								Comment On 
								<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($favorite['submission_title']); ?>"><?php echo htmlspecialchars($favorite['submission_title']); ?></a>
							</div>
							<div class="user-favorite-posted-date">Marked as Favorite <?php echo $ovUtilities->CalculateTimeAgo($favorite['favorite_date']); ?></div>
						</div>
	<?php
					} else {
							$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']);
	?>

							<div class="favorite">
								<div class="favorite-title"><a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($favorite['submission_title']); ?>"><?php echo htmlspecialchars($favorite['submission_title']); ?></a></div>
								<div class="user-favorite-posted-date">Marked as Favorite <?php echo $ovUtilities->CalculateTimeAgo($favorite['favorite_date']); ?></div>
							</div>
	<?php
					}
				}
			} else {
				// no favorites
	?>
				<div class="margin_tb_20"><?php echo $ovoUser->Username(); ?> hasn't favorited anything yet, check back later!</div>
	<?php
			}
			break;
		case "friends":
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
			break;
		default:
			$activities = $ovoUser->GetRecentActivity($page_number);
			$last_page = $activities['last-page'];
			$activities = $activities['activities'];
			
			if ($activities) {
				foreach($activities as $activity) {
					if ($activity['activity_type'] == "submission") {
						$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']);
						$comment_count = $ovComment->GetCommentCount($activity['submission_id']);
	?>
						<div class="user-submission">
							<div class="user-submission-score">
								<div class="user-submission-score-box"><?php echo $ovSubmission->GetSubmissionScore($activity['submission_id']); ?></div>
							</div>
							<div class="user-submission-details">
							<div class="user-submission-title">
								<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo $activity['submission_title']; ?></a>
							</div>
							<div class="user-submission-date">
								Submitted <?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?>
							</div>
							<div class="user-submission-links">
								<ul>
									<li><a href="<?php echo $page_url; ?>#comments"><?php echo $comment_count; ?> Comments</a></li>
								</ul>
							</div>
							</div>
						</div>
						<div class="submission-seperator"></div>
	<?php
					} elseif ($activity['activity_type'] == "comment") {
						$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']);
	?>
						<div class="user-comment">
							<div class="user-comment-title">Commented on <a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo htmlspecialchars($activity['submission_title']); ?></a></div>
							<div class="user-comment-text"><?php echo $activity['comment_body']; ?></div>
							<div class="user-comment-posted-date">Posted <?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?></div>
						</div>
	<?php						
					} elseif ($activity['activity_type'] == "favorite") {
						if ($activity['activity_sub_type'] == "comment") {
							$page_url = "/" . strtolower($activity['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']);
		?>

							<div class="favorite">
								<div class="favorite-title">
									Favorited 
									<a href="/users/<?php echo strtolower($activity['comment_username']); ?>" title="<?php echo $favorite['comment_username']; ?>"><?php echo $activity['comment_username']; ?>'s</a> 
									Comment On 
									<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo htmlspecialchars($activity['submission_title']); ?></a>
								</div>
								<div class="user-favorite-posted-date">Marked as Favorite <?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?></div>
							</div>
		<?php
						} else {
								$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $activity['submission_id'] . "/" . $ovUtilities->ConvertToUrl($activity['submission_title']);
		?>

								<div class="favorite">
									<div class="favorite-title">Favorited <a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($activity['submission_title']); ?>"><?php echo htmlspecialchars($activity['submission_title']); ?></a></div>
									<div class="user-favorite-posted-date">Marked as Favorite <?php echo $ovUtilities->CalculateTimeAgo($activity['date']); ?></div>
								</div>
		<?php
						}
					}
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
			break;
	}
	?>
	</div>
	<div class="sidebar" style="margin-top:0">
		<?php if ($ovSettings->SidebarAd() != "") { ?>
			<div class="sidebar-ad"><p>ADVERTISEMENT</p><?php echo $ovSettings->SidebarAd(); ?></div>
		<?php } ?>
	</div>
	
	<div id="user_report_form_<?php echo $ovoUser->ID(); ?>" class="modal_form" title="Report User" style="display:none">
		<div class="margin_tb_15">
			<label for="reason_<?php echo $ovoUser->ID(); ?>">Reason</label>
			<select id="reason_<?php echo $ovoUser->ID(); ?>" name="reason">
				<option value="Spammer">Spammer</option>
				<option value="Offensive">Offensive</option>
				<option value="Violation">Violates TOU</option>
				<option value="Other">Other</option>
			</select>
		</div>
		<div class="margin_tb_15">
			<label for="details_<?php echo $ovoUser->ID(); ?>">Details</label>
			<br/>
			<textarea ID="details_<?php echo $ovoUser->ID(); ?>" name="details" style="height:100px;width:100%" class="limit255" charsleft="user_report_chars_left_<?php echo $ovoUser->ID(); ?>"></textarea>
			<div class="align_right" id="user_report_chars_left_<?php echo $ovoUser->ID(); ?>">255 characters remaining</div>
		</div>

		<input type="hidden" id="object_id_<?php echo $ovoUser->ID(); ?>" value="<?php echo $ovoUser->ID(); ?>" />

		<div class="align_right">
			<button onclick="SubmitReport('user', '<?php echo $ovoUser->ID(); ?>', 'user_report_form_<?php echo $ovoUser->ID(); ?>')" class="normal-button">Submit Report</button>
		</div>
	</div>

<?php
include (get_footer());
?>
<?php
if (isset($_GET['name'])) {
	$username = $_GET['name'];
} else {
	header("Location: /m/error");
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

$base_url = "/m/users/" . strtolower($ovoUser->Username());

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

include (get_mobile_head());
?>
<title><?php echo $page_title; ?></title>
</head>
<body>
<?php
include (get_mobile_header());
?>

<div class="user-details">
	<div class="user-avatar">
		<a href="<?php echo $ovoUser->Avatar(); ?>" title="<?php echo htmlspecialchars($ovoUser->Username()); ?>" id="user-avatar-link">
			<img src="<?php echo $ovoUser->Avatar(); ?>" alt="<?php echo htmlspecialchars($ovoUser->Username()); ?>" width="100" />
		</a>
	</div>
	<div class="user-info">
		<h2><?php echo htmlspecialchars($ovoUser->Username()); ?></h2>
		<div class="user-karma"><?php echo $ovoUser->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
		<?php if ($ovoUser->Location() != "") { ?>
			<div class="user-location"><strong>Location:</strong> <?php echo $ovoUser->Location(); ?></div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>
</div>
<div class="user-bio">
	<div>
		<a onclick="toggleUserDetails()" class="collapse-arrow" id="details-collapse-arrow">
			<img src="/img/arrow-collapsed.png" alt="" width="10" />
		</a>
		<a onclick="toggleUserDetails()" class="collapse-link" id="details-collapse-link">Show User Bio</a>
	</div>
	<div id="user-details-div">
		<p><?php echo $ovoUser->Details(); ?></p>
		<p><a href="<?php echo $ovoUser->Website(); ?>" target="_blank"><?php echo $ovoUser->Website(); ?></a></p>
	</div>
</div>

<?php if ($ovUserSecurity->IsUserLoggedIn() && $ovUserSecurity->LoggedInUserID() != $ovoUser->ID()) { ?>
<div class="submission-actions">
	<ul class="submission-actions-list">
		<?php if ($ovUser->IsFollowing($ovoUser->ID())) { ?>
			<li><a class="cancel-button" id="follow-button" onclick="unfollowUser('<?php echo $ovoUser->ID(); ?>')">Unfollow</a></li>
		<?php } else { ?>
			<li><a class="ok-button" id="follow-button" onclick="followUser('<?php echo $ovoUser->ID(); ?>')">Follow</a></li>
		<?php } ?>
		
		<?php if ($ovUser->IsBlocking($ovUserSecurity->LoggedInUserID(), $ovoUser->ID())) { ?>
			<li><a  class="ok-button" id="block-button" onclick="unblockUser('<?php echo $ovoUser->ID(); ?>')">Unblock</a></li>
		<?php } else { ?>
			<li><a  class="cancel-button" id="block-button" onclick="blockUser('<?php echo $ovoUser->ID(); ?>')">Block</a></li>
		<?php } ?>
	</ul>
	<div class="clearfix"></div>
</div>
<?php } ?>

<?php
if ($page == "favorites") {
	$favorites = $ovoUser->GetFavorites($page_number);
	$last_page = $favorites['last-page'];
	$favorites = $favorites['favorites'];
?>
<ul class="user-page-list">
	<li class="title-item">Favorites</li>
	<?php
		if ($favorites) {
			foreach($favorites as $favorite) {
				if ($favorite['favorite_type'] == "comment") {
					$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']);
	?>
						<li onclick="navigateTo('/m<?php echo $page_url; ?>')">
							<div class="submission-details">
								<div class="submission-info" style="margin-top:0">
									<?php echo $favorite['comment_username']; ?>'s comment on
								</div>
								<a href="/m<?php echo $page_url; ?>" 
									title="<?php echo htmlspecialchars($favorite['submission_title']); ?>">
										<?php echo htmlspecialchars($favorite['submission_title']); ?>
								</a>
							</div>
							<a class="arrow" href="/m<?php echo $page_url; ?>"></a>
							<div class="clearfix"></div>
						</li>					
	<?php
				} else {
						$page_url = "/" . strtolower($favorite['submission_type']) . "/" . $favorite['submission_id'] . "/" . $ovUtilities->ConvertToUrl($favorite['submission_title']);
	?>
						<li onclick="navigateTo('/m<?php echo $page_url; ?>')">
							<?php $vote_direction = $ovSubmission->CheckForVote($favorite['submission_id']); ?>
							<?php if ($vote_direction == 1) { ?>
								<div class="submission-has-voted-up"></div>
							<?php } elseif ($vote_direction == -1) { ?>
								<div class="submission-has-voted-down"></div>
							<?php } ?>
							<div class="submission-details">
								<a href="/m<?php echo $page_url; ?>" 
									title="<?php echo htmlspecialchars($favorite['submission_title']); ?>">
										<?php echo htmlspecialchars($favorite['submission_title']); ?>
								</a>
								<div class="submission-info">
									<div class="score"><?php echo $ovSubmission->GetSubmissionScore($favorite['submission_id']); ?></div>
								</div>
							</div>
							<a class="arrow" href="/m<?php echo $page_url; ?>"></a>
							<div class="clearfix"></div>
						</li>
	<?php
				}
			}
	 		if ($last_page > 1) { 
	?>
			<div class="pager">
				<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . "/favorites/" . ($page_number - 1); ?>">Previous</a><?php } ?>
				<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . "/favorites/" . ($page_number + 1); ?>">Next</a><?php } ?>
				<div class="clearfix"></div>
			</div>
	<?php
	 		}
		} else {
			// no favorites
	?>
			<li><?php echo $ovoUser->Username(); ?> hasn't favorited anything yet, check back later!</li>
	<?php
		}
	?>
</ul>
<?php
} elseif ($page == "submissions") {
	$submissions = $ovoUser->GetSubmissions($page_number);
	$last_page = $submissions['last-page'];
	$submissions = $submissions['submissions'];
?>
<ul class="user-page-list">
	<li class="title-item">Submissions</li>
<?php
	if ($submissions) {
		foreach($submissions as $sub) {
			$ovoSubmission = new ovoSubmission($sub);
			$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
			
?>
			<li onclick="navigateTo('/m<?php echo $ovoSubmission->PageURL(); ?>')">
				<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
				<?php if ($vote_direction == 1) { ?>
					<div class="submission-has-voted-up"></div>
				<?php } elseif ($vote_direction == -1) { ?>
					<div class="submission-has-voted-down"></div>
				<?php } ?>
				<div class="submission-details">
					<a href="/m<?php echo $ovoSubmission->PageURL(); ?>" 
						title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>">
							<?php echo htmlspecialchars($ovoSubmission->Title(), ENT_QUOTES, 'UTF-8'); ?>
					</a>
					<div class="submission-info">
						<div class="score"><?php echo $ovoSubmission->Score(); ?></div>
						<div class="comment-count"><img src="/<?php echo get_theme_directory(); ?>mobile/img/comments.png" alt="" /> <?php echo $comment_count; ?></div>
					</div>
				</div>
				<a class="arrow" href="/m<?php echo $ovoSubmission->PageURL(); ?>"></a>
				<div class="clearfix"></div>
			</li>
<?php
		}
		if ($last_page > 1) { 
?>
		<div class="pager">
			<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . "/submissions/" . ($page_number - 1); ?>">Previous</a><?php } ?>
			<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . "/submissions/" . ($page_number + 1); ?>">Next</a><?php } ?>
			<div class="clearfix"></div>
		</div>
<?php
 		}
	} else { // no submissions
?>
		<li><?php echo $ovoUser->Username(); ?> hasn't submitted anything yet, check back later!</li>
<?php
	}
?>
</ul>
<?php
} elseif ($page == "likes") {
	$submissions = $ovoUser->GetLikedSubmissions($page_number);
	$last_page = $submissions['last-page'];
	$submissions = $submissions['submissions'];
?>
<ul class="user-page-list">
	<li class="title-item">Likes</li>
<?php
	if ($submissions) {
		foreach($submissions as $sub) {
			$ovoSubmission = new ovoSubmission($sub);
			$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
			
?>
			<li onclick="navigateTo('/m<?php echo $ovoSubmission->PageURL(); ?>')">
				<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
				<?php if ($vote_direction == 1) { ?>
					<div class="submission-has-voted-up"></div>
				<?php } elseif ($vote_direction == -1) { ?>
					<div class="submission-has-voted-down"></div>
				<?php } ?>
				<div class="submission-details">
					<a href="/m<?php echo $ovoSubmission->PageURL(); ?>" 
						title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>">
							<?php echo htmlspecialchars($ovoSubmission->Title(), ENT_QUOTES, 'UTF-8'); ?>
					</a>
					<div class="submission-info">
						<div class="score"><?php echo $ovoSubmission->Score(); ?></div>
						<div class="comment-count"><img src="/<?php echo get_theme_directory(); ?>mobile/img/comments.png" alt="" /> <?php echo $comment_count; ?></div>
					</div>
				</div>
				<a class="arrow" href="/m<?php echo $ovoSubmission->PageURL(); ?>"></a>
				<div class="clearfix"></div>
			</li>
<?php
		}
		if ($last_page > 1) { 
?>
		<div class="pager">
			<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . "/likes/" . ($page_number - 1); ?>">Previous</a><?php } ?>
			<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . "/likes/" . ($page_number + 1); ?>">Next</a><?php } ?>
			<div class="clearfix"></div>
		</div>
<?php
 		}
	} else { // no submissions
?>
		<li><?php echo $ovoUser->Username(); ?> hasn't liked anything yet, check back later!</li>
<?php
	}
?>
</ul>
<?php
} elseif ($page == "dislikes") {
	$submissions = $ovoUser->GetDislikedSubmissions($page_number);
	$last_page = $submissions['last-page'];
	$submissions = $submissions['submissions'];
?>
<ul class="user-page-list">
	<li class="title-item">Dislikes</li>
<?php
	if ($submissions) {
		foreach($submissions as $sub) {
			$ovoSubmission = new ovoSubmission($sub);
			$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
			
?>
			<li onclick="navigateTo('/m<?php echo $ovoSubmission->PageURL(); ?>')">
				<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
				<?php if ($vote_direction == 1) { ?>
					<div class="submission-has-voted-up"></div>
				<?php } elseif ($vote_direction == -1) { ?>
					<div class="submission-has-voted-down"></div>
				<?php } ?>
				<div class="submission-details">
					<a href="/m<?php echo $ovoSubmission->PageURL(); ?>" 
						title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>">
							<?php echo htmlspecialchars($ovoSubmission->Title(), ENT_QUOTES, 'UTF-8'); ?>
					</a>
					<div class="submission-info">
						<div class="score"><?php echo $ovoSubmission->Score(); ?></div>
						<div class="comment-count"><img src="/<?php echo get_theme_directory(); ?>mobile/img/comments.png" alt="" /> <?php echo $comment_count; ?></div>
					</div>
				</div>
				<a class="arrow" href="/m<?php echo $ovoSubmission->PageURL(); ?>"></a>
				<div class="clearfix"></div>
			</li>
<?php
		}
		if ($last_page > 1) { 
?>
		<div class="pager">
			<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . "/likes/" . ($page_number - 1); ?>">Previous</a><?php } ?>
			<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . "/likes/" . ($page_number + 1); ?>">Next</a><?php } ?>
			<div class="clearfix"></div>
		</div>
<?php
 		}
	} else { // no submissions
?>
		<li><?php echo $ovoUser->Username(); ?> hasn't disliked anything yet, check back later!</li>
<?php
	}
?>
</ul>
<?php
} elseif ($page == "comments") {
	$comments = $ovoUser->GetComments($page_number);
	$last_page = $comments['last-page'];
	$comments = $comments['comments'];
?>
<ul class="user-page-list">
	<li class="title-item">Comments</li>
<?php
	if ($comments) {
		foreach($comments as $comment) {
?>
			<li onclick="navigateTo('/m<?php echo $comment['submission_url']; ?>')">
				<div class="submission-details">
				<div class="submission-info" style="margin-top:0;margin-bottom:0">Commented on</div>
				<a href="/m<?php echo $comment['submission_url']; ?>" title="<?php echo htmlspecialchars($comment['submission_title']); ?>">
					<?php echo htmlspecialchars($comment['submission_title']); ?>
				</a>
				<div class="user-comment-text"><?php echo $comment['body']; ?></div>
				</div>
				<a class="arrow" href="/m<?php $comment['submission_url']; ?>"></a>
				<div class="clearfix"></div>
			</li>
<?php
		}
		
		if ($last_page > 1) {
?>
			<div class="pager">
				<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . "/comments/" . ($page_number - 1); ?>">Previous</a><?php } ?>
				<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . "/comments/" . ($page_number + 1); ?>">Next</a><?php } ?>
				<div class="clearfix"></div>
			</div>
<?php
		}
	} else {
		// no comments
?>
		<li><?php echo $ovoUser->Username(); ?> hasn't commented on anything yet, check back later!</li>
<?php
	}
?>
</ul>
<?php
} elseif ($page == "friends") {
	$followers = $ovoUser->GetFollowers();
	$following = $ovoUser->GetFollowing();
?>
<div class="popular-upcoming">
	<a class="pop-upcoming active" onclick="toggleUserFriends('following')" id="following-toggle-link">Following</a>
	<a class="pop-upcoming" onclick="toggleUserFriends('followers')" id="followers-toggle-link">Followers</a>
</div>
<ul class="user-page-list">
	<li class="title-item" id="friends-title-li">Following</li>
	<?php
		if ($following) {
			foreach ($following as $friend) {
	?>
				<li onclick="navigateTo('/m/users/<?php echo strtolower($friend["username"]); ?>')" class="user-following-li">
					<div class="submission-details" style="vertical-align:middle">
						<a href="/m/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>" >
							<img src="<?php echo $friend["avatar"]; ?>" alt="" width="14" style="vertical-align:middle" />
						</a>&nbsp;
						<a href="/m/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>" style="vertical-align:middle">
							<?php echo $friend["username"]; ?>
						</a>
					</div>
					<a class="arrow" href="/m/users/<?php echo strtolower($friend["username"]); ?>"></a>
					<div class="clearfix"></div>
				</li>
	<?php
			}
		} else {
			// not following anyone
	?>
			<li class="user-following-li"><?php echo $ovoUser->Username(); ?> isn't following anyone yet, check back later!</li>
	<?php
		}
	?>
	
	<?php
		if ($followers) {
			foreach ($followers as $friend) {
	?>
				<li onclick="navigateTo('/m/users/<?php echo strtolower($friend["username"]); ?>')" class="user-follower-li" style="display:none">
					<div class="submission-details" style="vertical-align:middle">
						<a href="/m/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>" >
							<img src="<?php echo $friend["avatar"]; ?>" alt="" width="14" style="vertical-align:middle" />
						</a>&nbsp;
						<a href="/m/users/<?php echo strtolower($friend["username"]); ?>" title="<?php echo htmlspecialchars($friend["username"]); ?>" style="vertical-align:middle">
							<?php echo $friend["username"]; ?>
						</a>
					</div>
					<a class="arrow" href="/m/users/<?php echo strtolower($friend["username"]); ?>"></a>
					<div class="clearfix"></div>
				</li>
	<?php
			}
		} else {
			// not following anyone
	?>
			<li class="user-follower-li" style="display:none">No one is following <?php echo $ovoUser->Username(); ?> yet, check back later!</li>
	<?php
		}
	?>
</ul>
<?php	
} else {	// user home
?>
<ul class="user-page-list">
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/favorites')">
		<div class="user-page-list-title">Favorites</div>
		<a class="arrow" href="/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/favorites"></a>
		<div class="clearfix"></div>
	</li>
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/submissions')">
		<div class="user-page-list-title">Submissions</div>
		<a class="arrow" href="/m</users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/submissions"></a>
		<div class="clearfix"></div>
	</li>
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/comments')">
		<div class="user-page-list-title">Comments</div>
		<a class="arrow" href="/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/comments"></a>
		<div class="clearfix"></div>
	</li>
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/likes')">
		<div class="user-page-list-title">Likes</div>
		<a class="arrow" href="/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/likes"></a>
		<div class="clearfix"></div>
	</li>
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/dislikes')">
		<div class="user-page-list-title">Dislikes</div>
		<a class="arrow" href="/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/dislikes"></a>
		<div class="clearfix"></div>
	</li>
	<li onclick="navigateTo('/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/friends')">
		<div class="user-page-list-title">Friends</div>
		<a class="arrow" href="/m/users/<?php echo strtolower(htmlspecialchars($ovoUser->Username())); ?>/friends"></a>
		<div class="clearfix"></div>
	</li>
</ul>
<?php
}
?>


<!-- MODAL FORMS -->
<div style="display:none">
	<div id="report-form" class="modal-form">
		<h2>Report Submission</h2>
		<div class="modal-form-field">
			<select id="report-reason" name="report-reason" class="modal-select">
				<option value="Spam">Spam</option>
				<option value="Offensive">Offensive</option>
				<option value="Violation">Violation of TOU</option>
				<option value="Other">Other</option>
			</select>
		</div>
		<div class="modal-form-field"><textarea class="modal-textarea" name="report-details" id="report-details"></textarea></div>
		<div class="modal-form-field"><button class="normal-button modal-submit-button" onclick="submitReport('user', '<?php echo $ovoUser->ID(); ?>')">Submit</button></div>
	</div>
</div>
	
<?php include(get_mobile_footer()); ?>
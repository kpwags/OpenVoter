<?php
$submission_details = $ovSubmission->GetSubmissionDetails($submission_id);

if (!$submission_details) {
	header("Location: /error");
	exit();
}

$ovoSubmission = new ovoSubmission($submission_details);
$comment_count = $ovComment->GetCommentCount($submission_id);

$is_mobile = $ovContent->IsMobileBrowser();
if ($is_mobile && MOBILEEXISTS) {
	header("Location: /m" . $ovoSubmission->PageURL());
	exit();
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

$embed_code = "";
if (strtoupper($ovoSubmission->Type()) == "VIDEO") {
	if ($ovoSubmission->Domain() == "youtube.com") {
		$url = $ovoSubmission->URL();
		parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
		
		if (isset($my_array_of_vars['v'])) {
			$video_id = $my_array_of_vars['v'];
			$embed_code = "<iframe class=\"youtube-player\" type=\"text/html\" width=\"480\" height=\"270\" src=\"http://www.youtube.com/embed/$video_id\" frameborder=\"0\"></iframe>";
		}
	} elseif ($ovoSubmission->Domain() == "vimeo.com") {
		//
		$url = $ovoSubmission->URL();
		
		$result = preg_match('/(\d+)/', $url, $matches);
		
		if ($result) {
		    $video_id = $matches[0];
			$embed_code = "<iframe src=\"http://player.vimeo.com/video/$video_id\" width=\"480\" height=\"270\" frameborder=\"0\"></iframe>";
		}
	}
	
}

include (get_head());
?>
<title><?php echo $ovoSubmission->Title() . " | " . $ovSettings->Title() . $alert_count_text; ?></title>
<script type="text/javascript">
	$(document).ready(function() {
		$('#post-comment-body').focus(function() {
			toggleCommentTextArea('expand');
		});

		$('#post-comment-body').blur(function() {
			toggleCommentTextArea('collapse');
		});
	});
</script>
</head>
<body>
	<?php
	include ('category-bar-hidden.php');
	include (get_header());
	?>

	<input type="hidden" id="prepopulate_reply" value="<?php if ($ovUserSettings->PrepopulateReply()) { echo "yes"; } else { echo "no"; } ?>">
	<input type="hidden" id="submission-id" value="<?php echo $ovoSubmission->ID(); ?>" />
	
	<?php if ($ovSettings->TopAd() != "") { ?>
		<div class="top-ad" style="margin-top:12px"><p>ADVERTISEMENT</p><?php echo $ovSettings->TopAd(); ?></div>
	<?php } ?>
	<div class="submission-page">

		<!-- VOTING BUTTONS -->
		<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
		<div class="voting-buttons">
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<?php if ($vote_direction == 1) { ?>
					<div class="up-vote"><a class="voted-up" title="Vote this Up!" id="submission_vote_up_button_<?php echo $ovoSubmission->ID(); ?>" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')"></a></div>
				<?php } else { ?>
					<div class="up-vote"><a class="vote-up" id="submission_vote_up_button_<?php echo $ovoSubmission->ID(); ?>" title="Vote this Up!" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '1')"></a></div>
				<?php } ?>

				<div class="score">
					<?php if( strlen($ovoSubmission->Score()) > 4 ) { ?>
						<span style="font-size:12px" id="score_<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
					<?php } else { ?>
						<span id="score_<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
					<?php } ?>
				</div>

				<?php if ($vote_direction == -1) { ?>
					<div class="down-vote"><a class="voted-down" title="Vote this Down!" id="submission_vote_down_button_<?php echo $ovoSubmission->ID(); ?>" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')"></a></div>
				<?php } else { ?>
					<div class="down-vote"><a class="vote-down" id="submission_vote_down_button_<?php echo $ovoSubmission->ID(); ?>" title="Vote this Down!" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '-1')"></a></div>
				<?php } ?>
			<?php } else { ?>
				<!-- USER NOT LOGGED IN -->
				<div class="up-vote"><a class="vote-up" href="/login" title="Login To Vote"></a></div>

				<div class="score">
					<?php if( strlen($ovoSubmission->Score()) > 4 ) { ?>
						<span style="font-size:12px" id="score<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
					<?php } else { ?>
						<span id="score<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
					<?php } ?>
				</div>

				<div class="down-vote"><a class="vote-down" href="/login" title="Login to Vote"></a></div>
			<?php } ?>
		</div>

		<?php if ($embed_code == "") { ?><div class="submission-details"><?php } else { ?><div class="submission-details with-video"><?php } ?>
			<div class="title">
				<?php if (strtoupper($ovoSubmission->Type()) == "SELF") { ?>
					<span id="submission-title"><?php echo htmlspecialchars($ovoSubmission->Title()); ?></span>
				<?php } else {?>
					<a id="submission-title"
						href="<?php echo $ovoSubmission->URL(); ?>" 
						title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>" 
						target="<?php echo $ovUserSettings->OpenLinksIn(); ?>" 
						<?php if ($ovoSubmission->IsDomainRestricted()) { echo "rel=\"nofollow\""; } ?>>
							<?php echo htmlspecialchars($ovoSubmission->Title(), ENT_QUOTES, 'UTF-8'); ?>
					</a>
				<?php } ?>
			</div>
			<div class="summary">
				<?php if (strtoupper($ovoSubmission->Type()) != "SELF") { ?>
					<span class="domain">
						<a href="<?php echo htmlspecialchars($ovoSubmission->URL()); ?>" 
							title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>" 
							target="<?php echo $ovUserSettings->OpenLinksIn(); ?>" 
							<?php if ($ovoSubmission->IsDomainRestricted()) { echo "rel=\"nofollow\""; } ?>><?php echo $ovoSubmission->Domain(); ?></a>
					</span>
				<?php } ?>
				<span id="submission-summary"><?php echo $ovoSubmission->Summary(); ?></span>
			</div>
		</div>

		<?php if ($embed_code == "") { ?>
			<div class="submission-thumbnail">
				<?php if ($ovoSubmission->Thumbnail() == "/img/default_photo.jpg" && $ovoSubmission->UserID() == $ovUserSecurity->LoggedInUserID()) { ?>
					<img src="/<?php echo get_theme_directory(); ?>img/default-photo.png" alt="" id="submission-thumb" />
					<a class="normal-button fancybox-form-link" id="add-submission-thumb" href="#thumbnail-form">Add Thumbnail</a>
				<?php } else { ?>
					<img src="<?php echo $ovoSubmission->Thumbnail(); ?>" alt="" />
				<?php } ?>
			</div>
		<?php } else { ?>
			<div class="submission-video">
				<?php echo $embed_code; ?>
			</div>
		<?php } ?>

		<div class="clearfix"></div>

		<div class="user">
			Posted by 
			<img src="<?php echo $ovoSubmission->Avatar(); ?>" alt="<?php echo $ovoSubmission->Username(); ?>" width="14" />
			<a href="/users/<?php echo strtolower($ovoSubmission->Username()); ?>" title="<?php echo $ovoSubmission->Username(); ?>"><?php echo $ovoSubmission->Username(); ?></a>
			<span class="date"><?php echo $ovoSubmission->SubmissionDate(); ?><?php if ($ovoSubmission->IsPopular()) { ?>, <span class="popular">Made popular <?php echo $ovoSubmission->PopularDate(); ?></span><?php } ?></span>
		</div>

		<div class="sidebar">
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<div class="section">
					<h1>Actions</h1>
					<ul class="actions">
						<li>
							<a href="#share-form" onclick="ShowShareForm('<?php echo $ovoSubmission->ID(); ?>')" class="fancybox-form-link">
								<img src="/<?php echo get_theme_directory(); ?>img/icons/share.png" alt="" />Share
							</a>
						</li>

						<li>
							<a href="#report-form" onclick="ShowReportForm('submission', '<?php echo $ovoSubmission->ID(); ?>')" class="fancybox-form-link">
								<img src="/<?php echo get_theme_directory(); ?>img/icons/flag_red.png" alt="" />Report
							</a>
						</li>

						<li>
							<a onclick="ToggleSubmissionFavorite('<?php echo $ovoSubmission->ID(); ?>')">
								<img src="/<?php echo get_theme_directory(); ?>img/icons/star.png" alt="" /><span id="submission-favorite-link"><?php if ($ovSubmission->IsFavorite($ovoSubmission->ID())) { echo "Unfavorite"; } else { echo "Favorite"; } ?></span>
							</a>
						</li>

						<li>
							<a onclick="ToggleSubscription('<?php echo $ovoSubmission->ID(); ?>')">
								<img src="/<?php echo get_theme_directory(); ?>img/icons/subscribe.png" alt="" /><span id="submission-subscribe-link"><?php if ($ovSubmission->IsUserSubscribed($ovoSubmission->ID())) { echo "Unsubscribe"; } else { echo "Subscribe"; } ?></span>
							</a>
						</li>

						<?php if ($ovUserSecurity->LoggedInUserID() == $ovoSubmission->UserID() && $ovoSubmission->CanEdit()) { ?>
							<li>
								<a href="#edit-submission-form" class="fancybox-form-link">
									<img src="/<?php echo get_theme_directory(); ?>img/icons/edit.png" alt="" />Edit Submission
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>

			<div class="section">
				<h1>Stats</h1>
				<div class="stats-box">
					<div class="score"><?php echo $ovoSubmission->Score(); ?> Points</div>
					<?php
						$votes = $ovoSubmission->GetVotes();
						$num_likes = 0;
						$num_dislikes = 0;
						$percent_like = 0;
						if ($votes) {
							foreach ($votes as $v) {
								if ($v['direction'] == 1) {
									$num_likes++;
								} else {
									$num_dislikes++;
								}
							}

							$percent_like = ceil(($num_likes / count($votes)) * 100);
						}
					?>
					<div class="likes">
						<span class="like"><?php echo $num_likes; ?> upvotes</span><span class="dislike"><?php echo $num_dislikes; ?> downvotes</span>
					</div>
					<div class="percent-likes"><?php echo $percent_like; ?>% Like It</div>
				</div>
			</div>

			<?php 
				if ($ovoSubmission->Location() == "" || $ovoSubmission->UserID() == $ovUserSecurity->LoggedInUserID())
				{
					// if there is no location, and the logged in user is not the submitter, ignore the location section
			?>
				
				<div class="section" id="location_sidebar">
					<h1>Location</h1>

					<?php 
						if ($ovoSubmission->Location() == "") {
					?>
						<div class="location-image">
							<img src="/<?php echo get_theme_directory(); ?>img/default-map.png" alt="Location" id="location_image" />
							<div id="add-location-button">
								<a href="#location-form" class="fancybox-form-link normal-button">Add Location</a>
							</div>
						</div>
						<div class="location-name" id="location_text"></div>
					<?php
						} else {
							$location_image = "http://maps.google.com/maps/api/staticmap?center=" . urlencode($ovoSubmission->Location()) . "&zoom=13&size=228x228&sensor=false&markers=color:red|" . urlencode($ovoSubmission->Location());
					?>
						<div class="location-image"><img src="<?php echo $location_image; ?>" alt="Location" id="location_image" /></div>
						<div class="location-name" id="location_text"><?php echo $ovoSubmission->Location(); ?></div>
					<?php
						}
					?>
				</div>
			<?php 
				}
			?>

			<div class="section">
				<h1>Categories</h1>
				<?php
					$categories = $ovoSubmission->GetCategories();
					if ($categories)
					{
				?>
					<ul class="categories-tags">
						<?php
							foreach ($categories as $c)
							{
						?>
								<li>
									<a href="/c/<?php echo $c['url_name']; ?>">
										<img src="/<?php echo get_theme_directory(); ?>img/icons/category.png" alt="" /><?php echo $c['name']; ?>
									</a>
								</li>
						<?php
							}
						?>
					</ul>
				<?php 
					}
				?>
			</div>

			<div class="section">
				<h1>Tags</h1>
				<?php
					$tags = $ovoSubmission->GetTags();
					if ($tags)
					{
				?>
					<ul class="categories-tags">
						<?php
							foreach ($tags as $t)
							{
						?>
								<li>
									<a href="/t/<?php echo $t['url_name']; ?>">
										<img src="/<?php echo get_theme_directory(); ?>img/icons/tag.png" alt="" /><?php echo $t['name']; ?>
									</a>
								</li>
						<?php
							}
						?>
					</ul>
				<?php 
					}
				?>
			</div>
		</div>

		<div class="comments">
			<h1>Comments <?php if ($comment_count > 0) { echo "($comment_count)"; } ?></h1>
			<?php include (get_comments()); ?>
		</div>
		<div class="clearfix"></div>
	</div>

<!-- ADD LOCATION FORM -->
<div style="display:none">
	<div id="location-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Add Location</h1>
		<div>
			<input type="text" id="submission-location" placeholder="Location" /><button onclick="AddLocation()" class="normal-button">Save</button>
		</div>
	</div>
</div>

<!-- ADD THUMBNAIL FORM -->
<div style="display:none">
	<div id="thumbnail-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Add Thumbnail</h1>
		<div>
			<input type="text" id="submission-thumb-input" placeholder="Image URL" /><button onclick="AddThumbnail()" class="normal-button">Save</button>
			<br/><span class="form-hint">Paste the URL of the image in the box above</span>
		</div>
	</div>
</div>

<!-- EDIT FORM -->
<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
<div style="display:none">
	<div id="edit-submission-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Edit Submission</h1>
		<div>
			<input type="text" id="edit-submission-title" value="<?php echo $ovoSubmission->Title(); ?>" maxlength="255" />
		</div>
		<div>
			<?php if ($ovoSubmission->Type() != "SELF") { ?>
				<textarea id="edit-submission-summary" class="limit500" charsleft="edit-submission-summary-chars-left"><?php echo $ovoSubmission->Summary(); ?></textarea>
				<div class="align_right" id="edit-submission-summary-chars-left"><?php echo 500 - strlen($ovoSubmission->Summary()); ?> characters remaining</div>
			<?php } else { ?>
				<textarea id="edit-submission-summary"><?php echo $ovoSubmission->Summary(); ?></textarea>
			<?php } ?>
		</div>
		<div>
			<button onclick="EditSubmission()" class="normal-button">Save</button>
			<button onclick="ConfirmDeleteSubmission()" class="cancel-button">Delete</button>
		</div>
	</div>
</div>
<?php } ?>

<?php
include (get_footer());
?>
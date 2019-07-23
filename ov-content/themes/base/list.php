<?php
$subtype = "all";
if (isset($_GET['subtype'])) {
	$subtype = $_GET['subtype'];
} else {
	$subtype = "all";
}

if (isset($_GET['display_popular'])) {
	$display_popular = $_GET['display_popular'];
} else {
	$display_popular = "all";
}

if (isset($_GET['ordering'])) {
	$ordering = $_GET['ordering'];
} else {
	$ordering = "date";
}

if (isset($_GET['q'])) {
	$keywords = $_GET['q'];
} else {
	$keywords = "";
}

$is_popular = true;
$popular_string = "popular";
if (isset($_GET['popular'])) {
	if (strtolower($_GET['popular']) == "new") {
		$is_popular = false;
		$popular_string = "new";
	} else {
		$is_popular = true;
		$popular_string = "popular";
	}
}

$name = "all";
if (isset($_GET['name'])) {
	$name = strtolower($_GET['name']);
}

$page_number = 1;
if (isset($_GET['p'])) {
	$page_number = $_GET['p'];
}

$list_details = false;

if ($type == "category") {
	if ($name == "all") {
		$page_title = "All Submissions | " . $ovSettings->Title();
	} else {
		$page_title = $ovContent->GetCategoryNameFromSlug($name) . " | " . $ovSettings->Title();
	}
	$base_url = "/c/$name/";
} elseif ($type == "tag") {
	$page_title = $ovContent->GetTagNameFromSlug($name) . " | " . $ovSettings->Title();
	$base_url = "/t/$name/";
} elseif ($type == "friends") {
	$page_title = "Friends' Recent Activity | " . $ovSettings->Title();
	$base_url = "/friend-activity/";
} elseif ($type == "user-list") {
	if (!isset($_GET['username']) || !isset($_GET['list-name'])) {
		// username or list name not set, go to 404
		header("Location: /error");
		exit();
	}

	$list_details = $ovList->GetListDetailsByUserAndName($_GET['username'], $_GET['list-name']);

	if (!$list_details) {
		// couldn't find list, go to error
		header("Location: /error");
		exit();
	}

	if ( $list_details['is_private'] && ( strtolower($list_details['username']) != strtolower($ovUserSecurity->LoggedInUsername()) ) ) {
		// private list, viewed by wrong user, go to error
		header("Location: /error");
		exit();
	}

	$page_title = $list_details['username'] . "/" . $list_details['name'] . " | " . $ovSettings->Title();
	$base_url = "/lists/" . $_GET['username'] . "/" . $_GET['list-name'] . "/";
} else {
	$page_title = $ovSettings->Title();
	$base_url = "/c/all/";
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

$is_mobile = $ovContent->IsMobileBrowser();

if ($is_mobile && MOBILEEXISTS) {
	switch ($type) {
		case "category":
			header("Location: /m/c/$name");
			exit();
			break;
		case "tag":
			header("Location: /m/t/$name");
			exit();
			break;
		case "friends":
		case "search":
			break;
		default:
			header("Location: /m/home");
			exit();
			break;
	}
}

include (get_head());
?>
<title><?php echo $page_title . $alert_count_text; ?></title>
</head>
<body>
	<?php
	include (get_header());
	include (get_category_bar_open());
	?>
	
	<div class="list-page-main-content">
		<?php if ($ovSettings->TopAd() != "") { ?>
			<div class="top-ad"><p>ADVERTISEMENT</p><?php echo $ovSettings->TopAd(); ?></div>
		<?php } ?>
		<?php if ($type != "friends" && $type != "search" & $type != "user-list") { ?>
			<div class="type-filter">
				<ul class="type-filter">
					<li><a href="<?php echo $base_url . "$popular_string/all"; ?>" <?php if ($subtype == "all") { ?> class="active"<?php } ?>>All</a></li>
					<li><a href="<?php echo $base_url . "$popular_string/stories"; ?>" <?php if ($subtype == "stories") { ?> class="active"<?php } ?>>Stories</a></li>
					<li><a href="<?php echo $base_url . "$popular_string/photos"; ?>" <?php if ($subtype == "photos") { ?> class="active"<?php } ?>>Photos</a></li>
					<li><a href="<?php echo $base_url . "$popular_string/videos"; ?>" <?php if ($subtype == "videos") { ?> class="active"<?php } ?>>Videos</a></li>
					<li><a href="<?php echo $base_url . "$popular_string/podcasts"; ?>" <?php if ($subtype == "podcasts") { ?> class="active"<?php } ?>>Podcasts</a></li>
					<li><a href="<?php echo $base_url . "$popular_string/self"; ?>" <?php if ($subtype == "self") { ?> class="active"<?php } ?>>Self</a></li>
				</ul>
			</div>
			<div class="popular-filter">
				<ul class="popular-filter">
					<li><a href="<?php echo $base_url . "popular/$subtype"; ?>" <?php if ($is_popular) { ?> class="active"<?php } ?>>All</a></li>
					<li><a href="<?php echo $base_url . "new/$subtype"; ?>" <?php if (!$is_popular) { ?> class="active"<?php } ?>>New</a></li>
				</ul>
			</div>
			<div class="clearfix"></div>
		<?php } elseif ($type == "user-list") { ?>
			<div class="type-filter">
				<ul class="type-filter">
					<li><a href="<?php echo $base_url . "all"; ?>" <?php if ($subtype == "all") { ?> class="active"<?php } ?>>All</a></li>
					<li><a href="<?php echo $base_url . "stories"; ?>" <?php if ($subtype == "stories") { ?> class="active"<?php } ?>>Stories</a></li>
					<li><a href="<?php echo $base_url . "photos"; ?>" <?php if ($subtype == "photos") { ?> class="active"<?php } ?>>Photos</a></li>
					<li><a href="<?php echo $base_url . "videos"; ?>" <?php if ($subtype == "videos") { ?> class="active"<?php } ?>>Videos</a></li>
					<li><a href="<?php echo $base_url . "podcasts"; ?>" <?php if ($subtype == "podcasts") { ?> class="active"<?php } ?>>Podcasts</a></li>
					<li><a href="<?php echo $base_url . "self"; ?>" <?php if ($subtype == "self") { ?> class="active"<?php } ?>>Self</a></li>
				</ul>
			</div>
			<div class="clearfix"></div>
		<?php } ?>
		
		<?php if ($type == "search") { ?>
			<!-- SEARCH BOX AREA -->
			<div class="search-form-box">
				<form action="/php/prepare_search.php" method="post">
					<div>
						<input type="text" size="19" name="keywords" id="keywords" placeholder="Search Keywords" value="<?php echo str_replace("+", " ", $keywords); ?>" />
					</div>
					<span class="select-box">Type:<select name="submission_type">
							<option value="all" <?php if($subtype == "all") { echo "selected"; } ?>>All</option>
							<option value="story" <?php if($subtype == "story") { echo "selected"; } ?>>Stories</option>
							<option value="photo" <?php if($subtype == "photo") { echo "selected"; } ?>>Photos</option>
							<option value="video" <?php if($subtype == "video") { echo "selected"; } ?>>Videos</option>
							<option value="podcast" <?php if($subtype == "podcast") { echo "selected"; } ?>>Podcasts</option>
							<option value="self" <?php if($subtype == "self") { echo "selected"; } ?>>Self</option>
						</select>
					</span>
					<span class="select-box">Popular:<select name="submission_popular">
							<option value="all" <?php if($display_popular == "all") { echo "selected"; } ?>>All</option>
							<option value="yes" <?php if($display_popular == "yes") { echo "selected"; } ?>>Popular</option>
							<option value="no" <?php if($display_popular == "no") { echo "selected"; } ?>>Upcoming</option>
						</select>
					</span>
					<span class="select-box">Order By:<select name="ordering">
							<option value="date" <?php if($ordering == "date") { echo "selected"; } ?>>Date</option>
							<option value="score" <?php if($ordering == "score") { echo "selected"; } ?>>Score</option>
						</select>
					</span>
					<input type="submit" name="submit" value="Search" class="normal-button" />
				</form>
			</div>
		<?php } ?>
		
		<?php
			if ($type == "category" || $type == "index") {
				if ($name == "all") {
					$submissions = $ovSubmission->GetAllSubmissions($subtype, $is_popular, $page_number);
				} else {
					$submissions = $ovSubmission->GetForCategory($name, $subtype, $is_popular, $page_number);	
				}
			} elseif ($type == "tag") {
				$submissions = $ovSubmission->GetForTag($name, $subtype, $is_popular, $page_number);
				$submissions_sidebar = $ovSubmission->GetTopSubmissionsForTag($name, $subtype, $is_popular);
			} elseif ($type == "friends") {
				$submissions = $ovSubmission->GetFriendSubmissions(false, $page_number);
				$submissions_sidebar = false;
			} elseif ($type == "user-list") {
				$submissions = $ovList->GetListSubmissions($list_details['id'], $subtype, $page_number);
			} elseif ($type == "search") {
				$submissions = $ovSubmission->Search($keywords, $subtype, $display_popular, $ordering, $page_number);
				$submissions_sidebar = $ovSubmission->GetTopSubmissionsForCategory("popular", "all", true);
			}
			$last_page = $submissions['last-page'];
			$submissions = $submissions['submissions'];
			
		?>
	
		<?php if ($submissions) { ?>
		
			<?php foreach ($submissions as $sub) { 
					$ovoSubmission = new ovoSubmission($sub);
					$comment_count = $ovComment->GetCommentCount($ovoSubmission->ID());
			?>
				<div class="submission">
					<?php $vote_direction = $ovSubmission->CheckForVote($ovoSubmission->ID()); ?>
					<div class="voting_buttons">
						<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
							<?php if ($vote_direction == 1) { ?>
								<div class="up_vote_div"><a class="story_up_voted" title="Vote this Up!" id="submission_vote_up_button_<?php echo $ovoSubmission->ID(); ?>" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')"></a></div>
							<?php } else { ?>
								<div class="up_vote_div"><a class="story_up_vote" id="submission_vote_up_button_<?php echo $ovoSubmission->ID(); ?>" title="Vote this Up!" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '1')"></a></div>
							<?php } ?>

							<div class="submission_score">
								<?php if( strlen($ovoSubmission->Score()) > 4 ) { ?>
									<span style="font-size:12px" id="score_<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
								<?php } else { ?>
									<span id="score_<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
								<?php } ?>
							</div>

							<?php if ($vote_direction == -1) { ?>
								<div class="down_vote_div"><a class="story_down_voted" title="Vote this Down!" id="submission_vote_down_button_<?php echo $ovoSubmission->ID(); ?>" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '0')"></a></div>
							<?php } else { ?>
								<div class="down_vote_div"><a class="story_down_vote" id="submission_vote_down_button_<?php echo $ovoSubmission->ID(); ?>" title="Vote this Down!" onclick="SubmissionVote('<?php echo $ovoSubmission->ID(); ?>', '-1')"></a></div>
							<?php } ?>
						<?php } else { ?>
							<!-- USER NOT LOGGED IN -->
							<div class="up_vote_div"><a class="story_up_vote" href="/login" title="Login To Vote"></a></div>

							<div class="submission_score">
								<?php if( strlen($ovoSubmission->Score()) > 4 ) { ?>
									<span style="font-size:12px" id="score<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
								<?php } else { ?>
									<span id="score<?php echo $ovoSubmission->ID(); ?>"><?php echo $ovoSubmission->Score(); ?></span>
								<?php } ?>
							</div>

							<div class="down_vote_div"><a class="story_down_vote" href="/login" title="Login to Vote"></a></div>
						<?php } ?>
					</div>
					<div class="submission-content">
						<!-- THUMBNAIL -->
						<?php 
							if (strtoupper($ovoSubmission->Type()) == "PHOTO" || ( (strtoupper($ovoSubmission->Type()) == "STORY" || strtoupper($ovoSubmission->Type()) == "PODCAST") && $ovoSubmission->Thumbnail() != "/img/default_photo.jpg")) { 
								// show thumbnail
						?>
							<div class="submission-thumbnail">
								<a href="<?php echo $ovoSubmission->PageURL(); ?>" title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>">
									<img src="<?php echo $ovoSubmission->Thumbnail(); ?>" alt="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>" width="100"/>
								</a>
							</div>
						<?php 
							} 
						?>

						<div class="submission-details">
							<div class="submission-title">
								<a href="<?php echo $ovoSubmission->PageURL(); ?>" 
									title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>">
										<?php echo htmlspecialchars($ovoSubmission->Title(), ENT_QUOTES, 'UTF-8'); ?>
								</a>
							</div>
							<div class="submission-summary">
								<span class="submission-domain">
									<a href="<?php echo htmlspecialchars($ovoSubmission->URL()); ?>" 
										title="<?php echo htmlspecialchars($ovoSubmission->Title()); ?>" 
										target="<?php echo $ovUserSettings->OpenLinksIn(); ?>" 
										<?php if ($ovoSubmission->IsDomainRestricted()) { echo "rel=\"nofollow\""; } ?>><?php echo $ovoSubmission->Domain(); ?></a>
								</span>
								<?php echo $ovoSubmission->Summary(); ?>
							</div>
							<div class="submitted-by">
								Submitted <?php echo $ovoSubmission->SubmissionDate(); ?>
							</div>
							<div class="submission-links">
								<ul>
									<li><img src="<?php echo $ovoSubmission->Avatar(); ?>" alt="" height="16" /><a href="/users/<?php echo strtolower($ovoSubmission->Username()); ?>" title="<?php echo $ovoSubmission->Username(); ?>"><?php echo $ovoSubmission->Username(); ?></a></li>
									<li><img src="/<?php echo get_theme_directory() . "img/icons/comments.png" ?>" alt="" height="16" /><a href="<?php echo $ovoSubmission->PageURL(); ?>#comments" title="<?php echo $comment_count; ?> Comments"><?php echo $comment_count; ?> Comments</a></li>
									<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
										<li><img src="/<?php echo get_theme_directory() . "img/icons/share.png" ?>" alt="" height="16" /><a href="#share-form" onclick="ShowShareForm('<?php echo $ovoSubmission->ID(); ?>')" class="fancybox-form-link">Share This</a></li>
										<li><img src="/<?php echo get_theme_directory() . "img/icons/flag_red.png" ?>" alt="" height="16" /><a href="#report-form" onclick="ShowReportForm('submission', '<?php echo $ovoSubmission->ID(); ?>')" class="fancybox-form-link">Report This</a></li>
									<?php } ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="submission-seperator"></div>
			<?php } ?>
			
			<?php
			if ($last_page > 1) {
				if ($type == "search") {
					$ovUtilities->PrintPaginationRow("/search?q=$keywords&subtype=$subtype&display_popular=$display_popular&ordering=$ordering&p=", $page_number, $last_page);
				} elseif ($type == "friends") {
					$ovUtilities->PrintPaginationRow("/friend-activity/", $page_number, $last_page);
				} elseif ($type == "user-list") {
					$ovUtilities->PrintPaginationRow("/lists/" . $_GET['username'] . "/" . $_GET['list-name'] . "?p=", $page_number, $last_page);
				} else {
					$ovUtilities->PrintPaginationRow("$base_url" . "$popular_string/$subtype/", $page_number, $last_page);
				}
				
			}
			?>
		
		<?php } else { ?>
			<div class="margin_tb_20">It seems that there aren't any submissions here.</div>
		<?php } ?>
	</div>


<?php
include (get_footer());
?>
<?php
include (get_mobile_head());

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

$name = "popular";
if (isset($_GET['name'])) {
	$name = strtolower($_GET['name']);
}

$page_number = 1;
if (isset($_GET['p'])) {
	$page_number = $_GET['p'];
}

$type = "home";
if (isset($_GET['type'])) {
	$type = $_GET['type'];
}
if ($type == "category") {
	$page_title = $ovContent->GetCategoryNameFromSlug($name) . " | " . $ovSettings->Title();
	$base_url = "/m/c/$name/";
} elseif ($type == "tag") {
	$page_title = $ovContent->GetTagNameFromSlug($name) . " | " . $ovSettings->Title();
	$base_url = "/m/t/$name/";
} else {
	$page_title = $ovSettings->Title();
	$base_url = "/m/c/popular/";
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}
?>
<title><?php echo $page_title . $alert_count_text; ?></title>
</head>
<body>
	<?php
	include (get_mobile_header());
	?>
	
	<?php
		if ($type == "tag") {
			$submissions = $ovSubmission->GetForTag($name, $subtype, $is_popular, $page_number);
		} elseif ($type == "friends") {
			$submissions = $ovSubmission->GetFriendSubmissions(false, $page_number);
		} elseif ($type == "search") {
			$submissions = $ovSubmission->Search($keywords, $subtype, $display_popular, $ordering, $page_number);
		} else {
			$submissions = $ovSubmission->GetForCategory($name, $subtype, $is_popular, $page_number);
		}
		$last_page = $submissions['last-page'];
		$submissions = $submissions['submissions'];
		
	?>

	<?php if ($type == "category" || $type == "home" || $type == "tag") { ?>
		<div class="popular-upcoming">
			<a class="pop-upcoming <?php if ($is_popular) { echo "active"; }?>" href="<?php echo $base_url; ?>popular">Popular</a>
			<a class="pop-upcoming <?php if (!$is_popular) { echo "active"; }?>" href="<?php echo $base_url; ?>new">New</a>
		</div>
	<?php } ?>

	<?php if ($submissions) { ?>		
		<ul class="submission-list">
		<?php foreach ($submissions as $sub) { 
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
		<?php } ?>
		</ul>
		
		<?php if ($last_page > 1) { ?>
		<div class="pager">
			<?php if ($page_number != 1) { ?><a class="previous" href="<?php echo $base_url . $popular_string . "/" . ($page_number - 1); ?>">Previous</a><?php } ?>
			<?php if ($page_number < $last_page) { ?><a class="next" href="<?php echo $base_url . $popular_string . "/" . ($page_number + 1); ?>">Next</a><?php } ?>
			<div class="clearfix"></div>
		</div>
		<?php } ?>
	<?php } else { ?>
		<div class="no-submissions">No Submissions Here</div>
	<?php } ?>
	
	
	
<?php include(get_mobile_footer()); ?>
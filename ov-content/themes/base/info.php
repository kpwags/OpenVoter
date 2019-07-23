<?php
if ($type == "help") {
	$page_title = "Help With " . $ovSettings->Title();
} elseif ($type == "privacy") {
	$page_title = $ovSettings->Title() . " Privacy Policy";
} elseif ($type == "terms") {
	$page_title = $ovSettings->Title() . " Terms of Use";
} else {
	$page_title = "About " . $ovSettings->Title();
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title><?php echo $page_title . " | " . $ovSettings->Title() . $alert_count_text; ?></title>
</head>
<body>
	<?php
		include ('category-bar-hidden.php');
		include (get_header());
	?>
	
	<h1><?php echo $page_title; ?></h1>
	
	<?php
		switch ($type) {
			case "help":
				if ($ovSettings->Help() != "") {
					echo $ovSettings->Help();
				} else {
					echo "<p>No Help Specified</p>";
				}
				break;
			case "faq":
				include 'about/faq.php';
				break;
			case "terms":
				if ($ovSettings->TermsOfUse() != "") {
					echo $ovSettings->TermsOfUse();
				} else {
					echo "<p>No Terms of Use Specified</p>";
				}
				break;
			case "about":
			default:
				if ($ovSettings->About() != "") {
					echo $ovSettings->About();
				} else {
					echo "<p>No About Specified</p>";
				}
				break;
		}
	?>
	
<?php
include (get_footer());
?>
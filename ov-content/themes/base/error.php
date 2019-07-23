<?php
$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Error | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>

</head>
<body>
	<?php
	include (get_header());
	?>
	<div class="margin_tb_10"></div>
	<h1>Oops!</h1>
	<p>Boy is this embarassing, the page you're looking for can't be found. Why not try a search, or check out these recent submissions.</p>
	<div class="error-search">
		<form action="/php/prepare_search.php" method="post">
			<input type="text" size="40" name="keywords" id="keywords" class="textbox_16" placeholder="Search <?php echo $ovSettings->Title(); ?>" />
			<input type="submit" name="submit" value="Search" class="normal-button" />
		</form>
	</div>
	<div class="margin_tb_20">
		<ul class="error-sub">
		<?php
			$submissions = $ovSubmission->GetForCategory('popular', 'all', false, 1);
			if ($submissions) {
				$submissions = $submissions['submissions'];
				foreach ($submissions as $sub) {
					$page_url = "/" . strtolower($sub['type']) . "/" . $sub['id'] . "/" . strtolower($sub['title']);
		?>
					<li>
						<a href="<?php echo $page_url; ?>" title="<?php echo htmlspecialchars($sub['title']); ?>">
							<?php echo htmlspecialchars($sub['title']); ?>
						</a>
					</li>
		<?php			
				}
			} else {
		?>
				<li>No Recent Submissions</li>
		<?php
			}
		?>
		</ul>
	</div>
	
<?php
include (get_footer());
?>
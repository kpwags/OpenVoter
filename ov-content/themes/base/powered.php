<?php
$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Powered By openvoter | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
</head>
<body>
	<?php
	include ('category-bar-hidden.php');
	include (get_header());
	?>
	
	<div class="powered-by">
		<p>This site is powered by</p>
		<a href="http://www.openvoter.org" title="OpenVoter">
			<img src="/img/openvoter-logo.jpg" alt="Powered By OpenVoter" border="0" />
		</a>
	</div>
	
	<div class="align_center"><a href="http://www.openvoter.org">OpenVoter Homepage</a></div>
	
	<div class="align_center margin_tb_10">
		<img style="vertical-align:middle;padding-right:5px" src="/img/twitter.jpg" alt="Twitter" height="16" />
		<a href="http://www.twitter.com/openvoter" title="Follow OpenVoter on Twitter" target="_blank" style="vertical-align:middle;">Follow OpenVoter on Twitter</a>
	</div>
	
	<div class="align_center bold margin_tb_10"><b>Version <?php echo $ovSettings->Version(); ?></b></div>
	
	<div class="align_center bold margin_tb_25">&copy; 2008-2012 <a href="http://www.openvoter.org/">OpenVoter</a></div>
	
<?php
include (get_footer());
?>
<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
	
	require_once 'ov-config.php';
	require_once 'ovapi.php';
	$ovAPI = new ovAPI();
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();

	$format = "json";
	if (isset($_GET['format'])) {
		$format = strtolower($_GET['format']);
	}
	
	if ($ovSettings->EnableAPI()) {
		if (isset($_GET['name'])) {
			$tag = urldecode($_GET['name']);
			if (isset($_GET['popular']) && strtolower($_GET['popular']) == "yes") {
				$is_popular = "yes";
			} elseif (isset($_GET['popular']) && strtolower($_GET['popular']) == "no") {
				$is_popular = "no";
			} else {
				$is_popular = "all";
			}
		
			if (isset($_GET['type'])) {
				$type = $_GET['type'];
			} else {
				$type = "all";
			}
		
			if (isset($_GET['offset'])) {
				$offset = $_GET['offset'];
			} else {
				$offset = 0;
			}
		
			if (isset($_GET['limit'])) {
				$limit = $_GET['limit'];
			} else {
				$limit = 10;
			}
		
			if ($limit > 20) {
				$limit = 20;
			}
		
			$submissions = $ovAPI->GetSubmissionsByTag($tag, $is_popular, $type, $offset, $limit);
		} else {
			$submissions = false;
		}
	}
?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>
<?php if ($ovSettings->EnableAPI()) { ?>
<?php if ($format == "xml") { ?>
<?php header ("Content-Type:text/xml"); ?>
<OpenVoter>
	<submissions>
<?php if ($submissions) { ?>
<?php foreach ($submissions as $sub) { ?>
		<submission>
			<id><?php echo $sub['id']; ?></id>
			<type><?php echo htmlspecialchars($sub['type']); ?></type>
			<title><?php echo htmlspecialchars($sub['title']); ?></title>
			<summary><?php echo "<![CDATA[" . htmlspecialchars($sub['summary']) . "]]>"; ?></summary>
			<url><?php echo htmlspecialchars($sub['url']); ?></url>
			<score><?php echo htmlspecialchars($sub['score']); ?></score>
			<thumbnail><?php echo htmlspecialchars($sub['thumbnail']); ?></thumbnail>
			<date><?php echo htmlspecialchars($sub['date']); ?></date>
			<popular><?php echo htmlspecialchars($sub['popular']); ?></popular>
			<popularDate><?php echo htmlspecialchars($sub['popular_date']); ?></popularDate>
			<location><?php echo htmlspecialchars($sub['location']); ?></location>
			<pageUrl><?php echo htmlspecialchars($sub['page_url']); ?></pageUrl>
			<categories><?php echo htmlspecialchars($sub['categories']); ?></categories>
			<tags><?php echo htmlspecialchars($sub['tags']); ?></tags>
			<commentCount><?php echo htmlspecialchars($sub['num_comments']); ?></commentCount>
			<user>
				<username><?php echo htmlspecialchars($sub['username']); ?></username>
				<avatar><?php echo htmlspecialchars($sub['avatar']); ?></avatar>
			</user>	
		</submission>
<?php } ?>
<?php } ?>
	</submissions>
<?php } else { ?>
	<error>API Not Enabled</error>
</OpenVoter>
<?php } else { 
	header ("Content-Type:application/json");
	if ($submissions) {
		echo json_encode($submissions);
	} else {

	} 
} ?>
<?php } ?>
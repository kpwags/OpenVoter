<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
	header ("Content-Type:text/xml");
	
	require_once 'ov-config.php';
	require_once 'ovapi.php';
	$ovAPI = new ovAPI();
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	if ($ovSettings->EnableAPI()) {
		if (isset($_GET['comments'])) {
			if ($_GET['comments'] == "yes") {
				$return_comments = true;
			} else {
				$return_comments = false;
			}
		} else {
			$return_comments = false;
		}
	
		if (isset($_GET['url'])) {
			$url = urlencode($_GET['url']);
			$sub = $ovAPI->GetSubmissionByUrl($url, $return_comments);
		} elseif(isset($_GET['id'])) {
			$id = $_GET['id'];
			$sub = $ovAPI->GetSubmissionByID($id, $return_comments);
		} else {
			$sub = false;
		}
	}
?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>
<OpenVoter>
<?php if ($ovSettings->EnableAPI()) { ?>
<?php if ($sub) { ?>
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
	<comments>
<?php if (is_array($sub['comments'])) { ?>
<?php foreach ($sub['comments'] as $cmt) { ?>
		<comment>
			<id><?php echo htmlspecialchars($cmt['id']); ?></id>
			<user>
				<username><?php echo htmlspecialchars($cmt['username']); ?></username>
				<avatar><?php echo htmlspecialchars($cmt['avatar']); ?></avatar>
			</user>
			<date><?php echo htmlspecialchars($cmt['date']); ?></date>
			<score><?php echo htmlspecialchars($cmt['score']); ?></score>
			<body><?php echo "<![CDATA[" . htmlspecialchars($cmt['body']) . "]]>"; ?></body>
		</comment>
<?php } ?>
<?php } ?>
	</comments>
</submission>
<?php } ?>
<?php } else { ?>
	<error>API Not Enabled</error>
<?php } ?>
</OpenVoter>
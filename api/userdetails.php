<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
	header ("Content-Type:text/xml");
	
	require_once 'ov-config.php';
	require_once 'ovapi.php';
	$ovAPI = new ovAPI();
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	if ($ovSettings->EnableAPI()) {
		if (isset($_GET['username'])) {
			$username = $_GET['username'];
			$user = $ovAPI->GetUserDetails($username);
		} else {
			$user = false;
		}
	}
?>
<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>
<OpenVoter>
<?php if ($ovSettings->EnableAPI()) { ?>
	<user>
<?php if ($user) { ?>
	
		<id><?php echo $user['id']; ?></id>
		<username><?php echo htmlspecialchars($user['username']); ?></username>
		<details><?php echo "<![CDATA[" . htmlspecialchars($user['details']) . "]]>"; ?></details>
		<location><?php echo htmlspecialchars($user['location']); ?></location>
		<website><?php echo htmlspecialchars($user['website']); ?></website>
		<avatar><?php echo htmlspecialchars($user['avatar']); ?></avatar>
		<points><?php echo htmlspecialchars($user['points']); ?></points>
		<numSubmissions><?php echo htmlspecialchars($user['num_submissions']); ?></numSubmissions>
		<numComments><?php echo htmlspecialchars($user['num_comments']); ?></numComments>
		<numVotes><?php echo htmlspecialchars($user['num_votes']); ?></numVotes>
		<numFavorites><?php echo htmlspecialchars($user['num_favorites']); ?></numFavorites>
		<numFollowers><?php echo htmlspecialchars($user['num_followers']); ?></numFollowers>
		<numFollowing><?php echo htmlspecialchars($user['num_following']); ?></numFollowing>
		<joinDate><?php echo htmlspecialchars($user['join_date']); ?></joinDate>
<?php } ?>
	</user>
<?php } else { ?>
	<error>API Not Enabled</error>
<?php } ?>
</OpenVoter>
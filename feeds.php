<?php
	header("Content-Type: application/xml; charset=UTF-8"); 
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	ini_set("include_path", "./:./ov-include:./../ov-include");
	/*
		Copyright 2008-2010 OpenVoter
		
		This file is part of OpenVoter.
	
		OpenVoter is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, version 3.
	
		OpenVoter is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.
	
		You should have received a copy of the GNU General Public License
		along with OpenVoter.  If not, see <http://www.gnu.org/licenses/>.
	*/
	require_once 'ov-config.php';
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovcontent.php';
	$ovContent = new ovContent();
	
	require_once 'ovrss.php';
	$ovRSS = new ovRSS();
	
	/*
		ARGUMENTS
		type: all (default), popular, upcoming, category, tag, user, comment
		id: category url name, tag url name, username, submission id
		subtype: ALL, STORY, PHOTO, VIDEO
		popular: yes, no
	*/
	
	$rss_type = "all";
	if (isset($_GET['type'])) {
		$rss_type = $_GET['type'];
	}
	
	$subtype = "all";
	if (isset($_GET['subtype'])) {
		$subtype = $_GET['subtype'];
	}
	
	$id = "";
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
	}
	
	$popular = "all";
	if (isset($_GET['popular'])) {
		$popular = $_GET['popular'];
	}
	
	$rss_info = $ovRSS->GetFeedInfo($rss_type, $subtype, $id, $popular);
	
	switch ($rss_type)
	{
		case "category":
			$feed_items = $ovRSS->GetSubmissionsForCategory($id, $popular, $subtype);
			break;
		case "tag":
			$feed_items = $ovRSS->GetSubmissionsForTag($id, $popular, $subtype);
			break;
		case "user":
			$feed_items = $ovRSS->GetSubmissionsForUser($id);
		case "all":
			$feed_items = $ovRSS->GetAllSubmissions($popular, $subtype);
			break;
		default:
			$feed_items = $ovRSS->GetAllSubmissions("all", "all");
			break;
	}
?>

<rss version="2.0">
	<channel>
		<title><?php echo $rss_info['title']; ?></title>
		<link><?php echo $rss_info['link']; ?></link>
		<description><?php echo $rss_info['desc']; ?></description>
		
		<?php 
			if ($feed_items && count($feed_items) > 0) {
				foreach ($feed_items as $item) {
		?>
					<item>
						<title><?php echo $item['title']; ?></title>
						<link><?php echo $item['link']; ?></link>
						<guid><?php echo $item['link']; ?></guid>
						<description><![CDATA[<?php echo $item['description']; ?>]]></description>
					</item>
		<?php
				}
			}
		?>
		
	</channel>
</rss>
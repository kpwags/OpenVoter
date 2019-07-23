<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
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
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	require_once 'ovsubmission.php';
	$ovSubmission = new ovSubmission();
	
	require_once 'ovcomment.php';
	$ovComment = new ovComment();
	
	require_once 'ovcontent.php';
	$ovContent = new ovContent;
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovalerting.php';
	$ovAlerting = new ovAlerting();

	require_once 'ovutilities.php';
	$ovUtilities = new ovUtilities();
	
	require_once 'ovuser.php';
	$ovUser = new ovUser();

	require_once 'ovlist.php';
	$ovList = new ovList();
	
	require_once 'ovouser.php';
	require_once 'ovocomment.php';
	require_once 'ovosubmission.php';
	
	$ovUserSecurity->ValidateSession();
	
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
	} else {
		$action = "";
	}
	
	switch ($action) {
		case "toggle_subscription":
			$id = strip_tags($_GET['id']);
			$user_id = $ovUserSecurity->LoggedInUserID();
			
			if ($ovSubmission->IsUserSubscribed($id)) {
				$ovSubmission->Unsubscribe($user_id, $id);
				$message = "unsubscribed";
			} else {
				$ovSubmission->Subscribe($user_id, $id);
				$message = "subscribed";
			}
			
			$json = array('status' => 'OK', 'message' => $message);
			
			echo json_encode($json);
			break;

		case "toggle_submission_favorite":
			$id = strip_tags($_GET['id']);
			$user_id = $ovUserSecurity->LoggedInUserID();

			if ($ovSubmission->IsFavorite($id)) {
				$ovSubmission->DeleteFavorite($id);
				$message = "is_not_favorite";
			} else {
				$ovSubmission->AddFavorite($id);
				$message = "is_favorite";
			}

			$json = array('status' => 'OK', 'message' => $message);
			
			echo json_encode($json);
			break;

		case "add_location":
			$id = strip_tags($_GET['id']);
			$location = strip_tags($_GET['location']);

			$ovSubmission->AddLocation($id, $location);

			$json = array('status' => 'OK', 'message' => '');

			echo json_encode($json);
			break;

		case "edit_submission":
			$id = strip_tags($_GET['id']);
			$title = strip_tags($_GET['title']);
			$summary = strip_tags($_GET['summary']);

			$json = $ovSubmission->EditSubmission($id, $title, $summary);
			
			echo json_encode($json);
			break;

		case "delete_submission":
			$id = strip_tags($_GET['id']);

			$json = $ovSubmission->UserDeleteSubmission($id);
			
			echo json_encode($json);
			break;

		case "submit_report":
			$reason = strip_tags($_GET['reason']);
			$details = strip_tags($_GET['details']);
			$type = strtolower(strip_tags($_GET['objecttype']));
			$id = strip_tags($_GET['objectid']);

			$result = "ERROR";
			switch($type) {
				case 'submission':
					$result = $ovContent->ReportSubmission($id, $reason, $details);
					break;
				case 'comment':
					$result = $ovContent->ReportComment($id, $reason, $details);
					break;
				case 'user':
					$result = $ovContent->ReportUser($id, $reason, $details);
					break;
			}
			
			if ($result == "OK") {
				$response = array('action' => 'submit_report', 'status' => 'OK', 'message' => 'Thank you for alerting us to this.');
			} elseif ($result == "REPEAT") {
				$response = array('action' => 'submit_report', 'status' => 'REPEAT', 'message' => 'It seems like you already reported this, don\'t worry, we\'ll get to it');
			} else {
				$response = array('action' => 'submit_report', 'status' => 'ERROR', 'message' => 'An unknown error occurred, please try submitting the report again');
			}

			echo json_encode($response);
			break;

		case "share_submission":
			$submission_id = strip_tags($_GET['id']);
			$message = strip_tags($_GET['message']);
			$share_with = strip_tags($_GET['share_with']);
			
			if ($ovUserSecurity->IsUserLoggedIn()) {
				$ovAlerting->ProcessNewShareAlerts($submission_id, $ovUserSecurity->LoggedInUserID(), $share_with, $message);
				$json = array('status' => 'OK', 'message' => 'You have shared this with your friends');
			} else {
				$json = array('status' => 'ERROR', 'message' => 'You must be logged in to share');
			}

			echo json_encode($json);
			break;

		case "add_thumbnail":
			$id = strip_tags($_GET['id']);
			$thumb_url = urldecode($_GET['url']);
			
			$thumb_url = str_replace(" ", "+", $thumb_url);
			$filename = "submission-" . $id . ".jpg";
			$last_three = substr($thumb_url, -3);
			$last_four = substr($thumb_url, -4);

			if ($last_three == "jpg" || $last_three == "gif" || $last_three == "png" || $last_four == "jpeg") {
				$json = $ovSubmission->SetSubmissionThumbnail($id, $thumb_url, $filename);
			} else {
				$json = array('status' => 'ERROR', 'message' => 'Invalid image');
			}

			echo json_encode($json);
			break;

		case "validate_username_email":
			$identifier = strip_tags($_GET['identifier']);
			$valid = $ovUser->DoesUserExist($identifier);
			if ($valid == false) {
				echo "<response><action>validate_username_email</action><status><code>OK</code><message></message></status></response>";
			} else {
				echo "<response><action>validate_username_email</action><status><code>ERROR</code><message>Taken</message></status></response>";
			}
			break;

		case "block_user":
			$user_id = $_GET['id'];
			
			$ovUser->BlockUser($user_id);
			
			$json = array('action' => 'block_user', 'status' => 'OK', 'message' => '');
			
			echo json_encode($json);
			break;

		case "unblock_user":
			$user_id = $_GET['id'];
			
			$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
			$ovoUser->UnblockUser($user_id);
			
			$json = array('action' => 'unblock_user', 'status' => 'OK', 'message' => '');
			
			echo json_encode($json);
			break;

		case "follow_user":
			$user_id = $_GET['id'];
			
			$result = $ovUser->FollowUser($user_id);
			
			if ($result == "OK") {
				$json = array('action' => 'follow_user', 'status' => 'OK', 'message' => '');
			} elseif ($result == "BLOCKED") {
				$json = array('action' => 'follow_user', 'status' => 'ERROR', 'message' => 'This User Has You Blocked, Sorry.');
			} else {
				$json = array('action' => 'follow_user', 'status' => 'ERROR', 'message' => 'An Unknown Error Has Occurred');
			}

			echo json_encode($json);
			break;

		case "unfollow_user":
			$user_id = $_GET['id'];
			
			$ovUser->UnfollowUser($user_id);
			
			$json = array('action' => 'unfollow_user', 'status' => 'OK', 'message' => '');
			
			echo json_encode($json);
			break;

		case "mark_alert_read":
			$alert_type = $_GET['alert_type'];
			$alert_id = $_GET['id'];
			$ovAlerting->MarkAlertRead($alert_type, $alert_id);
			echo "<response><action>$action</action><status><code>OK</code><message></message></status></response>";
			break;
		case "mark_all_alerts_read":
			$alert_type = $_GET['alert_type'];
			if ($alert_type == "all") {
				$ovAlerting->MarkAllAlertsRead();
			} else {
				$ovAlerting->MarkAllAlertsRead($alert_type);
			}
			echo "<response><action>$action</action><status><code>OK</code><message></message></status></response>";
			break;
		case "delete_avatar":
			$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
			$ovoUser->DeleteAvatar();
			echo "<response><action>$action</action><status><code>OK</code><message></message></status></response>";
			break;
		
		case "comment_vote":
			$comment_id = $_GET['id'];
			$direction = $_GET['direction'];
			$comment = $ovComment->GetCommentDetails($comment_id);
			$ovoComment = new ovoComment($comment);
			$ovoComment->Vote($direction);
			$comment_score = $ovComment->GetCommentScore($comment_id);
			echo "<response><action>$action</action><status><code>OK</code><message>$comment_score</message></status></response>";
			break;

		case "post_comment":
			$submission_id = $_GET['id'];
			$body = strip_tags(urldecode($_GET['body']));

			if (!$ovUserSecurity->CanUserPostComment()) {
				$json = array('status' => 'ERROR', 'errorMessage' => "Sorry, it looks like you've reached your comment limit for the last 24 hours. This happens if your karma level is too low. Try finding some really interesting submissions and posting some good comments so you can get back in the black.");
			} else {
				$json = $ovComment->PostComment($body, $submission_id);
			}
			
			echo json_encode($json);
			break;

		case "post_comment_reply":
			$submission_id = $_GET['submission_id'];
			$body = strip_tags(urldecode($_GET['body']));
			$reply_id = $_GET['reply_id'];

			if (!$ovUserSecurity->CanUserPostComment()) {
				$json = array('status' => 'ERROR', 'errorMessage' => "Sorry, it looks like you've reached your comment limit for the last 24 hours. This happens if your karma level is too low. Try finding some really interesting submissions and posting some good comments so you can get back in the black.");
			} else {
				$json = $ovComment->PostComment($body, $submission_id, true, $reply_id);
			}

			echo json_encode($json);
			break;

		case "toggle_comment_favorite":
			$id = strip_tags($_GET['id']);
			$user_id = $ovUserSecurity->LoggedInUserID();

			if ($ovComment->IsFavorite($id)) {
				$ovComment->DeleteFavorite($id);
				$message = "is_not_favorite";
			} else {
				$ovComment->AddFavorite($id);
				$message = "is_favorite";
			}
			
			$json = array('status' => 'OK', 'message' => $message);
			
			echo json_encode($json);
			break;
			
		case "edit_comment":
			$comment_id = $_GET['id'];
			$body = strip_tags(urldecode($_GET['body']));

			$ovComment->EditComment($comment_id, $body);
			$comment = $ovComment->GetCommentDetails($comment_id);
			$ovoComment = new ovoComment($comment);
			
			if ($ovoComment) {
				$json = array('status' => 'OK', 'message' => 'Edit successful', 'body' => $ovoComment->Body());
			} else {
				$json = array('status' => 'ERROR', 'message' => 'Unable to edit comment');
			}

			echo json_encode($json);
			break;

		case "delete_comment":
			$comment_id = $_GET['id'];
			$ovComment->DeleteComment($comment_id);

			$json = array('status' => 'OK', 'message' => 'Comment Deleted');

			echo json_encode($json);
			break;
		case "submission_vote":
			$submission_id = $_GET['id'];
			$direction = $_GET['direction'];
			$submission = $ovSubmission->GetSubmissionDetails($submission_id);
			$ovoSubmission = new ovoSubmission($submission);
			$ovoSubmission->Vote($direction);
			
			$submission_score = $ovSubmission->GetSubmissionScore($submission_id);

			echo "<response><action>$action</action><status><code>OK</code><message>$submission_score</message></status></response>";
			break;
		case "get_security_question":
			$email = strip_tags($_GET['email']);
			$security_question = $ovUserSecurity->GetSecurityQuestion($email);
			if ($security_question) {
				echo "<response><action>$action</action><status><code>OK</code><message><![CDATA[" . $security_question . "]]></message></status></response>";
			} else {
				echo "<response><action>$action</action><status><code>ERROR</code><message>No user with email address.</message></status></response>";
			}
			break;
		case "check_security_answer":
			$answer = urldecode($_GET['answer']);
			$email = strip_tags($_GET['email']);
			$result = $ovUserSecurity->CheckecurityAnswer($email, $answer);
			if ($result) {
				echo "<response><action>$action</action><status><code>OK</code><message></message></status></response>";
			} else {
				echo "<response><action>$action</action><status><code>ERROR</code><message>Incorrect response.</message></status></response>";
			}
			break;
		case "reset_password":
			$password = $_GET['password'];
			$email = strip_tags($_GET['email']);
			$ovUserSecurity->ResetPassword($email, $password);
			echo "<response><action>$action</action><status><code>OK</code><message></message></status></response>";
			break;

		case "validate_url":
			if (!$ovUserSecurity->CanUserPostSubmission()) {
				$json = array('status' => 'ERROR', 'message' => 'Sorry, it looks like you\'ve reached your submission limit for the last 24 hours. This happens if your karma level is too low. Try finding some really interesting submissions and posting some good comments so you can get back in the black.');
			} else {
				$url = urldecode($_GET['url']);
				$type = $_GET['type'];
				$result = $ovContent->validateURL($url, $type);
				$code = $result['code'];
				$message = $result['message'];
			
				if ($code == "INVALID") {
					$json = array('status' => 'ERROR', 'message' => 'Sorry, but we could not find that URL');
				} elseif ($code == "BANNED") {
					$json = array('status' => 'ERROR', 'message' => 'Sorry, but the domain your submission is from has been banned');
				} elseif ($code == "EXISTS") {
					$json = array('status' => 'EXISTS', 'submissionUrl' => $message);
				} elseif ($code == "OK") {
					$sub_info = $ovContent->GetTitleAndDescription($url);
					$title = $sub_info['title'];
					$description = $sub_info['description'];

					$json = array('status' => 'OK', 'title' => $title, 'summary' => $description);
				} else {
					$json = array('status' => 'ERROR', 'message' => 'Sorry, but we were not able to process the URL, please try again later');
				}
			}

			echo json_encode($json);
			break;

		case "get_thumbnails":
			$url = urldecode($_GET['url']);

			$images = $ovContent->imageGrabber($url);

			if ($images && count($images) > 0) {
				$json = array('status' => 'NOIMAGES', 'messages' => 'No images found');
				
				$x = 1; 
				$image_array = array();
				foreach ($images as $img) { 

					if( @getimagesize($img) )		
					{
						try {
							list($width,$height) = @getimagesize($img) ;
						} catch (Exception $e) {}
						if($width >= 200 && $height >= 200)
						{	// we only want images with dimensions of 200x200 or bigger
							$image_url = $img;
							$image_id = "thumbnail-choice-" . $x;
							
							$i['src'] = $image_url;
							$i['id'] = $image_id;
						}
						$x++;
					}
				}
				
				$json = array('status' => 'OK', 'images' => $image_array);
			} else {
				$json = array('status' => 'ERROR', 'message' => 'Unable to find images');
			}
			
			echo json_encode($json);
			break;

		case "submit_link":
			$url = urldecode($_GET['url']);
			$type = urldecode($_GET['type']);
			$title = urldecode($_GET['title']);
			$summary = urldecode($_GET['summary']);
			$tags = urldecode($_GET['tags']);
			$categories = urldecode($_GET['categories']);
			$thumbnail = urldecode($_GET['thumbnail']);
			$ignore_dupes = urldecode($_GET['ignore_duplicates']);

			$keyword_string = str_replace(" ", "+", $title) ;
			$submission_count = $ovSubmission->SearchCount($keyword_string, "all", "all", "3 DAY");

			if ($submission_count > 0 && $ignore_dupes == false) {
				$submissions = $ovSubmission->Search($keyword_string, "all", "all", "date", 1, "3 DAY");
				$submissions = $submissions['submissions'];
				$json = array('status' => 'DUPLICATES', 'submissions' => $submissions);
			} else {
				$category_array = explode(",", $categories);

				$tags = $ovContent->BreakDownTags($tags);
				$db_tags = $ovContent->AddTags($tags);

				$submission_id = $ovSubmission->AddSubmission($title, $summary, $url, $type);

				if ($submission_id)	{
					$filename = "submission-" . $submission_id . ".jpg";
					if ($type == "PHOTO") {
						$last_three = substr($url, -3);
						$last_four = substr($url, -4);
						if ($last_three == "jpg" || $last_three == "gif" || $last_three == "png" || $last_four == "jpeg")
						{
							$thumbnail = $url;
						}
						
						if ($thumbnail == "") {
							$ovSubmission->SetSubmissionDBThumbnail($submission_id, "/img/default_photo.jpg");
						} else {
							$last_three = substr($thumbnail, -3);
							$last_four = substr($thumbnail, -4);
							if ($last_three == "jpg" || $last_three == "gif" || $last_three == "png" || $last_four == "jpeg") {
								$ovSubmission->SetSubmissionThumbnail($submission_id, $thumbnail, $filename);
							} else {
								$ovSubmission->SetSubmissionDBThumbnail($submission_id, "/img/default_photo.jpg");
							}
						}
					}
					
					$ovSubmission->AddSubmissionCategories($submission_id, $category_array);
					$ovSubmission->AddSubmissionTags($submission_id, $db_tags);
					$ovSubmission->AddVote($submission_id, 1);

					$page_url = "/" . strtolower($type) . "/$submission_id/" . $ovUtilities->ConvertToUrl($title);
					$json = array('status' => 'OK', 'url' => $page_url);
				} else {
					$json = array('status' => 'ERROR', 'message' => 'Unable to add submission, please refresh and try again.');
				}
			}

			echo json_encode($json);
			break;

		case "add_list":
			$name = urldecode($_GET['name']);
			$is_private = urldecode($_GET['is_private']);

			if ($is_private == "private") {
				$is_private = true;
			} else {
				$is_private = false;
			}

			$json = $ovList->JSONAddNewList($name, $is_private);
			
			echo json_encode($json);
			break;

		case "edit_list":
			$id = urldecode($_GET['id']);
			$name = urldecode($_GET['name']);
			$is_private = urldecode($_GET['is_private']);

			if ($is_private == "private") {
				$is_private = true;
			} else {
				$is_private = false;
			}

			$json = $ovList->JSONEditList($id, $name, $is_private);
			
			echo json_encode($json);
			break;

		case "adjust_lists_for_user":
			$user_to_add = urldecode($_GET['user_to_add']);
			
			if (strlen(urldecode($_GET['add_lists'])) > 0) {
				$add_lists = explode(",", urldecode($_GET['add_lists']));
			} else {
				$add_lists = false;
			}

			if (strlen(urldecode($_GET['delete_lists'])) > 0) {
				$delete_lists = explode(",", urldecode($_GET['delete_lists']));
			} else {
				$delete_lists = false;
			}

			$json = $ovList->JSONAddRemoveUsersFromList($user_to_add, $add_lists, $delete_lists);

			echo json_encode($json);
			break;

		case "remove_user_from_list":
			$user_id = urldecode($_GET['user_id']);
			$list_id = urldecode($_GET['list_id']);

			$ovList->DeleteUserFromList($user_id, $list_id);

			$json = array('action' => 'remove_user_from_list', 'status' => 'OK');

			echo json_encode($json);
			break;

		case "delete_list":
			$list_id = urldecode($_GET['id']);

			$json = $ovList->JSONDeleteList($list_id);

			echo json_encode($json);
			break;

		case "get_user_friends":
			$ovoUser = new ovoUser(false, $ovUserSecurity->LoggedInUsername());
			$friends = $ovoUser->GetFollowing();

			if ($friends) {
				echo json_encode(array('status' => 'OK', 'friends' => $friends));
			} else {
				echo json_encode(array('status' => 'WARNING', 'errorMessage' => 'Unable to retrieve friends'));
			}
			break;

		default:
			echo "<response><action>$action</action><status><code>ERROR</code><message>Unknown Call</message></status></response>";
			break;
	}
?>
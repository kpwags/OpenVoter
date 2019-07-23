<?php
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /login?redirecturl=/manage-lists");
	exit();
}

$action = $_GET['action'];

if (isset($_GET['list'])) {
	$current_list = $_GET['list'];
} else {
	$current_list = null;
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Manage Your Lists | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
<!--<link rel="stylesheet" type="text/css" href="/js/autocomplete/jquery.autocomplete.css" />
<script type="text/javascript" src="/js/autocomplete/jquery.autocomplete.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		var friends = Array();

		$.ajax({
			url: '/php/ov-ajax.php',
			data: {
				action: 'get_user_friends'
			},
			type: 'GET',
			success: function(data) {
				var response = $.parseJSON(data);

				if (response.status == "OK") {
					
					$.each(response.friends, function(index, friend) {
						friends.push(friend.username);
					});

					$('#autocomplete-list').autocomplete({
						source: friends
					});
				}
			}
		});

		
	});
</script>-->
</head>
<body>
<?php 
	include ('category-bar-hidden.php');
	include (get_header());
?>

<?php 
if ($action == "edit") {
	
} else {
	// view user lists
?>
<h1>Manage Your Lists</h1>

<p>Lists are a feature of <?php echo $ovSettings->Title(); ?>. They allow you to create lists of fellow users so you can view all of their submissions on a single page.</p>
<p>A great way to use this feature would be if you and a bunch of others are all interested in space and astronomy, you could create a list to group them all together to make it 
	easier to see what they're sharing from a single screen.</p>
<p class="border-bottom-c9c9c9">By default, every user has one list that contains everyone they're following called friends.</p>

<div class="list-view">
<?php
	$lists = $ovList->GetUserLists();
	if ($lists && count($lists) > 0)
	{
?>
		<ul class="view-lists">
<?php
		foreach ($lists as $l)
		{
			$members = $ovList->GetMembersInList($l['id']);
			if (!$members) {
				// no members in list or error, just initialize to empty array
				$members = array();
			}
?>

			<li onclick="viewListDetails('<?php echo $l['id']; ?>')">
				<div class="list-title"><?php echo $l['name']; ?></div>
				<div class="list-member-count"><?php echo count($members); ?> Members</div>
				<div class="arrow"></div>
			</li>

<?php
		}
?>
		</ul>
<?php
	} else {
		// no lists
?>

<?php
	}
?>
</div>
<div class="list-detail-view">

<div class="margin_b_20">
	<a href="#add-new-list-form" class="normal-button fancybox-form-link">Create New List</a>
</div>

<?php
	if ($lists && count($lists) > 0)
	{
		foreach ($lists as $l)
		{
			$a = false;
			$members = $ovList->GetMembersInList($l['id']);
			if (!$members) {
				// no members in list or error, just initialize to empty array
				$members = array();
			}
?>
			<div class="list-detail" id="list-details-<?php echo $l['id']; ?>" <?php if ($current_list == $l['id']) { ?> style="display:block" <?php } ?>>
				
				<div class="error-box" style="display:none" id="list-error-<?php echo $l['id']; ?>"></div>

				<div class="list-actions">
					<a href="#edit-list-form" onclick="launchEditForm('<?php echo $l['id']; ?>', '<?php echo $l['name']; ?>', <?php if ($l['is_private']) { echo "true"; } else { echo "false"; } ?>)" class="normal-button fancybox-form-link">Edit</a>
					<a href="#confirm-delete-list-form" onclick="launchDeleteListForm('<?php echo $l['id']; ?>', '<?php echo $l['name']; ?>')" class="cancel-button fancybox-form-link">Delete</a>
				</div>
				<div class="list-name">
					<?php echo $l['name']; ?>
					<?php if ($l['is_private']) { ?>
						<img src="/<?php echo get_theme_directory(); ?>img/private-list.png" alt="" class="qtooltip-right" title="This is a Private List" />
					<?php } ?>
				</div>
				<div class="clearfix"></div>

				<div class="list-members-header">Members</div>
				<div id="listMembersArea<?php echo $l['id']; ?>">

				<?php 
					if (count($members) > 0) {
				?>
						<ul class="list-members" id="listMembers<?php echo $l['id']; ?>">
				<?php
						foreach ($members as $member) {
							$ovoListMember = new ovoUser(false, $member['username']);
				?>
							<li id="list<?php echo $l['id']; ?>-user<?php echo $member['id']; ?>">
								<a class="remove-list-member qtooltip" onclick="RemoveUserFromList('<?php echo $member['id']; ?>', '<?php echo $l['id']; ?>')" title="Remove <?php echo htmlspecialchars($member['username']); ?> from list"></a>
								<a href="/users/<?php echo strtolower($member['username']); ?>" class="user-link user-box-link" box-id="list-<?php echo $l['id']; ?>-box-<?php echo $member['id']; ?>">
									<img src="<?php echo $member['avatar']; ?>" alt="" /><?php echo $member['username']; ?>
								</a>
								<div class="user-box" id="list-<?php echo $l['id']; ?>-box-<?php echo $member['id']; ?>">
									<div class="user-box-avatar">
										<img src="<?php echo $ovoListMember->Avatar(); ?>" alt="" />
									</div>

									<div class="user-box-details">
										<div class="user-box-username"><?php echo $ovoListMember->Username(); ?></div>
										<div class="user-box-karma"><?php echo $ovoListMember->KarmaPoints(); ?> <?php echo $ovSettings->KarmaName(); ?></div>
									</div>
									<div class="clearfix"></div>
								</div>
							</li>
				<?php
						}
				?>
						</ul>
				<?php
					} else {
						// no members
				?>
						<p class="no-members">No Members</p>
				<?php
					}
				?>
				</div>
			</div>
<?php
		}
?>
		</ul>
<?php
	} else {
		// no lists
?>
	<div class="margin_tb_10">You don't have any lists...why not create one?</div>
<?php
	}
?>
</div>
<div class="clearfix"></div>
<?php
	// end all lists
}
?>

<!-- ADD NEW LIST FORM -->
<div style="display:none">
	<div id="add-new-list-form" class="list-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Create new List</h1>
		<div class="error-box" style="display:none" id="add-list-error"></div>
		<div>
			<input type="text" id="list-name" placeholder="List Name" />
		</div>
		<div>
			<input type="checkbox" name="list-private" id="list-private" value="yes" /> <label for="list-private">Make Private</label>
		</div>
		<div>
			<button onclick="listManagementAddList()" class="normal-button">Add</button>
		</div>
	</div>
</div>

<!-- EDIT LIST FORM -->
<div style="display:none">
	<div id="edit-list-form" class="list-form">
		<a class="fancybox-close-button" onclick="closePopup()"></a>
		<h1>Edit List</h1>
		<div class="error-box" style="display:none" id="edit-list-error"></div>
		<div>
			<input type="text" id="edit-list-name" placeholder="List Name" />
		</div>
		<div>
			<input type="checkbox" name="edit-list-private" id="edit-list-private" value="yes" /> <label for="edit-list-private">Make Private</label>
		</div>
		<div>
			<input type="hidden" name="edit-list-id" id="edit-list-id" />
			<button onclick="listManagementEditList()" class="normal-button">Save Changes</button>
		</div>
	</div>
</div>

<!-- CONFIRM DELETE FORM -->
<div style="display:none">
	<div id="confirm-delete-list-form" class="delete-list-form">
		<p class="font_16">Are you sure you want to delete the list <span id="list-delete-name"></span>?</p>
		<div class="margin_t_20">
			<input type="hidden" id="list-delete-id" />
			<button onclick="deleteList()" class="normal-button">Delete List</button>
			<button onclick="$.fancybox.close();" class="cancel-button">Cancel</button>
		</div>
	</div>
</div>

<?php
include (get_footer());
?>
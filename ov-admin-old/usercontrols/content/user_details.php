<?php
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
	$user_details = $ovAdminContent->GetUserDetails($content_id);

?>
<h1>User Details</h1>

<?php 
if (isset($_REQUEST['passwordreset'])) {
	if ($_REQUEST['passwordreset'] == 'yes') {
?>
		<div class="margin_tb_15 success_text">Password Reset</div>
<?php
	} else {
?>
		<div class="margin_tb_15 error_text">An error occurred</div>
<?php
	}
} 
?>

<?php if ($user_details && is_array($user_details)) { ?>
	<div class="report-details">
		<div class="report-user-avatar">
			<img src="<?php echo $user_details['avatar']; ?>" alt="" width="70"/>
		</div>
		<div class="report-user-details">
			<div class="report-user-username"><a href="/users/<?php echo strtolower($user_details['username']); ?>" target="_blank"><?php echo $user_details['username']; ?></a></div>
			<div><strong>Email:</strong> <?php echo $user_details['email']; ?></div>
			<div><strong>Website:</strong> <a href="<?php echo strtolower($user_details['website']); ?>" target="_blank"><?php echo $user_details['website']; ?></a></div>
			<div><strong>Location:</strong> <?php echo $user_details['location']; ?></div>
			<div><strong>Suspended:</strong> <?php echo $user_details['suspended']; ?></div>
			<?php if ($user_details['suspended'] == "Yes") { ?>
				<div><strong>Suspended Since:</strong> <?php echo $user_details['date_suspended']; ?></div>
			<?php } ?>
			<p><?php echo $user_details['details']; ?></p>
			<?php if ($user_details['ip_addresses']) { ?>
				<p>
					<strong>IP Addresses:</strong><br/>
					<?php foreach ($user_details['ip_addresses'] as $ip) { ?>
						<?php echo $ip; ?><br/>
					<?php } ?>
				</p>
			<?php } ?>
		</div>
		<div class="clearfix"></div>		
	</div>

	<h3>Actions</h3>
	<div>
		<a onclick="OpenResetPasswordForm('<?php echo $user_details['id']; ?>', '<?php echo $user_details['username']; ?>')" title="Reset Password" class="normal-button">Reset Password</a></li>
		<a href="/ov-admin/voting-record?username=<?php echo $user_details['username']; ?>&amp;type=submission&amp;direction=0&amp;start_date=<?php echo date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-7, date("Y"))); ?>" title="Voting Record" class="normal-button">Voting Record</a></li>
		<a onclick="return ConfirmAction('Are you sure you want to delete <?php echo $user_details['username']; ?>?')" href="/ov-admin/php/delete_user.php?user_id=<?php echo $user_details['id']; ?>" title="Remove" class="cancel-button">Remove</a></li>
		<?php if (!$ovAdminContent->IsUserSuspendedByID($user_details['id'])) { ?>
			<a href="/ov-admin/php/suspend_user.php?user_id=<?php echo $user_details['id']; ?>" title="Suspend User" class="cancel-button">Suspend User</a></li>
		<?php } else { ?>
			<a href="/ov-admin/php/unsuspend_user.php?user_id=<?php echo $user_details['id']; ?>" title="Unsuspend User" class="ok-button">Unsuspend User</a></li>
		<?php } ?>
		<a onclick="return ConfirmAction('Are you sure you want to ban <?php echo $user_details['username']; ?>?')" href="javascript:OpenBanUserForm('<?php echo $user_details['id']; ?>', '<?php echo $user_details['username']; ?>')" title="Ban User" class="cancel-button">Ban User</a></li>
	</div>
<?php } else { ?>
	<div class="error_text">No User Found.</div>
<?php } ?>
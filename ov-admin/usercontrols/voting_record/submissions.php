<h1>
	Submission Voting Record for <?php echo $username; ?>
	<?php echo " (" . count($record) . ")"; ?>
</h1>
<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
	<thead>
		<tr>
			<th width="180">Submission Posted By</th>
			<th width="300">Submission</th>
			<th width="100">Direction</th>
			<th width="130">Date</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($record as $vote) { ?>
			<tr>
				<td><?php echo $vote['username']; ?></td>
				<td><a href="<?php echo $vote['link']; ?>" target="_blank"><?php echo $vote['submission']; ?></a></td>
				<td>
					<?php if ($vote['direction'] == 1) { ?> 
						<span class="success_text">Up</span>
					<?php } else { ?> 
						<span class="error_text">Down</span>
					<?php }?>
				</td>
				<td><?php echo $vote['date']; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
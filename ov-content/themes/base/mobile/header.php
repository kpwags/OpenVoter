<div id="logo">
	<a href="/m/home"><img src="/<?php echo get_theme_directory(); ?>mobile/img/openvoter-logo.jpg" alt=""/></a>
</div>
<div id="header">
	<ul>
		<li><a href="#category-list" id="category-link">categories</a></li>
		<li>
			<?php if ($ovUserSecurity->IsUserLoggedIn()) { ?>
				<?php 
					$alert_count = $ovAlerting->GetAlertCount(); 
					$alert_string = "";
					if ($alert_count > 0) {
						$alert_string = " ($alert_count)";
					}
				?>
				<a href="/m/notifications">alerts<span id="alert-count"><?php echo $alert_string; ?></span></a>
			<?php } ?>
		</li>
	</ul>
</div>


<div style="display:none">
	<div id="category-list">
	<?php
		$categories = $ovContent->GetCategories();
	?>
	<?php if (isset($categories) && count($categories) > 0 && is_array($categories)) { ?>
	<ul>
		<?php foreach ($categories as $cat) { ?>
			<li onclick="navigateTo('/m/c/<?php echo $cat['url_name']; ?>')"><a href="/m/c/<?php echo $cat['url_name']; ?>"><?php echo $cat['name']; ?></a></li>
			<?php
				$parent_category = $ovContent->GetParentCategory($name);

				if (!$parent_category) {
					 $parent_category = $name;
				}
				$subcategories = $ovContent->GetCategories($cat['name']);
				if (isset($subcategories) && count($subcategories) > 0 && is_array($subcategories)) {
					foreach ($subcategories as $subcat) {
			?>
						<li class="subcategory" onclick="navigateTo('/m/c/<?php echo $cat['url_name']; ?>')"><a href="/m/c/<?php echo $subcat['url_name']; ?>"><?php echo $subcat['name']; ?></a></li>
			<?php
					}
				}
			?>
		<?php } ?>
	</ul>
	<?php } ?>
	</div>
</div>
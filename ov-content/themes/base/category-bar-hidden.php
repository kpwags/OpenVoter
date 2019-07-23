<div id="hidden-sidebar">
	<a id="hidden-sidebar-button"></a>
	<div id="slider">
		<div class="slider-content">
			<?php

				$categories = $ovContent->GetCategories();

				if ($page == "list" && $type == "category") {
					$parent_category = $ovContent->GetParentCategory($name);
					
					if (!$parent_category) {
						 $parent_category = $name;
					}
					
					$subcategories = $ovContent->GetCategories($parent_category);
				}
				else 
				{
					$parent_category = "";
				}
				?>


				<div id="hidden-category-bar">
					<ul>
						<li class="parent-category" <?php if ($name == "all") { ?>id="active-category" <?php } ?>><a href="/c/all" class="category-link">All</a></li>
						<?php if (count($categories) > 0 ) { ?>
							<?php foreach ($categories as $category) { ?>
								<li class="parent-category" <?php if ($name == $category['url_name'] || $parent_category == $category['url_name']) { ?>id="active-category" <?php } ?>>
									<a href="/c/<?php echo $category['url_name']; ?>" title="<?php echo $category['name']; ?>"  <?php if ($name == $category['url_name']) { ?>class="category-link active-category-link" <?php } else { ?>class="category-link" <?php } ?> ><?php echo $category['name']; ?></a>
								</li>
							<?php } ?>
						<?php } ?>
						<li class="parent-category"><a href="#" class="category-link">My Groups</a></li>
					</ul>
				</div>
		</div>
	</div>
</div>
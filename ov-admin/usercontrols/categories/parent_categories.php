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
?>
<h1>Categories</h1>
<?php if ($category_list && count($category_list) > 0) { ?>
	<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
		<thead>
			<tr>
				<th width="130">Name</th>
				<th width="130">URL Name</th>
				<th width="75">Subcategories</th>
				<th width="175">&nbsp;</th>
				<th width="100">&nbsp;</th>
				<th width="100">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($category_list as $category) { ?>
				<tr>
					<td><?php echo $category['name']; ?></td>
					<td><?php echo $category['url_name']; ?></td>
					<td><?php echo $category['num_subcategories']; ?></td>
					<td><img src="/ov-admin/img/icons/forward.png" alt="">&nbsp;<a href="/ov-admin/categories?category_id=<?php echo $category['id']; ?>" title="Edit Subcategories">Edit Subcategories</a></td>
					<td><img src="/ov-admin/img/icons/edit.png" alt="">&nbsp;<a href="javascript:OpenEditCategoryForm('<?php echo $category['id']; ?>', '<?php echo $category['name']; ?>', '<?php echo $category['url_name']; ?>', '<?php echo $category['sort_order']; ?>')" title="Edit">Edit</a></td>
					<td><img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a onclick="return ConfirmAction('Are you sure you want to delete this category')" href="/ov-admin/php/delete_category.php?id=<?php echo $category['id']; ?>" title="Delete">Delete</a></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="margin_tb_15">No Categories.</div>
<?php } ?>
<div class="add_row">
	<img><img src="/ov-admin/img/icons/add.png" alt=""><a href="javascript:OpenAddParentCategoryForm()" title="Add Category">Add Category</a>
</div>
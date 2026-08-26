	<!-- BEGIN PAGE CONTAINER-->
	<div class="page-content"> 
		<div class="content">  
			<div class="page-title">
				<i class="icon-custom-left"></i>
				<h3>Settings - <span class="semi-bold">Admin Users</span></h3>
			</div>
			{include file="errmsg.tpl"}
			<div class="col-md-12">
				<div class="grid simple">
					<div class="grid-title no-border">
						<h4>Admin <span class="semi-bold">Accounts</span></h4>
					</div>
					<div class="grid-body no-border">
						<div class="row m-b-10">
							<div class="col-xs-12">
								<div class="pull-left">
									<a href="index.php?m=adminusersadd" class="btn btn-success btn-cons">Add Admin User</a>
								</div>
								<div class="pull-right">
									<form method="GET" action="index.php" class="form-inline">
										<input type="hidden" name="m" value="adminusers" />
										<select name="role" class="form-control">
											<option value="">All Roles</option>
											<option value="admin" {if $role_filter == 'admin'}selected="selected"{/if}>Admin</option>
											<option value="superadmin" {if $role_filter == 'superadmin'}selected="selected"{/if}>Superadmin</option>
										</select>
										<select name="status" class="form-control">
											<option value="">All Status</option>
											<option value="active" {if $status_filter == 'active'}selected="selected"{/if}>Active</option>
											<option value="inactive" {if $status_filter == 'inactive'}selected="selected"{/if}>Inactive</option>
										</select>
										<button type="submit" class="btn btn-white btn-cons">Filter</button>
									</form>
								</div>
							</div>
						</div>
						{if $admins_total >= 1}
							<div class="s-pagination">{$paging}</div>
							<table class="table no-more-tables m-0">
								<thead>
									<tr>
										<th>ID</th>
										<th>USERNAME</th>
										<th>EMAIL</th>
										<th>ROLE</th>
										<th>LAST LOGIN</th>
										<th>STATUS</th>
										<th>ACTION</th>
									</tr>
								</thead>
								<tbody>
									{section name=i loop=$admins}
									<tr>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$admins[i].id}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$admins[i].username|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$admins[i].email|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$admins[i].role|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{if $admins[i].last_login}{$admins[i].last_login}{else}-{/if}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{if $admins[i].is_active == '1'}<span class="text-success">Active</span>{else}<span class="text-danger">Inactive</span>{/if}</td>
										<td class="action {if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">
											<div>
												<a class="btn btn-success btn-xs btn-mini" href="index.php?m=adminusersedit&AID={$admins[i].id}">EDIT</a>
												{if $admins[i].is_active == '1'}
													<a class="btn btn-warning btn-xs btn-mini" href="index.php?m=adminusers&a=deactivate&AID={$admins[i].id}" onClick="javascript:return confirm('Deactivate this admin user?');">DEACTIVATE</a>
												{else}
													<a class="btn btn-info btn-xs btn-mini" href="index.php?m=adminusers&a=activate&AID={$admins[i].id}" onClick="javascript:return confirm('Activate this admin user?');">ACTIVATE</a>
												{/if}
												<a class="btn btn-danger btn-xs btn-mini" href="index.php?m=adminusers&a=delete&AID={$admins[i].id}" onClick="javascript:return confirm('Soft-delete this admin user?');">SOFT DELETE</a>
												<a class="btn btn-danger btn-xs btn-mini" href="index.php?m=adminusers&a=harddelete&AID={$admins[i].id}" onClick="javascript:return confirm('Hard-delete this admin user? This cannot be undone.');">HARD DELETE</a>
											</div>
										</td>
									</tr>
									{/section}
								</tbody>
							</table>
							<div class="s-pagination">{$paging}</div>
						{else}
							<div class="alert alert-info"><button class="close" data-dismiss="alert"></button>No Admin Users Found</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END PAGE CONTAINER -->

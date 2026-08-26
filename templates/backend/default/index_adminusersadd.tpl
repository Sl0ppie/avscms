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
					<div class="grid-title no-border"><h4>Add <span class="semi-bold">Admin User</span></h4></div>
					<div class="grid-body no-border">
						<form class="form-no-horizontal-spacing" method="POST" action="index.php?m=adminusersadd" autocomplete="off">
							<div class="form-group"><label class="col-md-3 control-label">Username</label><div class="col-md-9"><input type="text" name="username" value="{$admin.username|escape:'html'}" class="form-control {if $err.username}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Email</label><div class="col-md-9"><input type="text" name="email" value="{$admin.email|escape:'html'}" class="form-control {if $err.email}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Password</label><div class="col-md-9"><input type="password" name="password" value="" class="form-control {if $err.password}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Confirm Password</label><div class="col-md-9"><input type="password" name="password_confirm" value="" class="form-control {if $err.password_confirm}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Role</label><div class="col-md-9"><select name="role" class="form-control"><option value="admin" {if $admin.role == 'admin'}selected="selected"{/if}>Admin</option><option value="superadmin" {if $admin.role == 'superadmin'}selected="selected"{/if}>Superadmin</option></select></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Active</label><div class="col-md-9"><div class="checkbox check-success"><input id="is_active" type="checkbox" name="is_active" value="1" {if $admin.is_active == '1'}checked="checked"{/if}><label for="is_active">Enable this account</label></div></div><div class="clearfix"></div></div>
							<div class="form-actions"><div class="pull-right"><input type="submit" name="add_admin_user" value="Add Admin" class="btn btn-success btn-cons"> <a href="index.php?m=adminusers" class="btn btn-white btn-cons">Cancel</a></div></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END PAGE CONTAINER -->

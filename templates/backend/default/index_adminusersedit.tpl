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
					<div class="grid-title no-border"><h4>Edit <span class="semi-bold">{$admin.username|escape:'html'}</span></h4></div>
					<div class="grid-body no-border">
						{if $admin}
						<form class="form-no-horizontal-spacing" method="POST" action="index.php?m=adminusersedit&AID={$admin.id}" autocomplete="off">
							<div class="form-group"><label class="col-md-3 control-label">Username</label><div class="col-md-9"><input type="text" value="{$admin.username|escape:'html'}" class="form-control" disabled="disabled"><span class="help">Username changes are managed from account settings.</span></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Email</label><div class="col-md-9"><input type="text" name="email" value="{$admin.email|escape:'html'}" class="form-control {if $err.email}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Role</label><div class="col-md-9"><select name="role" class="form-control {if $err.role}error{/if}"><option value="admin" {if $admin.role == 'admin'}selected="selected"{/if}>Admin</option><option value="superadmin" {if $admin.role == 'superadmin'}selected="selected"{/if}>Superadmin</option></select></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Active</label><div class="col-md-9"><div class="checkbox check-success"><input id="is_active" type="checkbox" name="is_active" value="1" {if $admin.is_active == '1'}checked="checked"{/if}><label for="is_active">Enable this account</label></div></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Reset Password</label><div class="col-md-9"><input type="password" name="password" value="" class="form-control {if $err.password}error{/if}"><span class="help">Leave blank to keep the current password.</span></div><div class="clearfix"></div></div>
							<div class="form-group"><label class="col-md-3 control-label">Confirm Password</label><div class="col-md-9"><input type="password" name="password_confirm" value="" class="form-control {if $err.password_confirm}error{/if}"></div><div class="clearfix"></div></div>
							<div class="form-actions"><div class="pull-right"><input type="submit" name="edit_admin_user" value="Save" class="btn btn-success btn-cons"> <a href="index.php?m=adminusers" class="btn btn-white btn-cons">Cancel</a></div></div>
						</form>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END PAGE CONTAINER -->

	<!-- BEGIN PAGE CONTAINER-->
	<div class="page-content"> 
		<div class="content">  
			<div class="page-title">
				<i class="icon-custom-left"></i>
				<h3>Settings - <span class="semi-bold">Activity Log</span></h3>
			</div>
			{include file="errmsg.tpl"}
			<div class="col-md-12">
				<div class="grid simple">
					<div class="grid-title no-border"><h4>Admin <span class="semi-bold">Activity</span></h4></div>
					<div class="grid-body no-border">
						<form method="GET" action="index.php" class="form-inline m-b-10">
							<input type="hidden" name="m" value="activitylog" />
							{if $is_superadmin}
							<select name="admin_id" class="form-control">
								<option value="0">All Admins</option>
								{section name=i loop=$admin_options}
									<option value="{$admin_options[i].id}" {if $filter_admin == $admin_options[i].id}selected="selected"{/if}>{$admin_options[i].username|escape:'html'}</option>
								{/section}
							</select>
							{/if}
							<input type="date" name="date_from" value="{$filter_date_from|escape:'html'}" class="form-control" />
							<input type="date" name="date_to" value="{$filter_date_to|escape:'html'}" class="form-control" />
							<button type="submit" class="btn btn-success btn-cons">Filter</button>
							<a href="index.php?m=activitylog" class="btn btn-white btn-cons">Reset</a>
						</form>
						{if $logs_total >= 1}
							<div class="s-pagination">{$paging}</div>
							<table class="table no-more-tables m-0">
								<thead>
									<tr><th>DATE</th><th>ADMIN</th><th>MODULE</th><th>ACTION</th><th>DETAILS</th><th>IP</th></tr>
								</thead>
								<tbody>
									{section name=i loop=$logs}
									<tr>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].created_at}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].admin_username|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].module|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].action|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].details|escape:'html'}</td>
										<td class="{if $smarty.section.i.index mod 2 == 0}grey{else}white{/if}">{$logs[i].ip_address|escape:'html'}</td>
									</tr>
									{/section}
								</tbody>
							</table>
							<div class="s-pagination">{$paging}</div>
						{else}
							<div class="alert alert-info"><button class="close" data-dismiss="alert"></button>No Activity Logs Found</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END PAGE CONTAINER -->

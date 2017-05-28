<body>

<!-- Header -->
<div id="header">
	<div class="shell">
		<!-- Logo + Top Nav -->
		<div id="top">
			<h1><a href="#">SpringTime</a></h1>
			<div id="top-navigation">
				<?php echo __('Welcome')?>, <a href="#"><strong><?php echo $userName?></strong></a>
				<span>|</span>
				<a href="#">Help</a>
				<span>|</span>
				<a href="#">Profile Settings</a>
				<span>|</span>
				<a href="<?php echo href_website('user/logout')?>">Log out</a>
			</div>
		</div>
		<!-- End Logo + Top Nav -->
		
		<!-- Main Nav -->
		<div id="navigation">
			<ul>
			    <li><a href="<?php echo href_admin('dashboard/stats')?>" class="<?php echo ($menu == 'dashboard' ? 'active' : '')?>"><span><?php echo __('Dashboard')?></span></a></li>
			    <li><a href="<?php echo href_admin('config/list_items')?>" class="<?php echo ($menu == 'config' ? 'active' : '')?>"><span><?php echo __('Config')?></span></a></li>
			    <li><a href="<?php echo href_admin('cache/list_cache')?>" class="<?php echo ($menu == 'cache' ? 'active' : '')?>"><span><?php echo __('Cache')?></span></a></li>
			    <li><a href="<?php echo href_admin('users/list_users')?>" class="<?php echo ($menu == 'users' ? 'active' : '')?>"><span><?php echo __('Users')?></span></a></li>
			</ul>
		</div>
		<!-- End Main Nav -->
	</div>
</div>
<!-- End Header -->

<!-- Container -->
<div id="container">
	<div class="shell">
	
	    <?php include(VIEW_INCLUDES_DIR . '/breadcrumbs.php');?>
		
		<?php include(VIEW_INCLUDES_DIR .'/messages.php');?>
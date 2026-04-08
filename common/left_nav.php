<nav class="navbar navbar-default navbar-static-top m-b-0">
	<div class="navbar-header"> 
		<div class="adm-logo">
			<a class="logo pull-left" href="<?php echo URL;?>">
				<b><img src="<?php echo $logoUrl;?>" alt="home" class="logo" /></b>

			</a>
		</div>
		<div class="user-profile">
			<h5 class="adm-logo-title"><?php echo defined('COMPANY_SHORT_NAME')?COMPANY_SHORT_NAME:SITE_NAME;?></h5>
		</div>
		<a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>

		<ul class="nav navbar-top-links navbar-left hidden-xs">
			<li><a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light"><i class="icon-arrow-left-circle ti-menu"></i></a></li>
		</ul>
		<div class="logout-box">
			<ul> 
				<li><a href="?<?php echo MODULE_URL;?>=profile"><i class="fa fa-user"></i> <?php echo $userData['username'];?></a> </li>
				<li><a class="" href="<?php echo URL;?>?logout=1"><i class="fa fa-power-off"></i> Logout</a></li>

			</ul>

		</div>
	</div>
</nav>
<div class="navbar-default sidebar" role="navigation">
	<div class="sidebar-nav navbar-collapse slimscrollsidebar">
		<ul class="nav adm-navbar-left">
			<li>
				<input type="text" onkeyup="manueSearch()" id="menueSearch" class="form-control">
			</li>
		</ul>
		<ul class="nav adm-navbar-left mainManue" id="side-menu">
			<li> <a href="<?php echo URL;?>" class="waves-effect"><i class="fa fa-home" data-icon="v"></i><span class="hide-menu"><?=l('dashboard')?></span></a></li>
			<?php
				$cmIDs=[];
				$mDatas=[];
				$modules=$db->selectAll('module','where isActive=1 order by sequence,parent asc');
				if(!empty($modules)){
					$general->arrayIndexChange($modules,'id');
					foreach($modules as $m){
						$cmIDs[$m['id']]=$m['id'];
						if($m['parent']==0){
							if(!isset($mDatas[$m['id']])){
								$mDatas[$m['id']]=[
									'ic'=>[],
									't'=>$m['title'],
									's'=>$m['slug'],
									'o'=>$m['sequence'],
									'c'=>[]
								];
							}
							$mDatas[$m['id']]['ic'][$m['id']]=$m['id'];
							if($m['slug']!='n'){
								$mDatas[$m['id']]['c'][$m['id']]=[
									't'=>$m['title'],
									's'=>$m['slug'],
									'o'=>0,
									'c'=>[]
								];
							}
						}
						else{
							if(!isset($mDatas[$m['parent']])){
								$p=$modules[$m['parent']];
								$mDatas[$m['parent']]=[
									'ic'=>[],
									't'=>$p['title'],
									's'=>$p['slug'],
									'o'=>$p['sequence'],
									'c'=>[]
								];
							}
							$mDatas[$m['parent']]['ic'][$m['id']]=$m['id'];
							$mDatas[$m['parent']]['c'][$m['id']]=[
								't'=>$m['title'],
								'o'=>$m['sequence'],
								's'=>$m['slug'],
							];
						}
					}
				}
				$general->arraySortByColumn($mDatas,'o');
				foreach($mDatas as $m){
					$show=false;
					foreach($m['ic'] as $i){
						if($db->modulePermission($i)){
							$show=true;
							break;
						}
					}
					if($show==true){
					?>
					<li>
						<a href="javascript:void();" class="waves-effect active">
							<i class="fa fa-user" data-icon="v"></i>
							<span class="hide-menu"><?php echo $m['t'];?><span class="fa arrow"></span></span></a>
						<ul class="nav nav-second-level">
							<?php
								$serial=1;
								foreach($m['c'] as $module_id=>$c){
									if($db->modulePermission($module_id)){
									?><li><a href="?<?php echo MODULE_URL;?>=<?php echo $c['s'];?>"><?php echo $serial++.' '.$c['t'];?></a></li><?php
									}
								}
							?>
						</ul>
					</li>    
					<?php
					}
				}
				if(GROUP_ID==SUPERADMIN_USER){
				?>
				<li> <a href="javascript:void();" class="waves-effect"><i class="fa fa-user" data-icon="v"></i> <span class="hide-menu"> Super Admin<span class="fa arrow"></span></span></a>
					<ul class="nav nav-second-level">
						<li><a href="?<?php echo MODULE_URL;?>=module">Module</a></li>
						<li><a href="?<?php echo MODULE_URL;?>=permission">Permission</a></li>
					</ul>
				</li>
				<?php
				}
			?>
		</ul>
		<ul class="bottom-menu-ul" id="menuUL">
			<?php
				foreach($mDatas as $m){
					$show=false;
					foreach($m['ic'] as $i){
						if($db->modulePermission($i)){
							$show=true;
							break;
						}
					}
					if($show==true){
						$ulLIArray[$m['t']][$c['t']]=$c['t'];
					?>
					<li>
						<a href="javascript:void();" id="<?=$m['t']?>" ><?php echo $m['t'];?></a>
						<ul class="bottom-submenu-ul">
							<?php
								$i=1;
								foreach($m['c'] as $id=>$c){
									if($db->modulePermission($id)){
									?><li>
										<a  style="margin-left: 10px;" id="<?=$m['t'].'_'.$c['s']?>"  href="?<?php echo MODULE_URL;?>=<?php echo $c['s'];?>"><?php echo $c['t'];?></a>
									</li>
									<?php
									}
								}
							?>
						</ul>
					</li>    
					<?php
					}
				}
			?>
		</ul>
	</div>
</div>
<script>
	var obj={};
	var sUL=$('#menuUL');
	sUL.css('display','none');
	$(document).ready(function(){

		var a, ul,li,i,txtValue;
		ul = document.getElementById("menuUL");
		li = ul.getElementsByTagName("li");
		for (i = 0; i < li.length; i++) {
			a = li[i].getElementsByTagName("a")[0];
			txtValue = a.textContent || a.innerText;
			obj[i]={tag:a,tex:txtValue.toUpperCase(),id:a.getAttribute('id')}

		}
		objArray=Object.entries(obj);
		//console.log(objArray);
	});
	function manueSearch(){
		var mUL=$('.mainManue');
		mUL.css('display','none');
		var sUL=$('#menuUL');
		sUL.css('display','');
		var  input = document.getElementById("menueSearch");
		var  filter = input.value.toUpperCase();
		var i;
		if(filter!=''){
			objArray=Object.entries(obj);
			filtered = objArray.filter(([value,index])=>f(index,value));
			function f(index,value){
				var v=parseInt(value);
				// console.log(Object);
				if (obj[v].tex.indexOf(filter) > -1) {
					//console.log(obj[v].tag);
					obj[v].tag.style.display = "";
					//var textToSearch = 'bedroom';
					if(obj[v].id.indexOf('_') > -1){
						var id= obj[v].id.split("_");
						var tag=document.getElementById(id[0]);
						tag.style.display = "";
					}

				} else {
					obj[v].tag.style.display = "none";
				}
			}
		}
		else{
			mUL.css('display',''); 
			sUL.css('display','none'); 
		}

	}

</script>

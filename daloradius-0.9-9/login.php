<?php 
	isset($_REQUEST['error']) ? $error = $_REQUEST['error'] : $error = "";
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<script src="library/javascript/pages_common.js" type="text/javascript"></script>
<title>daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<body onLoad="document.login.operator_user.focus()">
<?php
    include_once ("lang/main.php");
    //
?>

<div id="wrapper">
<div id="innerwrapper">

		
		<div id="sidebar">
		
		<h2><? echo $l['text']['LoginRequired'] ?></h2>
				
		<h3><? echo $l['text']['LoginPlease'] ?></h3>

				<form name="login" action="dologin.php" class="sidebar" method="post" >
					<ul class="subnav">
						<li><a href="#" >Usuario</a> </li>
						<input name="operator_user" value="administrator" type="text" tabindex=1 />
						<li><a href="#" >Clave</a> </li>
						<input name="operator_pass" value="" type="password" tabindex=2 />
						<br/>
						<li><a href="#" >Location</a> </li>
						<select name="location" tabindex=3 class="generic" >
							<option value="default">Default</option>
							<?php
								if (isset($configValues['CONFIG_LOCATIONS']) && is_array($configValues['CONFIG_LOCATIONS'])) {
							        	foreach ($configValues['CONFIG_LOCATIONS'] as $locations=>$val)
							                	echo "<option value='$locations'>$locations</option>";
								}
							?>
						</select>
						<br/><br/><br/>
						<input class="sidebutton" type="submit" value="Login" tabindex=3 />
					</ul>
				</form>
						
		</div>
		
		
		
		<div id="contentnorightbar">
		
				<h2 id="Intro"><a href="#" onclick="javascript:toggleShowDiv('helpPage')"><?php echo $l['Intro']['login.php'] ?></a></h2>
				
                                <div id="helpPage" style="display:none;visibility:visible" >				
					<?php echo $l['helpPage']['login'] ?>
				</div>
				
<?php
	 if ($error) { 
		echo $error;
		echo $l['messages']['loginerror'];
	}
?>
				


		
		</div>
		
		<div id="footer">
		
																<?php
        include 'page-footer.php';
?>

		
		</div>
		
</div>
</div>


</body>
</html>

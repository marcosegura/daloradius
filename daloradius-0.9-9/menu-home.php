
<?php
        include_once ("lang/main.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?php echo $l['header']['titles']; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/1.css" type="text/css" media="screen,projection" />

</head>
 
<body>



<div id="wrapper">
<div id="innerwrapper">
		
<?php
	$m_active = "Home";
	include_once ("include/menu/menu-items.php");
	include_once ("include/menu/home-subnav.php");
?>      

<div id="sidebar">

	<h2>Home</h2>

	<h3>Estado</h3>

	<ul class="subnav">

		<li><a href="rep-stat-server.php"><b>&raquo;</b><?php echo $l['button']['ServerStatus'] ?></a></li>
		<li><a href="rep-stat-services.php"><b>&raquo;</b><?php echo $l['button']['ServicesStatus'] ?></a></li>
		<li><a href="rep-lastconnect.php"><b>&raquo;</b><?php echo $l['button']['LastConnectionAttempts'] ?></a></li>


	





</div>


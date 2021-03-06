<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 * Description:
 *              returns user status (active, expired, disabled)
 *		as well as performs different user operations (disable user, enable user, etc)
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *
 *********************************************************************************************************
 */


/*
 * The following handles disabling the user
 */
if ((isset($_GET['userDisable'])) && (isset($_GET['username']))) {
	userDisable($_GET['username'], $_GET['divContainer']);
}



/*
 * The following handles refilling of user session for billing purposes
 */
if ((isset($_GET['refillSessionTime'])) && (isset($_GET['username'])))
	userRefillSessionTime($_GET['username'], $_GET['divContainer']);

if ((isset($_GET['refillSessionTraffic'])) && (isset($_GET['username'])))
	userRefillSessionTraffic($_GET['username'], $_GET['divContainer']);




function userDisable($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	//echo "alert('{$username}');";

	if (!is_array($username))
		$username = array($username, NULL);

	$allUsers = "";
	$allUsersSuccess = array();
	$allUsersFailure = array();

	foreach ($username as $variable=>$value) {
	
	        $user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		$sql = "SELECT Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
			" WHERE Attribute='Auth-Type' AND Value='Reject' AND Username='$user'";
		$res = $dbSocket->query($sql);
		if ($numrows = $res->numRows() <= 0) {
	
		        $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		                " VALUES (0,'$user','Auth-Type',':=','Reject')";
		        $res = $dbSocket->query($sql);
	
			array_push($allUsersSuccess, $user);
		} else {
			array_push($allUsersFailure, $user);
		}
	
	}

	if (count($allUsersSuccess) > 0) {
		$users = "";
		foreach($allUsersSuccess as $value)
			$users .= $value . ", ";

		$users = substr($users, 0, -2);
	        printqn("
	               var divContainer = document.getElementById('{$divContainer}');
	               divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> are now disabled.</div>';
	        ");
	}

	if (count($allUsersFailure) > 0) {
		$users = "";
		foreach($allUsersFailure as $value)
			$users .= $value . ", ";

		$users = substr($users, 0, -2);
	        printqn("
	               var divContainer = document.getElementById('{$divContainer}');
	               divContainer.innerHTML += '<div class=\"failure\">User(s) <b>$users</b> are already disabled.</div>';
	        ");
	}


        include '../../library/closedb.php';

}

function checkDisabled($username) {

	include 'library/opendb.php';

	$username = $dbSocket->escapeSimple($username);

        $sql = "SELECT Attribute,Value FROM ".$configValues['CONFIG_DB_TBL_RADCHECK'].
		" WHERE Attribute='Auth-Type' AND Value='Reject' AND Username='$username'";
	$res = $dbSocket->query($sql);
	if ($numrows = $res->numRows() >= 1) {
	
	        echo "<div class='failure'>
	              	Please note, the user <b>$username</b> is currently disabled.<br/>
			To enable the user, remove the Auth-Type entry set to Reject.<br/>
	              </div>";

	}

	include 'library/closedb.php';

}



function userRefillSessionTime($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	if (!is_array($username))
		$username = array($username);

	$allUsers = "";

	foreach ($username as $variable=>$value) {
	
		$user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		// we update the sessiontime value to be 0 - this will only work though
		// for accumulative type accounts. For TTF accounts we need to completely
		// delete the record.
		// to handle this - as a work-around I've modified the accessperiod sql
		// counter definition in radiusd.conf to check for records with AcctSessionTime>=1
		
		
		$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" SET AcctSessionTime=0 ".
			" WHERE Username='$user'";
		
		$res = $dbSocket->query($sql);

	}

	// take care of recording the billing action in billing_history table
	foreach ($username as $variable=>$value) {

		$user = $dbSocket->escapeSimple($value);                // clean username argument from harmful code

		$sql = "SELECT ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planName, ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTimeRefillCost, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".paymentmethod, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".cash, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardname, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardnumber, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardverification, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardtype, ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardexp ".
			" FROM ".
			$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].", ".
			$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." ".
			" WHERE ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname=".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
			" AND ".
			"(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username='".$user."')";
		$res = $dbSocket->query($sql);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$refillCost = $row['planTimeRefillCost'];

		$currDate = date('Y-m-d H:i:s');
		$currBy = $_SESSION['operator_user'];

		$sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'].
			" (id,username,planName,billAmount,billAction,billPerformer,billReason,".
			" paymentmethod,cash,creditcardname,creditcardnumber,creditcardverification,creditcardtype,creditcardexp,".
			" creationdate,creationby".
			")".
			" VALUES ".
			" (0,'$user','".$row['planName']."','".$row['planTimeRefillCost']."','Refill Session Time','daloRADIUS Web Interface','Refill Session Time','".
				$row['paymentmethod']."','".$row['cash']."','".$row['creditcardname']."','".
				$row['creditcardnumber']."','".$row['creditcardverification']."','".$row['creditcardtype']."','".$row['creditcardexp']."',".
				"'$currDate', '$currBy'".
			")";
		$res = $dbSocket->query($sql);

	}


	$users = substr($allUsers, 0, -2);
	printqn("
		var divContainer = document.getElementById('{$divContainer}');
	        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> session time has been successfully refilled and billed.</div>';
	");

	include '../../library/closedb.php';

}



function userRefillSessionTraffic($username, $divContainer) {

	include 'pages_common.php';
	include('../../library/checklogin.php');
	include '../../library/opendb.php';

	if (!is_array($username))
		$username = array($username);

	$allUsers = "";

	foreach ($username as $variable=>$value) {
	
	        $user = $dbSocket->escapeSimple($value);		// clean username argument from harmful code
		$allUsers .= $user . ", ";

		$sql = "UPDATE ".$configValues['CONFIG_DB_TBL_RADACCT'].
			" SET AcctInputOctets=0, AcctOutputOctets=0 ".
			" WHERE Username='$user'";
		$res = $dbSocket->query($sql);

	}

        // take care of recording the billing action in billing_history table
        foreach ($username as $variable=>$value) {

                $user = $dbSocket->escapeSimple($value);                // clean username argument from harmful code

                $sql = "SELECT ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planName, ".
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planTrafficRefillCost, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".paymentmethod, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".cash, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardname, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardnumber, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardverification, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardtype, ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".creditcardexp ".
                        " FROM ".
                        $configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].", ".
                        $configValues['CONFIG_DB_TBL_DALOBILLINGPLANS']." ".
                        " WHERE ".
                        "(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".planname=".$configValues['CONFIG_DB_TBL_DALOBILLINGPLANS'].".planname)".
                        " AND ".
                        "(".$configValues['CONFIG_DB_TBL_DALOUSERBILLINFO'].".username='".$user."')";
                $res = $dbSocket->query($sql);
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);

                $refillCost = $row['planTrafficRefillCost'];

                $currDate = date('Y-m-d H:i:s');
                $currBy = $_SESSION['operator_user'];

                $sql = "INSERT INTO ".$configValues['CONFIG_DB_TBL_DALOBILLINGHISTORY'].
                        " (id,username,planName,billAmount,billAction,billPerformer,billReason,".
                        " paymentmethod,cash,creditcardname,creditcardnumber,creditcardverification,creditcardtype,creditcardexp,".
                        " creationdate,creationby".
                        ")".
                        " VALUES ".
                        " (0,'$user','".$row['planName']."','".$row['planTrafficRefillCost']."','Refill Session Traffic','daloRADIUS Web Interface','Refill Session Traffic','".
                                $row['paymentmethod']."','".$row['cash']."','".$row['creditcardname']."','".
                                $row['creditcardnumber']."','".$row['creditcardverification']."','".$row['creditcardtype']."','".$row['creditcardexp']."',".
                                "'$currDate', '$currBy'".
                        ")";
                $res = $dbSocket->query($sql);
	}


	$users = substr($allUsers, 0, -2);
	printqn("
		var divContainer = document.getElementById('{$divContainer}');
	        divContainer.innerHTML += '<div class=\"success\">User(s) <b>$users</b> session traffic has been successfully refilled and billed.</div>';
	");

        include '../../library/closedb.php';

}


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
 *              this extension creates a pie chart of new users
 *
 * Authors:     Liran Tal <liran@enginx.com>
 *              Filippo Maria Del Prete <filippo.delprete@gmail.com>
 *
 *********************************************************************************************************
 */
        include('checklogin.php');

        if (isset($_GET['startdate']))
                $startdate = $_GET['startdate'];
        if (isset($_GET['enddate']))
                $enddate = $_GET['enddate'];

        include 'opendb.php';
        include 'libchart/libchart.php';

        header("Content-type: image/png");

        $chart = new VerticalChart(680,500);

        $sql = "SELECT count(*), CONCAT(MONTH(Creationdate),'-',YEAR(Creationdate)) AS Month from ".
                        $configValues['CONFIG_DB_TBL_DALOUSERINFO'].
                        " WHERE CreationDate >='$startdate' AND CreationDate <='$enddate' ".
                        " group by Month;";
        $res = $dbSocket->query($sql);

        while($row = $res->fetchRow()) {
                $chart->addPoint(new Point("$row[1]", "$row[0]"));
        }

        $chart->setTitle("Usuarios nuevos por mes");
        $chart->render();

        include 'closedb.php';

?>

<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified for Excel output (C) 2010 by Wardiyono (wynerst@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Library Member List */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';


$page_title = 'Summary Transaction Detail';
$reportView = false;

if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
      <h2><?php echo __('Summary Transaction Detail'); ?></h2>
    </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Transaction Type'); ?></div>
            <div class="divRowContent">
              <?php
                  $type_options[] = array(0,'Loan');
                  $type_options[] = array(1,'Return');
                  $type_options[] = array(2,'ALL');
              echo simbio_form_element::selectList('is_return', $type_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Membership Type'); ?></div>
            <div class="divRowContent">
            <?php
            $mtype_q = $dbs->query('SELECT member_type_id, member_type_name FROM mst_member_type');
            $mtype_options = array();
            $mtype_options[] = array('0', __('ALL'));
            while ($mtype_d = $mtype_q->fetch_row()) {
                $mtype_options[] = array($mtype_d[0], $mtype_d[1]);
            }
            echo simbio_form_element::selectList('member_type', $mtype_options);
            ?>
            </div>
        </div>
        <div class="divRow">
          <div class="divRowLabel"><?php echo __('Generation'); ?></div>
          <div class="divRowContent">
            <?php
            $generation_options[] = array(1,'ALL');
            $generation_options[] = array(2016,'2016');
            $generation_options[] = array(2017,'2017');
            echo simbio_form_element::selectList('generation', $generation_options);
            ?>
          </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Faculty'); ?></div>
            <div class="divRowContent">
            <?php
                $faculty_options[] = array(0,'ALL');
                $faculty_options[] = array(1,'Fakultas Teknologi Eksplorasi dan Produksi');
                $faculty_options[] = array(2,'Fakultas Teknologi Industri');
                $faculty_opmember_typetions[] = array(3,'Fakultas Management dan Bisnis');
                $faculty_options[] = array(4,'Fakultas Perencanaan infrastruktur');
                $faculty_options[] = array(5,'Fakultas Sains dan Komputer');
                $faculty_options[] = array(6,'Fakultas Komunikasi dan Diplomasi');
            echo simbio_form_element::selectList('faculty', $faculty_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Start Date'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('startDate', '2000-01-01'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('End Date'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('untilDate', date('Y-m-d')); ?>
            </div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="button" name="moreFilter" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    </form>
    </div>
    </fieldset>
    <!-- filter end -->
    <div class="dataListHeader" style="padding: 3px;"><span id="pagingBox"></span></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
?>
  <link href="<?php echo SWB; ?>template/core.style.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>colorbox/colorbox.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>chosen/chosen.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JWB; ?>jquery.imgareaselect/css/imgareaselect-default.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo SWB; ?>admin/<?php echo $sysconf['admin_template']['css']; ?>" rel="stylesheet" type="text/css" />
  <style type="text/css">
      body {
        background: #eee;
      }

  </style>
  <script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
<?php
    // generate dashboard content
     $start_date    = date("F j, Y");
     $isreturn = intval($_GET['is_return']);

     $table_spec = 'loan AS l
         LEFT JOIN member AS m ON l.member_id=m.member_id
         LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';

    $criteria = 'm.member_id IS NOT NULL';
    if (isset($_GET['is_return'])) {
        $criteria .= $isreturn != 2 ? ' AND l.is_return='.$isreturn : '' ;
    }

    if (isset($_GET['member_type']) AND !empty($_GET['member_type'])) {
        $mtype = intval($_GET['member_type']);
        $criteria .= ' AND m.member_type_id='.$mtype;
    // set date from TODAY
    }
    //more generation
        if (isset($_GET['generation']) AND !empty($_GET['generation']) AND $_GET['generation']!=1) {
        $generation = $dbs->escape_string(trim($_GET['generation']));
        $criteria .= ' AND m.generation=\''.$generation.'\'';
    }
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
      if($isreturn == 1){
        $criteria .= ' AND (TO_DAYS(l.return_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
      } else {
        $criteria .= ' AND (TO_DAYS(l.loan_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
      }

      $startDate  =date_create($_GET['startDate']);
      $startDate  =date_format($startDate,"F j, Y");
      $untilDate  =date_create($_GET['untilDate']);
      $untilDate  =date_format($untilDate,"F j, Y");
      $start_date = $startDate.' - '.$untilDate;
    }

    // get total ilmu_komputer
    function total_data($major,$table_spec,$criteria,$dbs){
      if($isreturn == 1){
        $sql_data = 'SELECT  COUNT(m.member_id) AS total  FROM '.$table_spec.' WHERE m.major=\''.$major.'\'  AND '.$criteria;
      } else {
        $sql_data = 'SELECT  COUNT(m.member_id) AS total  FROM '.$table_spec.' WHERE m.major=\''.$major.'\'  AND '.$criteria;
      }
      $total_sql_data = $dbs->query($sql_data);
      $total_sql_data      = $total_sql_data->fetch_object();
      $get_total_sql_data = $total_sql_data->total;
      return $get_total_sql_data;
    }


?>
<div class="contentDesc">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 s-dashboard">
              <div class="panel panel-info">
                <div class="panel-heading">
                  <h2 class="panel-title"><?php echo $start_date; ?></h2>
                </div>
                <div class="panel-body">
                    <canvas id="line-chartjs" height="319"></canvas>
                </div>
                <div class="panel-footer">
                    <div class="s-dashboard-legend" align="left">
                        <?php if ($_GET['faculty']==1 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#f2f2f2;"></i> Teknik Geofisika
                        (<?php echo total_data("Teknik Geofisika",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#459CBD;"></i> Teknik Geologi
                        (<?php echo total_data("Teknik Geologi",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#5D45BD;"></i> Teknik Perminyakan
                        (<?php echo total_data("Teknik Perminyakan",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } if ($_GET['faculty']==2 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#3949AB;"></i> Teknik Elektro
                        (<?php echo total_data("Teknik Elektro",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#27ae60;"></i> Teknik Mesin
                        (<?php echo total_data("Teknik Mesin",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#2980b9;"></i> Teknik Kimia
                        (<?php echo total_data("Teknik Kimia",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#8e44ad;"></i> Teknik Logistik
                        (<?php echo total_data("Teknik Logistik",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } if ($_GET['faculty']==4 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#2c3e50;"></i> Teknik Sipil
                        (<?php echo total_data("Teknik Sipil",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#f39c12;"></i> Teknik Lingkungan
                        (<?php echo total_data("Teknik Lingkungan",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } if ($_GET['faculty']==5 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#d35400;"></i> Ilmu Komputer
                        (<?php echo total_data("Ilmu Komputer",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#c0392b;"></i> Ilmu Kimia
                        (<?php echo total_data("Ilmu Kimia",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } if ($_GET['faculty']==3 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#C0CA33;"></i> Management
                        (<?php echo total_data("Management",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#6D4C41;"></i> Ekonomi
                        (<?php echo total_data("Ekonomi",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } if ($_GET['faculty']==6 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#039BE5;"></i> Ilmu Komunikasi
                        (<?php echo total_data("Ilmu Komunikasi",$table_spec,$criteria,$dbs);?>)</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#D81B60;"></i> Hubungan Internasional (<?php echo total_data("Hubungan Internasional",$table_spec,$criteria,$dbs);?>)</div>
                        <?php } ?>
                    </div>
                </div>
              </div>
            </div>

              </div>
            </div>
        </div>
        <div class="clearfix"></div>

    </div>
</div>
<script src="<?php echo JWB?>chartjs/Chart.min.js"></script>
<script>
$(function(){
    var lineChartData = {
        labels : ['Transaction'],
      datasets :
        [
            <?php if ($_GET['faculty']==1 OR $_GET['faculty']==0) { ?>
            {
              fillColor : "#f2f2f2",
              data : [<?php echo total_data("Teknik Geofisika",$table_spec,$criteria,$dbs);?>]
            },{
              fillColor : "#459CBD",
              data : [<?php echo total_data("Teknik Geologi",$table_spec,$criteria,$dbs);?>]
            },{
              fillColor : "#5D45BD",
              data : [<?php echo total_data("Teknik Perminyakan",$table_spec,$criteria,$dbs);?>]
            },
            <?php } if ($_GET['faculty']==2 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#3949AB",
                data : [<?php echo total_data("Teknik Elektro",$table_spec,$criteria,$dbs);?>]

            },{
                fillColor : "#27ae60",
                data : [<?php echo total_data("Teknik Mesin",$table_spec,$criteria,$dbs);?>]

            },{
                fillColor : "#2980b9",
                data : [<?php echo total_data("Teknik Kimia",$table_spec,$criteria,$dbs);?>]

            },{
                fillColor : "#8e44ad",
                data : [<?php echo total_data("Teknik Logistik",$table_spec,$criteria,$dbs);?>]

            },
            <?php } if ($_GET['faculty']==4 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#2c3e50",
                data : [<?php echo total_data("Teknik Sipil",$table_spec,$criteria,$dbs);?>]

            },{
                fillColor : "#f39c12",
                data : [<?php echo total_data("Teknik Lingkungan",$table_spec,$criteria,$dbs);?>]

            },
            <?php } if ($_GET['faculty']==5 OR $_GET['faculty']==0) { ?>
            {
                label: "Humidity",
                fillColor : "#d35400",
                data : [<?php echo total_data("Ilmu Komputer",$table_spec,$criteria,$dbs);?>]

            },{
                fillColor : "#c0392b",
                data : [<?php echo total_data("Ilmu Kimia",$table_spec,$criteria,$dbs);?>]

            },
            <?php } if ($_GET['faculty']==3 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#C0CA33",
                data : [<?php echo total_data("Management",$table_spec,$criteria,$dbs)?>]
            },{
                fillColor : "#6D4C41",
                data : [<?php echo total_data("Ekonomi",$table_spec,$criteria,$dbs);?>]
            },
            <?php } if ($_GET['faculty']==6 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#039BE5",
                data : [<?php echo total_data("Ilmu Komunikasi",$table_spec,$criteria,$dbs);?>]
            },{
                fillColor : "#D81B60",
                data : [<?php echo total_data("Hubungan Internasional",$table_spec,$criteria,$dbs);?>]
            }
            <?php } ?>
        ]
    }

    var c = $('#line-chartjs');
    var container = $(c).parent();
    var ct = c.get(0).getContext("2d");
    $(window).resize( respondCanvas );
    function respondCanvas(){
        c.attr('width', $(container).width() ); //max width
        c.attr('height', $(container).height() ); //max height
        //Call a function to redraw other content (texts, images etc)
        var myChart = new Chart(ct).Bar(lineChartData,{
            barShowStroke: false,
            barDatasetSpacing : 4,
            animation: false
        });
    }
    respondCanvas();


});

</script>
<?php } ?>

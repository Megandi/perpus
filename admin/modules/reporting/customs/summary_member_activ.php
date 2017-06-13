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


$page_title = 'Summary Member Active';
$reportView = false;

if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
      <h2><?php echo __('Summary Member List Active'); ?></h2>
    </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
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
            <div class="divRowLabel"><?php echo __('Gender'); ?></div>
            <div class="divRowContent">
            <?php
            $gender_chbox[0] = array('ALL', __('ALL'));
            $gender_chbox[1] = array('1', __('Male'));
            $gender_chbox[2] = array('0', __('Female'));
            echo simbio_form_element::radioButton('gender', $gender_chbox, 'ALL');
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Register Date From'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('startDate', '2000-01-01'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Register Date Until'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('untilDate', date('Y-m-d')); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Generation'); ?></div>
            <div class="divRowContent">
            <?php
                $generation_options[] = array(1,'ALL');
                $generation_options[] = array(16,'2016');
                $generation_options[] = array(17,'2017');
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
                $faculty_options[] = array(3,'Fakultas Management dan Bisnis');
                $faculty_options[] = array(4,'Fakultas Perencanaan infrastruktur');
                $faculty_options[] = array(5,'Fakultas Sains dan Komputer');
                $faculty_options[] = array(6,'Fakultas Komunikasi dan Diplomasi');
            echo simbio_form_element::selectList('faculty', $faculty_options);
            ?>
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
    $get_date       = '';
    $get_loan       = '';
    $get_return     = '';
    $get_extends    = '';
     $start_date    = date("F j, Y");  

    $table_spec = 'member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';
    $criteria = 'm.member_id IS NOT NULL AND TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')';
    if (isset($_GET['member_type']) AND !empty($_GET['member_type'])) {
        $mtype = intval($_GET['member_type']);
        $criteria .= ' AND m.member_type_id='.$mtype;
 // set date from TODAY
    }
    if (isset($_GET['gender']) AND $_GET['gender'] != 'ALL') {
        $gender = intval($_GET['gender']);
        $criteria .= ' AND m.gender='.$gender;
    }
    //more generation
        if (isset($_GET['generation']) AND !empty($_GET['generation']) AND $_GET['generation']!=1) {
        $generation = $dbs->escape_string(trim($_GET['generation']));
        $criteria .= ' AND m.generation=\''.$generation.'\'';    
    }
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(m.register_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
                    $startDate=date_create($_GET['startDate']);
        $startDate=date_format($startDate,"F j, Y");
        $untilDate=date_create($_GET['untilDate']);
        $untilDate=date_format($untilDate,"F j, Y");
            $start_date     = $startDate.' - '.$untilDate;
    }

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Ilmu Komputer\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;
    echo $get_ilmu_komputer;

        // get total ilmu_komputer
    $sql_total_teknik_geofisika = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Geofisika\' AND '.$criteria;
    $total_teknik_geofisika = $dbs->query($sql_total_teknik_geofisika);
    $teknik_geofisika     = $total_teknik_geofisika->fetch_object();
    $get_teknik_geofisika = $teknik_geofisika->total;

    // get total ilmu_komputer
    $sql_total_teknik_perminyakan = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Perminyakan\' AND '.$criteria;
    $total_teknik_perminyakan = $dbs->query($sql_total_teknik_perminyakan);
    $teknik_perminyakan      = $total_teknik_perminyakan->fetch_object();
    $get_teknik_perminyakan = $teknik_perminyakan->total;

    // get total ilmu_komputer
    $sql_total_teknik_elektro = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Elektro\' AND '.$criteria;
    $total_teknik_elektro = $dbs->query($sql_total_teknik_elektro);
    $teknik_elektro      = $total_teknik_elektro->fetch_object();
    $get_teknik_elektro = $teknik_elektro->total;

    // get total ilmu_komputer
    $sql_total_teknik_mesin = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Mesin\' AND '.$criteria;
    $total_teknik_mesin = $dbs->query($sql_total_teknik_mesin);
    $teknik_mesin      = $total_teknik_mesin->fetch_object();
    $get_teknik_mesin = $teknik_mesin->total;

    // get total ilmu_komputer
    $sql_total_teknik_kimia = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Kimia\' AND '.$criteria;
    $total_teknik_kimia = $dbs->query($sql_total_teknik_kimia);
    $teknik_kimia      = $total_teknik_kimia->fetch_object();
    $get_teknik_kimia = $teknik_kimia->total;

    // get total ilmu_komputer
    $sql_total_teknik_logistik = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Logistik\' AND '.$criteria;
    $total_teknik_logistik = $dbs->query($sql_total_teknik_logistik);
    $teknik_logistik      = $total_teknik_logistik->fetch_object();
    $get_teknik_logistik = $teknik_logistik->total;

    // get total ilmu_komputer
    $sql_total_teknik_sipil = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Sipil\' AND '.$criteria;
    $total_teknik_sipil = $dbs->query($sql_total_teknik_sipil);
    $teknik_sipil      = $total_teknik_sipil->fetch_object();
    $get_teknik_sipil = $teknik_sipil->total;

    // get total ilmu_komputer
    $sql_total_teknik_lingkungan = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'Teknik Lingkungan\' AND '.$criteria;
    $total_teknik_lingkungan = $dbs->query($sql_total_teknik_lingkungan);
    $teknik_lingkungan      = $total_teknik_lingkungan->fetch_object();
    $get_teknik_lingkungan = $teknik_lingkungan->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = 'SELECT  COUNT(member_id) AS total  FROM '.$table_spec.' WHERE m.major=\'"ilmu komputer"\' AND '.$criteria;
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;


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
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#f2f2f2;"></i> Teknik Geofisika</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#459CBD;"></i> Teknik Geologi</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#5D45BD;"></i> Teknik Perminyakan</div>
                        <?php } if ($_GET['faculty']==2 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#3949AB;"></i> Teknik Elektro</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#27ae60;"></i> Teknik Mesin</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#2980b9;"></i> Teknik Kimia</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#8e44ad;"></i> Teknik Logistik</div>
                        <?php } if ($_GET['faculty']==4 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#2c3e50;"></i> Teknik Sipil</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#f39c12;"></i> Teknik Lingkungan</div>
                        <?php } if ($_GET['faculty']==5 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#d35400;"></i> Ilmu Komputer</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#c0392b;"></i> Ilmu Kimia</div>
                        <?php } if ($_GET['faculty']==3 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#C0CA33;"></i> Management</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#6D4C41;"></i> Ekonomi</div>
                        <?php } if ($_GET['faculty']==6 OR $_GET['faculty']==0) { ?>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#039BE5;"></i> Komunikasi</div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#D81B60;"></i> Hubungan Internasional</div>
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
        labels : ['Member Aktif'],
      datasets : 
        [
            <?php if ($_GET['faculty']==1 OR $_GET['faculty']==0) { ?>
            {
              fillColor : "#f2f2f2",
              data : [<?php echo $get_teknik_geofisika;?>]
            },{
              fillColor : "#459CBD",
              data : [<?php echo $get_teknik_geologi;?>]
            },{
                fillColor : "#5D45BD",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },
            <?php } if ($_GET['faculty']==2 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#3949AB",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },{
                fillColor : "#27ae60",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },{
                fillColor : "#2980b9",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },{
                fillColor : "#8e44ad",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },
            <?php } if ($_GET['faculty']==4 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#2c3e50",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },{
                fillColor : "#f39c12",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },
            <?php } if ($_GET['faculty']==5 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#d35400",
                data : [<?php echo $get_ilmu_komputer;?>]
            
            },{
                fillColor : "#c0392b",
                data : [<?php echo $get_teknik_geofisika;?>]
            
            },
            <?php } if ($_GET['faculty']==3 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#C0CA33",
                data : [<?php echo $get_teknik_geofisika;?>]
            },{
                fillColor : "#6D4C41",
                data : [<?php echo $get_teknik_geofisika;?>]
            },
            <?php } if ($_GET['faculty']==6 OR $_GET['faculty']==0) { ?>
            {
                fillColor : "#039BE5",
                data : [<?php echo $get_teknik_geofisika;?>]
            },{
                fillColor : "#D81B60",
                data : [<?php echo $get_teknik_geofisika;?>]
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
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
                $generation_options[] = array(16,'2016');
                $generation_options[] = array(17,'2017');
            echo simbio_form_element::selectList('generation', $generation_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Major'); ?></div>
            <div class="divRowContent">
            <?php
                $major_options[] = array(52,'Ilmu Komputer');
                $major_options[] = array(51,'Ilmu Kimia');
            echo simbio_form_element::selectList('major', $major_options);
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
    $start_date     = '111'; // set date from TODAY

    // get total ilmu_komputer
    $sql_total_ilmu_komputer = ' SELECT 
                            COUNT(member_id) AS total
                        FROM 
                            member';
    $total_ilmu_komputer = $dbs->query($sql_total_ilmu_komputer);
    $ilmu_komputer      = $total_ilmu_komputer->fetch_object();
    $get_ilmu_komputer = $ilmu_komputer->total;

    // return transaction date
    $get_date       = $start_date;
    $get_return     = substr($get_return,0,-1);
    $get_extends    = substr($get_extends,0,-1);

    // get total summary
    $sql_total_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan';
    $total_coll = $dbs->query($sql_total_coll);
    $total      = $total_coll->fetch_object();
    $get_total  = $total->total;

    // get loan summary
    $sql_loan_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND is_return = 0';
    $total_loan         = $dbs->query($sql_loan_coll);
    $loan               = $total_loan->fetch_object();
    $get_total_loan     = $loan->total;

    // get return summary
    $sql_return_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND is_return = 1';
    $total_return         = $dbs->query($sql_return_coll);
    $return               = $total_return->fetch_object();
    $get_total_return     = $return->total;

    // get extends summary
    $sql_extends_coll = ' SELECT 
                            COUNT(loan_id) AS total
                        FROM 
                            loan
                        WHERE
                            is_lent = 1
                            AND renewed = 1
                            AND is_return = 0';
    $total_extends         = $dbs->query($sql_extends_coll);
    $renew                 = $total_extends->fetch_object();
    $get_total_extends     = $renew->total;

    // get overdue
    $sql_overdue_coll = ' SELECT 
                            COUNT(fines_id) AS total
                        FROM 
                            fines';
    $total_overdue         = $dbs->query($sql_overdue_coll);
    $overdue               = $total_overdue->fetch_object();
    $get_total_overdue     = $overdue->total;

    // get titles
    $sql_title_coll = ' SELECT 
                            COUNT(biblio_id) AS total
                        FROM 
                            biblio';
    $total_title         = $dbs->query($sql_title_coll);
    $title               = $total_title->fetch_object();
    $get_total_title     = number_format($title->total,0,'.',',');

    // get item
    $sql_item_coll = ' SELECT 
                            COUNT(item_id) AS total
                        FROM 
                            item';
    $total_item          = $dbs->query($sql_item_coll);
    $item                = $total_item->fetch_object();
    $get_total_item      = number_format($item->total,0,'.',',');
    $get_total_available = $item->total - $get_total_loan;
    $get_total_available = number_format($get_total_available,0,'.',',');
?>
<div class="contentDesc">    
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 s-dashboard">
              <div class="panel panel-info">
                <div class="panel-heading">
                  <h2 class="panel-title"><?php echo __('Latest Transactions') ?></h2>
                </div>
                <div class="panel-body">
                    <canvas id="line-chartjs" height="319"></canvas>            
                </div>
                <div class="panel-footer">
                    <div class="s-dashboard-legend" align="left">
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#f2f2f2;"></i> <?php echo __('Ilmu Komputer') ?></div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#459CBD;"></i> <?php echo __('Ilmu Kimia') ?></div>
                        <div style="display: inline-block; margin-right: 10px;"><i class="fa fa-square" style="color:#5D45BD;"></i> <?php echo __('T. Geofisika') ?></div>
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
      labels : [<?php echo $get_date?>],
      datasets : 
        [
            {
              fillColor : "#f2f2f2",
              data : [<?php echo $get_ilmu_komputer?>]
            },{
              fillColor : "#459CBD",
              data : [5]
            },{
                fillColor : "#5D45BD",
                data : [9]
            }
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
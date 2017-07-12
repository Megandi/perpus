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

require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Fines Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}
if ($_GET['reportViewlist']==true) {
    if (isset($_GET['date'])) {
      $date = $_GET['date'];

      ob_start();
      // table spec
      $table_spec = 'fines AS f
          LEFT JOIN member AS m
          ON f.member_id=m.member_id
          LEFT JOIN loan AS l
          ON f.loan_id=l.loan_id
          LEFT JOIN item AS i
          ON l.item_code=i.item_code
          LEFT JOIN biblio AS b on i.biblio_id=b.biblio_id';

      // create datagrid
      $reportgrid = new report_datagrid();
      $reportgrid->setSQLColumn('m.generation AS \''._('Generation').'\'','m.major AS \''._('Major').'\'',
          'm.member_name AS \''._('Member Name').'\'','b.title AS \''.('Item').'\'' ,'f.debet AS \''.('fines').'\'','l.loan_date AS \''._('Loan date').'\'');
      $reportgrid->setGroupBy('fines_date');
      // is there any search
      $criteria = 'f.fines_date=\''.$date.'\'';
      $reportgrid->setSQLorder('m.member_name ASC');
      $reportgrid->setSQLCriteria($criteria);
      echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);
    }
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
    exit();
}
if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
      <h2><?php echo __('Fines Report List'); ?></h2>
    </div>
    <div class="infoBox" >
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Date From'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('startDate', '2000-01-01'); ?>
            </div>
            <div class="divRowLabel"><?php echo __('Date Until'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::dateField('untilDate', date('Y-m-d')); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Record each page'); ?></div>
            <div class="divRowContent"><input type="text" name="recsEachPage" size="5" maxlength="5" value="<?php echo $num_recs_show; ?>" /> <?php echo __('Set between 20 and 90000'); ?></div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="button" name="moreFilter" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    <div style=" float: right; margin-top: 12px; width: calc(100% + 10px);"  >
    <div class="infoBox" style="">
    <?php echo __('Report Sort By'); ?>
    </div>
    <div class="sub_section">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowContent">
            <?php
            $tipe_options[] = array('debet', __('Total'));
            $tipe_options[] = array('fines_date', __('Fines Date'));
            $by_options[] = array('ASC', __('ASC'));
            $by_options[] = array('DESC', __('DESC'));
            echo simbio_form_element::selectList('tipe', $tipe_options);
            echo simbio_form_element::selectList('by', $by_options);

            ?>
            </div>
            <div style="padding-top: 10px; clear: both;">
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Sort by'); ?>" />
    </div>
        </div>
    </div>
    </div>
    </form>
	</div>
    </fieldset>
    <!-- filter end -->
    <div class="dataListHeader" style="padding: 3px;"><span id="pagingBox"></span></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
     <iframe name="reportViewlist" id="reportViewlist" src="<?php echo $_SERVER['PHP_SELF'].'?reportViewlist=true'; ?>" frameborder="0" style="width: 100%; height: 300px;padding:10px;"></iframe>
     <?php
    } else {

    ob_start();
    // table spec
    $table_spec = 'fines';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('fines_date AS \''.__('Fines Date').'\'','fines_date AS \''.__('Fines Date').'\'','SUM(debet) AS \''.__('Total').'\'');

    // is there any search
    $criteria = 'loan_id IS NOT NULL';
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(fines_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 90000)?$recsEachPage:$num_recs_show;
    }
     // sort bay
    if (isset($_GET['by']) AND !empty($_GET['by']) AND isset($_GET['tipe']) AND !empty($_GET['tipe']) ) {
        $sort_by = $dbs->escape_string(trim($_GET['by']));
        $sort_tipe = $dbs->escape_string(trim($_GET['tipe']));
        $criteria .= ' GROUP BY fines_date ';
        $reportgrid->setSQLorder($sort_tipe.' '.$sort_by);
    }
    else
    {
        $criteria .= ' GROUP BY fines_date ';
        $reportgrid->setSQLorder('fines_date ASC');
    }
    $reportgrid->setSQLCriteria($criteria);

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show,'', true);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';
	$xlsquery = 'SELECT m.member_id AS \''.__('Member ID').'\''.
        ', m.member_name AS \''.__('Member Name').'\''.
        ', mt.member_type_name AS \''.__('Membership Type').'\' FROM '.$table_spec.' WHERE '.$criteria;

	unset($_SESSION['xlsdata']);
	$_SESSION['xlsquery'] = $xlsquery;
	$_SESSION['tblout'] = "member_list";

	echo '<a href="../xlsoutput.php" class="button">'.__('Export to spreadsheet format').'</a>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}

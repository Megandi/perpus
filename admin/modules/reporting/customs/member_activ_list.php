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

$page_title = 'Members Report Active';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <fieldset>
    <div class="per_title">
      <h2><?php echo __('Member List Active'); ?></h2>
    </div>
    <div class="infoBox" >
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
            <div class="divRowLabel"><?php echo __('Member ID').'/'.__('Member Name'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'id_name', '', 'style="width: 50%"'); ?>
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
            <div class="divRowLabel"><?php echo __('Address'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'address', '', 'style="width: 50%"'); ?>
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
                $generation_options[] = array(2016,'2016');
                $generation_options[] = array(2017,'2017');
                $generation_options[] = array(2018,'2018');
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
               <div class="divRow">
            <div class="divRowLabel"><?php echo __('Major'); ?></div>
            <div class="divRowContent">
            <?php
                $major_options[] = array(1,'ALL');
                $major_options[] = array('Teknik Geofisika','Teknik Geofisika');
                $major_options[] = array('Teknik Geologi','Teknik Geologi');
                $major_options[] = array('Teknik Perminyakan','Teknik Perminyakan');
                $major_options[] = array('Teknik Elektro','Teknik Elektro');
                $major_options[] = array('Teknik Mesin','Teknik Mesin');
                $major_options[] = array('Teknik Kimia','Teknik Kimia');
                $major_options[] = array('Teknik Logistik','Teknik Logistik');
                $major_options[] = array('Teknik Sipil','Teknik Sipil');
                $major_options[] = array('Teknik Lingkungan','Teknik Lingkungan');
                $major_options[] = array('Ilmu Komputer','Ilmu Komputer');
                $major_options[] = array('Ilmu Kimia','Ilmu Kimia');
                $major_options[] = array('Management','Management');
                $major_options[] = array('Ekonomi','Ekonomi');
                $major_options[] = array('Ilmu Komunikasi','Ilmu Komunikasi');
                $major_options[] = array('Hubungan Internasional','Hubungan Internasional');
            echo simbio_form_element::selectList('major', $major_options);
            ?>
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
            $tipe_options[] = array('member_id', __('Member ID'));
            $tipe_options[] = array('member_since_date', __('Member Since'));
            $tipe_options[] = array('major', __('Major'));
            $tipe_options[] = array('generation', __('Generation'));
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
<?php
} else {

    ob_start();
    // table spec
    $table_spec = 'member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('m.generation AS \''.__('Generation').'\'','m.major AS \''.__('Major').'\'','m.member_id AS \''.__('Member ID').'\'',
        'm.member_name AS \''.__('Member Name').'\'',
        'mt.member_type_name AS \''.__('Membership Type').'\'',
        'm.member_since_date AS \''.__('Membership Since').'\'');

    // is there any search
    $criteria = 'm.member_id IS NOT NULL AND TO_DAYS(expire_date)>TO_DAYS(\''.date('Y-m-d').'\')';
    if (isset($_GET['member_type']) AND !empty($_GET['member_type'])) {
        $mtype = intval($_GET['member_type']);
        $criteria .= ' AND m.member_type_id='.$mtype;
    }
    if (isset($_GET['id_name']) AND !empty($_GET['id_name'])) {
        $id_name = $dbs->escape_string($_GET['id_name']);
        $criteria .= ' AND (m.member_id LIKE \'%'.$id_name.'%\' OR m.member_name LIKE \'%'.$id_name.'%\')';
    }
    if (isset($_GET['gender']) AND $_GET['gender'] != 'ALL') {
        $gender = intval($_GET['gender']);
        $criteria .= ' AND m.gender='.$gender;
    }
    if (isset($_GET['address']) AND !empty($_GET['address'])) {
        $address = $dbs->escape_string(trim($_GET['address']));
        $criteria .= ' AND m.member_address LIKE \'%'.$address.'%\'';
    }
    //more generation
        if (isset($_GET['generation']) AND !empty($_GET['generation']) AND $_GET['generation']!=1) {
        $generation = $dbs->escape_string(trim($_GET['generation']));
        $criteria .= ' AND m.generation=\''.$generation.'\'';
    }
    //more generation
        if (isset($_GET['major']) AND !empty($_GET['major']) AND $_GET['major']!=1) {
        $major = $dbs->escape_string(trim($_GET['major']));
        $criteria .= ' AND m.major=\''.$major.'\'';
    }
    //more generation
        if (isset($_GET['faculty']) AND !empty($_GET['faculty']) AND $_GET['faculty']!=0) {
        $faculty = $dbs->escape_string(trim($_GET['faculty']));
        $criteria .= ' AND substr(m.member_id,3,1)=\''.$faculty.'\'';
    }
    // register date
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(m.register_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
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
        $reportgrid->setSQLorder('m.'.$sort_tipe.' '.$sort_by);
    }
    else
    {
        $reportgrid->setSQLorder('m.member_name ASC');
    }
    $reportgrid->setSQLCriteria($criteria);

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';
	   $xlsquery = 'SELECT m.generation AS \''.__('Generation').'\'','m.major AS \''.__('Major').'\'','m.member_id AS \''.__('Member ID').'\'',
      'm.member_name AS \''.__('Member Name').'\'',
      'mt.member_type_name AS \''.__('Membership Type').'\'',
      'm.member_since_date AS \''.__('Membership Since').'\' FROM '.$table_spec.' WHERE '.$criteria;



	unset($_SESSION['xlsdata']);
	$_SESSION['xlsquery'] = $xlsquery;
	$_SESSION['tblout'] = "member_list";

	echo '<a href="../xlsoutput.php" class="button">'.__('Export to spreadsheet format').'</a>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}

<?php
/**
 * @Author: ido
 * @Date:   2016-06-16 21:34:22
 * @Modified by:   ido, wardiyono
 * @Modified time: 2016-06-16 21:40:48
 * @Last Modified time: 2016-09-10 14:24
 */

/* P2P/Copy Cataloging Server Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if (isset($_POST['saveData']) AND $can_read AND $can_write) {
  $days = trim(strip_tags($_POST['days']));
  $price = trim(strip_tags($_POST['price']));
  // check form validity
    if (empty($days) OR empty($price)) {
      utility::jsAlert(__('Days And Price can\'t be empty'));
      exit();
    } else {
      $data['days'] = $dbs->escape_string($days);
      $data['price'] = $dbs->escape_string($price);
      $data['last_update'] = date('Y-m-d H:i:s');

      // create sql op object
      $sql_op = new simbio_dbop($dbs);
      if (isset($_POST['updateRecordID'])) {
        // remove input date
        unset($data['input_date']);
        // filter update record ID
        $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
        // update the data
        $update = $sql_op->update('mst_fine', $data, 'fine_id='.$updateRecordID);
        if ($update) {
            utility::jsAlert(__('Fine Management Successfully Updated'));
            echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
        } else { utility::jsAlert(__('Fine Management FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
        exit();
      } else {
        // insert the data
        if ($sql_op->insert('mst_fine', $data)) {
            utility::jsAlert(__('New Fine Management Successfully Saved'));
            echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
        } else { utility::jsAlert(__('Fine Management FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
        exit();
      }
    }
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
  if (!($can_read AND $can_write)) {
    die();
  }
}

?>
<fieldset class="menuBox">
<div class="menuBoxInner masterFileIcon">
  <div class="per_title">
      <h2><?php echo __('Fine Management'); ?></h2>
  </div>
  <div class="sub_section">
    <div class="btn-group">

    </div>
  </div>
</div>
</fieldset>
<?php
  if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
  }

  $itemID = '1';
  $rec_q = $dbs->query('SELECT * FROM mst_fine WHERE fine_id='.$itemID);
  $rec_d = $rec_q->fetch_assoc();

  // create new instance
  $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
  $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="button"';

  // form table attributes
  $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
  $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
  $form->table_content_attr = 'class="alterCell2"';

  // edit mode flag set
    $form->edit_mode = true;
    // record ID for delete process
    $form->record_id = '1';
    // form record title
    $form->record_title = $rec_d['days'];
    // submit button attribute
    $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="button"';


  /* Form Element(s) */
  // Server name
  $form->addTextField('text', 'days', __('Days'), $rec_d['days'], 'style="width: 50%;" maxlength="255"');
  // Server URI
  $form->addTextField('text', 'price', __('Price'), $rec_d['price'], 'style="width: 50%;"');

  // edit mode messagge
  if ($form->edit_mode) {
      echo '<div class="infoBox">'.__('Last Update ').$rec_d['last_update'].'</div>'; //mfc
  }
  // print out the form object
  echo $form->printOut();

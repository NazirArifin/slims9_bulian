<?php
/**
 * @Created by        : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Date-Time         : 2020-04-11 10:04:00
 * @Filename          : index.php
 */

use SLiMS\Plugins;
use SLiMS\Filesystems\Storage;

defined('INDEX_AUTH') OR die('Direct Access Not Allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privilege check
$can_read = utility::havePrivilege('membership', 'r');

if (! $can_read) {
  die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// execute registered hook
Plugins::getInstance()->execute(Plugins::MEMBERSHIP_INIT);

function showMemberImage($obj_db, $array_data){
  global $sysconf;
  $imageDisk = Storage::images();
  $image = 'images/persons/photo.png';
  $_q = $obj_db->query('SELECT member_image,member_name,member_address,member_phone FROM member WHERE member_id = "'.$array_data[0].'"');
  if(isset($_q->num_rows)){
    $_d = $_q->fetch_row();
    if($_d[0] != NULL){     
      $image = $imageDisk->isExists('persons/'.$_d[0])?'images/persons/'.$_d[0]:'images/persons/photo.png';
    }
    $addr  = $_d[2]!=''?'<i class="fa fa-map-marker" aria-hidden="true"></i></i>&nbsp;'.$_d[2]:'';
    $phone = $_d[3]!=''?'<i class="fa fa-phone" aria-hidden="true"></i>&nbsp;'.$_d[3]:'';
  }

  $imageUrl = SWB . 'lib/minigalnano/createthumb.php?filename=' . $image . '&width=120';
  $_output = '<div class="media"> 
              <a href="'.$imageUrl.'" class="openPopUp notAJAX" title="'.$_d[1].'" width="300" height="400" >
              <img class="mr-3 rounded" src="'.$imageUrl.'" alt="cover image" width="60"></a>
              <div class="media-body">
                <div class="title">'.$array_data[2].'</div>
                <div class="sub">'.$phone.'</div>
                <div class="sub">'.$addr.'</div>
              </div>
            </div>';
  return $_output;
}

function showPrintButton($obj_db, $array_data) {
  return '<a href="https://api.unira.ac.id/print/bebaspustaka?member_id=' . $array_data[0] . '" class="btn btn-default btn-sm" title="' . __('Print') . '"><i class="fa fa-print"></i></a>';
}

function httpQuery($query = []) {
  return http_build_query(array_unique(array_merge($_GET, $query)));
}

// add new free loan
if (isset($_GET['do']) && $_GET['do'] == 'add') {
  

  ?>
    <div class="menuBox">
      <div class="menuBoxInner memberIcon">
        <div class="per_title">
          <h2><?php echo __('Bebas Pustaka') ?></h2>
        </div>
        <div class="sub_section">
          <div class="btn-group">
            <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery(['do' => '']) ?>" class="btn btn-default"><?php echo __('Cancel') ?></a>
          </div>
        </div>
      </div>
    </div>
  <?php
  $form = new simbio_form_table_AJAX('flForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
  $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';

  // form table attributes
  $form->table_attr = 'id="dataList" class="s-table table"';
  $form->table_header_attr = 'class="alterCell font-weight-bold"';
  $form->table_content_attr = 'class="alterCell2"';

  // member code
  $str_input  = '<div class="container-fluid">';
  $str_input .= '<div class="row">';
  $str_input .= simbio_form_element::textField('text', 'memberID', $rec_d['member_id']??'', 'id="memberID" onblur="ajaxCheckID(\''.SWB.'admin/AJAX_check_id.php\', \'member\', \'member_id\', \'msgBox\', \'memberID\')" class="form-control col-4"');
  $str_input .= '<div id="msgBox" class="col mt-2"></div>';
  $str_input .= '</div>';
  $str_input .= '</div>';
  $form->addAnything(__('NIM Anggota').'*', $str_input);

  echo $form->printOut();
  return;
}

/* search form */
?>

  <div class="menuBox">
    <div class="menuBoxInner memberIcon">
      <div class="per_title">
        <h2><?php echo __('Bebas Pustaka') ?></h2>
      </div>
      <div class="sub_section">
        <div class="btn-group">
          <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery(['do' => 'add']) ?>" class="btn btn-default"><?php echo __('Add New') ?></a>
        </div>
        <form name="search" action="<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get" class="form-inline">Cari Member 
          <input type="text" name="keywords" value="" class="form-control col-md-3" placeholder="NIM / Nama Anggota">
          <input type="submit" id="doSearch" value="<?php echo __('Search') ?>" class="s-btn btn btn-default">
        </form>
      </div>
    </div>
  </div>
<?php
/* main content */
$table_spec = 'free_loan as fl LEFT JOIN member AS m ON fl.member_id = m.member_id LEFT JOIN mst_member_type AS mt ON m.member_type_id = mt.member_type_id';
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn(
  'm.member_id AS \'' . __('Member ID') . '\'',
  'm.member_name AS \'' . __('Member Name') . '\'',
  'mt.member_type_name AS \'' . __('Membership Type') . '\'',
  '1 AS \'' . __('Action') . '\''
);
// $datagrid->modifyColumnContent(1, 'callback{showMemberImage}');
// $datagrid->modifyColumnContent(3, 'callback{showPrintButton}');
// $datagrid->setSQLorder('m.member_id');

// is there any search
$criteria = 'fl.member_id IS NOT NULL ';
if (isset($_GET['keywords']) AND $_GET['keywords'] != '') {
  $keywords = $dbs->escape_string($_GET['keywords']);
  $criteria .= ' AND (m.member_id LIKE \'%' . $keywords . '%\' OR m.member_name LIKE \'%' . $keywords . '%\')';
}
$datagrid->setSQLCriteria($criteria);

// set table and table header attributes
$datagrid->table_name = 'memberList';
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 7, false);

if (isset($_GET['keywords']) AND $_GET['keywords'] != '') {
  echo '<div class="infoBox">';
  echo __('Found') . ' ' . $datagrid->num_rows . ' ' . __('from your search with keyword') . ' <strong>' . htmlentities($_GET['keywords']) . '</strong>';
  echo '</div>';
}

echo $datagrid_result;
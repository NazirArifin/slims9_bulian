<?php
/**
 * @Created by        : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Date-Time         : 2023-08-15 10:04:00
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

// function showMemberImage($obj_db, $array_data){
//   global $sysconf;
//   $imageDisk = Storage::images();
//   $image = 'images/persons/photo.png';
//   $_q = $obj_db->query('SELECT member_image,member_name,member_address,member_phone FROM member WHERE member_id = "'.$array_data[0].'"');
//   if(isset($_q->num_rows)){
//     $_d = $_q->fetch_row();
//     if($_d[0] != NULL){     
//       $image = $imageDisk->isExists('persons/'.$_d[0])?'images/persons/'.$_d[0]:'images/persons/photo.png';
//     }
//     $addr  = $_d[2]!=''?'<i class="fa fa-map-marker" aria-hidden="true"></i></i>&nbsp;'.$_d[2]:'';
//     $phone = $_d[3]!=''?'<i class="fa fa-phone" aria-hidden="true"></i>&nbsp;'.$_d[3]:'';
//   }

//   $imageUrl = SWB . 'lib/minigalnano/createthumb.php?filename=' . $image . '&width=120';
//   $_output = '<div class="media"> 
//               <a href="'.$imageUrl.'" class="openPopUp notAJAX" title="'.$_d[1].'" width="300" height="400" >
//               <img class="mr-3 rounded" src="'.$imageUrl.'" alt="cover image" width="60"></a>
//               <div class="media-body">
//                 <div class="title">'.$array_data[2].'</div>
//                 <div class="sub">'.$phone.'</div>
//                 <div class="sub">'.$addr.'</div>
//               </div>
//             </div>';
//   return $_output;
// }

/**
 * Generates a print button link for the Bebas Pustaka plugin.
 *
 * @param object $obj_db The database object.
 * @param array $array_data An array containing the member ID.
 * @return string The HTML code for the print button link.
 */
function showPrintButton($obj_db, $array_data) {
  $data = [
    'reason' => $array_data[3],
    'created' => date('d-m-Y', strtotime($array_data[4]))
  ];

  return '<a href="https://api.unira.ac.id/print/bebaspustaka/' . $array_data[0] . '/' . $array_data[5] . '?' . http_build_query($data) . '" class="btn btn-default btn-sm" title="' . __('Print') . '" target="_blank"><i class="fa fa-print"></i></a>';
}


/**
 * Build a query string for HTTP requests by merging the given query parameters with the current $_GET parameters.
 *
 * @param array $query An array of query parameters to merge with the current $_GET parameters.
 * @return string The resulting query string.
 */
function httpQuery($query = []) {
  return http_build_query(array_unique(array_merge($_GET, $query)));
}

// save data --------------------------------------------------------------------------
if (isset($_POST['saveData']) && $can_read) {
  $memberID = $dbs->escape_string($_POST['memberID']);
  $academicYear = $dbs->escape_string($_POST['academicYear']);
  $reason = $dbs->escape_string($_POST['reason']);
  
  $anyError = false;
  if ($memberID == '') {
    toastr(__('Member ID is required'))->error();
    $anyError = true;
  }
  if ($academicYear == '') {
    toastr(__('Academic Year is required'))->error();
    $anyError = true;
  }
  if ($reason == '') {
    toastr(__('Reason is required'))->error();
    $anyError = true;
  }
  if ($anyError) die();

  // cari di loan apakah ada yang is_return = 0
  $sql_string = 'SELECT loan_id FROM loan WHERE member_id = "' . $memberID . '" AND is_return = 0 LIMIT 1';
  $result = $dbs->query($sql_string);
  if ($result->num_rows > 0) {
    toastr(__('Member still has an active loan'))->error();
    die();
  }

  // cari di skripsi hanya jika checkingOptions contains skripsi
  if (isset($_POST['checkingOptions']) && in_array('skripsi', $_POST['checkingOptions'])) {
    $sql_string = 'SELECT id, is_valid FROM skripsi WHERE member_id = "' . $memberID . '" LIMIT 1';
    $result = $dbs->query($sql_string);
    // jika tidak ada skripsi yang diupload, maka tidak perlu dicek
    if ($result->num_rows == 0) {
      toastr(__('Member has not uploaded any skripsi'))->error();
      die();
    }
    $row = $result->fetch_assoc();
    // jika ada skripsi yang diupload, maka cek apakah skripsi tersebut sudah divalidasi oleh admin
    if ($row['is_valid'] == 0) {
      toastr(__('Skripsi has not been validated'))->error();
      die();
    }
  }
  
  $sql_string = 'INSERT INTO free_loan (member_id, academic_year, reason, created_at) VALUES ("' . $memberID . '", "' . $academicYear . '", "' . $reason . '", "' . date('Y-m-d H:i:s') . '")';
  $dbs->query($sql_string);
  $error = $dbs->error;
  if ($error) {
    die('SQL ERROR : ' . $error);
  }
  toastr(__('Data saved'))->success();
  // change content of #mainContent from $_SERVER['PHP_SELF'] . '?' . httpQuery(['do' => 'add']) to $_SERVER['PHP_SELF'] . '?' . httpQuery()
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '?' . httpQuery() . '\');</script>';
  die();
}

// check member id ---------------------------------------------------------------------
if (isset($_GET['do']) && $_GET['do'] == 'checkMember') {
  $sql_string = 'SELECT member_id, member_name FROM member WHERE member_id = "' . $dbs->escape_string($_GET['memberID']) . '" LIMIT 1';
  $result = $dbs->query($sql_string);
  $error = $dbs->error;
  if ($error) {
    die('SQL ERROR : ' . $error);
  }
  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo '<strong>' . $row['member_name'] . '</strong>';
  } else {
    echo '<strong style="color: #FF0000;">Anggota tidak ditemukan</strong>';
  }
  die();
}

// add new free loan ------------------------------------------------------------------------------
if (isset($_GET['do']) && $_GET['do'] == 'add') {
  $client = new GuzzleHttp\Client([
    'base_uri' => 'https://api.unira.ac.id',
    'timeout'  => 2.0,
    'verify' => false
  ]);

  try {
    $response = $client->request('GET', '/v1/thajaran?sort=-nama,-semester');
  } catch (GuzzleHttp\Exception\ConnectException $e) {
    die('<div class="errorBox">' . __('Failed to connect to API server') . '</div>');
  }

  $json = json_decode($response->getBody()->getContents(), true);
  $thAjaran = [
    '' => __('Select Academic Year')
  ];
  // find selected th ajaran by using attributes['status'], if status == 1 then selected
  $selectedThAjaran = array_filter($json['data'], function($th) {
    return $th['attributes']['status'] == 1;
  })[0]['id'] . '';
  $prefixYear = substr(date('Y'), 0, 2);
  foreach ($json['data'] as $th) {
    $thAjaran[] = [
      $th['id'] . '',
      $prefixYear . substr($th['attributes']['nama'], 0, 2) . '/' . $prefixYear . substr($th['attributes']['nama'], 2, 2) . ' - ' . ($th['attributes']['semester'] == 1 ? 'GASAL' : 'GENAP')
    ];
  }
  
  ?>
    <script>
      async function ajaxCheckMember(url, table, field, msgBox, id) {
        const memberID = document.getElementById(id).value;
        // remove do query in url
        url = url.replace(/&do=[^&]+/, '');
        const response = await fetch(url + '&do=checkMember&memberID=' + memberID);
        const data = await response.text();
        // if data contains #FF0000 then it's an error, so clear the input
        if (data.indexOf('#FF0000') > -1) {
          document.getElementById(id).value = '';
        }
        document.getElementById(msgBox).innerHTML = data;
      }
    </script>
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
  $form = new simbio_form_table_AJAX('flForm', $_SERVER['PHP_SELF'] . '?' . httpQuery(['do' => '']), 'post');
  $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';

  // form table attributes
  $form->table_attr = 'id="dataList" class="s-table table"';
  $form->table_header_attr = 'class="alterCell font-weight-bold"';
  $form->table_content_attr = 'class="alterCell2"';

  // member code
  $str_input  = '<div class="container-fluid">';
  $str_input .= '<div class="row">';
  $str_input .= simbio_form_element::textField('text', 'memberID', $rec_d['member_id']??'', 'id="memberID" onblur="ajaxCheckMember(\'' . $_SERVER['PHP_SELF'] . '?' . httpQuery() . '\', \'member\', \'member_id:member_name\', \'msgBox\', \'memberID\')" class="form-control col-4"');
  $str_input .= '<div id="msgBox" class="col mt-2"></div>';
  $str_input .= '</div>';
  $str_input .= '</div>';
  $form->addAnything(__('Member ID').'*', $str_input);
  // tahun akademik
  $form->addSelectList('academicYear', __('Academic Year') . '*' , $thAjaran, $selectedThAjaran, 'class="form-control col-4"');
  // alasan
  $form->addTextField('textarea', 'reason', __('Reason') . '*', '', 'class="form-control col-8"');
  // pengecekan apakah di loan atau juga di upload skripsi/tesis
  $form->addCheckBox('checkingOptions', __('Loan Checking'), [
    ['loan', __('Loan')],
    ['skripsi', __('Skripsi/Tesis')]
  ], 'loan', 'items for checking');
  
  echo $form->printOut();
  die();
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
  'fl.reason AS \'' . __('Reason') . '\'',
  'fl.created_at AS \'' . __('Created At') . '\'',
  'fl.id AS \'' . __('Action') . '\''
);
// $datagrid->modifyColumnContent(0, 'callback{showMemberImage}');
$datagrid->modifyColumnContent(5, 'callback{showPrintButton}');
$datagrid->setSQLorder('fl.created_at DESC');

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
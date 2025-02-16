<?php
/**
 * @Created by      : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Created on      : 2023-08-21 08:30:00
 * @Filename        : index.php
 */

use \Google\Client;
use \Google\Service\Drive;
use \Google\Service\Drive\DriveFile;
use \Google\Service\Drive\Permission;
use SLiMS\Plugins;

defined('INDEX_AUTH') OR die('Direct Access Not Allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

require_once 'helper.php';

// privilege check
$can_read = utility::havePrivilege('membership', 'r');

if (! $can_read) {
  die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// execute registered hook
Plugins::getInstance()->execute(Plugins::MEMBERSHIP_INIT);

function httpQuery($query = []) {
  return http_build_query(array_unique(array_merge($_GET, $query)));
}

// upload skripsi to drive
if (isset($_GET['do']) && $_GET['do'] == 'backup') {
  $result = $dbs->query('SELECT * FROM skripsi WHERE is_valid = 1');
  $skripsi = [];
  while ($row = $result->fetch_assoc()) {
    $skripsi[] = $row['file'];
  }

  // create google service
  try {
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/config/credentials.json');
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Drive::DRIVE);
    $service = new Drive($client);

    // get folder id
    $files = [];
    $pagetoken = null;
    do {
      $response = $service->files->listFiles([
        'q' => 'name = \'Skripsi\' and mimeType = \'application/vnd.google-apps.folder\'',
        'pageToken' => $pagetoken,
        'spaces' => 'drive',
        'fields' => 'nextPageToken, files(id, name)',
      ]);
      $files = array_merge($files, $response->getFiles());
      $pagetoken = $response->getNextPageToken();
    } while ($pagetoken != null);
    $folder_id = $files[0]->getId();

    // upload skripsi
    foreach ($skripsi as $file) {
      $fileMetadata = new DriveFile([
        'name' => $file,
        'parents' => [$folder_id],
      ]);
      // $content = file_get_contents(__DIR__ . '/files/' . $file);
      echo __DIR__;
    }

  } catch (Exception $e) {
    echo $e->getMessage();
    utility::jsToastr(__('Gagal membuat service Google Drive'), 'Gagal membuat service Google Drive', 'error');
  }
}

// delete skripsi
if (isset($_GET['do']) && $_GET['do'] == 'delete' && isset($_GET['mid'])) {
  $dbs->query('UPDATE skripsi SET is_valid = 2 WHERE member_id = \'' . $dbs->escape_string($_GET['mid']) . '\'');
  utility::jsToastr(__('Skripsi berhasil dihapus'), 'Data skripsi berhasil dihapus', 'success');
}

// verify skripsi
if (isset($_GET['do']) && $_GET['do'] == 'verify' && isset($_GET['mid'])) {
  $dbs->query('UPDATE skripsi SET is_valid = 1 WHERE member_id = \'' . $dbs->escape_string($_GET['mid']) . '\'');
  utility::jsToastr(__('Skripsi berhasil diverifikasi'), 'Data skripsi berhasil diverifikasi', 'success');
}

?>
  <script type="text/javascript">
const current = '<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>';

function updateSkripsi(url) {
  let message = 'Apakah anda yakin akan menolak data skripsi ini?';
  if (url.indexOf('verify') > -1) {
    message = 'Apakah anda yakin akan memverifikasi skripsi ini? Pastikan bahwa file skripsi sudah sesuai dengan ketentuan dan mahasiswa sudah menyerahkan hardcopy skripsi ke perpustakaan';
  }

  if (! confirm(message)) {
    return;
  }
  parent.$('#mainContent').simbioAJAX(url);
}

function backupSkripsi() {
  if (! confirm('Apakah anda yakin akan membackup data skripsi?')) {
    return;
  }
  parent.$('#mainContent').simbioAJAX(current + '&do=backup');
}
  </script>

  <div class="menuBox">
    <div class="menuBoxInner memberIcon">
      <div class="per_title">
        <h2><?php echo __('Skripsi/Tesis') ?></h2>
      </div>
      <div class="sub_section" style="display: flex;justify-content: space-between;">
        <form name="search" action="<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get" class="form-inline">Cari Member 
          <input type="text" name="keywords" value="" style="min-width: 200px;" class="form-control col-md-3" placeholder="NIM / Nama Anggota">
          <input type="submit" id="doSearch" value="<?php echo __('Search') ?>" class="s-btn btn btn-default">
        </form>

        <div class="btn btn-info" onclick="backupSkripsi()">
          <i class="fa fa-upload"></i> Backup Skripsi
        </div>
      </div>
    </div>
  </div>
<?php

/* main content */
$table_spec = 'skripsi AS s LEFT JOIN member AS m ON s.member_id = m.member_id';
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn(
  'm.member_id AS \'' . __('Member ID') . '\'',
  'm.member_name AS \'' . __('Member Name') . '\'',
  's.is_valid AS \'' . __('Status') . '\'',
  's.file AS \'' . __('File') . '\'',
  's.is_valid AS \'' . __('Action') . '\'',
);
$datagrid->modifyColumnContent(1, 'callback{showMemberImage}');
$datagrid->modifyColumnContent(2, 'callback{translateStatusAdmin}');
$datagrid->modifyColumnContent(3, 'callback{showFile}');
$datagrid->modifyColumnContent(4, 'callback{showActionAdmin}');
$datagrid->setSQLorder('s.is_valid ASC');

// is there any search
$criteria = 's.is_valid < 2';
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

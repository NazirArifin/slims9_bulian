<?php
/**
 * @Created by      : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Created on      : 2023-08-21 08:30:00
 * @Filename        : index.php
 */

use SLiMS\Plugins;

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

// delete skripsi
if (isset($_GET['do']) && $_GET['do'] == 'delete' && isset($_GET['mid'])) {
  $dbs->query('DELETE FROM skripsi WHERE member_id = \'' . $dbs->escape_string($_GET['mid']) . '\'');
  utility::jsToastr(__('Skripsi berhasil dihapus'), 'Data skripsi berhasil dihapus', 'success');
}

?>
  <script type="text/javascript">
const current = '<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>';

function verifySkripsi(mid, status) {
  console.log(mid, status);
}

function deleteSkripsi(url) {
  // confirm delete
  const confirmDelete = confirm('Apakah anda yakin akan menghapus skripsi ini?');
  if (! confirmDelete) {
    return;
  }
  parent.$('#mainContent').simbioAJAX(url);
}
  </script>

  <div class="menuBox">
    <div class="menuBoxInner memberIcon">
      <div class="per_title">
        <h2><?php echo __('Skripsi/Tesis') ?></h2>
      </div>
      <div class="sub_section">
        <form name="search" action="<?php echo $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get" class="form-inline">Cari Member 
          <input type="text" name="keywords" value="" class="form-control col-md-3" placeholder="NIM / Nama Anggota">
          <input type="submit" id="doSearch" value="<?php echo __('Search') ?>" class="s-btn btn btn-default">
        </form>
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
  's.is_valid AS \'' . __('File') . '\'',
  's.is_valid AS \'' . __('Action') . '\'',
);
$datagrid->modifyColumnContent(2, 'callback{translateStatus}');
$datagrid->modifyColumnContent(3, 'callback{showFile}');
$datagrid->modifyColumnContent(4, 'callback{showActionAdmin}');
$datagrid->setSQLorder('s.is_valid ASC');

// is there any search
$criteria = 's.member_id IS NOT NULL';
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

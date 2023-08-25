<?php
/**
 * Plugin Name: Login Simat
 * Plugin URI:
 * Description: Login Simat
 * Version: 0.0.1
 * Author: Mohammad Nazir Arifin
 * Author URI: github.com/NazirArifin
 */

use GuzzleHttp\Client;
use SLiMS\Plugins;

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerHook('membership_init', function() {
  // this is the hook code, executed each time membership_init is called
  // because this only should be executed on user login, we need to check if the user is logging in
  if (! isset($_POST['memberID']) || ! isset($_POST['memberPassWord'])) {
    return;
  }
  
  $username = trim(strip_tags($_POST['memberID']));
  $password = trim(strip_tags($_POST['memberPassWord']));
  global $dbs;
  
  /**
   * Edited on: 2023-08-18
   * Purpose: Check user in SIMAT UNIRA. If user does not exist, then create new user.
   *          If user exist, then update user data.
   */ 
  $client = new Client([
    'base_uri' => 'https://api.unira.ac.id',
    'timeout'  => 2.0,
    'verify' => false,
  ]);

  try {
    $response = $client->post('/v1/token', [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'form_params' => [
        'username' => $username,
        'password' => $password,
        'client' => 'simbio'
      ],
    ]);
  } catch(\GuzzleHttp\Exception\RequestException $e) {
    utility::writeLogs($dbs, 'member', $username, 'Login', sprintf(__('Login FAILED for member %s from address %s'),$username,ip()));
    redirect()->withMessage('wrong_password', __('Login FAILED! Wrong Member ID or password!'))->back();
  }

  $json = json_decode($response->getBody()->getContents(), true);
  $tokenAccess = $json['data']['attributes']['access'];
  $tokenRefresh = $json['data']['attributes']['refresh'];

  // get /v1/saya
  $response = $client->get('/v1/saya', [
    'headers' => [
      'Authorization' => 'Bearer '.$tokenAccess,
    ]
  ]);

  $json = json_decode($response->getBody()->getContents(), true);
  $idUser = $json['data']['id'];
  $type = $json['data']['attributes']['type'];

  if (! class_exists('simbio_date')) {
    require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
  }

  $result = $dbs->query("SELECT * FROM member WHERE member_id = '$idUser'");
  $row = $result->fetch_assoc();

  $data['member_id'] = $idUser;
  $data['member_name'] = $json['data']['attributes']['nama'];
  $data['inst_name'] = 'Universitas Madura';
  $data['birth_date'] = implode('-', array_reverse(explode('/', $json['data']['attributes']['lahirtanggal'])));
  $data['register_date'] = date('Y-m-d');
  $data['expire_date'] = simbio_date::getNextDate(5000, $data['register_date']);
  $data['member_since_date'] = $data['register_date'];
  $data['pin'] = $password;
  $data['member_address'] = $json['data']['attributes']['alamat'];
  $data['member_mail_address'] = $json['data']['attributes']['email'];
  $data['member_phone'] = $json['data']['attributes']['telepon'];
  $data['member_fax'] = '';
  $data['postal_code'] = '';
  $data['member_notes'] = '';
  $data['member_email'] = $json['data']['attributes']['email'];
  $data['is_pending'] = '0';
  $data['input_date'] = date('Y-m-d');
  $data['last_update'] = date('Y-m-d');
  $data['mpasswd'] = password_hash($password, PASSWORD_BCRYPT);

  // jika $type == 'mhs', maka kita cek di tabel member
  if ($type == 'mhs') {
    // attributes['status'] harus 'aktif' / 'lulus' / 'bss'
    $allowedStatus = ['aktif', 'lulus', 'bss'];
    if (!in_array($json['data']['attributes']['status'], $allowedStatus)) {
      utility::writeLogs($dbs, 'member', $username, 'Login', sprintf(__('Login FAILED for member %s from address %s'),$username,ip()));
      redirect()->withMessage('wrong_password', __('Login FAILED! Wrong Member ID or password!'))->back();
    }

    $data['member_type_id'] = 1;
    $data['gender'] = $json['data']['attributes']['jenisKelamin'] == 'L' ? 1 : '0';
  }
  // jika $type == 'dkr', maka kita cek di tabel member
  if ($type == 'dkr') {
    $data['member_type_id'] = 2;
    $data['gender'] = 1;
  }

  // download image and then save to
  $filename = 'images/persons/member_' . $idUser . '.jpg';
  if (! file_exists($filename)) {  
    $client->get('https://api.unira.ac.id/' . $json['data']['attributes']['thumbnail'], ['sink' => $filename]);
    if (file_exists($filename)) {
      $data['member_image'] = basename($filename);
    }
  }

  // create sql op object
  $sql_op = new simbio_dbop($dbs);

  if ($row) {
    // update data member if any
    unset($data['input_date']);
    Plugins::getInstance()->execute(Plugins::MEMBERSHIP_BEFORE_UPDATE, ['data' => $data]);

    // update the data
    $update = $sql_op->update('member', $data, "member_id = '$idUser'");
    Plugins::getInstance()->execute(Plugins::MEMBERSHIP_AFTER_UPDATE, ['data' => api::member_load($dbs, $data['member_id'])]);
  } else {
    $insert = $sql_op->insert('member', $data);
    Plugins::getInstance()->execute(Plugins::MEMBERSHIP_AFTER_SAVE, ['data' => api::member_load($dbs, $data['member_id'])]);
  }
});
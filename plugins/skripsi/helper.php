<?php
/**
 * @Created by      : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Created on      : 2023-08-21 08:30:00
 * @Filename        : helper.php
 */
use GuzzleHttp\Client;

defined('INDEX_AUTH') OR die('Direct Access Not Allowed!');

function translateProdi($prodi) {
  $prodiList = [
    '11' => [ 'nama' => 'HUKUM', 'strata' => 'S1' ],
    '12' => [ 'nama' => 'S2 HUKUM', 'strata' => 'S2' ],
    '21' => [ 'nama' => 'MANAJEMEN', 'strata' => 'S1' ],
    '22' => [ 'nama' => 'AKUNTANSI', 'strata' => 'S1' ],
    '23' => [ 'nama' => 'BISNIS DIGITAL', 'strata' => 'S1' ],
    '31' => [ 'nama' => 'ADMINISTRASI PUBLIK', 'strata' => 'S1' ],
    '41' => [ 'nama' => 'PETERNAKAN', 'strata' => 'S1' ],
    '42' => [ 'nama' => 'AGRIBISNIS', 'strata' => 'S1' ],
    '51' => [ 'nama' => 'TEKNIK SIPIL', 'strata' => 'S1' ],
    '52' => [ 'nama' => 'INFORMATIKA', 'strata' => 'S1' ],
    '53' => [ 'nama' => 'TEKNIK INDUSTRI', 'strata' => 'S1' ],
    '61' => [ 'nama' => 'PENDIDIKAN BAHASA INDONESIA', 'strata' => 'S1' ],
    '62' => [ 'nama' => 'PENDIDIKAN MATEMATIKA', 'strata' => 'S1' ],
    '63' => [ 'nama' => 'PENDIDIKAN BAHASA INGGRIS', 'strata' => 'S1' ],
  ];

  return $prodiList[$prodi];
}

function showFormAddSkripsi() {
  $client = new Client([
    'base_uri' => 'https://api.unira.ac.id',
    'timeout'  => 2.0,
    'verify' => false,
  ]);
  try {
    $response = $client->post('/graphql', [
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode([
        'query' => 'query getSkripsi($prodi: Int, $page: Int, $limit: Int, $search: String, $judul: String, $pembimbing: String) {
          skripsi2(prodi: $prodi, page: $page, limit: $limit, search: $search, judul: $judul, pembimbing: $pembimbing) {
            total
            skripsi {
              id
              mahasiswa {
                prodi {
                  nama
                }
              }
              judul
            }
          }
        }',
        'variables' => [
          'prodi' => null,
          'page' => 1,
          'limit' => 10,
          'search' => $_SESSION['mid'],
          'judul' => '',
          'pembimbing' => ''
        ]
      ])
    ]);
    $json = json_decode($response->getBody()->getContents(), true);
  } catch(\GuzzleHttp\Exception\RequestException $e) {
    utility::jsToastr('error', 'Gagal mengambil data dari SIMAT UNIRA. Silahkan coba lagi nanti.');
    return;
  }

  if ($json['data']['skripsi2']['total'] == 0) {
    ?>
    <div class="alert alert-danger text-center">
      <strong>Anda belum memiliki skripsi/tesis!</strong> Silahkan hubungi bagian akademik / Ketua Program Studi untuk mengisi data skripsi/tesis.
      <br><br>
      <a href="?p=member&sec=thesis" class="btn btn-sm btn-light"><?php echo __('Cancel') ?></a>
    </div>
    <?php
    return;
  }

  $skripsi = $json['data']['skripsi2']['skripsi'][0];
  $judul = $skripsi['judul'];
  $prodi = $skripsi['mahasiswa']['prodi']['nama'];

  if (isset($_SESSION['flash']) && isset($_SESSION['flash']['messages'])) {
    $flash = $_SESSION['flash']['messages']['error'];
    ?>
    <div class="alert alert-danger text-center">
      <?php echo $flash['message'] ?>
    </div>
    <?php
    unset($_SESSION['flash']);
  }
 
 ?>
    
    <form enctype="multipart/form-data" id="skripsiAddForm" action="?p=member&sec=thesis&do=save" method="post">
      <div class="row mb-0">
        <label for="skripsiTitle" class="col-sm-2 col-form-label">Judul Skripsi/Tesis</label>
        <div class="col-sm-10">
          <textarea name="title" id="skripsiTitle" cols="30" rows="3" class="form-control-plaintext" readonly><?php echo $judul ?></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label for="skripsiProdi" class="col-sm-2 col-form-label">Program Studi</label>
        <div class="col-sm-6">
          <input type="text" name="prodi" id="skripsiProdi" class="form-control-plaintext" value="<?php echo $prodi ?>" readonly>
        </div>
      </div>
      <div class="row mb-3">
        <label for="skripsiYear" class="col-sm-2 col-form-label">Tahun Lulus</label>
        <div class="col-sm-2">
          <select name="year" id="skripsiYear" class="custom-select">
            <option value="">Pilih Tahun</option>
            <?php
              for ($i = date('Y'); $i >= 2000; $i--) {
                echo '<option value="' . $i . '"' . ($i == date('Y') ? ' selected' : '') . '>' . $i . '</option>';
              }
            ?>
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label for="skripsiFile" class="col-sm-2 col-form-label">File Skripsi/Tesis</label>
        <div class="col-sm-6">
          <!-- <div class="custom-file"> -->
            <input type="file" name="file" id="customFile" accept=".pdf">
            <!-- <label class="custom-file-label" for="customFile">Choose file</label> -->
          <!-- </div> -->
          <small class="form-text text-muted">
            File harus berformat <strong>PDF</strong> lengkap berisi cover, lembar pengesahan, abstrak, daftar isi, daftar gambar, daftar tabel, bab 1, bab 2, bab 3, bab 4, bab 5, daftar pustaka, lampiran. <strong>Maksimal ukuran file 10 MB.</strong>
          </small>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-10">
          <button type="submit" value="save_skripsi" class="btn btn-sm btn-primary">Simpan</button>
          <a href="?p=member&sec=thesis" class="btn btn-sm btn-light"><?php echo __('Cancel') ?></a>
        </div>
      </div>
    </form>
  <?php
}

function translateDate($obj_db, $array_data) {
  list($date, $time) = explode(' ', $array_data[2]);
  return implode('/', array_reverse(explode('-', $date))) . ' ' . $time;
}

function translateStatus($obj_db, $array_data) {
  $status = $array_data[3];
  $statusList = [
    '0' => 'Menunggu Verifikasi',
    '1' => 'Sudah Diverifikasi',
    '2' => 'Ditolak',
  ];
  return $statusList[$status];
}

function showAction($obj_db, $array_data) {
  if ($array_data[4] == 3) {
    return '<a href="?p=member&sec=thesis&do=delete&id=' . $array_data[0] . '" class="btn btn-sm btn-danger">Edit</a>';
  }
}
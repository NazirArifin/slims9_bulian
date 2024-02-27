<?php
/**
 * @Created by          : Mohammad Nazir Arifin
 * @Date                : 2024-02-27 08.00
 * @File name           : SkripsiController.php
 */

class SkripsiController extends Controller {
    protected $sysconf;

    /**
     * @var mysqli
     */
    protected $db;

    function __construct($sysconf, $obj_db)
    {
        $this->sysconf = $sysconf;
        $this->db = $obj_db;
    }

    function getTotalSkripsi() {
        $query = $this->db->query("SELECT COUNT(id) FROM skripsi");
        $total = ($query->fetch_row())[0];

        // verified jika is_valid = 2
        $query = $this->db->query("SELECT COUNT(id) FROM skripsi WHERE is_valid = 2");
        $verified = ($query->fetch_row())[0];

        // fetch data prodi di https://api.unira.ac.id/v1/prodi
        $prodi = $this->fetchProdi();
        // only take the data from the response $prodi['data']
        $prodi = $prodi['data'];
        // take id and atttributes.nama from $prodi
        $prodi = array_map(function($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['attributes']['nama'],
                'total' => 0,
                'verified' => 0,
                'new' => 0,
            ];
        }, $prodi);
        // strip data that id is not 2 digit
        $prodi = array_filter($prodi, function($item) {
            return strlen($item['id']) == 2;
        });

        // get total skripsi per prodi, verified, and new 
        $query = $this->db->query("SELECT COUNT(id), SUBSTRING(member_id, 5, 2) FROM skripsi GROUP BY SUBSTRING(member_id, 5, 2)");

        while ($row = $query->fetch_row()) {
            $prodi_id = $row[1];
            $total = $row[0];

            $prodi_key = array_search($prodi_id, array_column($prodi, 'id'));
            $prodi[$prodi_key]['total'] = intval($total);
        }

        // get total verified skripsi per prodi
        $queryVerified = $this->db->query("SELECT COUNT(id), SUBSTRING(member_id, 5, 2) FROM skripsi WHERE is_valid = 2 GROUP BY SUBSTRING(member_id, 5, 2)");

        // get total unverified skripsi per prodi
        $queryUnVerified = $this->db->query("SELECT COUNT(id), SUBSTRING(member_id, 5, 2) FROM skripsi WHERE is_valid != 2 GROUP BY SUBSTRING(member_id, 5, 2)");

        while ($row = $queryVerified->fetch_row()) {
            $prodi_id = $row[1];
            $verified = $row[0];

            $prodi_key = array_search($prodi_id, array_column($prodi, 'id'));
            $prodi[$prodi_key]['verified'] = $verified;
        }

        // get total unverified skripsi per prodi
        while ($row = $queryUnVerified->fetch_row()) {
            $prodi_id = $row[1];
            $unverified = $row[0];

            $prodi_key = array_search($prodi_id, array_column($prodi, 'id'));
            $prodi[$prodi_key]['new'] = $prodi[$prodi_key]['total'] - $prodi[$prodi_key]['verified'];
        }
        
        parent::withJson([
            'data' => [
                'total' => $total,
                'verified' => $verified,
                'new' => $total - $verified,
                'prodi' => $prodi
            ]
        ]);
    }

    // fetch prodi with curl
    function fetchProdi() {
      $url = 'https://api.unira.ac.id/v1/prodi';
      // $ch = curl_init($url);
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

      // $response = curl_exec($ch);

      // if (curl_errno($ch)) {
      //   echo 'Error:' . curl_error($ch);
      // }

      // curl_close($ch);

      // $decodedResponse = json_decode($response, true);

      // if (json_last_error() !== JSON_ERROR_NONE) {
      //   echo 'Decoding Error: ' . json_last_error_msg();
      // }

      $response = file_get_contents($url);
      $decodedResponse = json_decode($response, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Decoding Error: ' . json_last_error_msg();
      }

      return $decodedResponse;
    }
}
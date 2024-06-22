<?php
session_start();      


require_once("../../assets/js/plugin/dompdf/autoload.inc.php");

use Dompdf\Dompdf;



if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
  
  header('location: ../../login.php?pesan=2');
}

else {
  
  require_once "../../config/database.php";
  
  require_once "../../helper/fungsi_tanggal_indo.php";

  
  $stok = $_GET['stok'];

  
  $no = 1;

  
  $dompdf = new Dompdf();
  
  $options = $dompdf->getOptions();
  $options->setIsRemoteEnabled(true); 
  $options->setChroot('C:\xampp\htdocs\gudang'); 
  $dompdf->setOptions($options);

  
  
  if ($stok == 'Seluruh') {
    
    $html = '<!DOCTYPE html>
            <html>
            <head>
              <title>Laporan Stok Seluruh Barang</title>
              <link rel="stylesheet" href="../../assets/css/laporan.css">
            </head>
            <body class="text-dark">
              <div class="text-center mb-4">
                <h1>LAPORAN STOK SELURUH BARANG</h1>
              </div>
              <hr>
              <div class="mt-4">
                <table class="table table-bordered" width="100%" cellspacing="0">
                  <thead class="bg-primary text-white text-center">
                    <tr>
                      <th>No.</th>
                      <th>ID Barang</th>
                      <th>Nama Barang</th>
                      <th>Jenis Barang</th>
                      <th>Stok</th>
                      <th>Satuan</th>
                    </tr>
                  </thead>
                  <tbody class="text-dark">';
    
    $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, b.nama_jenis, c.nama_satuan
										                FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
										                ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
                                    ORDER BY a.id_barang ASC")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    while ($data = mysqli_fetch_assoc($query)) {
      
      $html .= '		<tr>
                      <td width="50" class="text-center">' . $no++ . '</td>
                      <td width="80" class="text-center">' . $data['id_barang'] . '</td>
                      <td width="200">' . $data['nama_barang'] . '</td>
                      <td width="140">' . $data['nama_jenis'] . '</td>';
      
      
      if ($data['stok'] <= $data['stok_minimum']) {
        $html .= '		<td width="70" class="text-right">
                        <span class="badge badge-warning">' . $data['stok'] . '</span>
                      </td>';
      }
      
      else {
        $html .= '		<td width="70" class="text-right">' . $data['stok'] . '</td>';
      }
      $html .= '			<td width="80">' . $data['nama_satuan'] . '</td>
								    </tr>';
    }
    $html .= '		</tbody>
                </table>
              </div>
              <div class="text-right mt-5">............, ' . tanggal_indo(date('Y-m-d')) . '</div>
            </body>
            </html>';

    
    $dompdf->loadHtml($html);
    
    $dompdf->setPaper('A4', 'landscape');
    
    $dompdf->render();
    
    $dompdf->stream('Laporan Stok Seluruh Barang.pdf', array('Attachment' => 0));
  }
  
  else {
    
    $html = '<!DOCTYPE html>
            <html>
            <head>
              <title>Laporan Stok Barang Minimum</title>
              <link rel="stylesheet" href="../../assets/css/laporan.css">
            </head>
            <body class="text-dark">
              <div class="text-center mb-4">
                <h1>LAPORAN STOK BARANG YANG MENCAPAI BATAS MINIMUM</h1>
              </div>
              <hr>
              <div class="mt-4">
                <table class="table table-bordered" width="100%" cellspacing="0">
                  <thead class="bg-primary text-white text-center">
                    <tr>
                      <th>No.</th>
                      <th>ID Barang</th>
                      <th>Nama Barang</th>
                      <th>Jenis Barang</th>
                      <th>Stok</th>
                      <th>Satuan</th>
                    </tr>
                  </thead>
                  <tbody class="text-dark">';
    
    $query = mysqli_query($mysqli, "SELECT a.id_barang, a.nama_barang, a.jenis, a.stok_minimum, a.stok, a.satuan, b.nama_jenis, c.nama_satuan
										                FROM tbl_barang as a INNER JOIN tbl_jenis as b INNER JOIN tbl_satuan as c 
                                    ON a.jenis=b.id_jenis AND a.satuan=c.id_satuan 
										                WHERE a.stok<=a.stok_minimum ORDER BY a.id_barang ASC")
                                    or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));
    
    while ($data = mysqli_fetch_assoc($query)) {
      
      $html .= '		<tr>
                      <td width="50" class="text-center">' . $no++ . '</td>
                      <td width="80" class="text-center">' . $data['id_barang'] . '</td>
                      <td width="200">' . $data['nama_barang'] . '</td>
                      <td width="140">' . $data['nama_jenis'] . '</td>
                      <td width="70" class="text-right">' . $data['stok'] . '</td>
                      <td width="80">' . $data['nama_satuan'] . '</td>
                    </tr>';
    }
    $html .= '		</tbody>
                </table>
              </div>
              <div class="text-right mt-5">............, ' . tanggal_indo(date('Y-m-d')) . '</div>
            </body>
            </html>';

    
    $dompdf->loadHtml($html);
    
    $dompdf->setPaper('A4', 'landscape');
    
    $dompdf->render();
    
    $dompdf->stream('Laporan Stok Barang Minimum.pdf', array('Attachment' => 0));
  }
}

<?php

function nama_hari_indo($tanggal) {
	
	$tgl = substr($tanggal,8,2);
	$bln = substr($tanggal,5,2);
	$thn = substr($tanggal,0,4);

	$info = date('w', mktime(0,0,0,$bln,$tgl,$thn));

	switch ($info) {
		case '0': return "Minggu"; break;
		case '1': return "Senin"; break;
		case '2': return "Selasa"; break;
		case '3': return "Rabu"; break;
		case '4': return "Kamis"; break;
		case '5': return "Jumat"; break;
		case '6': return "Sabtu"; break;
	}
}


function tanggal_indo($tanggal){
	$bulan = array (
		1 =>'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
	);
	$explode = explode('-', $tanggal);
	
	
	
	return $explode[2] . ' ' . $bulan[ (int)$explode[1] ] . ' ' . $explode[0];
}


function nama_bulan_indo($tanggal){
	$bulan = array (
		1 =>'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
	);
	$explode = explode('-', $tanggal);
	
	
	return $bulan[ (int)$explode[0] ] ;
}
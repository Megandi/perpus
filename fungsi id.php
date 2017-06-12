<?php
function kode_angkatan($id)
{
	$id=substr($id,4,2);
	return "20".$id;
}
function kode_prodi($id)
{
	$id=substr($id,2,2);
	return $id;
}
function nama_prodi($id)
{	switch ($id) {
		case 52:
			$id="Ilmu Komputer";
			break;
		case 51:
			$id="Ilmu Kimia";
			break;
		default:
			$id="tidak ada";
			break;
	}
	return $id;
}

echo kode_angkatan(105216052)."</br>";
echo kode_prodi(105216052)."</br>";
echo nama_prodi(kode_prodi(105216052));
?>
<?php

require 'function.php';

$id = $_GET['id_kategori'];

if(hapus_kategori($id) > 0){
    echo "
        <script>
            alert('Data berhasil dihapus!');
            document.location.href='index_kategori.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Data gagal dihapus!');
            document.location.href='index_kategori.php';
        </script>
    ";
}

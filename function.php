<?php
$conn = mysqli_connect("localhost", "root", "", "simbs");


// FUNGSI MENAMPILKAN DATA
function query($query){
    global $conn;

    $result = mysqli_query($conn, $query);
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ) {
        $rows[] = $row;
    }
    return $rows;
}


//TAMBAH DATA BUKU
function tambah_data($data){
    global $conn;


    $judul = $data['judul'];
    $genre = $data['genre'];
    $penulis = $data['penulis'];
    $penerbit = $data['penerbit'];
    $gambar = $data['gambar'];
    $id_kategori = $data['id_kategori'];

    // upload gambar
   $gambar = upload_gambar($judul); 
    if( !$gambar ) {
        return false;
    }

    $query = "INSERT INTO buku 
                (judul, genre, penulis, penerbit, gambar, id_kategori)
              VALUES
                ('$judul', '$genre', '$penulis', '$penerbit', '$gambar', '$id_kategori')";
    
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);      
}

// TAMBAH DATA KATEGORI
function tambah_kategori($data){
    global $conn;


    $nama = $data['nama_kategori'];


    $query = "INSERT INTO kategori 
                (nama_kategori)
              VALUES
                ('$nama')";
    
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);      
}

//  FUNGSI UPLOAD GAMBAR
function upload_gambar($judul) {


    // setting gambar
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];


    // cek apakah tidak ada gambar yang diupload
    if( $error === 4 ) {
        echo "<script>
                alert('pilih gambar terlebih dahulu!');
              </script>";
        return false;
    }


    // cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if( !in_array($ekstensiGambar, $ekstensiGambarValid) ) {
        echo "<script>
                alert('yang anda upload bukan gambar!');
              </script>";
        return false;
    }


    // cek jika ukurannya terlalu besar
    // maks --> 5MB
    if( $ukuranFile > 5000000 ) {
        echo "<script>
                alert('ukuran gambar terlalu besar!');
              </script>";
        return false;
    }


    // lolos pengecekan, gambar siap diupload
    // generate nama gambar baru
    $namaFileBaru =  $judul;
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;


    move_uploaded_file($tmpName, 'img/' . $namaFileBaru);


    return $namaFileBaru;
}

// HAPUS DATA BUKU
function hapus_data($id){
    global $conn;


    $query = "DELETE FROM buku WHERE id_buku = $id";


    $result = mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}

// HAPUS DATA KATEGORI
function hapus_kategori($id){
    global $conn;


    $query = "DELETE FROM kategori WHERE id_kategori = $id";


    $result = mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}

// FUNGSI UNTUK MENGUBAH DATA DI DATABASE
function ubah_data($data){
    global $conn;

    $id = $data['id_buku'];
    $judul = $data['judul'];
    $genre = $data['genre'];
    $penulis = $data['penulis'];
    $penerbit = $data['penerbit'];
    $gambar = $data['gambar'];


    $query = "UPDATE buku SET
                judul = '$judul',
                genre = '$genre',
                penulis = '$penulis',
                penerbit = '$penerbit'
              WHERE id_buku = '$id'
             ";


     $result = mysqli_query($conn, $query);
     
     return mysqli_affected_rows($conn);
}

// FUNGSI UBAH KATEGORI
function ubah_kategori($data){
    global $conn;

    $id = $data['id_kategori'];
    $nama_kategori = $data['nama_kategori'];

    $query = "UPDATE kategori SET
                nama_kategori = '$nama_kategori'
              WHERE id_kategori = '$id'
             ";


     $result = mysqli_query($conn, $query);
     
     return mysqli_affected_rows($conn);
}

// fungsi untuk mencari data
function search_data($keyword){
    global $conn;


     $query = "SELECT buku.*, kategori.nama_kategori 
          FROM buku 
          INNER JOIN kategori 
          ON buku.id_kategori = kategori.id_kategori
          WHERE buku.judul LIKE '%$keyword%'
          OR buku.penulis LIKE '%$keyword%'
          OR buku.penerbit LIKE '%$keyword%'
          OR kategori.nama_kategori LIKE '%$keyword%'
          ORDER BY buku.tanggal_input DESC";


    return query($query);
}

function search_kategori($keyword){
    global $conn;


     $query = "SELECT *
              FROM kategori
              WHERE nama_kategori  LIKE '%$keyword%'"
              ;

    return query($query);
}

// fungsi untuk register
function register($data){
    global $conn;


    $username = strtolower($data['username']);
    $email = $data['email'];
    $password = mysqli_real_escape_string($conn, $data['password']);
    $konfirmasi_password = mysqli_real_escape_string($conn, $data['confirm_password']);


    // query untuk ngecek username yang diinputkan oleh user di database
    $query = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    $result = mysqli_fetch_assoc($query);


    if($result != NULL){
        return "Username atau email sudah terdaftar, gunakan yang lain";
    }


    if($password != $konfirmasi_password){
        return "Konfirmasi password tidak sesuai!";
    }

    // Validasi jumlah karakter
    if(strlen($password) < 8){
        return "Password minimal 8 karakter!";
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);


    // tambahkan userbaru ke database
    mysqli_query($conn, "INSERT INTO user (username, email, password) VALUES('$username', '$email', '$password')");


    return true;
}


// fungsi untuk login
function login($data){
    global $conn;


    $username = $data['username'];
    $password = $data['password'];


    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);


    if(mysqli_num_rows($result) === 1){
        $row = mysqli_fetch_assoc($result);


        if(password_verify($password, $row['password'])){
            $_SESSION["login"] = true;
            $_SESSION["username"] = $row['username'];
            return true;
        } else {
           
            return "Password salah!";
        }


    }else{
        return "Username tidak terdaftar!";
    }
}


?>
<?php
// Tetapan direktori penyimpanan
$upload_dir = "../uploads/";

// 1. Semak sama ada folder 'uploads' wujud, jika tiada bina secara automatik
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// 2. Semak sama ada borang telah menghantar fail
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $file = $_FILES['profile_pic'];
    
    // Hadkan saiz (contoh: maksimum 2MB)
    $max_size = 2 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        die("Ralat: Saiz fail terlalu besar. Maksimum adalah 2MB.");
    }
    
    // Semak format / jenis fail gambar yang dibenarkan
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_mime = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_mime, $allowed_types)) {
        die("Ralat: Hanya format JPG, JPEG, PNG, dan WEBP sahaja dibenarkan.");
    }
    
    // Dapatkan ekstensi fail asal
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    // Bina nama fail baharu yang unik bagi mengelakkan pertindihan nama
    $new_filename = "admin_" . time() . "_" . uniqid() . "." . $ext;
    $target_file = $upload_dir . $new_filename;
    
    // 3. Pindahkan fail dari direktori sementara ke folder 'uploads/'
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        echo "Muat naik berjaya! Fail disimpan sebagai: " . $new_filename;
        
        // Di sini anda boleh masukkan kod untuk simpan nama fail ($new_filename) ke dalam pangkalan data MySQL
        // Contoh: mysqli_query($conn, "UPDATE users SET profile_pic = '$new_filename' WHERE id = '$user_id'");
        
    } else {
        echo "Ralat: Gagal memindahkan fail yang dimuat naik.";
    }
} else {
    echo "Tiada fail dipilih atau berlaku ralat semasa muat naik.";
}
?>
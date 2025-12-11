<?php
require_once 'config.php';
header('Content-Type: application/json');

if (isset($_GET['no_matrik'])) {
    $no_matrik = mysqli_real_escape_string($conn, $_GET['no_matrik']);
    $query = "SELECT * FROM pelajar WHERE no_matrik = '$no_matrik'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $pelajar = mysqli_fetch_assoc($result);
        echo json_encode([
            'exists' => true,
            'nama' => $pelajar['nama'],
            'kelas' => $pelajar['kelas'],
            'no_telefon' => $pelajar['no_telefon'],
            'alamat' => $pelajar['alamat']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
?>


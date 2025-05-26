<?php
// 1. PASTIKAN config.php di-include PALING ATAS
include 'config.php';

// Cek apakah koneksi berhasil (PENTING!)
if (!$conn) {
    die("Koneksi ke database gagal. Periksa file config.php.");
}

$pesan = ''; // Variabel untuk pesan feedback

// 2. PROSES FORM JIKA ADA POST REQUEST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_pelanggan = $_POST['id_pelanggan'];
    $id_barber = $_POST['id_barber'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];

    // Pastikan 'id_layanan' ada sebelum diakses
    $id_layanan_arr = isset($_POST['id_layanan']) ? $_POST['id_layanan'] : [];

    // Validasi dasar: Pastikan layanan dipilih
    if (empty($id_layanan_arr)) {
        $pesan = "Gagal: Anda harus memilih setidaknya satu layanan.";
    } else {
        // PERHATIAN: Perlu logika lebih canggih untuk hitung durasi!
        // Untuk contoh ini, kita hitung durasi total dari layanan yang dipilih.
        $total_durasi = 0;
        $in_placeholders = implode(',', array_fill(0, count($id_layanan_arr), '?'));
        $types = str_repeat('i', count($id_layanan_arr));

        $stmt_durasi = $conn->prepare("SELECT SUM(Durasi_Menit) as total FROM Layanan WHERE ID_Layanan IN ($in_placeholders)");
        $stmt_durasi->bind_param($types, ...$id_layanan_arr);
        $stmt_durasi->execute();
        $result_durasi = $stmt_durasi->get_result();
        $row_durasi = $result_durasi->fetch_assoc();
        $total_durasi = $row_durasi['total'] ? $row_durasi['total'] : 30; // Default 30 menit jika gagal
        $stmt_durasi->close();


        // Mulai transaksi database
        $conn->begin_transaction();

        try {
            // Insert ke tabel Reservasi
            $stmt_reservasi = $conn->prepare("INSERT INTO Reservasi (ID_Pelanggan, ID_Barber, Tanggal, Waktu_Mulai, Total_Durasi, Status_Reservasi) VALUES (?, ?, ?, ?, ?, 'Booked')");
            // Pastikan tipe data benar: i (int), i (int), s (string/date), s (string/time), i (int)
            $stmt_reservasi->bind_param("iissi", $id_pelanggan, $id_barber, $tanggal, $waktu, $total_durasi);
            $stmt_reservasi->execute();

            // Dapatkan ID Reservasi yang baru dibuat
            $id_reservasi_baru = $conn->insert_id;

            // Insert ke tabel Detail_Reservasi
            $stmt_detail = $conn->prepare("INSERT INTO Detail_Reservasi (ID_Reservasi, ID_Layanan) VALUES (?, ?)");
            foreach ($id_layanan_arr as $id_layanan) {
                $stmt_detail->bind_param("ii", $id_reservasi_baru, $id_layanan);
                $stmt_detail->execute();
            }

            // Commit transaksi
            $conn->commit();
            $pesan = "Reservasi berhasil ditambahkan!";
            $stmt_reservasi->close();
            $stmt_detail->close();

        } catch (mysqli_sql_exception $exception) {
            // Rollback jika ada error
            $conn->rollback();
            $pesan = "Gagal menambahkan reservasi: " . $exception->getMessage();
        }
    }
}

// 3. SELALU AMBIL DATA UNTUK DROPDOWN (setelah proses POST jika ada)
$pelanggan_result = $conn->query("SELECT ID_Pelanggan, Nama FROM Pelanggan");
$barber_result = $conn->query("SELECT ID_Barber, Nama FROM Barber");
$layanan_result = $conn->query("SELECT ID_Layanan, Nama_Layanan, Harga FROM Layanan"); // Ini query yang error sebelumnya

// Periksa apakah query $layanan_result berhasil
if (!$layanan_result) {
    die("Error mengambil data layanan: " . $conn->error);
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Tambah Reservasi</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <h1>Tambah Reservasi Baru</h1>
        <p><a href="index.php">Kembali ke Menu Utama</a></p>
        <hr>

        <?php if ($pesan): ?>
        <p class="feedback"><?php echo $pesan; ?></p>
        <?php endif; ?>

        <form action="tambah_reservasi.php" method="post">
            <label for="id_pelanggan">Pilih Pelanggan:</label>
            <select name="id_pelanggan" id="id_pelanggan" required>
                <option value="">--Pilih Pelanggan--</option>
                <?php
                // Pastikan $pelanggan_result ada sebelum looping
                if ($pelanggan_result && $pelanggan_result->num_rows > 0) {
                    while($row = $pelanggan_result->fetch_assoc()) {
                        echo "<option value='" . $row['ID_Pelanggan'] . "'>" . htmlspecialchars($row['Nama']) . "</option>";
                    }
                }
                ?>
            </select>

            <label for="id_barber">Pilih Barber:</label>
            <select name="id_barber" id="id_barber" required>
                <option value="">--Pilih Barber--</option>
                <?php
                // Pastikan $barber_result ada sebelum looping
                if ($barber_result && $barber_result->num_rows > 0) {
                    while($row = $barber_result->fetch_assoc()) {
                        echo "<option value='" . $row['ID_Barber'] . "'>" . htmlspecialchars($row['Nama']) . "</option>";
                    }
                }
                ?>
            </select>

            <label for="tanggal">Tanggal Reservasi:</label>
            <input type="date" id="tanggal" name="tanggal" required>

            <label for="waktu">Waktu Reservasi:</label>
            <input type="time" id="waktu" name="waktu" required>

            <label>Pilih Layanan (Bisa lebih dari satu):</label>
            <div class="checkbox-group">
                <?php
                // INI BAGIAN YANG ERROR (LINE 40)
                // Sekarang $layanan_result dijamin ada karena query di atas
                if ($layanan_result && $layanan_result->num_rows > 0) { // Periksa $layanan_result juga
                    while($row = $layanan_result->fetch_assoc()) {
                        echo "<input type='checkbox' name='id_layanan[]' value='" . $row['ID_Layanan'] . "' id='layanan_".$row['ID_Layanan']."'> ";
                        echo "<label for='layanan_".$row['ID_Layanan']."'>" . htmlspecialchars($row['Nama_Layanan']) . " (Rp " . number_format($row['Harga']) . ")</label><br>";
                    }
                } else {
                    echo "Tidak ada layanan tersedia.";
                }
                ?>
            </div><br>

            <input type="submit" value="Buat Reservasi">
        </form>

    </div>

    <?php
    // 4. SELALU TUTUP KONEKSI DI AKHIR SCRIPT
    // Pastikan $conn ada sebelum ditutup
    if ($conn) {
        $conn->close(); // INI BAGIAN YANG ERROR (LINE 56)
    }
    ?>

</body>

</html>

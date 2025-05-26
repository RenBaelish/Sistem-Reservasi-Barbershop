<?php
include 'config.php'; // Hubungkan ke database
?>
<!DOCTYPE html>
<html>

<head>
    <title>Daftar Reservasi</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h1>Daftar Reservasi</h1>
        <p><a href="index.php">Kembali ke Menu Utama</a></p>
        <hr>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Pelanggan</th>
                    <th>Barber</th>
                    <th>Status</th>
                    <th>Layanan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query untuk mengambil data reservasi dengan join dan group_concat
                $sql = "SELECT
                            r.ID_Reservasi,
                            r.Tanggal,
                            r.Waktu_Mulai,
                            p.Nama AS Nama_Pelanggan,
                            b.Nama AS Nama_Barber,
                            r.Status_Reservasi,
                            GROUP_CONCAT(l.Nama_Layanan SEPARATOR ', ') AS Daftar_Layanan
                        FROM Reservasi r
                        JOIN Pelanggan p ON r.ID_Pelanggan = p.ID_Pelanggan
                        JOIN Barber b ON r.ID_Barber = b.ID_Barber
                        LEFT JOIN Detail_Reservasi dr ON r.ID_Reservasi = dr.ID_Reservasi
                        LEFT JOIN Layanan l ON dr.ID_Layanan = l.ID_Layanan
                        GROUP BY r.ID_Reservasi
                        ORDER BY r.Tanggal DESC, r.Waktu_Mulai DESC";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Tampilkan data untuk setiap baris
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["ID_Reservasi"] . "</td>";
                        echo "<td>" . $row["Tanggal"] . "</td>";
                        echo "<td>" . $row["Waktu_Mulai"] . "</td>";
                        echo "<td>" . htmlspecialchars($row["Nama_Pelanggan"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Nama_Barber"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Status_Reservasi"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Daftar_Layanan"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada data reservasi.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        $conn->close(); // Tutup koneksi
        ?>

    </div>
</body>

</html>

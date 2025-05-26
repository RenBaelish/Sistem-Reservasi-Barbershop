<?php
include 'config.php'; // Hubungkan ke database
?>
<!DOCTYPE html>
<html>

<head>
    <title>Daftar Pelanggan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h1>Daftar Pelanggan</h1>
        <p><a href="index.php">Kembali ke Menu Utama</a></p>
        <hr>

        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th>Jenis Kelamin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT ID_Pelanggan, Nama, No_HP, Email, Jenis_Kelamin FROM Pelanggan ORDER BY Nama";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["ID_Pelanggan"] . "</td>";
                        echo "<td>" . htmlspecialchars($row["Nama"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["No_HP"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Jenis_Kelamin"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data pelanggan.</td></tr>";
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

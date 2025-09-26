<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "laundry_db";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koenksi Gagal:" . mysqli_connect_error());
}
if ($_SERVER["REQUEST_METHOD"] == ["POST"]) {
    if (isset($_POST["tambah"])) {
        $id = !empty($_POST['id']) ? $_POST['id'] : null;
        $id_pelanggan = $_POST['id_pelanggan'];
        $id_jenis = $_POST['id_jenis'];
        $harga = $_POST['harga'];
        $total = $_POST['total'];
        $tanggal_terima = date('Y-m-d');
        $tanggal_selesai = date('Y-m-d', strtotime('+3 days'));

        if ($id) {
            $query = "UPDATE transaksi SET
                    id_pelanggan = '$id_pelanggan',
                    id_jenis = '$id_jenis',
                    harga = '$harga',
                    jumlah = '$jumlah',
                    total = '$total'
                    WHERE id_transaksi = '$id'";
        } else {
            $query = "INSERT INTO transaksi (id_pelanggan, id_jenis, tanggal_terima, tanggal_selesai, harga, jumlah, total)
                        VALUES ('$id_pelanggan', '$id_jenis', '$tanggal_terima', '$tanggal_selesai', '$harga', '$jumlah', '$total')
                        ORDER BY id_transaksi DESC";
        }
        mysqli_query($conn, $query);
        header("Location: index.php");
    }
    if (isset($_POST["hapus"])) {
        $id = explode(",", $_POST['id']);
        foreach ($ids as $id) {
            $id = mysqli_real_escape_string($conn, $id);
            $query = "DELETE FROM transaksi WHERE id_transaksi = '$id'";
            mysqli_query($conn, $query);
        }
        header("Location: index.php");
    }
}

$pelanggan_result = mysqli_query($conn, "SELECT * FROM pelanggan");
$jenis_result = mysqli_query($conn, "SELECT * FROM jenis");
$transaksi_result = mysqli_query($conn, "SELECT t.id_transaksi, t.id_pelanggan, t.id_jenis, p.nama, j.jenis, j.harga, t.tanggal_terima, t.tanggal_selesai, t.jumlah, t.total
                                                        FROM transaksi t
                                                        JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                                                        JOIN jenis j ON t.id_jenis = j.id_jenis
                                                        ORDER BY id_transaksi DESC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Laundry</title>
</head>

<div class="container">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Jenis Laundry</th>
                    <th>Tanggal Terima</th>
                    <th>Tanggal Selesai</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($transaksi_result)) { ?>
                    <tr class="selectable-row" data-id="<?php $row['id_transaksi']; ?>"
                        data-pelanggan="<?php $row['id_pelanggan']; ?>" data-jenis="<?php $row['id_jenis']; ?>"
                        data-harga="<?php $row['harga']; ?>" data-jumlah="<?php $row['jumlah']; ?>"
                        data-total="<?php $row['total']; ?>">
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['jenis']; ?></td>
                        <td><?php echo $row['tanggal_terima']; ?></td>
                        <td><?php echo $row['tanggal_selesai']; ?></td>
                        <td>Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['jumlah']; ?></td>
                        <td>Rp<?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div>
            <form action="" method="post" id="actionForm">
                <input type="hidden" name="id" id="selectedId">
                <button type="button" onclick="setUpdate()">UPDATE</button>
                <button type="submit" name="hapus" onclick="return confirm('Anda yakin ingin menghapus data ini?')">DELETE</button>
            </form>
        </div>
    </div>
    <div class="form">
        <form action="" method="post" id="formLaundry">
            <input type="hidden" name="id" id="formId" required>
            <div class="form-group">
                <select name="id_pelanggan" id="formPelanggan" required>
                    <option value="">---Pilih Pelanggan---</option>
                    <?php mysqli_data_seek($pelanggan_result,0);
                    while($row = mysqli_fetch_assoc($pelanggan_result)){?>
                        <option value="<?php echo $row['id_pelanggan'];?>">
                            <?php echo $row['nama'];?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <select name="id_jenis" id="formJenis" required onchange="tampilHarga()">
                    <option value="">---Pilih Jenis---</option>
                    <?php mysqli_data_seek($jenis_result,0);
                    while($row = mysqli_fetch_assoc($jenis_result)){?>
                        <option value="<?php echo $row['id_jenis'];?>" data-harga="<?php echo $row['harga'];?>">
                            <?php echo $row['jenis'];?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </form>
    </div>
</div>

<body>
    <div class="table"></div>
    <div class="form"></div>
    <script>
        function formatRupiah(number) {
            return parseInt(number).toLocaleString('id_ID');
        }
        function tampilHarga() {
            let select = document.getElementById("jenis");
            let harga = select.options[select.selectedIndex].getAtrribute("data-harga");

            document.getElementById("harga").value = harga ? "Rp" + formatRupiah(harga) : "";
            document.getElementBy.Id("harga_numeric").value = harga ? harga : "";

            hitungTotal();
        }
        function hitungTotal() {
            let harga = document.getElementById("harga_numeri").value;
            let jumlah = document.getElementById("jumlah").value;

            if (harga && jumlah) {
                let total = parseInt(harga) * parseInt(jumlah);
                document.getElementById("total").value = "Rp" + formatRupiah(total);
                document.getElementById("total_numeric").value = total;
            } else {
                doecument.getElementById("total").value = "";
                doecument.getElementById("total_numeric").value = "";
            }
        }
        let selectedIds = [];
        document.querySelectorAll(".selectable-row").forEach(row => {
            row.assEventListener("click", function () {
                let id = this.getAtrribute("data-id");

                if (selectedIds.includes(id)) {
                    selectedId = selectedIds.filter(x => x !== $id);
                    rhis.classList.remove("selected");
                } else {
                    selectedIds.push(id);
                    this.classList.add("selected");
                }
                document.getElementById("selectedId").value = selectedIds.join(",");
            });
        });
        function setUpdate() {
            if (selectedIds.length !== 1) {
                alert("Pilih tepat satu baris untuk update!");
                return;
            }
            let row = document.querySelector(".selectable-row[data-id='" + selectedIds[0] + "']");
            document.getElementById("formId").value = row.getAttribute("data-id");
            document.getElementById("formPelanggan").value = row.getAttribute("data-pelanggan");
            document.getElementById("formJenis").value = row.getAttribute("data-jenis");
            document.getElementById("harga_numeric").value = row.getAttribute("data-harga");
            document.getElementById("jumlah").value = row.getAttribute("data-jumlah");
            document.getElementById("total_numeric").value = row.getAttribute("data-total");
            tampilHarga();
        }
        function resetForm() {
            document.getElementById("formId").value = "";
            document.getElementById("formPelanggan").value = "";
            document.getElementById("formJenis").value = "";
            document.getElementById("harga").value = "";
            document.getElementById("harga_numeric").value = "";
            document.getElementById("jumlah").value = 1;
            document.getElementById("total").value = "";
            document.getElementById("total_numeric").value = "";
        }
        widow.onload = function () {
            tampilHarga();
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has("success")) {
                resretFrom();
                window.history.replaceState({}, document.title, "index.php");
            }
        }
    </script>
</body>

</html>
// ===== Fungsi generik untuk mengambil dan menampilkan data JSON =====
async function muatData(namaFile, daftarKunci) {
    const tbody = document.querySelector(".table-responsive table tbody");
    const loading = document.getElementById("loading-indicator");
    if (!tbody) return;
    loading.style.display = "block";
    tbody.innerHTML = "";
    try {
        // Delay 3 detik untuk simulasi koneksi lambat
        await new Promise((resolve) => setTimeout(resolve, 3000));
        const res = await fetch("../data/" + namaFile);
        if (!res.ok) {
            throw new Error(
                "Gagal mengambil data (status " + res.status + ")"
            );
        }
        const data = await res.json();
        data.forEach(function (item) {
            const tr = document.createElement("tr");
            let isiBaris = "";
            daftarKunci.forEach(function (kunci) {
                isiBaris += "<td>" + item[kunci] + "</td>";
            });
            isiBaris +=
                "<td>" +
                "<button type=\"button\">Edit</button> " +
                "<button type=\"button\" class=\"btn-hapus\">Hapus</button>" +
                "</td>";

            tr.innerHTML = isiBaris;
            tbody.appendChild(tr);
        });
    } catch (err) {
        tbody.innerHTML =
            "<tr><td colspan=\"" +
            (daftarKunci.length + 1) +
            "\">Gagal memuat data: " +
            err.message +
            "</td></tr>";
    } finally {

        loading.style.display = "none";
    }
}


// ===== Menentukan data yang dimuat berdasarkan halaman =====
document.addEventListener("DOMContentLoaded", function () {
    const path = window.location.pathname;
    if (path.includes("/buku/")) {
        muatData(
            "buku.json",
            [
                "judul",
                "pengarang",
                "tahun",
                "stok",
                "kategori"
            ]
        );
    } else if (path.includes("/anggota/")) {
        muatData(
            "anggota.json",
            [
                "no_anggota",
                "nama",
                "alamat",
                "no_hp"
            ]
        );
    }
});
</div> <!-- Penutup container utama dari header.php -->

<!-- ============================================== -->
<!-- 1. MODAL KONFIRMASI LOGOUT -->
<!-- ============================================== -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border: none; border-radius: 15px;">
      <div class="modal-header" style="background-color: #1d4197; color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-right me-2"></i> Konfirmasi Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-circle text-warning mb-3" style="font-size: 3rem;"></i>
        <h5 class="text-dark fw-bold mb-2">Yakin ingin keluar?</h5>
        <p class="text-secondary mb-0">Sesi akan diakhiri dan Anda harus login kembali.</p>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4">
        <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
        <a href="/astarrent/modules/00_auth/logout.php" class="btn btn-astar px-4 fw-bold" style="border-radius: 8px; background-color: #1d4197; color: white;">Ya, Logout</a>
      </div>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- 2. MODAL KONFIRMASI HAPUS (DELETE GLOBAL) -->
<!-- ============================================== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border: none; border-radius: 15px;">
      <div class="modal-header bg-danger text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="modal-title fw-bold"><i class="bi bi-trash-fill me-2"></i> Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <i class="bi bi-x-octagon-fill text-danger mb-3" style="font-size: 3rem;"></i>
        <h5 class="text-dark fw-bold mb-2">Hapus Data Ini?</h5>
        <p class="text-secondary mb-0">Data yang dihapus tidak dapat dikembalikan lagi!</p>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4">
        <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
        <a href="#" id="btnConfirmDelete" class="btn btn-danger px-4 fw-bold">Ya, Hapus Data</a>
      </div>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- 3. MODAL ALERT NOTIFIKASI (SUKSES/GAGAL DARI PHP) -->
<!-- ============================================== -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border: none; border-radius: 15px;">
      <div class="modal-header" id="alertHeader" style="color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="modal-title fw-bold" id="alertTitle">Notifikasi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <i id="alertIcon" class="mb-3" style="font-size: 3rem;"></i>
        <h5 class="text-dark fw-bold mb-2" id="alertMessage"></h5>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4">
        <button type="button" class="btn px-5 fw-bold text-white" data-bs-dismiss="modal" id="alertBtnClose" style="background-color: #1d4197;">OK, Mengerti</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- 4. MODAL GLOBAL UNTUK BACA TEKS PANJANG (DETAIL)-->
<!-- ============================================== -->
<div class="modal fade" id="detailTeksModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background-color: #1d4197; color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="modal-title fw-bold"><i class="bi bi-card-text me-2"></i>Detail Informasi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Teks panjangnya akan disuntikkan ke dalam tag P ini oleh JavaScript -->
        <p id="tempatTeksDetail" class="text-dark mb-0" style="text-align: justify; line-height: 1.6; font-size: 0.95rem;"></p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px; width: 100%;">Tutup</button>
      </div>
    </div>
  </div>
</div>
<!-- ============================================== -->
<!-- 5. MODAL PENOLAKAN PEMINJAMAN (NATIVE FORM)    -->
<!-- ============================================== -->
<div class="modal fade" id="modalTolakPjm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header text-white bg-danger" style="border-radius: 15px 15px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-x-circle-fill me-2"></i> Konfirmasi Penolakan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formTolak" method="POST" action="">
        <div class="modal-body p-4">
          <input type="hidden" name="id_peminjaman" id="input_id_peminjaman">

          <div class="alert alert-warning py-2 mb-3" style="font-size: 13px;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Alasan ini akan terlihat di Dashboard Mahasiswa.
          </div>
          <label class="form-label text-danger fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
          <textarea name="alasan_tolak" class="form-control border-danger" rows="3" required placeholder="Contoh: Barang sedang diservis..."></textarea>
        </div>
        <div class="modal-footer justify-content-center border-0 pb-4 px-4 gap-3">
          <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
          <!-- Native Submit -->
          <button type="submit" name="submit_tolak" class="btn btn-danger fw-bold px-4" style="border-radius: 8px;">Kirim Penolakan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Hanya mengatur Form Action dan Munculin Modal
  let modalTolak;

  function bukaModalTolak(idPeminjaman, urlEndpoint) {
    if (!modalTolak) modalTolak = new bootstrap.Modal(document.getElementById('modalTolakPjm'));
    document.getElementById('formTolak').action = urlEndpoint;
    document.getElementById('input_id_peminjaman').value = idPeminjaman;
    modalTolak.show();
  }

  let modalKategori;
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("custom-dropdown-item")) {
      let container = e.target.closest(".custom-dropdown-container");
      if (container) {
        let inputHidden = container.querySelector("input[name='kategori']");
        if (inputHidden && inputHidden.value === 'kategori_baru') {
          if (!modalKategori) {
            modalKategori = new bootstrap.Modal(document.getElementById('modalKategoriAset'));
            document.getElementById('modalKategoriAset').addEventListener('hidden.bs.modal', function() {
              if (inputHidden.value === 'kategori_baru') {
                inputHidden.value = '';
                document.getElementById('text_' + inputHidden.id.replace('input_', '')).innerText = '-- Pilih --';
              }
            });
          }
          modalKategori.show();
        }
      }
    }
  });
</script>

<!-- ============================================== -->
<!-- 6. MODAL KATEGORI DRAFT PENGADAAN (NATIVE FORM)-->
<!-- ============================================== -->
<?php $base_url = "/" . explode("/", $_SERVER['REQUEST_URI'])[1]; ?>
<div class="modal fade" id="modalKategoriAset" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header text-white" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Kategori Aset Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Menggunakan Absolute URL yang dinamis beradaptasi dengan nama folder lokal XAMPP Anda -->
      <form method="POST" action="<?= $base_url ?>/modules/04_rantai_pasok/master_kategori/tendik/create/create_aset.php">
        <div class="modal-body p-4">
          <div class="alert alert-warning py-2 mb-3" style="font-size: 13px;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Kategori ini akan berstatus <b>Draft</b> sampai disetujui Finance.
          </div>
          <label class="form-label text-astar fw-bold">Nama Kategori Baru <span class="text-danger">*</span></label>
          <input type="text" name="nama_kategori" class="form-control border-primary" required placeholder="Contoh: Kamera DSLR, Drone, dll">
        </div>
        <div class="modal-footer justify-content-center border-0 pb-4 px-4 gap-3">
          <button type="button" class="btn btn-light fw-bold px-4 text-secondary border" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
          <!-- Native Submit -->
          <button type="submit" name="submit_kategori_draft" class="btn btn-astar fw-bold px-4" style="border-radius: 8px;">Simpan & Gunakan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// ===================================================================================
// PENGUNCI UI KATEGORI (HANYA MUNCUL JIKA SESSION DRAFT AKTIF)
// ===================================================================================
if (isset($_SESSION['draft_kategori_id'])):
?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      let inputHidden = document.querySelector("input[name='kategori']");
      if (inputHidden) {
        let dropdownContainer = inputHidden.closest('.custom-dropdown-container');
        let selectedBox = dropdownContainer.querySelector('.custom-dropdown-selected');

        dropdownContainer.style.pointerEvents = "none";
        selectedBox.style.backgroundColor = "#e9ecef";
        selectedBox.style.borderColor = "#c6cacc";
        selectedBox.style.color = "#6c757d";

        let textPeringatan = document.createElement('small');
        textPeringatan.className = "text-danger mt-1 d-block fw-bold";
        textPeringatan.innerHTML = "<i class='bi bi-lock-fill'></i> Terkunci. Klik 'Batal Draft' di atas jika ingin mengganti.";
        dropdownContainer.parentNode.appendChild(textPeringatan);
      }
    });
  </script>
<?php endif; ?>

<!-- ============================================== -->
<!-- 7. MODAL PELUNASAN SANKSI (GLOBAL)             -->
<!-- ============================================== -->
<div class="modal fade" id="modalLunas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Konfirmasi Pelunasan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST" id="formLunas">
        <div class="modal-body text-center p-4">
          <input type="hidden" name="nim" id="lunas_nim">
          <i class="bi bi-patch-check-fill text-success mb-3" style="font-size: 3rem;"></i>
          <h5 class="text-dark fw-bold mb-2">Selesaikan Sanksi?</h5>
          <p class="text-secondary mb-0">Hapus kewajiban milik <b id="lunas_nama" class="text-dark"></b>?</p>
        </div>
        <div class="modal-footer justify-content-center border-0 pb-4">
          <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="submit_lunas" class="btn btn-success fw-bold px-4">Ya, Proses Lunas</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // JS GLOBAL UNTUK MODAL PELUNASAN SANKSI
  function bukaModalLunas(nim, nama, urlEndpoint) {
    document.getElementById('lunas_nim').value = nim;
    document.getElementById('lunas_nama').innerText = nama;
    document.getElementById('formLunas').action = urlEndpoint;
    new bootstrap.Modal(document.getElementById('modalLunas')).show();
  }
</script>

<!-- SCRIPT BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script Pemanggil Modal Detail -->
<script>
  function lihatDetailTeks(teksLengkap) {
    // Masukkan teks ke dalam modal
    document.getElementById('tempatTeksDetail').innerText = teksLengkap;
    // Munculkan modal
    var myModal = new bootstrap.Modal(document.getElementById('detailTeksModal'));
    myModal.show();
  }
</script>

<!-- SCRIPT LOGIKA POP-UP GLOBAL -->
<script>
  // Fungsi untuk memanggil Modal Delete secara dinamis
  function konfirmasiHapus(urlDelete) {
    document.getElementById('btnConfirmDelete').href = urlDelete;
    var myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    myModal.show();
  }
</script>

<?php
// MENGAKTIFKAN MODAL ALERT JIKA ADA SESSION DARI PHP
if (isset($_SESSION['notif_pesan'])):
  $tipe = $_SESSION['notif_tipe'];
  $pesan = $_SESSION['notif_pesan'];

  $bgColor = ($tipe == 'success') ? '#198754' : '#dc3545'; // Hijau atau Merah
  $iconClass = ($tipe == 'success') ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger';
  $title = ($tipe == 'success') ? 'Berhasil!' : 'Terjadi Kesalahan!';
?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById('alertHeader').style.backgroundColor = '<?= $bgColor ?>';
      document.getElementById('alertTitle').innerText = '<?= $title ?>';
      document.getElementById('alertIcon').className = '<?= $iconClass ?>';
      document.getElementById('alertMessage').innerText = '<?= $pesan ?>';

      var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
      alertModal.show();
    });
  </script>
<?php
  // Hapus session setelah ditampilkan biar gak muncul terus
  unset($_SESSION['notif_tipe']);
  unset($_SESSION['notif_pesan']);
endif;
?>

<!-- Inisialisasi DataTables Global secara Otomatis (Dengan Custom Dropdown) -->
<script>
  $(document).ready(function() {
    if ($.fn.DataTable) {
      $('.datatable-astar').DataTable({
        "language": {
          "lengthMenu": "Tampilkan _MENU_ baris",
          "search": "Cari:",
          "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
          "infoFiltered": "(difilter dari _MAX_ total data)",
          "zeroRecords": "Tidak ada data yang cocok ditemukan",
          "emptyTable": "Tidak ada data tersedia di tabel ini",
          "paginate": {
            "first": "Pertama",
            "last": "Terakhir",
            "next": "Berikutnya",
            "previous": "Sebelumnya"
          }
        },
        "responsive": true,
        "pageLength": 10,
        "lengthMenu": [
          [5, 10, 25, 50, -1],
          [5, 10, 25, 50, "Semua"]
        ],

        // =========================================================================
        // SIHIR: MENYULAP DROPDOWN DATATABLES MENJADI TEMA ASTARRENT
        // =========================================================================
        "initComplete": function(settings, json) {
          let api = this.api();
          let wrapper = $(api.table().container());
          let dtSelect = wrapper.find('.dataTables_length select');

          if (dtSelect.length > 0) {
            // 1. Sembunyikan select bawaan browser (yang jelek di Mac/Safari)
            dtSelect.hide();

            let idUnik = 'dt_drop_' + Math.floor(Math.random() * 9000 + 1000);
            let currentVal = dtSelect.val();
            let currentText = dtSelect.find('option:selected').text();

            // 2. Bangun HTML Custom Dropdown.
            // PERBAIKAN: Menambahkan event.preventDefault() dan stopPropagation() agar <label> DataTables tidak mengganggu klik kita!
            let html = `
                <div class="custom-dropdown-container mx-2" id="container_${idUnik}" style="display: inline-block; width: 85px; vertical-align: middle;">
                    <div class="custom-dropdown-selected" onclick="event.preventDefault(); event.stopPropagation(); toggleDropdown('${idUnik}')" style="padding: 4px 10px; height: 32px; display: flex; align-items: center; justify-content: space-between;">
                        <span id="text_${idUnik}" style="font-weight:bold;">${currentText}</span>
                        <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
                    </div>
                    <div class="custom-dropdown-options shadow text-start" id="options_${idUnik}" style="width: 100px;">
                `;

            // 3. Looping opsi angka dari DataTables bawaan
            dtSelect.find('option').each(function() {
              let val = $(this).val();
              let txt = $(this).text();
              let activeClass = (val == currentVal) ? 'active' : '';
              // PERBAIKAN: Tambahkan preventDefault di pilihan dropdown-nya juga
              html += `<div class="custom-dropdown-item ${activeClass}" onclick="event.preventDefault(); event.stopPropagation(); selectDTLength('${idUnik}', '${val}', '${txt}', this)">${txt}</div>`;
            });

            html += `</div></div>`;

            // 4. Suntikkan HTML kita ke sebelah select asli
            dtSelect.after(html);

            // 5. Simpan referensi select asli agar bisa dipicu saat diklik
            $('#container_' + idUnik).data('nativeSelect', dtSelect);
          }
        }
      });
    }
  });

  // FUNGSI KHUSUS UNTUK MENANGANI KLIK PADA DROPDOWN DATATABLES
  function selectDTLength(id, nilai, label, el) {
    // Ubah teks yang tampil
    document.getElementById('text_' + id).innerText = label;

    // Tutup opsi
    document.getElementById('options_' + id).style.display = 'none';

    // Pindahkan class active (Warna biru tebal)
    let opsi = document.getElementById('options_' + id).children;
    for (let i = 0; i < opsi.length; i++) {
      opsi[i].classList.remove('active');
    }
    el.classList.add('active');

    // MAGIC: Trigger perubahan pada select bawaan DataTables agar tabelnya ikut berubah!
    let nativeSelect = $('#container_' + id).data('nativeSelect');
    nativeSelect.val(nilai).trigger('change');
  }
</script>

<script>
  // ===================================================================================
  // JS GLOBAL: FUNGSI CETAK PDF (ISOLATED WINDOW & PURE HTML EXTRACTION)
  // ===================================================================================
  function cetakPDF() {
    let tableEl = document.getElementById('tableLaporan');
    if (!tableEl) {
      window.print();
      return;
    }

    // 1. Cek DataTables dan Buka Semua Baris
    let isDT = (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#tableLaporan'));
    let dtTable, originalLen;

    if (isDT) {
      dtTable = $('#tableLaporan').DataTable();
      originalLen = dtTable.page.len();
      dtTable.page.len(-1).draw(); // Tampilkan seluruh data
    }

    // 2. Jeda agar DataTables selesai render
    setTimeout(function() {

      // 3. EKSTRAKSI MURNI (Membuang "racun" CSS dari DataTables)
      let theadHTML = tableEl.querySelector('thead') ? tableEl.querySelector('thead').innerHTML : '';
      let tbodyHTML = tableEl.querySelector('tbody') ? tableEl.querySelector('tbody').innerHTML : '';
      let tfootHTML = tableEl.querySelector('tfoot') ? tableEl.querySelector('tfoot').innerHTML : '';

      // Hapus panah Sorting DataTables (▲/▼) menggunakan Regex
      theadHTML = theadHTML.replace(/<span class="dt-column-order">.*?<\/span>/gi, '');
      theadHTML = theadHTML.replace(/▲|▼|↑|↓/gi, '');

      // Rakit kembali menjadi tabel yang suci
      let cleanTable = '<table>' +
        '<thead>' + theadHTML + '</thead>' +
        '<tbody>' + tbodyHTML + '</tbody>' +
        (tfootHTML ? '<tfoot>' + tfootHTML + '</tfoot>' : '') +
        '</table>';

      let printHeader = document.querySelector('.print-header') ? document.querySelector('.print-header').innerHTML : '<h3>LAPORAN ASTARRENT</h3>';

      // 4. Buka Tab Virtual Print
      let printWindow = window.open('', '_blank', 'width=1000,height=800');

      printWindow.document.write('<!DOCTYPE html><html lang="id"><head><title>Print Laporan ASTARrent</title>');
      printWindow.document.write('<style>');

      // ATURAN KERTAS & MENGHILANGKAN URL BROWSER
      printWindow.document.write('@page { size: A4 portrait; margin: 15mm; }');
      printWindow.document.write('body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }');

      // ATURAN HEADER LAPORAN
      printWindow.document.write('.print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }');
      printWindow.document.write('h3 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; font-weight: bold; color: #000;}');
      printWindow.document.write('.print-date { font-size: 10px; color: #555; margin-top: 5px;}');

      // ATURAN TABEL ANTI TERPOTONG & REPEAT HEADER
      printWindow.document.write('table { width: 100%; border-collapse: collapse; page-break-inside: auto; margin-top: 10px; }');
      printWindow.document.write('tr { page-break-inside: avoid !important; page-break-after: auto !important; }');
      printWindow.document.write('thead { display: table-header-group !important; }');
      printWindow.document.write('tfoot { display: table-footer-group !important; }');
      printWindow.document.write('th, td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; word-wrap: break-word; }');
      printWindow.document.write('th { background-color: #f0f0f0 !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');

      // ATURAN TYPOGRAPHY & BADGE
      printWindow.document.write('.text-start { text-align: left !important; }');
      printWindow.document.write('.text-end { text-align: right !important; }');
      printWindow.document.write('.text-danger { color: #dc3545 !important; }');
      printWindow.document.write('.text-success { color: #198754 !important; }');
      printWindow.document.write('.text-primary { color: #0d6efd !important; }');
      printWindow.document.write('.text-secondary, .text-muted { color: #555 !important; }');
      printWindow.document.write('.fw-bold, b { font-weight: bold !important; }');
      printWindow.document.write('code { font-family: monospace; font-size: 12px; }');
      printWindow.document.write('.badge { border: 1px solid #333; padding: 2px 5px; border-radius: 4px; font-weight: bold; display: inline-block; font-size: 9px; margin: 2px;}');
      printWindow.document.write('small { font-size: 9px; color: #555; display: block; margin-top: 2px; }');

      printWindow.document.write('</style></head><body>');

      // 5. Cetak ke Layar Virtual
      printWindow.document.write('<div class="print-header">' + printHeader + '</div>');
      printWindow.document.write(cleanTable);

      printWindow.document.write('</body></html>');
      printWindow.document.close();

      // 6. Tunggu rendering OS lalu Print
      setTimeout(function() {
        printWindow.focus();
        printWindow.print();
        printWindow.close();

        // Kembalikan DataTables ke halaman 1
        if (isDT) dtTable.page.len(originalLen).draw();
      }, 800); // Naikkan jeda 800ms agar browser Mac punya waktu memproses CSS

    }, 500);
  }
</script>

</body>

</html>
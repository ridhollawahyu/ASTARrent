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
<!-- 5. MODAL PENOLAKAN PEMINJAMAN (GLOBAL)         -->
<!-- ============================================== -->
<div class="modal fade" id="modalTolakPjm" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header text-white bg-danger" style="border-radius: 15px 15px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-x-circle-fill me-2"></i> Konfirmasi Penolakan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="input_id_peminjaman">
        <input type="hidden" id="input_url_endpoint">
        <div class="alert alert-warning py-2 mb-3" style="font-size: 13px;">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Alasan ini akan langsung terlihat di Dashboard Mahasiswa.
        </div>
        <label class="form-label text-danger fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
        <textarea id="input_alasan_tolak" class="form-control border-danger" rows="3" placeholder="Contoh: Barang sedang diservis, kegiatan tidak diizinkan, dll..."></textarea>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4 px-4 gap-3">
        <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
        <button type="button" class="btn btn-danger fw-bold px-4" onclick="kirimTolakAJAX()" style="border-radius: 8px;">Kirim Penolakan</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- 6. MODAL KATEGORI DRAFT PENGADAAN (GLOBAL)     -->
<!-- ============================================== -->
<div class="modal fade" id="modalKategoriAset" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header text-white" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Kategori Aset Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-warning py-2 mb-3" style="font-size: 13px;">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Kategori ini akan berstatus <b>Draft</b> sampai disetujui Finance.
        </div>
        <label class="form-label text-astar fw-bold">Nama Kategori Baru <span class="text-danger">*</span></label>
        <input type="text" id="input_kategori_baru" class="form-control border-primary" placeholder="Contoh: Kamera DSLR, Drone, dll">
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4 px-4 gap-3">
        <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
        <button type="button" class="btn btn-astar fw-bold px-4" onclick="simpanKategoriAJAX()" style="border-radius: 8px;">Simpan & Gunakan</button>
      </div>
    </div>
  </div>
</div>

<script>
  // ===================================================================================
  // FUNGSI ALERT GLOBAL TINGKAT DEWA (BISA RE-OPEN MODAL APAPUN SECARA DINAMIS)
  // ===================================================================================
  function tampilkanAlertJS(tipe, pesan, modalToReopen = null) {
    let bgColor = (tipe === 'success') ? '#198754' : '#dc3545';
    let iconClass = (tipe === 'success') ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger';

    document.getElementById('alertHeader').style.backgroundColor = bgColor;
    document.getElementById('alertTitle').innerText = (tipe === 'success') ? 'Berhasil!' : 'Terjadi Kesalahan!';
    document.getElementById('alertIcon').className = iconClass;
    document.getElementById('alertMessage').innerText = pesan;

    let alertModalEl = document.getElementById('alertModal');
    let alertModal = new bootstrap.Modal(alertModalEl);

    // Fitur Cerdas: Re-open modal asal jika ditutup
    if (modalToReopen) {
      const onAlertHidden = function() {
        setTimeout(() => modalToReopen.show(), 150);
        alertModalEl.removeEventListener('hidden.bs.modal', onAlertHidden);
      };
      alertModalEl.addEventListener('hidden.bs.modal', onAlertHidden);
    }
    alertModal.show();
  }

  // ===================================================================================
  // JS: LOGIKA MODAL TOLAK PEMINJAMAN
  // ===================================================================================
  let modalTolak;

  function bukaModalTolak(idPeminjaman, urlEndpoint) {
    if (!modalTolak) modalTolak = new bootstrap.Modal(document.getElementById('modalTolakPjm'));
    document.getElementById('input_id_peminjaman').value = idPeminjaman;
    document.getElementById('input_url_endpoint').value = urlEndpoint;
    document.getElementById('input_alasan_tolak').value = '';
    modalTolak.show();
  }

  function kirimTolakAJAX() {
    let id_pjm = document.getElementById('input_id_peminjaman').value;
    let url_endpoint = document.getElementById('input_url_endpoint').value;
    let alasan = document.getElementById('input_alasan_tolak').value;

    if (alasan.trim() === '') {
      modalTolak.hide();
      setTimeout(() => tampilkanAlertJS('error', 'Alasan penolakan tidak boleh kosong!', modalTolak), 400);
      return;
    }

    let formData = new FormData();
    formData.append('aksi', 'tolak');
    formData.append('id_peminjaman', id_pjm);
    formData.append('alasan_tolak', alasan);

    fetch(url_endpoint, {
        method: 'POST',
        body: formData
      })
      .then(async response => {
        const text = await response.text();
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error("Respons server rusak.");
        }
      })
      .then(data => {
        if (data.status === 'success') {
          modalTolak.hide();
          window.location.reload();
        } else {
          modalTolak.hide();
          setTimeout(() => tampilkanAlertJS('error', data.pesan, modalTolak), 400);
        }
      })
      .catch(error => {
        modalTolak.hide();
        setTimeout(() => tampilkanAlertJS('error', error.message, modalTolak), 400);
      });
  }

  // ===================================================================================
  // JS: LOGIKA KATEGORI DRAFT PENGADAAN
  // ===================================================================================
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
          document.getElementById('input_kategori_baru').value = '';
        }
      }
    }
  });

  function simpanKategoriAJAX() {
    let namaKategori = document.getElementById('input_kategori_baru').value;
    if (namaKategori.trim() === '') {
      modalKategori.hide();
      setTimeout(() => tampilkanAlertJS('error', 'Nama Kategori tidak boleh kosong!', modalKategori), 400);
      return;
    }

    let formData = new FormData();
    formData.append('nama_kategori', namaKategori);

    fetch('../../master_kategori/create/create_aset.php', {
        method: 'POST',
        body: formData
      })
      .then(async response => {
        const text = await response.text();
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error("Respons server rusak.");
        }
      })
      .then(data => {
        if (data.status === 'success') {
          modalKategori.hide();
          window.location.reload();
        } else {
          modalKategori.hide();
          setTimeout(() => tampilkanAlertJS('error', data.pesan, modalKategori), 400);
        }
      })
      .catch(error => {
        modalKategori.hide();
        setTimeout(() => tampilkanAlertJS('error', error.message, modalKategori), 400);
      });
  }
</script>

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

</body>

</html>
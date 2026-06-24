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
<!-- SCRIPT BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
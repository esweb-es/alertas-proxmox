    </div> <!-- End of body-wrapper (opened in topbar) -->
    </div> <!-- End of page-wrapper (opened in topbar) -->
  </div> <!-- End of main-wrapper (opened in header) -->
  
  <div class="dark-transparent sidebartoggler"></div>
  
  <!-- Import Js Files -->
  <script src="<?= base_url('assets/js/vendor.min.js') ?>"></script>
  <script src="<?= base_url('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= base_url('assets/libs/simplebar/dist/simplebar.min.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/app.dark.init.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/theme.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/app.min.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/sidebarmenu.js') ?>"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Configuración global de Toasts
    const Toast = Swal.mixin({
      toast: true,
      position: 'bottom',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    // Mostrar mensaje de éxito
    <?php if (session('message') !== null) : ?>
      Toast.fire({
        icon: 'success',
        title: <?= json_encode(session('message')) ?>
      });
    <?php endif ?>

    // Mostrar mensaje de error simple
    <?php if (session('error') !== null) : ?>
      Toast.fire({
        icon: 'error',
        title: <?= json_encode(session('error')) ?>
      });
    <?php endif ?>

    // Mostrar múltiples errores de validación
    <?php if (session('errors') !== null) : ?>
      <?php 
        $allErrors = "";
        foreach(session('errors') as $e) { $allErrors .= "• " . $e . "<br>"; }
      ?>
      Toast.fire({
        icon: 'error',
        title: '¡Revisa los errores!',
        html: <?= json_encode('<div class="text-start fs-2">' . $allErrors . '</div>') ?>
      });
    <?php endif ?>

    // Helper global para acciones POST con confirmación (Borrado, Resolver, etc.)
    function confirmAction(url, title, text, icon = 'warning', confirmText = 'Sí, proceder', confirmColor = '#fa896b') {
      Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#5d87ff',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = url;
          
          const csrf = document.createElement('input');
          csrf.type = 'hidden';
          csrf.name = '<?= csrf_token() ?>';
          csrf.value = '<?= csrf_hash() ?>';
          
          form.appendChild(csrf);
          document.body.appendChild(form);
          form.submit();
        }
      })
    }

    // Helper específico para borrados
    function confirmDelete(url) {
      confirmAction(url, '¿Eliminar permanentemente?', 'Esta acción borrará el registro de forma definitiva.', 'warning', 'Sí, eliminar', '#fa896b');
    }
    // Fix global: dropdowns dentro de table-responsive no se cortan
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el, {
          popperConfig: { strategy: 'fixed' }
        });
      });
    });
  </script>
</body>

</html>

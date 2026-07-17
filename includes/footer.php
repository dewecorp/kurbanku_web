  </div><!-- end main content -->
</main><!-- end main area -->

<!-- NOTIFIKASI TOAST -->
<div class="fixed top-5 right-5 z-[99999] flex flex-col gap-2 pointer-events-none" id="toast-container"></div>

</div><!-- end qurbanku-app-root -->

<script>
const sessionUser = <?= json_encode($_SESSION['user'] ?? null) ?>;
window.__bootUser = sessionUser;
</script>
<script src="../assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
<script>
try {
  if (window.lucide && typeof lucide.createIcons === 'function') lucide.createIcons();
} catch (e) { console.warn('lucide icons skip', e); }
try {
  if (typeof initApp === 'function') {
    initApp(window.__bootUser);
  } else {
    console.error('app.js gagal dimuat');
  }
} catch (e) { console.error('initApp error', e); }
</script>
</body>
</html>

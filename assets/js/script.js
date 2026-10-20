document.addEventListener('DOMContentLoaded', () => {
  // Password visibility controls.
  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? '◌' : '◉';
      button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  // Responsive navigation drawer.
  const hamburger = document.querySelector('[data-hamb]');
  const sidebar = document.querySelector('.sidebar');
  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => {
      const open = sidebar.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        sidebar.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
      }
    });
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
      sidebar.classList.remove('open');
      hamburger.setAttribute('aria-expanded', 'false');
    }));
  }

  // Theme selection is kept on this browser/device and applies to all pages.
  const root = document.documentElement;
  const themeButtons = document.querySelectorAll('[data-theme-toggle]');
  const updateThemeButton = () => {
    const dark = root.dataset.theme === 'dark';
    themeButtons.forEach((button) => {
      const icon = button.querySelector('[data-theme-icon]');
      const label = button.querySelector('[data-theme-label]');
      if (icon) icon.textContent = dark ? '☀' : '☾';
      if (label) label.textContent = dark ? 'Mode cerah' : 'Mode gelap';
      button.title = dark ? 'Aktifkan mode cerah' : 'Aktifkan mode gelap';
      button.setAttribute('aria-label', button.title);
      button.setAttribute('aria-pressed', String(dark));
    });
    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.setAttribute('content', dark ? '#0e1420' : '#f5f7fb');
  };
  updateThemeButton();
  themeButtons.forEach((button) => button.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('alatqu-theme', next); } catch (error) { /* Theme still applies for this page. */ }
    updateThemeButton();
  }));

  // Per-account profile photo saved in local browser storage, with resize to limit storage use.
  const userId = document.body.dataset.userId || 'guest';
  const photoKey = `alatqu-profile-photo:${userId}`;
  const photoInput = document.querySelector('[data-profile-photo-input]');
  const removePhotoButton = document.querySelector('[data-remove-profile-photo]');
  const photoMessage = document.querySelector('[data-profile-photo-message]');
  const avatarImages = Array.from(document.querySelectorAll('.js-profile-avatar'));
  const avatarFallbacks = Array.from(document.querySelectorAll('.js-avatar-fallback'));

  const showMessage = (message, isError = false) => {
    if (!photoMessage) return;
    photoMessage.textContent = message;
    photoMessage.style.color = isError ? 'var(--danger)' : 'var(--success)';
  };
  const applyPhoto = (photo) => {
    avatarImages.forEach((image) => {
      if (photo) {
        image.src = photo;
        image.hidden = false;
      } else {
        image.removeAttribute('src');
        image.hidden = true;
      }
    });
    avatarFallbacks.forEach((fallback) => { fallback.hidden = Boolean(photo); });
    if (removePhotoButton) removePhotoButton.hidden = !photo;
  };
  try {
    const savedPhoto = localStorage.getItem(photoKey);
    const validPhoto = typeof savedPhoto === 'string' && savedPhoto.startsWith('data:image/jpeg;base64,') && savedPhoto.length < 700000;
    applyPhoto(validPhoto ? savedPhoto : null);
  } catch (error) { applyPhoto(null); }

  if (photoInput) {
    photoInput.addEventListener('change', () => {
      const file = photoInput.files && photoInput.files[0];
      if (!file) return;
      if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        showMessage('Pilih file JPG, PNG, atau WebP.', true);
        photoInput.value = '';
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showMessage('Ukuran foto maksimal 5 MB.', true);
        photoInput.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onerror = () => showMessage('Foto tidak dapat dibaca. Coba pilih file lain.', true);
      reader.onload = () => {
        const image = new Image();
        image.onerror = () => showMessage('File gambar tidak valid.', true);
        image.onload = () => {
          const maxDimension = 480;
          const scale = Math.min(1, maxDimension / Math.max(image.naturalWidth, image.naturalHeight));
          const canvas = document.createElement('canvas');
          canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
          canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
          const context = canvas.getContext('2d');
          if (!context) {
            showMessage('Browser ini tidak mendukung pemrosesan foto.', true);
            return;
          }
          context.drawImage(image, 0, 0, canvas.width, canvas.height);
          try {
            const optimizedPhoto = canvas.toDataURL('image/jpeg', 0.82);
            localStorage.setItem(photoKey, optimizedPhoto);
            applyPhoto(optimizedPhoto);
            showMessage('Foto profil berhasil diperbarui.');
          } catch (error) {
            showMessage('Penyimpanan browser penuh. Hapus data situs atau gunakan foto lebih kecil.', true);
          }
        };
        image.src = String(reader.result || '');
      };
      reader.readAsDataURL(file);
    });
  }
  if (removePhotoButton) {
    removePhotoButton.addEventListener('click', () => {
      try { localStorage.removeItem(photoKey); } catch (error) { /* Avatar can still be reset visually. */ }
      applyPhoto(null);
      if (photoInput) photoInput.value = '';
      showMessage('Foto profil dihapus.');
    });
  }

  // Logout confirmation modal.
  document.querySelectorAll('[data-logout]').forEach((link) => link.addEventListener('click', (event) => {
    const modal = document.getElementById('logoutModal');
    if (!modal) return;
    event.preventDefault();
    modal.dataset.logoutUrl = link.href;
    modal.classList.add('show');
  }));
  document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => {
    button.closest('.modal')?.classList.remove('show');
  }));
  document.querySelectorAll('.modal').forEach((modal) => modal.addEventListener('click', (event) => {
    if (event.target === modal) modal.classList.remove('show');
  }));
});

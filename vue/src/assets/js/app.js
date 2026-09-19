/* js/app.js */
document.addEventListener('DOMContentLoaded', () => {
  // --- Переключение темы (Light / Dark) ---
  const themeToggleBtns = document.querySelectorAll('.theme-toggle');

  const applyTheme = (theme) => {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  };

  // Инициализация темы
  const savedTheme = localStorage.getItem('theme') ||
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  applyTheme(savedTheme);

  themeToggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const isDark = document.documentElement.classList.contains('dark');
      applyTheme(isDark ? 'light' : 'dark');
    });
  });

  // --- Копирование ссылки в буфер обмена ---
  const copyButtons = document.querySelectorAll('[data-copy]');
  copyButtons.forEach(button => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-copy');
      const input = document.getElementById(targetId);

      if (input) {
        navigator.clipboard.writeText(input.value || input.innerText).then(() => {
          const tooltip = button.querySelector('.tooltip');
          if (tooltip) {
            tooltip.classList.add('show');
            setTimeout(() => tooltip.classList.remove('show'), 2000);
          }
        });
      }
    });
  });

  // --- Переключение модальных окон (для Login/Register на главной) ---
  const modalTriggers = document.querySelectorAll('[data-modal-target]');
  const modalCloses = document.querySelectorAll('[data-modal-close]');

  modalTriggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      const target = document.getElementById(trigger.getAttribute('data-modal-target'));
      if (target) target.classList.remove('hidden');
    });
  });

  modalCloses.forEach(closeBtn => {
    closeBtn.addEventListener('click', () => {
      const modal = closeBtn.closest('.modal');
      if (modal) modal.classList.add('hidden');
    });
  });
});

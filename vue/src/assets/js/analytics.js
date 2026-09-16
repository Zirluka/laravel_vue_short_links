/* js/analytics.js */
document.addEventListener('DOMContentLoaded', () => {
  // Проверка наличия Chart.js и элементов
  if (typeof Chart === 'undefined') return;

  const isDarkMode = document.documentElement.classList.contains('dark');
  const textColor = isDarkMode ? '#9CA3AF' : '#4B5563';
  const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

  // 1. График кликов по дням (Line Chart)
  const clicksCtx = document.getElementById('clicksChart')?.getContext('2d');
  if (clicksCtx) {
    new Chart(clicksCtx, {
      type: 'line',
      data: {
        labels: ['10 Сен', '11 Сен', '12 Сен', '13 Сен', '14 Сен', '15 Сен', '16 Сен'],
        datasets: [{
          label: 'Клики',
          data: [120, 190, 300, 250, 420, 380, 510],
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37, 99, 235, 0.1)',
          fill: true,
          tension: 0.3,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: textColor }, grid: { color: gridColor } },
          y: { ticks: { color: textColor }, grid: { color: gridColor } }
        }
      }
    });
  }

  // 2. График устройств (Doughnut Chart)
  const devicesCtx = document.getElementById('devicesChart')?.getContext('2d');
  if (devicesCtx) {
    new Chart(devicesCtx, {
      type: 'doughnut',
      data: {
        labels: ['Desktop', 'Mobile', 'Tablet'],
        datasets: [{
          data: [62, 31, 7],
          backgroundColor: ['#2563EB', '#3B82F6', '#93C5FD'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { color: textColor, padding: 15 } }
        }
      }
    });
  }
});

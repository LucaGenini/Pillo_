document.addEventListener('DOMContentLoaded', () => {
  fetch('unload.php')
    .then(response => response.json())
    .then(data => {
      updateNaechsteEinnahme(data.naechste);
      updateFachStatus(data.fach_status);
      renderMatrixChart(data.verlauf);
      renderBarChart(data.monatsstatistik);
    })
    .catch(error => {
      console.error('🚨 Fehler beim Datenabruf:', error);
    });

  // Umschalter für Ansicht
  document.getElementById('toggle-view')?.addEventListener('click', toggleView);
});

function updateNaechsteEinnahme(naechste) {
  const container = document.getElementById('naechste-einnahme');
  container.textContent = `${naechste.uhrzeit} – ${naechste.fach} – ${naechste.medikament}`;
}

function updateFachStatus(statusArray) {
  const container = document.getElementById('einnahme-status');
  container.innerHTML = '';
  statusArray.forEach(status => {
    const div = document.createElement('div');
    div.className = `fach ${status.statusFarbe}`;
    div.innerHTML = `
      <p>${status.fach}</p>
      <p>${status.status}</p>
      <p>${status.zeit}</p>
    `;
    container.appendChild(div);
  });
}

let matrixChart, barChart;

function renderMatrixChart(verlaufData) {
  const ctx = document.getElementById('verlaufChart').getContext('2d');
  const days = ['MO', 'DI', 'MI', 'DO', 'FR', 'SA', 'SO'];

  const farben = {
    green: '#4caf50',
    yellow: '#ffeb3b',
    red: '#f44336',
    'no-med': '#ffffff',
    future: '#e0e0e0'
  };

  const statusLabels = {
    green: 'pünktlich',
    yellow: 'verspätet',
    red: 'nicht eingenommen',
    'no-med': 'nichts geplant',
    future: 'zukünftig'
  };

  const data = verlaufData.flatMap((fachObj, rowIndex) =>
    fachObj.wochentage.map((status, colIndex) => ({
      x: colIndex,
      y: rowIndex,
      v: status
    }))
  );

  matrixChart = new Chart(ctx, {
    type: 'matrix',
    data: {
      datasets: [{
        label: 'Einnahmen',
        data: data,
        backgroundColor: ctx => farben[ctx.raw.v] || '#ccc',
        borderColor: '#aaa',
        borderWidth: 1,
        width: () => 36,
        height: () => 36
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          type: 'linear',
          min: -0.5,
          max: 6.5,
          ticks: {
            callback: value => days[value] || '',
            stepSize: 1,
            font: { size: 14, weight: 'bold' },
            color: '#00005D'
          }
        },
        y: {
          type: 'linear',
          min: -0.5,
          max: verlaufData.length - 0.5,
          ticks: {
            callback: value => verlaufData[value]?.fach || '',
            font: { size: 14, weight: 'bold' },
            color: '#00005D'
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => {
              const fachObj = verlaufData[ctx.raw.y];
              const fachName = fachObj?.fach || `Fach ${ctx.raw.y + 1}`;
              const tag = days[ctx.raw.x];
              const status = statusLabels[ctx.raw.v] || ctx.raw.v;
              return `${tag} – ${fachName}\nStatus: ${status}`;
            }
          }
        }
      }
    }
  });
}

function renderBarChart(monatsstatistik) {
  const ctx = document.getElementById('monatsChart').getContext('2d');
  const labels = monatsstatistik.map(e => e.monat);
  const datenFach1 = monatsstatistik.map(e => e.fach_1);
  const datenFach2 = monatsstatistik.map(e => e.fach_2);

  barChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Fach 1',
          data: datenFach1,
          backgroundColor: '#533DF6'
        },
        {
          label: 'Fach 2',
          data: datenFach2,
          backgroundColor: '#3CF417'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          title: {
            display: true,
            text: 'Einnahmequote (%)'
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}%`
          }
        }
      }
    }
  });
}

function toggleView() {
  const verlaufBox = document.querySelector('.history-box');
  const statistikBox = document.querySelector('.statistik-box');

  verlaufBox.style.display = verlaufBox.style.display === 'none' ? 'block' : 'none';
  statistikBox.style.display = statistikBox.style.display === 'none' ? 'block' : 'none';
}

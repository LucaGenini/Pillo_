document.addEventListener("DOMContentLoaded", () => {
  fetch("unload.php")
    .then((response) => response.json())
    .then((data) => {
      console.log("📦 Daten empfangen:", data);
      updateNaechsteEinnahme(data.naechste);
      updateFachStatus(data.fach_status);
      renderMatrixChart(data.verlauf);
      updateMotivation(data.motivation);
      renderWochenStatistik(data.wochenstatistik);
      renderJahresStatistik(data.monatsstatistik);
    })
    .catch((error) => console.error("🚨 Fehler beim Datenabruf:", error));

  document.getElementById("btn-monat")?.addEventListener("click", () => switchStats("monat"));
  document.getElementById("btn-jahr")?.addEventListener("click", () => switchStats("jahr"));

  const popup = document.getElementById("popupForm");
  const openBtn = document.querySelector(".btn-primary");
  const closeBtn = popup.querySelector(".close-btn");

  openBtn?.addEventListener("click", () => popup.style.display = "block");
  closeBtn?.addEventListener("click", () => popup.style.display = "none");
  window.addEventListener("click", (e) => { if (e.target === popup) popup.style.display = "none"; });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && popup.style.display === "block") {
      popup.style.display = "none";
    }
  });
});

function switchStats(view) {
  document.getElementById("monatsansicht").style.display = view === "monat" ? "block" : "none";
  document.getElementById("jahresansicht").style.display = view === "jahr" ? "block" : "none";

  document.getElementById("btn-monat").classList.toggle("active", view === "monat");
  document.getElementById("btn-jahr").classList.toggle("active", view === "jahr");
}

function updateNaechsteEinnahme(naechste) {
  const container = document.getElementById("naechste-einnahme");
  container.textContent = naechste?.uhrzeit
    ? `${naechste.wochentag}, ${naechste.uhrzeit} – ${naechste.fach} – ${naechste.medikament}`
    : "Keine Daten verfügbar";
}

function updateFachStatus(statusArray) {
  const container = document.getElementById("einnahme-status");
  container.innerHTML = "";
  statusArray.forEach(status => {
    const div = document.createElement("div");
    div.className = `fach ${status.statusFarbe}`;
    div.innerHTML = `<p>${status.fach}</p><p>${status.status}</p><p>${status.zeit}</p>`;
    container.appendChild(div);
  });
}

function renderMatrixChart(verlaufData) {
  const ctx = document.getElementById("verlaufChart").getContext("2d");
  const days = ["MO", "DI", "MI", "DO", "FR", "SA", "SO"];
  const farben = { green: "#4caf50", yellow: "#ffeb3b", red: "#f44336", "no-med": "#ffffff", future: "#e0e0e0" };

  const data = verlaufData.flatMap((fachObj, rowIndex) =>
    fachObj.wochentage.map((status, colIndex) => ({ x: colIndex, y: rowIndex, v: status }))
  );

  new Chart(ctx, {
    type: "matrix",
    data: {
      datasets: [{
        label: "Einnahmen",
        data: data,
        backgroundColor: (ctx) => farben[ctx.raw.v] || "#ccc",
        borderColor: "#aaa",
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
          type: "linear",
          position: "bottom",
          min: -0.5,
          max: 6.5,
          ticks: {
            callback: value => days[value] || "",
            color: "#00005D"
          },
          grid: { display: false }
        },
        y: {
          type: "linear",
          position: "left",
          min: -0.5,
          max: verlaufData.length - 0.5,
          ticks: {
            callback: value => verlaufData[value]?.fach || "",
            color: "#00005D"
          },
          grid: { display: false }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: ctx => `${days[ctx.raw.x]} – ${verlaufData[ctx.raw.y]?.fach || ''}\nStatus: ${ctx.raw.v}`
          }
        }
      }
    }
  });
}

function updateMotivation(motivationList) {
  const list = document.getElementById("motivation-liste");
  if (!list) return;
  list.innerHTML = motivationList.map(m => `<li>${m}</li>`).join("");
}

function renderWochenStatistik(wochenData) {
  const fach1 = wochenData.map(e => ({ korrekt: e.fach1 }));
  const fach2 = wochenData.map(e => ({ korrekt: e.fach2 }));
  const labels = wochenData.map(e => e.kw);

  renderStackedChartDualFach("wochenStackedChart", "Wochenstatistik", fach1, fach2, labels);
}

function renderJahresStatistik(monatsData) {
  const fach1 = monatsData.map(e => ({ korrekt: e.fach1 }));
  const fach2 = monatsData.map(e => ({ korrekt: e.fach2 }));
  const labels = monatsData.map(e => e.monat);

  renderStackedChartDualFach("jahresStackedChart", "Jahresstatistik", fach1, fach2, labels);
}

function renderStackedChartDualFach(canvasId, title, fach1Data, fach2Data, labels) {
  const ctx = document.getElementById(canvasId).getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Fach 1 – Eingenommen",
          data: fach1Data.map(d => d.korrekt),
          backgroundColor: "#3CF417",
          stack: "fach1"
        },
        {
          label: "Fach 1 – Nicht eingenommen",
          data: fach1Data.map(d => 100 - d.korrekt),
          backgroundColor: "#F44336",
          stack: "fach1"
        },
        {
          label: "Fach 2 – Eingenommen",
          data: fach2Data.map(d => d.korrekt),
          backgroundColor: "#3CF417",
          stack: "fach2"
        },
        {
          label: "Fach 2 – Nicht eingenommen",
          data: fach2Data.map(d => 100 - d.korrekt),
          backgroundColor: "#F44336",
          stack: "fach2"
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { labels: { color: "#00005D" } },
        title: {
          display: true,
          text: title,
          color: "#00005D",
          font: { size: 16, weight: "bold" }
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.raw}%${ctx.raw === 0 ? " (Keine Einnahme?)" : ""}`
          }
        }
      },
      scales: {
        x: {
          stacked: true,
          ticks: { color: "#00005D" }
        },
        y: {
          stacked: true,
          beginAtZero: true,
          max: 100,
          ticks: { color: "#00005D" },
          title: {
            display: true,
            text: "% korrekt eingenommen",
            color: "#00005D"
          }
        }
      }
    }
  });
}

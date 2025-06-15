document.addEventListener("DOMContentLoaded", () => {
  fetch("unload.php")
    .then((response) => response.json())
    .then((data) => {
      updateNaechsteEinnahme(data.naechste);
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

const STATUS_COLORS = {
  green: "#4CAF50",
  yellow: "#FBC02D",
  red: "#F44336",
  "no-med": "#FAFAFA",
  future: "#BDBDBD"
};

const labelsDE = {
  green: "Pünktlich",
  yellow: "Verspätet",
  red: "Ausgefallen",
  "no-med": "Keine Einnahme geplant",
  future: "Geplant"
};

function updateNaechsteEinnahme(naechste) {
  const container = document.getElementById("naechste-einnahme");
  container.textContent = naechste?.uhrzeit
    ? `${naechste.wochentag}, ${naechste.uhrzeit} – ${naechste.fach} – ${naechste.medikament}`
    : "Keine Daten verfügbar";
}

function renderMatrixChart(verlaufData) {
  const ctx = document.getElementById("verlaufChart").getContext("2d");
  const legende = document.getElementById("matrix-legende");
  const days = ["MO", "DI", "MI", "DO", "FR", "SA", "SO"];
  const heuteIndex = (new Date().getDay() + 6) % 7;

  const data = verlaufData.flatMap((fachObj, rowIndex) =>
    fachObj.wochentage.map((eintrag, colIndex) => ({
      x: colIndex,
      y: rowIndex,
      v: typeof eintrag === "string" ? eintrag : eintrag.status,
      zeit: eintrag.zeit || "",
      medikament: eintrag.medikament || "",
      fach: fachObj.fach,
      highlight: colIndex === heuteIndex
    }))
  );

  new Chart(ctx, {
    type: "matrix",
    data: {
      datasets: [{
        label: "Einnahmestatus",
        data: data,
        backgroundColor: ctx => STATUS_COLORS[ctx.raw.v] || "#ccc",
        borderColor: ctx =>
          ctx.raw.highlight ? "#0000FF" :
          ctx.raw.v === "future" ? "transparent" : "#aaa",
        borderWidth: ctx =>
          ctx.raw.highlight ? 2 :
          ctx.raw.v === "future" ? 0 : 1,
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
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: ctx => {
              const tag = days[ctx[0].raw.x];
              const fach = ctx[0].raw.fach || "";
              return `${tag} – ${fach}`;
            },
            label: ctx => {
              const { v, zeit, medikament } = ctx.raw;
              const status = labelsDE[v] || v;
              let details = [`Status: ${status}`];
              if (v === "future") {
                if (zeit) details.push(`Geplant um: ${zeit}`);
                if (medikament) details.push(`Medikament: ${medikament}`);
              }
              return details;
            }
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

function switchStats(view) {
  document.getElementById("monatsansicht").style.display = view === "monat" ? "block" : "none";
  document.getElementById("jahresansicht").style.display = view === "jahr" ? "block" : "none";
  document.getElementById("btn-monat").classList.toggle("active", view === "monat");
  document.getElementById("btn-jahr").classList.toggle("active", view === "jahr");
}

function renderWochenStatistik(wochenData) {
  const fach1 = wochenData.map(e => normalizeStats(e.fach1));
  const fach2 = wochenData.map(e => normalizeStats(e.fach2));
  const labels = wochenData.map(e => e.kw);
  renderStackedChartDualFach("wochenStackedChart", "", fach1, fach2, labels);
}

function renderJahresStatistik(monatsData) {
  const heute = new Date();
  const aktuelleMonatIndex = heute.getMonth();
  const aktuelleJahr = heute.getFullYear();
  const thisMonthKey = new Intl.DateTimeFormat('en', { month: 'short' }).format(heute) + ' ' + aktuelleJahr;

  const fach1 = monatsData.map((e, i) => {
    const ignore = i < 9 && e.fach1.gruen === 0 && e.fach1.gelb === 0 && e.fach1.rot === 0;
    return ignore ? { gruen: 0, gelb: 0, rot: 0 } : normalizeStats(e.fach1);
  });
  const fach2 = monatsData.map((e, i) => {
    const ignore = i < 9 && e.fach2.gruen === 0 && e.fach2.gelb === 0 && e.fach2.rot === 0;
    return ignore ? { gruen: 0, gelb: 0, rot: 0 } : normalizeStats(e.fach2);
  });
  const labels = monatsData.map(e => e.monat);
  renderStackedChartDualFach("jahresStackedChart", "", fach1, fach2, labels, true);
}

function normalizeStats(statObj) {
  const sum = statObj.gruen + statObj.gelb + statObj.rot;
  if (sum === 0) return { gruen: 0, gelb: 0, rot: 0 };
  return {
    gruen: Math.round((statObj.gruen / sum) * 100),
    gelb: Math.round((statObj.gelb / sum) * 100),
    rot: Math.round((statObj.rot / sum) * 100)
  };
}

function renderStackedChartDualFach(canvasId, title, fach1Data, fach2Data, labels, isJahreschart = false) {
  const ctx = document.getElementById(canvasId).getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        { label: "Fach 1 – Pünktlich", data: fach1Data.map(d => d.gruen), backgroundColor: STATUS_COLORS.green, stack: "fach1" },
        { label: "Fach 1 – Verspätet", data: fach1Data.map(d => d.gelb), backgroundColor: STATUS_COLORS.yellow, stack: "fach1" },
        { label: "Fach 1 – Ausgefallen", data: fach1Data.map(d => d.rot), backgroundColor: STATUS_COLORS.red, stack: "fach1" },
        { label: "Fach 2 – Pünktlich", data: fach2Data.map(d => d.gruen), backgroundColor: STATUS_COLORS.green, stack: "fach2" },
        { label: "Fach 2 – Verspätet", data: fach2Data.map(d => d.gelb), backgroundColor: STATUS_COLORS.yellow, stack: "fach2" },
        { label: "Fach 2 – Ausgefallen", data: fach2Data.map(d => d.rot), backgroundColor: STATUS_COLORS.red, stack: "fach2" }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          stacked: true,
          ticks: {
            callback: value => `${value}%`,
            color: "#00005D"
          },
          grid: { color: "#eee" }
        },
        x: {
          stacked: true,
          ticks: { color: "#00005D" },
          grid: { display: false }
        }
      },
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: title,
          color: "#00005D",
          font: { size: 18, weight: "bold" }
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.raw}%`
          }
        }
      }
    }
  });
}

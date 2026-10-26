import Chart from 'chart.js/auto';
const PALETTE = ["#FA7035", "#F2B33D", "#106BC5", "#8B6FD6", "#3A9A3F", "#DC4A46", "#2BB3B1", "#C2569B", "#7A8286", "#A1683A"];

const css = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
const fmtEur = v => "€" + (Math.abs(v) >= 100 ? Math.round(v).toLocaleString("en-GB") : v.toFixed(2));
const fmtPct = p => (p >= 0 ? "+" : "−") + Math.abs(p * 100).toFixed(1) + "%";
const toDate = label => new Date(label + "T00:00:00Z");

function lineOptions(days) {
    const muted = css("--color-muted"), grid = css("--color-line");
    return {
        responsive: true, maintainAspectRatio: false, animation: {duration: 250},
        interaction: {mode: "index", intersect: false},
        plugins: {legend: {display: false}, tooltip: {callbacks: {label: ctx => (ctx.dataset.label ? ctx.dataset.label + ": " : "") + fmtEur(ctx.parsed.y)}}},
        scales: {
            x: {ticks: {color: muted, maxTicksLimit: 7, maxRotation: 0, callback(v) { return toDate(this.getLabelForValue(v)).toLocaleDateString("en-GB", {day: "numeric", month: "short", timeZone: "UTC", ...(days > 200 ? {year: "2-digit"} : {})}); }}, grid: {display: false}},
            y: {ticks: {color: muted, callback: v => fmtEur(v)}, grid: {color: grid}}
        }
    };
}

const builders = {
    value(canvas, data) {
        const accent = css("--color-brand");
        const gradient = canvas.getContext("2d").createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, accent + "55");
        gradient.addColorStop(1, accent + "00");
        return {type: "line", data: {labels: data.labels, datasets: [{data: data.values, borderColor: accent, backgroundColor: gradient, fill: true, pointRadius: data.values.length > 1 ? 0 : 3, borderWidth: 2, tension: .2}]}, options: lineOptions(data.days)};
    },
    card(canvas, data) {
        const color = css(data.trend === "up" ? "--color-up" : "--color-down");
        return {type: "line", data: {labels: data.labels, datasets: [
            {label: "Value", data: data.values, borderColor: color, backgroundColor: color + "22", fill: true, pointRadius: data.values.length > 1 ? 0 : 3, borderWidth: 2, tension: .15, spanGaps: false},
            {label: "30D avg", data: data.average, borderColor: css("--color-muted"), borderDash: [5, 4], pointRadius: 0, borderWidth: 1.3, fill: false}
        ]}, options: lineOptions(data.days)};
    },
    series(canvas, data) {
        const total = data.values.reduce((a, b) => a + b, 0);
        return {type: "doughnut", data: {labels: data.labels, datasets: [{data: data.values, backgroundColor: data.labels.map((_, i) => PALETTE[i % PALETTE.length]), borderColor: css("--color-panel"), borderWidth: 2}]},
            options: {responsive: true, maintainAspectRatio: false, cutout: "62%", plugins: {legend: {position: "bottom", labels: {color: css("--color-muted"), boxWidth: 10, font: {size: 11}}},
                tooltip: {callbacks: {label: ctx => `${ctx.label}: ${fmtEur(ctx.parsed)} (${(ctx.parsed / total * 100).toFixed(0)}%)`}}}}};
    },
    sets(canvas, data) {
        const up = css("--color-up"), down = css("--color-down");
        return {type: "bar", data: {labels: data.labels, datasets: [{data: data.values, backgroundColor: data.changes.map(c => (c >= 0 ? up : down) + "cc"), borderRadius: 4}]},
            options: {...lineOptions(0), indexAxis: "y", interaction: {mode: "nearest", intersect: true},
                plugins: {legend: {display: false}, tooltip: {callbacks: {label: ctx => { const i = ctx.dataIndex, n = data.counts[i]; return [`${fmtEur(data.values[i])} · ${n} card${n > 1 ? "s" : ""}`, `${data.range}: ${fmtPct(data.changes[i])}`]; }}}},
                scales: {x: {ticks: {color: css("--color-muted"), callback: v => fmtEur(v)}, grid: {color: css("--color-line")}}, y: {ticks: {color: css("--color-ink"), font: {size: 11.5}}, grid: {display: false}}}}};
    },
};

export function destroyCharts(root) {
    root.querySelectorAll("canvas[data-chart]").forEach(canvas => Chart.getChart(canvas)?.destroy());
}

export function initCharts(root) {
    root.querySelectorAll("canvas[data-chart]").forEach(canvas => {
        const data = JSON.parse(canvas.dataset.chartData);
        new Chart(canvas, builders[canvas.dataset.chart](canvas, data));
    });
}

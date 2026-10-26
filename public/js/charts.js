import Chart from 'chart.js/auto';
import {css, fmtEur, fmtPct, lineOptions} from './price-stats';

const PALETTE = ["#FA7035", "#F2B33D", "#106BC5", "#8B6FD6", "#3A9A3F", "#DC4A46", "#2BB3B1", "#C2569B", "#7A8286", "#A1683A"];

const builders = {
    value(canvas, data) {
        const accent = css("--color-brand");
        const gradient = canvas.getContext("2d").createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, accent + "55");
        gradient.addColorStop(1, accent + "00");
        return {type: "line", data: {labels: data.labels, datasets: [{data: data.values, borderColor: accent, backgroundColor: gradient, fill: true, pointRadius: data.values.length > 1 ? 0 : 3, borderWidth: 2, tension: .2}]}, options: lineOptions(data.days)};
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

export function initCharts(root) {
    root.querySelectorAll("canvas[data-chart]").forEach(canvas => {
        const data = JSON.parse(canvas.dataset.chartData);
        new Chart(canvas, builders[canvas.dataset.chart](canvas, data));
    });
}

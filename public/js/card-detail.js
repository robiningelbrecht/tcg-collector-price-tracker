import Chart from 'chart.js/auto';
import {RANGES, cls, css, fmtDate, fmtEur, fmtPct, fmtSEur, lineOptions, movingAvg, pctOver, rangeLabel, rangeStart, segButtons, windowStats} from './price-stats';

export function initCardDetail(root) {
    const historyElement = root.querySelector("[data-card-history]");
    if (!historyElement) return () => {};

    const {dates: labels, hist} = JSON.parse(historyElement.textContent);
    const ma = movingAvg(hist, 30);
    let range = 365, chart = null;

    root.querySelector("[data-chips]").innerHTML = [["7D", 7], ["30D", 30], ["90D", 90], ["1Y", 365], ["2Y", 729]]
        .map(([l, d]) => { const p = pctOver(hist, d); return `<span class="chip">${l} <b class="${cls(p)}">${fmtPct(p)}</b></span>`; }).join("");

    function render() {
        segButtons(root.querySelector("[data-range]"), RANGES, range, v => { range = v; render(); });
        const a = rangeStart(hist, range), st = windowStats(hist, range), rl = rangeLabel(range);
        const color = st.pct >= 0 ? css("--color-up") : css("--color-down");
        const points = hist.slice(a);
        chart?.destroy();
        chart = new Chart(root.querySelector("[data-chart]"), {type: "line", data: {labels: labels.slice(a), datasets: [
            {label: "Value", data: points, borderColor: color, backgroundColor: color + "22", fill: true, pointRadius: points.length > 1 ? 0 : 3, borderWidth: 2, tension: .15, spanGaps: false},
            {label: "30D avg", data: ma.slice(a), borderColor: css("--color-muted"), borderDash: [5, 4], pointRadius: 0, borderWidth: 1.3, fill: false}
        ]}, options: lineOptions(range)});
        root.querySelector("[data-stats]").innerHTML = [
            ["Holding value", fmtEur(st.last)],
            [`Change · ${rl}`, `<span class="${cls(st.pct)}">${fmtSEur(st.chg)} (${fmtPct(st.pct)})</span>`],
            [`High · ${rl}`, `${fmtEur(st.hi)} <span class="sub">${fmtDate(labels[st.hiI])}</span>`],
            [`Low · ${rl}`, `${fmtEur(st.lo)} <span class="sub">${fmtDate(labels[st.loI])}</span>`],
            ["From high", `<span class="${st.fromHi > -0.001 ? "up" : "down"}">${st.fromHi > -0.001 ? "At high" : fmtPct(st.fromHi)}</span>`],
            ["Max drawdown", `<span class="down">${fmtPct(st.mdd)}</span>`],
            ["Volatility", `${(st.vol * 100).toFixed(0)}% <span class="sub">annualised</span>`],
        ].map(([l, v]) => `<div class="stat"><div class="l">${l}</div><div class="v">${v}</div></div>`).join("");
    }

    render();

    return () => chart?.destroy();
}

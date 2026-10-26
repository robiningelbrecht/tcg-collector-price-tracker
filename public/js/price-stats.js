export const RANGES = [["7D", 7], ["30D", 30], ["90D", 90], ["6M", 182], ["1Y", 365], ["2Y", 729]];

export const fmtEur = v => "€" + (Math.abs(v) >= 100 ? Math.round(v).toLocaleString("en-GB") : v.toFixed(2));
export const fmtSEur = v => (v >= 0 ? "+" : "−") + fmtEur(Math.abs(v));
export const fmtPct = p => (p >= 0 ? "+" : "−") + Math.abs(p * 100).toFixed(1) + "%";
export const cls = p => p >= 0 ? "up" : "down";
export const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
export const esc = s => String(s ?? "").replace(/[&<>"]/g, m => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;"}[m]));
export const rangeLabel = days => RANGES.find(r => r[1] === days)[0];

export const toDate = label => new Date(label + "T00:00:00Z");
export const fmtDate = label => toDate(label).toLocaleDateString("en-GB", {day: "numeric", month: "short", year: "numeric", timeZone: "UTC"});

export const rangeStart = (s, days) => Math.max(0, s.length - 1 - days);

export function windowStats(s, days) {
    const a = rangeStart(s, days);
    let fi = -1, li = -1;
    for (let i = a; i < s.length; i++) if (s[i] != null) { if (fi < 0) fi = i; li = i; }
    const first = s[fi], last = s[li];
    let hi = -Infinity, lo = Infinity, hiI = fi, loI = fi, peak = -Infinity, mdd = 0, sum = 0, sum2 = 0, n = 0, prev = null;
    for (let i = fi; i <= li; i++) {
        const v = s[i];
        if (v == null) continue;
        if (v > hi) { hi = v; hiI = i; }
        if (v < lo) { lo = v; loI = i; }
        peak = Math.max(peak, v);
        mdd = Math.min(mdd, v / peak - 1);
        if (prev != null) { const lr = Math.log(v / prev); sum += lr; sum2 += lr * lr; n++; }
        prev = v;
    }
    const sd = n > 1 ? Math.sqrt((sum2 - sum * sum / n) / (n - 1)) : 0;
    return {first, last, chg: last - first, pct: last / first - 1, hi, lo, hiI, loI, mdd, vol: sd * Math.sqrt(365), fromHi: last / hi - 1};
}

export const pctOver = (s, days) => windowStats(s, days).pct;

export function movingAvg(s, w) {
    const out = [], q = [];
    let acc = 0, cnt = 0;
    for (let i = 0; i < s.length; i++) {
        const v = s[i];
        q.push(v);
        if (v != null) { acc += v; cnt++; }
        if (q.length > w) { const o = q.shift(); if (o != null) { acc -= o; cnt--; } }
        out.push(v == null || cnt < Math.min(w, 5) ? null : Math.round(acc / cnt * 100) / 100);
    }
    return out;
}

export function segButtons(el, items, active, onClick) {
    el.innerHTML = items.map(([l, v]) => `<button data-v="${v}" class="${v == active ? "on" : ""}">${l}</button>`).join("");
    el.querySelectorAll("button").forEach(b => b.onclick = () => onClick(isNaN(+b.dataset.v) ? b.dataset.v : +b.dataset.v));
}

export function lineOptions(rangeDays) {
    const muted = css("--color-muted"), grid = css("--color-line");
    return {
        responsive: true, maintainAspectRatio: false, animation: {duration: 250},
        interaction: {mode: "index", intersect: false},
        plugins: {legend: {display: false}, tooltip: {callbacks: {label: ctx => (ctx.dataset.label ? ctx.dataset.label + ": " : "") + fmtEur(ctx.parsed.y)}}},
        scales: {
            x: {ticks: {color: muted, maxTicksLimit: 7, maxRotation: 0, callback(v) { return toDate(this.getLabelForValue(v)).toLocaleDateString("en-GB", {day: "numeric", month: "short", timeZone: "UTC", ...(rangeDays > 200 ? {year: "2-digit"} : {})}); }}, grid: {display: false}},
            y: {ticks: {color: muted, callback: v => fmtEur(v)}, grid: {color: grid}}
        }
    };
}

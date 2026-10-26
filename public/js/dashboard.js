import Chart from 'chart.js/auto';
import {initCardDetail} from './card-detail';
import {RANGES, cls, css, esc, fmtDate, fmtEur, fmtPct, fmtSEur, lineOptions, pctOver, rangeLabel, rangeStart, segButtons, windowStats} from './price-stats';

const PALETTE = ["#FA7035", "#F2B33D", "#106BC5", "#8B6FD6", "#3A9A3F", "#DC4A46", "#2BB3B1", "#C2569B", "#7A8286", "#A1683A"];

export function initDashboard(dataElement) {
    const data = JSON.parse(dataElement.textContent);
    const placeholder = dataElement.dataset.placeholder;
    const labels = data.dates;
    const DAYS = labels.length;
    const seriesLabel = Object.fromEntries(data.series.map(s => [s.id, s.label]));
    const seriesOrder = data.series.map(s => s.id);

    const imgSrc = c => c.img ? `${c.img}/low.webp` : placeholder;
    const thumb = c => `<img class="thumb" loading="lazy" referrerpolicy="no-referrer" src="${imgSrc(c)}" alt="" onerror="this.onerror=null;this.src='${placeholder}'">`;
    const tags = c => (c.region === "JP" ? '<span class="tag">JP</span>' : "") + (c.qty > 1 ? `<span class="tag">×${c.qty}</span>` : "") + (c.isNew ? '<span class="tag">NEW</span>' : "");

    function forwardFill(s) {
        let last = null;
        return s.map(v => last = (v ?? last));
    }

    const cards = data.cards
        .filter(c => c.rank != null)
        .sort((a, b) => a.rank - b.rank)
        .map(c => { const filled = forwardFill(c.hist); return {...c, filled, value: filled[DAYS - 1]}; });
    const cardsById = Object.fromEntries(cards.map(c => [c.id, c]));

    let RANGE = 90, MODE = "pct", sortKey = "rank", sortDir = 1;
    const charts = {};

    function render() {
        segButtons(document.getElementById("range"), RANGES, RANGE, v => { RANGE = v; render(); });
        document.querySelectorAll("[data-mode]").forEach(el => segButtons(el, [["%", "pct"], ["€", "abs"]], MODE, v => { MODE = v; render(); }));
        cards.forEach(c => { c.st = windowStats(c.hist, RANGE); c.d7 = pctOver(c.hist, 7); c.pct = c.st.pct; c.vol = c.st.vol; c.absChg = c.st.chg; });
        renderKpis(); renderPortfolio(); renderEra(); renderMovers(); renderInsights(); renderSets(); renderTable();
    }

    function renderKpis() {
        const el = document.getElementById("kpis");
        if (!cards.length) { el.innerHTML = '<div class="panel"><div class="empty">No priced cards yet.</div></div>'; return; }
        const total = cards.reduce((a, c) => a + c.value, 0);
        const base = cards.reduce((a, c) => a + c.st.first, 0);
        const chg = total - base;
        const up = cards.filter(c => c.pct > 0.001).length, down = cards.filter(c => c.pct < -0.001).length;
        const byPct = [...cards].sort((a, b) => b.pct - a.pct);
        const best = byPct[0], worst = byPct[byPct.length - 1];
        const top10 = cards.slice(0, 10).reduce((a, c) => a + c.value, 0);
        const units = cards.reduce((a, c) => a + c.pricedQty, 0);
        el.innerHTML = [
            ["Collection value", fmtEur(total), `${units} priced copies · avg ${fmtEur(total / units)}`],
            [`Change · ${rangeLabel(RANGE)}`, `<span class="${cls(chg)}">${fmtSEur(chg)}</span>`, `<span class="${cls(chg)}">${fmtPct(chg / base)}</span> like-for-like`],
            ["Up / down", `<span class="up">${up}</span> / <span class="down">${down}</span>`, `${cards.length - up - down} flat`],
            ["Best performer", `<span class="${cls(best.pct)}">${fmtPct(best.pct)}</span>`, esc(best.name) + " · " + esc(best.set)],
            ["Worst performer", `<span class="${cls(worst.pct)}">${fmtPct(worst.pct)}</span>`, esc(worst.name) + " · " + esc(worst.set)],
            ["Concentration", ((top10 / total) * 100).toFixed(0) + "%", "of value in the 10 most valuable cards"],
        ].map(([l, v, s]) => `<div class="panel"><div class="kpi-l">${l}</div><div class="kpi-v">${v}</div><div class="kpi-s">${s}</div></div>`).join("");
    }

    function upsert(id, cfg) {
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(document.getElementById(id), cfg);
    }

    function renderPortfolio() {
        const a = Math.max(0, DAYS - 1 - RANGE);
        const series = [];
        for (let i = a; i < DAYS; i++) series.push(Math.round(cards.reduce((s, c) => s + (c.filled[i] ?? 0), 0)));
        const accent = css("--color-brand");
        const ctx = document.getElementById("pvChart").getContext("2d");
        const grad = ctx.createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, accent + "55");
        grad.addColorStop(1, accent + "00");
        upsert("pvChart", {type: "line", data: {labels: labels.slice(a), datasets: [{data: series, borderColor: accent, backgroundColor: grad, fill: true, pointRadius: series.length > 1 ? 0 : 3, borderWidth: 2, tension: .2}]}, options: lineOptions(RANGE)});
        document.getElementById("pvSub").textContent = series.length ? `${rangeLabel(RANGE)} high ${fmtEur(Math.max(...series))} · low ${fmtEur(Math.min(...series))}` : "";
    }

    function renderEra() {
        const eras = seriesOrder.filter(e => cards.some(c => c.era === e));
        const vals = eras.map(e => cards.filter(c => c.era === e).reduce((a, c) => a + c.value, 0));
        const total = vals.reduce((a, b) => a + b, 0);
        upsert("eraChart", {type: "doughnut", data: {labels: eras.map(e => seriesLabel[e]), datasets: [{data: vals, backgroundColor: eras.map((_, i) => PALETTE[i % PALETTE.length]), borderColor: css("--color-panel"), borderWidth: 2}]},
            options: {responsive: true, maintainAspectRatio: false, cutout: "62%", plugins: {legend: {position: "bottom", labels: {color: css("--color-muted"), boxWidth: 10, font: {size: 11}}},
                tooltip: {callbacks: {label: ctx => `${ctx.label}: ${fmtEur(ctx.parsed)} (${(ctx.parsed / total * 100).toFixed(0)}%)`}}}}});
    }

    function item(c, right) {
        return `<div class="it" data-id="${esc(c.id)}">${thumb(c)}<div class="n"><b>${esc(c.name)}${tags(c)}</b><span>${esc(c.set)} · ${esc(c.num)}</span></div><div class="r">${right}</div></div>`;
    }
    function bindList(el) { el.querySelectorAll(".it").forEach(n => n.onclick = () => openCard(cardsById[n.dataset.id])); }

    function renderMovers() {
        const key = MODE === "pct" ? "pct" : "absChg";
        const sorted = [...cards].sort((a, b) => b[key] - a[key]);
        const right = c => MODE === "pct"
            ? `<span class="${cls(c.pct)}">${fmtPct(c.pct)}</span><small>${fmtEur(c.st.first)} → ${fmtEur(c.value)}</small>`
            : `<span class="${cls(c.absChg)}">${fmtSEur(c.absChg)}</span><small>${fmtPct(c.pct)}${c.pricedQty > 1 ? ` · ×${c.pricedQty}` : ""}</small>`;
        const risers = sorted.filter(c => c[key] > 0).slice(0, 7);
        const fallers = sorted.reverse().filter(c => c[key] < 0).slice(0, 7);
        const R = document.getElementById("risers"), F = document.getElementById("fallers");
        R.innerHTML = risers.map(c => item(c, right(c))).join("") || '<div class="empty">Nothing went up in this range.</div>';
        F.innerHTML = fallers.map(c => item(c, right(c))).join("") || '<div class="empty">Nothing went down in this range.</div>';
        bindList(R); bindList(F);
    }

    function renderInsights() {
        const v = document.getElementById("volatile"), h = document.getElementById("nearHigh"), b = document.getElementById("belowHigh");
        v.innerHTML = [...cards].sort((a, b) => b.vol - a.vol).slice(0, 5).map(c => item(c, `${(c.vol * 100).toFixed(0)}%<small>annualised · ${rangeLabel(RANGE)}</small>`)).join("");
        const withTwo = cards.map(c => ({c, t: windowStats(c.hist, 729)}));
        h.innerHTML = withTwo.sort((a, b) => b.t.fromHi - a.t.fromHi).slice(0, 5).map(({c, t}) => item(c, t.fromHi > -0.001 ? `<span class="up">At high</span><small>${fmtEur(t.hi)}</small>` : `${fmtPct(t.fromHi)}<small>high ${fmtEur(t.hi)}</small>`)).join("");
        b.innerHTML = withTwo.sort((a, b) => a.t.fromHi - b.t.fromHi).slice(0, 5).map(({c, t}) => item(c, `<span class="down">${fmtPct(t.fromHi)}</span><small>high ${fmtEur(t.hi)} · ${fmtDate(labels[t.hiI])}</small>`)).join("");
        [v, h, b].forEach(bindList);
    }

    function renderSets() {
        const map = {};
        cards.forEach(c => { const m = map[c.set] ??= {set: c.set, value: 0, base: 0, n: 0}; m.value += c.value; m.base += c.st.first; m.n++; });
        const sets = Object.values(map).sort((a, b) => b.value - a.value).slice(0, 14);
        const up = css("--color-up"), down = css("--color-down");
        upsert("setChart", {type: "bar", data: {labels: sets.map(s => s.set), datasets: [{data: sets.map(s => Math.round(s.value)), backgroundColor: sets.map(s => (s.value >= s.base ? up : down) + "cc"), borderRadius: 4}]},
            options: {...lineOptions(RANGE), indexAxis: "y", interaction: {mode: "nearest", intersect: true},
                plugins: {legend: {display: false}, tooltip: {callbacks: {label: ctx => { const s = sets[ctx.dataIndex]; return [`${fmtEur(s.value)} · ${s.n} card${s.n > 1 ? "s" : ""}`, `${rangeLabel(RANGE)}: ${fmtPct(s.value / s.base - 1)}`]; }}}},
                scales: {x: {ticks: {color: css("--color-muted"), callback: v => fmtEur(v)}, grid: {color: css("--color-line")}}, y: {ticks: {color: css("--color-ink"), font: {size: 11.5}}, grid: {display: false}}}}});
    }

    const eraSel = document.getElementById("eraF");
    seriesOrder.filter(e => cards.some(c => c.era === e)).forEach(e => eraSel.insertAdjacentHTML("beforeend", `<option value="${esc(e)}">${esc(seriesLabel[e])}</option>`));
    ["q", "eraF", "regF"].forEach(id => document.getElementById(id).addEventListener("input", renderTable));
    document.querySelectorAll("th[data-k]").forEach(th => th.onclick = () => {
        const k = th.dataset.k;
        if (sortKey === k) sortDir = -sortDir; else { sortKey = k; sortDir = ["name", "rarity", "rank"].includes(k) ? 1 : -1; }
        renderTable();
    });

    function sparkline(canvas, values, color) {
        const dpr = window.devicePixelRatio || 1, w = canvas.clientWidth || 110, h = canvas.clientHeight || 30;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        const g = canvas.getContext("2d");
        g.scale(dpr, dpr);
        const vals = values.filter(v => v != null);
        if (vals.length < 2) return;
        const mn = Math.min(...vals), mx = Math.max(...vals);
        g.beginPath();
        let started = false;
        values.forEach((v, i) => { if (v == null) return; const x = i / (values.length - 1) * (w - 2) + 1, y = h - 3 - (v - mn) / ((mx - mn) || 1) * (h - 6); started ? g.lineTo(x, y) : (g.moveTo(x, y), started = true); });
        g.strokeStyle = color;
        g.lineWidth = 1.5;
        g.lineJoin = "round";
        g.stroke();
    }

    function renderTable() {
        const q = document.getElementById("q").value.trim().toLowerCase();
        const era = eraSel.value, reg = document.getElementById("regF").value;
        const rows = cards.filter(c => (!q || (c.name + " " + c.set + " " + c.rarity).toLowerCase().includes(q)) && (!era || c.era === era) && (!reg || c.region === reg));
        rows.sort((a, b) => { const x = a[sortKey], y = b[sortKey]; return (typeof x === "string" ? x.localeCompare(y) : x - y) * sortDir; });
        document.getElementById("thRange").textContent = rangeLabel(RANGE);
        document.querySelectorAll("th[data-k]").forEach(th => { th.querySelector(".ar")?.remove(); if (th.dataset.k === sortKey) th.insertAdjacentHTML("beforeend", `<span class="ar"> ${sortDir > 0 ? "▲" : "▼"}</span>`); });
        document.getElementById("tblCount").textContent = `${rows.length} of ${cards.length}`;
        const tb = document.getElementById("tbody");
        tb.innerHTML = rows.map(c => `<tr>
            <td class="num">${c.rank}</td>
            <td><div class="cn">${thumb(c)}<div><b>${esc(c.name)}${tags(c)}</b><span>${esc(c.set)} · ${esc(c.num)}</span></div></div></td>
            <td><span class="sub">${esc(c.rarity)}</span></td>
            <td class="num">${c.qty}</td>
            <td class="num"><b>${fmtEur(c.value)}</b>${c.pricedQty > 1 ? `<span class="sub block">${fmtEur(c.value / c.pricedQty)} avg</span>` : ""}</td>
            <td class="num ${cls(c.d7)}">${fmtPct(c.d7)}</td>
            <td class="num ${cls(c.pct)}">${fmtPct(c.pct)}</td>
            <td class="num">${(c.vol * 100).toFixed(0)}%</td>
            <td><canvas class="sp"></canvas></td></tr>`).join("");
        const up = css("--color-up"), down = css("--color-down");
        tb.querySelectorAll("tr").forEach((tr, i) => {
            const c = rows[i];
            sparkline(tr.querySelector("canvas"), c.hist.slice(rangeStart(c.hist, RANGE)), c.pct >= 0 ? up : down);
            tr.onclick = () => openCard(c);
        });
    }

    const modal = document.getElementById("modal");
    const modalBody = document.getElementById("modalBody");
    let destroyCardDetail = null, request = 0;

    async function openCard(c) {
        const current = ++request;
        destroyCardDetail?.();
        destroyCardDetail = null;
        modalBody.innerHTML = '<div class="empty p-5">Loading…</div>';
        modal.classList.add("open");
        document.body.style.overflow = "hidden";
        try {
            const response = await fetch(`/cards/${c.id}`);
            if (!response.ok) throw new Error(response.statusText);
            const html = await response.text();
            if (current !== request) return;
            modalBody.innerHTML = html;
            destroyCardDetail = initCardDetail(modalBody);
        } catch {
            if (current === request) modalBody.innerHTML = '<div class="empty p-5">Could not load this card.</div>';
        }
    }

    function closeModal() {
        request++;
        destroyCardDetail?.();
        destroyCardDetail = null;
        modal.classList.remove("open");
        modalBody.innerHTML = "";
        document.body.style.overflow = "";
    }
    document.getElementById("mclose").onclick = closeModal;
    modal.onclick = e => { if (e.target === modal) closeModal(); };
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });

    render();
}

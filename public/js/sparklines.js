const css = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

function draw(canvas) {
    const values = JSON.parse(canvas.dataset.sparkline);
    const dpr = window.devicePixelRatio || 1, w = canvas.clientWidth || 110, h = canvas.clientHeight || 30;
    canvas.width = w * dpr;
    canvas.height = h * dpr;
    const g = canvas.getContext("2d");
    g.scale(dpr, dpr);
    const known = values.filter(v => v != null);
    if (known.length < 2) return;
    const min = Math.min(...known), max = Math.max(...known);
    g.beginPath();
    let started = false;
    values.forEach((v, i) => {
        if (v == null) return;
        const x = i / (values.length - 1) * (w - 2) + 1, y = h - 3 - (v - min) / ((max - min) || 1) * (h - 6);
        started ? g.lineTo(x, y) : (g.moveTo(x, y), started = true);
    });
    g.strokeStyle = css({up: "--color-up", down: "--color-down"}[canvas.dataset.trend] ?? "--color-muted");
    g.lineWidth = 1.5;
    g.lineJoin = "round";
    g.stroke();
}

export function initSparklines(root) {
    root.querySelectorAll("canvas[data-sparkline]").forEach(draw);
}

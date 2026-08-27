// Daily work summary — two SVG line charts (auditor / validator output per
// day) sharing one draggable time-range brush in the card footer. Data comes
// from the WorkTrendChartComponent Livewire view via data-payload; the brush
// works fully client-side so dragging never hits the server.

window.WorkTrendChart = function (root) {
    const data = JSON.parse(root.dataset.payload);
    const N = data.dates.length;
    if (!N) return;

    const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const fmtShort = (ymd) => `${Number(ymd.slice(8))} ${MONTHS[Number(ymd.slice(5, 7)) - 1]}`;
    const fmtFull = (ymd) => `${fmtShort(ymd)} ${ymd.slice(0, 4)}`;
    // flag early-January ticks with the year so long ranges stay readable
    const fmtTick = (ymd) => (ymd.slice(5) <= '01-04' ? `${fmtShort(ymd)} '${ymd.slice(2, 4)}` : fmtShort(ymd));

    const combined = data.dates.map((_, i) => data.auditor[i] + data.validator[i]);

    const STYLE = {
        auditor: { line: 'stroke-current', dot: 'fill-current', area: 'fill-current' },
        validator: {
            line: 'stroke-[#3F72AF] dark:stroke-[#7BA3EE]',
            dot: 'fill-[#3F72AF] dark:fill-[#7BA3EE]',
            area: 'fill-[#3F72AF] dark:fill-[#7BA3EE]',
        },
    };

    const charts = [...root.querySelectorAll('.wt-chart')].map((wrap) => {
        const tip = document.createElement('div');
        tip.className =
            'absolute hidden pointer-events-none z-10 whitespace-nowrap rounded-sm border border-stone-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-2 py-1 text-[10px] leading-snug shadow-sm text-stone-700 dark:text-slate-200 tabular-nums';
        tip.style.transform = 'translate(-50%, -100%)';
        wrap.appendChild(tip);
        return { wrap, tip, series: wrap.dataset.series, style: STYLE[wrap.dataset.series], svg: null, hoverG: null };
    });

    const brush = root.querySelector('[data-brush]');
    const rangeLabel = root.querySelector('[data-range-label]');
    const resetBtn = root.querySelector('[data-reset]');
    const totalEls = {};
    root.querySelectorAll('[data-total]').forEach((el) => (totalEls[el.dataset.total] = el));

    const DEF_DAYS = 30;
    let sel = [Math.max(0, N - DEF_DAYS), N - 1]; // [startIdx, endIdx] inclusive
    let brushSvg = null;
    let win = null;
    let shadeL = null;
    let shadeR = null;
    let drag = null; // {mode:'l'|'r'|'pan', grabOff}

    const svgNS = 'http://www.w3.org/2000/svg';
    const svgEl = (tag, attrs, parent) => {
        const node = document.createElementNS(svgNS, tag);
        for (const k in attrs) node.setAttribute(k, attrs[k]);
        if (parent) parent.appendChild(node);
        return node;
    };
    const div = (cls, parent) => {
        const node = document.createElement('div');
        node.className = cls;
        if (parent) parent.appendChild(node);
        return node;
    };
    const txt = (parent, x, y, s, anchor) => {
        const t = svgEl('text', { x, y, 'text-anchor': anchor, 'font-size': 10, class: 'fill-stone-400 dark:fill-slate-500' }, parent);
        t.textContent = s;
        return t;
    };

    // smallest round step whose 4 divisions cover v — keeps y ticks at 0/¼/½/¾/max
    function niceStep(raw) {
        if (raw <= 1) return 1;
        const pow = Math.pow(10, Math.floor(Math.log10(raw)));
        for (const m of [1, 2, 5, 10]) if (m * pow >= raw) return m * pow;
        return 10 * pow;
    }

    function drawMain(c) {
        const w = Math.max(80, c.wrap.clientWidth);
        const h = 210, padL = 34, padR = 8, padT = 10, padB = 20;
        const iw = w - padL - padR, ih = h - padT - padB;

        if (c.svg) c.svg.remove();
        c.svg = svgEl('svg', { width: w, height: h, viewBox: `0 0 ${w} ${h}`, class: 'block' }, c.wrap);
        c.hoverG = null;
        c.geom = { w, padL, padT, iw, ih };

        const [i0, i1] = sel;
        const count = i1 - i0 + 1;
        const span = i1 - i0;
        const xAt = (i) => (span === 0 ? padL + iw / 2 : padL + ((i - i0) / span) * iw);
        const stepV = niceStep(Math.max(...data[c.series].slice(i0, i1 + 1), 0) / 4);
        const maxV = stepV * 4;
        const yAt = (v) => padT + ih - (v / maxV) * ih;

        for (let t = 0; t <= 4; t++) {
            const y = yAt(t * stepV);
            svgEl('line', { x1: padL, x2: padL + iw, y1: y, y2: y, class: 'stroke-stone-200 dark:stroke-slate-700' }, c.svg);
            txt(c.svg, padL - 6, y + 3, String(t * stepV), 'end');
        }

        const pts = [];
        for (let i = i0; i <= i1; i++) pts.push([xAt(i), yAt(data[c.series][i])]);
        c.points = pts;

        if (span > 0) {
            const base = padT + ih;
            const line = pts.map((p) => `${p[0].toFixed(1)},${p[1].toFixed(1)}`).join('L');
            svgEl(
                'path',
                { d: `M${line}L${pts[count - 1][0].toFixed(1)},${base}L${pts[0][0].toFixed(1)},${base}Z`, 'fill-opacity': 0.07, class: c.style.area },
                c.svg
            );
            svgEl('path', { d: `M${line}`, fill: 'none', 'stroke-width': 1.8, 'stroke-linejoin': 'round', 'stroke-linecap': 'round', class: c.style.line }, c.svg);
            if (count <= 60) pts.forEach((p) => svgEl('circle', { cx: p[0], cy: p[1], r: 2.4, class: c.style.dot }, c.svg));
        } else if (pts.length) {
            // single-day window: no line possible, show the value as a dot
            svgEl('circle', { cx: pts[0][0], cy: pts[0][1], r: 3.5, class: c.style.dot }, c.svg);
        }

        const lstep = Math.max(1, Math.ceil(count / Math.max(1, Math.floor(iw / 76))));
        for (let i = i0; i <= i1; i += lstep) {
            const anchor = i === i0 ? 'start' : i + lstep > i1 ? 'end' : 'middle';
            txt(c.svg, xAt(i), h - 6, fmtTick(data.dates[i]), anchor);
        }

        const hover = svgEl('rect', { x: padL, y: padT, width: iw, height: ih, fill: 'transparent' }, c.svg);
        hover.addEventListener('pointermove', (e) => {
            const rect = c.svg.getBoundingClientRect();
            let idx = span === 0 ? i0 : i0 + Math.round(((e.clientX - rect.left - padL) / iw) * span);
            showHover(c, Math.min(i1, Math.max(i0, idx)));
        });
        hover.addEventListener('pointerleave', () => hideHover(c));
    }

    function showHover(c, idx) {
        hideHover(c);
        const [x, y] = c.points[idx - sel[0]];
        const g = svgEl('g', {}, c.svg);
        svgEl('line', { x1: x, x2: x, y1: c.geom.padT, y2: c.geom.padT + c.geom.ih, 'stroke-dasharray': '3 3', 'stroke-opacity': 0.45, class: c.style.line }, g);
        svgEl('circle', { cx: x, cy: y, r: 3.5, class: c.style.dot }, g);
        c.hoverG = g;
        c.tip.innerHTML = `<div class="font-semibold">${fmtFull(data.dates[idx])}</div><div>Task: <span class="font-semibold">${data[c.series][idx].toLocaleString()}</span></div>`;
        c.tip.classList.remove('hidden');
        c.tip.style.left = `${Math.min(Math.max(x, 56), c.geom.w - 56)}px`;
        c.tip.style.top = `${y - 8}px`;
    }

    function hideHover(c) {
        if (c.hoverG) c.hoverG.remove();
        c.hoverG = null;
        c.tip.classList.add('hidden');
    }

    function buildBrush() {
        shadeL = div('absolute top-0 bottom-0 left-0 bg-stone-900/5 dark:bg-white/5 pointer-events-none', brush);
        shadeR = div('absolute top-0 bottom-0 right-0 bg-stone-900/5 dark:bg-white/5 pointer-events-none', brush);
        win = div('absolute top-0 bottom-0 border border-stone-500 dark:border-slate-400 bg-stone-900/5 dark:bg-white/10 cursor-grab active:cursor-grabbing', brush);
        const handleL = div('absolute top-0 bottom-0 -left-1 w-2 cursor-ew-resize flex justify-center', win);
        div('w-0.5 h-full bg-stone-600 dark:bg-slate-300', handleL);
        const handleR = div('absolute top-0 bottom-0 -right-1 w-2 cursor-ew-resize flex justify-center', win);
        div('w-0.5 h-full bg-stone-600 dark:bg-slate-300', handleR);
    }

    function drawBrush() {
        const w = Math.max(60, brush.clientWidth);
        const h = 46;
        if (brushSvg) brushSvg.remove();
        brushSvg = svgEl('svg', { width: w, height: h, viewBox: `0 0 ${w} ${h}`, class: 'block' });
        brush.insertBefore(brushSvg, brush.firstChild);

        const maxC = Math.max(...combined, 1);
        const xAt = (i) => (N === 1 ? w / 2 : (i / (N - 1)) * w);
        const yAt = (v) => h - 2 - (v / maxC) * (h - 8);
        const line = data.dates.map((_, i) => `${xAt(i).toFixed(1)},${yAt(combined[i]).toFixed(1)}`).join('L');
        const flat = N === 1 ? `${w / 2},${h - 2}` : null; // single day: invisible flat area
        svgEl('path', { d: `M${flat || line}L${w},${h}L0,${h}Z`, 'fill-opacity': 0.22, class: 'fill-current' }, brushSvg);
        if (!flat) svgEl('path', { d: `M${line}`, fill: 'none', 'stroke-width': 1, 'stroke-opacity': 0.45, class: 'stroke-current' }, brushSvg);
    }

    function updateWindow() {
        const l = N === 1 ? 0 : sel[0] / (N - 1);
        const r = N === 1 ? 1 : sel[1] / (N - 1);
        const lp = `${(l * 100).toFixed(3)}%`;
        win.style.left = lp;
        win.style.width = `${((r - l) * 100).toFixed(3)}%`;
        shadeL.style.width = lp;
        shadeR.style.width = `${((1 - r) * 100).toFixed(3)}%`;
        const days = sel[1] - sel[0] + 1;
        rangeLabel.textContent = `${data.dates[sel[0]]} → ${data.dates[sel[1]]} · ${days} day${days > 1 ? 's' : ''}`;
        for (const s of ['auditor', 'validator']) {
            if (totalEls[s]) {
                const sum = data[s].slice(sel[0], sel[1] + 1).reduce((a, b) => a + b, 0);
                totalEls[s].textContent = `${sum.toLocaleString()} in range`;
            }
        }
    }

    function renderAll() {
        charts.forEach(drawMain);
        drawBrush();
        updateWindow();
    }

    function fracFromEvent(e) {
        const rect = brush.getBoundingClientRect();
        return Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
    }

    function setEdge(m, idx) {
        if (m === 'l') sel[0] = Math.min(idx, sel[1]);
        else sel[1] = Math.max(idx, sel[0]);
    }

    brush.addEventListener('pointerdown', (e) => {
        if (N < 2) return;
        const f = fracFromEvent(e);
        const l = sel[0] / (N - 1);
        const r = sel[1] / (N - 1);
        const zone = 10 / brush.getBoundingClientRect().width; // ~10px handle grab zone
        if (Math.abs(f - l) <= zone) drag = { mode: 'l' };
        else if (Math.abs(f - r) <= zone) drag = { mode: 'r' };
        else if (f > l && f < r) drag = { mode: 'pan', grabOff: f - l };
        // click outside the window: jump the nearest edge there, keep dragging it
        else {
            const mode = f - l < r - f ? 'l' : 'r';
            setEdge(mode, Math.round(f * (N - 1)));
            drag = { mode };
        }
        brush.setPointerCapture(e.pointerId);
        e.preventDefault();
        renderAll();
    });

    brush.addEventListener('pointermove', (e) => {
        if (!drag) return;
        const idx = Math.round(fracFromEvent(e) * (N - 1));
        if (drag.mode === 'pan') {
            const len = sel[1] - sel[0];
            const start = Math.min(Math.max(0, Math.round((fracFromEvent(e) - drag.grabOff) * (N - 1))), N - 1 - len);
            sel = [start, start + len];
        } else {
            setEdge(drag.mode, idx);
        }
        renderAll();
    });

    const endDrag = () => (drag = null);
    brush.addEventListener('pointerup', endDrag);
    brush.addEventListener('pointercancel', endDrag);

    resetBtn.addEventListener('click', () => {
        sel = [Math.max(0, N - DEF_DAYS), N - 1];
        renderAll();
    });

    const ro = new ResizeObserver(() => {
        if (root.isConnected) renderAll();
        else ro.disconnect();
    });
    ro.observe(root);

    buildBrush();
    renderAll();
};

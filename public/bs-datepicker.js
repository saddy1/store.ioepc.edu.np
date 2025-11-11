
(function () {
  async function fetchMonth(y, m) {
    const res = await fetch(`/api/bs-month?year=${y}&month=${m}`);
    if (!res.ok) throw new Error('Failed to load month');
    return res.json();
  }

  async function bsToAd(y, m, d) {
    const res = await fetch(`/api/bs-to-ad?year=${y}&month=${m}&day=${d}`);
    if (!res.ok) throw new Error('Failed to convert');
    const j = await res.json();
    return j.result.ad;
  }

  async function adToBs(isoAd) {
    const res = await fetch(`/api/ad-to-bs?date=${isoAd}`);
    if (!res.ok) throw new Error('Failed to convert');
    const j = await res.json();
    return j.result.bs;
  }

  function clamp(v,a,b){ return Math.min(Math.max(v,a),b); }

  function buildMonthOptions(sel) {
    const names = ['बैशाख','जेठ','असार','श्रावण','भदौ','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
    sel.innerHTML = '';
    names.forEach((n,i)=> {
      const op = document.createElement('option');
      op.value = String(i+1); op.textContent = n; sel.appendChild(op);
    });
  }

  function buildYearOptions(sel, startYear, endYear, current) {
    sel.innerHTML = '';
    for (let y=startYear; y<=endYear; y++) {
      const op = document.createElement('option');
      op.value = y; op.textContent = y;
      if (y===current) op.selected = true;
      sel.appendChild(op);
    }
  }

  async function initOne(wrapper) {
    const input = wrapper.querySelector('[data-bsdp]');
    const btn = wrapper.querySelector('[data-bsdp-toggle]');
    const popup = wrapper.querySelector('[data-bsdp-popup]');
    const grid = wrapper.querySelector('[data-bsdp-grid]');
    const yearSel = wrapper.querySelector('[data-bsdp-year]');
    const monthSel = wrapper.querySelector('[data-bsdp-month]');
    const prevBtn = wrapper.querySelector('[data-bsdp-prev]');
    const nextBtn = wrapper.querySelector('[data-bsdp-next]');
    const todayBtn = wrapper.querySelector('[data-bsdp-today]');
    const info = wrapper.querySelector('[data-bsdp-info]');
    const hiddenAd = wrapper.querySelector('[data-bsdp-ad]');

    const startYear = parseInt(input.dataset.startYear || '1970', 10);
    const endYear   = parseInt(input.dataset.endYear || '2100', 10);

    buildMonthOptions(monthSel);
    let curY, curM;

    async function setFromInputOrToday() {
      if (input.value && /^\d{4}-\d{2}-\d{2}$/.test(input.value)) {
        const [y,m] = input.value.split('-').map(n=>parseInt(n,10));
        curY = clamp(y,startYear,endYear);
        curM = clamp(m,1,12);
        await updateGrid(curY,curM);
      } else {
        const todayAd = new Date().toISOString().slice(0,10);
        const bs = await adToBs(todayAd);
        const [y,m,d] = bs.split('-').map(n=>parseInt(n,10));
        curY = clamp(y,startYear,endYear);
        curM = clamp(m,1,12);
        await updateGrid(curY,curM,d);
      }
    }

    async function updateGrid(y,m,highlightD=null) {
      buildYearOptions(yearSel,startYear,endYear,y);
      monthSel.value = String(m);

      const data = await fetchMonth(y,m);
      grid.innerHTML = '';

      data.weeks.forEach(week=>{
        const tr = document.createElement('tr');
        week.forEach(cell=>{
          const td = document.createElement('td');
          td.textContent = cell.bsD;
          td.classList.add('py-1');
          td.style.cursor = 'pointer';
          if (!cell.isCurrentMonth) td.classList.add('text-muted');
          if (highlightD && cell.isCurrentMonth && cell.bsD===highlightD) td.classList.add('table-primary');
          td.title = `AD ${cell.ad}`;
          td.addEventListener('click', async ()=>{
            const bsStr = `${String(cell.bsY).padStart(4,'0')}-${String(cell.bsM).padStart(2,'0')}-${String(cell.bsD).padStart(2,'0')}`;
            input.value = bsStr;
            const adStr = await bsToAd(cell.bsY, cell.bsM, cell.bsD);
            hiddenAd.value = adStr;
            info.textContent = `Selected: BS ${bsStr} (AD ${adStr})`;
            popup.classList.add('d-none');
            input.dispatchEvent(new Event('change'));
          });
          tr.appendChild(td);
        });
        grid.appendChild(tr);
      });
    }

    btn.addEventListener('click', async ()=>{
      if (popup.classList.contains('d-none')) {
        popup.classList.remove('d-none');
        await setFromInputOrToday();
      } else {
        popup.classList.add('d-none');
      }
    });

    yearSel.addEventListener('change', async ()=>{ curY=parseInt(yearSel.value,10); await updateGrid(curY,curM); });
    monthSel.addEventListener('change', async ()=>{ curM=parseInt(monthSel.value,10); await updateGrid(curY,curM); });

    prevBtn.addEventListener('click', async ()=>{
      curM--; if (curM<1){curM=12;curY=Math.max(startYear,curY-1);}
      await updateGrid(curY,curM);
    });
    nextBtn.addEventListener('click', async ()=>{
      curM++; if (curM>12){curM=1;curY=Math.min(endYear,curY+1);}
      await updateGrid(curY,curM);
    });
    todayBtn.addEventListener('click', async ()=>{
      const todayAd = new Date().toISOString().slice(0,10);
      const bs = await adToBs(todayAd);
      const [y,m,d] = bs.split('-').map(n=>parseInt(n,10));
      curY = clamp(y,startYear,endYear); curM = clamp(m,1,12);
      await updateGrid(curY,curM,d);
    });

    document.addEventListener('click', (e)=>{
      if (!wrapper.contains(e.target)) popup.classList.add('d-none');
    });

    // if pre-filled, compute hidden AD once
    if (input.value && /^\d{4}-\d{2}-\d{2}$/.test(input.value)) {
      const [y,m,d] = input.value.split('-').map(n=>parseInt(n,10));
      bsToAd(y,m,d).then(ad => hiddenAd.value = ad);
    }
  }

  function init() {
    document.querySelectorAll('[data-bsdp]').forEach(el=>{
      const wrapper = el.closest('.position-relative');
      if (wrapper) initOne(wrapper);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();


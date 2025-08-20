document.addEventListener('DOMContentLoaded', () => {
  const ENDPOINT = '../backend/get_estado.php'; // <--- AJUSTA ESTA RUTA
  const el = document.getElementById('estado-boton');
  const mapEl = document.getElementById('map');

  if (!el) {
    console.error('No existe #estado-boton en el DOM');
    return;
  }

  function setClass(state) {
    el.classList.remove('text-primary','text-success','text-danger','text-muted');
    if (state === 'online') el.classList.add('text-success');
    else if (state === 'offline') el.classList.add('text-danger');
    else el.classList.add('text-muted');
  }

  function fmtLastSeen(ts) {
    if (!ts) return '—';
    const d = new Date((ts+'').replace(' ', 'T'));
    return isNaN(d) ? ts : d.toLocaleString();
  }
  const fmtMinutes = m => (m==null || isNaN(m)) ? '—' : `${m} min`;
  const fmtBattery = mv => (mv==null) ? '—' : `${(mv/1000).toFixed(2)} V (${mv} mV)`;

  async function cargarEstado() {
    try {
      const res = await fetch(ENDPOINT, {
        method: 'GET',
        credentials: 'include',           // incluye cookies de sesión
        headers: { 'Accept': 'application/json' }
      });

      const raw = await res.text();       // leemos como texto primero
      let data;
      try { data = JSON.parse(raw); }     // intentamos parsear a JSON
      catch {
        setClass('error');
        el.innerHTML = `<span class="fw-semibold">Error</span><br>
                        <small>Respuesta no es JSON válido:</small><br>
                        <code style="white-space:pre-wrap">${raw.slice(0,400)}</code>`;
        console.error('Respuesta cruda:', raw);
        return;
      }

      if (!res.ok || data.error) {
        setClass('error');
        el.innerHTML = `<span class="fw-semibold">Error</span><br>
                        <small>${data.error || 'No se pudo obtener el estado'}</small>`;
        console.error('Error backend:', data);
        return;
      }

      const estado = (data.estado || 'sin datos').toLowerCase();
      setClass(estado);
      el.innerHTML = `
        <span class="fw-semibold text-uppercase">${estado}</span>
        <br><small>Último latido: ${fmtLastSeen(data.last_seen)} (${fmtMinutes(data.minutes_since)})</small>
        <br><small>Batería: ${fmtBattery(data.battery_mv)}</small>
      `;

      if (mapEl && data.latitude != null && data.longitude != null) {
        const lat = parseFloat(data.latitude);
        const lon = parseFloat(data.longitude);
        const dLat = 0.003;
        const dLon = 0.005;
        const bbox = [
          (lon - dLon).toFixed(7),
          (lat - dLat).toFixed(7),
          (lon + dLon).toFixed(7),
          (lat + dLat).toFixed(7)
        ];
        mapEl.src = `https://www.openstreetmap.org/export/embed.html?bbox=${bbox[0]}%2C${bbox[1]}%2C${bbox[2]}%2C${bbox[3]}&layer=mapnik&marker=${lat}%2C${lon}`;
      }
    } catch (e) {
      setClass('error');
      el.innerHTML = `<span class="fw-semibold">Error</span><br>
                      <small>No se pudo conectar con el servidor</small>`;
      console.error(e);
    }
  }

  cargarEstado();
  setInterval(cargarEstado, 30000);
});

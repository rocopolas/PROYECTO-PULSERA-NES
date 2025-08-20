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
  const isoish = typeof ts === 'string' && ts.includes(' ') ? ts.replace(' ', 'T') : ts;
  const d = new Date(isoish);
  return isNaN(d) ? String(ts) : d.toLocaleString();
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

        if (Number.isFinite(lat) && Number.isFinite(lon)) {
          // clamp a rangos válidos
          const clampedLat = Math.max(-90, Math.min(90, lat));
          const clampedLon = Math.max(-180, Math.min(180, lon));

          // ~dLat ~ 0.003° ≈ 330 m; ajustá a gusto
          const dLat = 0.003;
          const cos = Math.cos((clampedLat * Math.PI) / 180) || 1; // evita NaN cerca de polos
          const dLon = 0.005 * cos; // ajusta por latitud

          const left   = (clampedLon - dLon).toFixed(7);
          const bottom = (clampedLat - dLat).toFixed(7);
          const right  = (clampedLon + dLon).toFixed(7);
          const top    = (clampedLat + dLat).toFixed(7);

          const url = new URL("https://www.openstreetmap.org/export/embed.html");
          url.searchParams.set("bbox", `${left},${bottom},${right},${top}`);
          url.searchParams.set("layer", "mapnik");
          url.searchParams.set("marker", `${clampedLat},${clampedLon}`);

          // opcional: forzar un zoom aproximado si no querés bbox
          // url.searchParams.set("zoom", "16");

          mapEl.src = url.toString();
        } else {
          console.warn("Coordenadas inválidas: ", data.latitude, data.longitude);
        }
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

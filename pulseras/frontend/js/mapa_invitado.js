document.addEventListener('DOMContentLoaded', () => {
  const ENDPOINT = '../backend/get_estado.php';
  const mapEl = document.getElementById('map');
  if (!mapEl) return;

  async function cargarMapa() {
    try {
      const res = await fetch(ENDPOINT, {
        method: 'GET',
        credentials: 'include',
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();
      if (!res.ok || data.error) return;
      if (data.latitude != null && data.longitude != null) {
        const lat = parseFloat(data.latitude);
        const lon = parseFloat(data.longitude);
        if (Number.isFinite(lat) && Number.isFinite(lon)) {
          const clampedLat = Math.max(-90, Math.min(90, lat));
          const clampedLon = Math.max(-180, Math.min(180, lon));
          const dLat = 0.003;
          const cos = Math.cos((clampedLat * Math.PI) / 180) || 1;
          const dLon = 0.005 * cos;
          const left   = (clampedLon - dLon).toFixed(7);
          const bottom = (clampedLat - dLat).toFixed(7);
          const right  = (clampedLon + dLon).toFixed(7);
          const top    = (clampedLat + dLat).toFixed(7);
          const url = new URL('https://www.openstreetmap.org/export/embed.html');
          url.searchParams.set('bbox', `${left},${bottom},${right},${top}`);
          url.searchParams.set('layer', 'mapnik');
          url.searchParams.set('marker', `${clampedLat},${clampedLon}`);
          mapEl.src = url.toString();
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  cargarMapa();
  setInterval(cargarMapa, 30000);
});

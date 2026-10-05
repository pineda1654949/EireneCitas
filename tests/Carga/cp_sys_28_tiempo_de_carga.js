/**
 * CP-SYS-28 (tiempo de carga): un usuario recorre las paginas principales.
 * Criterio: cada pagina responde en promedio en menos de 300 ms.
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE, iniciarSesion, proximoLunes } from './lib.js';

const PAGINAS = {
    login: '/login',
    home: '/home',
    citas: '/citas',
    citas_crear: '/citas/crear',
    api_agenda: null, // se arma con el psicologo y la fecha
};

export const options = {
    scenarios: {
        recorrido: { executor: 'per-vu-iterations', vus: 1, iterations: 30 },
    },
    thresholds: Object.fromEntries(
        Object.keys(PAGINAS).map((p) => [`http_req_duration{pagina:${p}}`, ['avg<300']]),
    ),
};

export default function () {
    if (__ITER === 0) {
        iniciarSesion('k6_paciente_100@eirene.test');
    }

    for (const [pagina, ruta] of Object.entries(PAGINAS)) {
        const url = ruta ?? `/api/agenda-del-dia?psicologo_id=${__ENV.PSICOLOGO_ID}&fecha=${proximoLunes(2)}`;
        const r = http.get(`${BASE}${url}`, { tags: { pagina } });
        check(r, { [`${pagina} 200`]: (x) => x.status === 200 || (pagina === 'login' && x.status === 302) });
    }

    sleep(0.5);
}

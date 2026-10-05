/**
 * CP-SYS-26 (estres): sube de 0 a 100 usuarios simultaneos para encontrar el
 * punto en que el servidor se degrada. Criterio: el sistema no falla (<5 %
 * de errores) aunque los tiempos aumenten.
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE, iniciarSesion } from './lib.js';

export const options = {
    scenarios: {
        estres: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '30s', target: 50 },
                { duration: '30s', target: 100 },
                { duration: '30s', target: 100 },
                { duration: '15s', target: 0 },
            ],
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.05'],
        checks: ['rate>0.95'],
    },
};

export default function () {
    if (__ITER === 0) {
        iniciarSesion(`k6_paciente_${((__VU - 1) % 120) + 1}@eirene.test`);
    }

    const r = http.get(`${BASE}/citas`, { tags: { pagina: 'citas' } });
    check(r, { 'respuesta 200': (x) => x.status === 200 });
    sleep(1);
}

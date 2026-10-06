/**
 * CP-SYS-27 (estabilidad): carga moderada y constante (10 usuarios) durante
 * 5 minutos. Criterio: sin errores y sin degradacion progresiva del tiempo
 * de respuesta. En produccion se recomienda repetirla por varias horas.
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE, iniciarSesion, proximoLunes } from './lib.js';

export const options = {
    scenarios: {
        estabilidad: { executor: 'constant-vus', vus: 10, duration: '5m' },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<500'],
    },
};

export default function () {
    if (__ITER === 0) {
        iniciarSesion(`k6_paciente_${__VU + 60}@eirene.test`);
    }

    const r1 = http.get(`${BASE}/home`, { tags: { pagina: 'home' } });
    const r2 = http.get(`${BASE}/api/agenda-del-dia?psicologo_id=${__ENV.PSICOLOGO_ID}&fecha=${proximoLunes(4)}`, { tags: { pagina: 'api_agenda' } });
    check(r1, { 'home 200': (r) => r.status === 200 });
    check(r2, { 'agenda 200': (r) => r.status === 200 });
    sleep(2);
}

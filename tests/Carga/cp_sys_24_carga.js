/**
 * CP-SYS-24 (carga): 25 pacientes navegando a la vez durante 1 minuto.
 * Criterio: tiempo medio < 300 ms, menos de 1 % de errores.
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE, iniciarSesion, proximoLunes } from './lib.js';

export const options = {
    scenarios: {
        carga: { executor: 'constant-vus', vus: 25, duration: '1m' },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['avg<300', 'p(95)<800'],
        checks: ['rate>0.99'],
    },
};

export default function () {
    if (__ITER === 0) {
        iniciarSesion(`k6_paciente_${__VU}@eirene.test`);
    }

    const respuestas = http.batch([
        ['GET', `${BASE}/home`, null, { tags: { pagina: 'home' } }],
        ['GET', `${BASE}/citas`, null, { tags: { pagina: 'citas' } }],
        ['GET', `${BASE}/citas/crear`, null, { tags: { pagina: 'citas_crear' } }],
        ['GET', `${BASE}/api/agenda-del-dia?psicologo_id=${__ENV.PSICOLOGO_ID}&fecha=${proximoLunes(3)}`, null, { tags: { pagina: 'api_agenda' } }],
    ]);

    respuestas.forEach((r) => check(r, { 'respuesta 200': (x) => x.status === 200 }));
    sleep(1);
}

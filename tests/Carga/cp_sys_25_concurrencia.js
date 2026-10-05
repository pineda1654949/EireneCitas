/**
 * CP-SYS-25 (concurrencia) y CP-SYS-06 a escala: 30 pacientes intentan reservar
 * EXACTAMENTE la misma hora con el mismo psicologo al mismo instante.
 * Resultado esperado (RN-03): una sola reserva exitosa, sin dobles reservas.
 *
 * k6 run -e BASE=... -e PSICOLOGO_ID=.. -e ESPECIALIDAD_ID=.. tests/Carga/cp_sys_25_concurrencia.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter } from 'k6/metrics';
import { BASE, iniciarSesion, proximoLunes, tokenCsrf } from './lib.js';

const exitosas = new Counter('reservas_exitosas');
const rechazadas = new Counter('reservas_rechazadas_por_horario_ocupado');
const errores = new Counter('respuestas_inesperadas');

const USUARIOS = 30;

export const options = {
    scenarios: {
        misma_hora: { executor: 'per-vu-iterations', vus: USUARIOS, iterations: 1, maxDuration: '2m' },
    },
    thresholds: {
        reservas_exitosas: ['count==1'],
        respuestas_inesperadas: ['count==0'],
    },
};

export function setup() {
    // Instante comun de disparo: da tiempo a que los 30 usuarios inicien sesion.
    return { disparo: Date.now() + 20000, fecha: proximoLunes(2) };
}

export default function ({ disparo, fecha }) {
    iniciarSesion(`k6_paciente_${__VU}@eirene.test`);
    const formulario = http.get(`${BASE}/citas/crear`);
    const token = tokenCsrf(formulario.body);

    // Todos esperan al mismo instante para enviar la reserva a la vez.
    const espera = (disparo - Date.now()) / 1000;
    if (espera > 0) sleep(espera);

    const respuesta = http.post(`${BASE}/citas`, {
        _token: token,
        especialidad_id: __ENV.ESPECIALIDAD_ID,
        psicologo_id: __ENV.PSICOLOGO_ID,
        fecha,
        hora: __ENV.HORA || '10:00',
    }, { redirects: 0 });

    const destino = String(respuesta.headers.Location || '');

    if (respuesta.status === 302 && /\/citas\/\d+$/.test(destino)) {
        exitosas.add(1);
    } else if (respuesta.status === 302) {
        rechazadas.add(1); // vuelve al formulario con "Ese horario no esta disponible"
    } else {
        errores.add(1, { status: String(respuesta.status) });
        console.warn(`Respuesta inesperada: HTTP ${respuesta.status} -> ${destino}`);
    }

    check(respuesta, { 'sin errores 500': (r) => r.status < 500 });
}

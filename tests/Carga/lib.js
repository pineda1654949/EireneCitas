/**
 * Utilidades comunes de las pruebas de carga (k6) - Fase 8, EireneCitas (Laravel 12).
 * La URL base se indica con: k6 run -e BASE=http://localhost/EireneCitas/public script.js
 */
import http from 'k6/http';
import { check } from 'k6';

export const BASE = __ENV.BASE || 'http://localhost/EireneCitas/public';
export const CLAVE = 'Carga2026x';

/** Extrae el token CSRF (_token) de un formulario de Laravel. */
export function tokenCsrf(html) {
    const coincidencia = /name="_token" value="([^"]+)"/.exec(html);
    return coincidencia ? coincidencia[1] : '';
}

/** Inicia sesion con el formulario real (cookie de sesion + CSRF). */
export function iniciarSesion(usuario, clave = CLAVE) {
    const formulario = http.get(`${BASE}/login`, { tags: { pagina: 'login' } });
    const respuesta = http.post(
        `${BASE}/login`,
        { _token: tokenCsrf(formulario.body), email: usuario, password: clave },
        { redirects: 0, tags: { pagina: 'login_post' } },
    );

    const ok = check(respuesta, {
        'login redirige al panel': (r) => r.status === 302 && String(r.headers.Location).endsWith('/home'),
    });

    return ok;
}

/** Proximo lunes (YYYY-MM-DD) desplazado N semanas. */
export function proximoLunes(semanas = 1) {
    const hoy = new Date();
    const dias = ((8 - hoy.getDay()) % 7) || 7;
    const fecha = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + dias + 7 * (semanas - 1));
    const mm = String(fecha.getMonth() + 1).padStart(2, '0');
    const dd = String(fecha.getDate()).padStart(2, '0');
    return `${fecha.getFullYear()}-${mm}-${dd}`;
}

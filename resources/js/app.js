/**
 * Interacciones de la interfaz de Eirene.
 * Sin scripts en linea: todo se engancha con atributos data-* para poder
 * aplicar una Content-Security-Policy estricta (script-src 'self').
 */
import { iniciarReservas } from './reserva';

document.addEventListener('DOMContentLoaded', () => {
    iniciarMenuLateral();
    iniciarMenusDesplegables();
    iniciarConfirmaciones();
    iniciarAlertas();
    iniciarReservas();
});

/** Menu lateral desplegable en pantallas pequenas. */
function iniciarMenuLateral() {
    const menu = document.getElementById('sidebar');
    const fondo = document.getElementById('sidebar-backdrop');
    if (!menu || !fondo) return;

    const abrir = (abierto) => {
        menu.classList.toggle('-translate-x-full', !abierto);
        fondo.classList.toggle('hidden', !abierto);
        document.body.classList.toggle('overflow-hidden', abierto);
    };

    document.querySelectorAll('[data-sidebar-open]').forEach((boton) => boton.addEventListener('click', () => abrir(true)));
    document.querySelectorAll('[data-sidebar-close]').forEach((boton) => boton.addEventListener('click', () => abrir(false)));
    fondo.addEventListener('click', () => abrir(false));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && abrir(false));
}

/** Menus desplegables (p. ej. el menu del usuario). */
function iniciarMenusDesplegables() {
    document.querySelectorAll('[data-dropdown]').forEach((contenedor) => {
        const boton = contenedor.querySelector('[data-dropdown-toggle]');
        const lista = contenedor.querySelector('[data-dropdown-menu]');
        if (!boton || !lista) return;

        const alternar = (abierto) => {
            lista.classList.toggle('hidden', !abierto);
            boton.setAttribute('aria-expanded', String(abierto));
        };

        boton.addEventListener('click', (e) => {
            e.stopPropagation();
            alternar(lista.classList.contains('hidden'));
        });
        document.addEventListener('click', (e) => !contenedor.contains(e.target) && alternar(false));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && alternar(false));
    });
}

/** Pide confirmacion antes de enviar formularios destructivos. */
function iniciarConfirmaciones() {
    document.querySelectorAll('form[data-confirm]').forEach((formulario) => {
        formulario.addEventListener('submit', (e) => {
            if (!window.confirm(formulario.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
}

/** Cierre manual y automatico de mensajes flash. */
function iniciarAlertas() {
    document.querySelectorAll('[data-alert]').forEach((alerta) => {
        alerta.querySelector('[data-dismiss]')?.addEventListener('click', () => alerta.remove());

        if (alerta.dataset.autoclose !== undefined) {
            setTimeout(() => {
                alerta.classList.add('opacity-0');
                setTimeout(() => alerta.remove(), 300);
            }, 6000);
        }
    });
}

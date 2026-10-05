/**
 * Formulario de reserva y reprogramacion de citas (RF-02, RF-06, RF-07).
 * Especialidad -> psicologos que la atienden -> matriz de horas del dia:
 *   Verde = libre (se puede elegir), Rojo = ocupado, Gris = bloqueado.
 *
 * Marcado esperado:
 *   <form data-reserva data-url-psicologos=".../especialidades/__ID__/psicologos"
 *         data-url-agenda=".../agenda-del-dia" data-cita-id="(opcional)">
 *     <select data-especialidad>  <select data-psicologo data-inicial="">  (o input hidden data-psicologo)
 *     <input type="date" data-fecha>
 *     <div data-horas data-inicial="">
 */
export function iniciarReservas() {
    document.querySelectorAll('form[data-reserva]').forEach(iniciarFormulario);
}

const ETIQUETAS = { libre: 'Libre', ocupado: 'Ocupado', bloqueado: 'No disponible' };

function iniciarFormulario(form) {
    const especialidad = form.querySelector('[data-especialidad]');
    const psicologo = form.querySelector('[data-psicologo]');
    const fecha = form.querySelector('[data-fecha]');
    const horas = form.querySelector('[data-horas]');
    const ayuda = form.querySelector('[data-horas-ayuda]');
    const leyenda = form.querySelector('[data-horas-leyenda]');

    let horaInicial = horas?.dataset.inicial || '';

    const pedirJson = async (url) => {
        const respuesta = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!respuesta.ok) throw new Error(`Error ${respuesta.status}`);
        return respuesta.json();
    };

    const mostrarAyuda = (texto) => {
        horas.replaceChildren();
        leyenda?.classList.add('hidden');
        if (ayuda) {
            ayuda.textContent = texto;
            ayuda.classList.remove('hidden');
        }
    };

    async function cargarPsicologos() {
        if (!especialidad || psicologo.type === 'hidden') return;

        const inicial = psicologo.dataset.inicial || '';
        psicologo.dataset.inicial = '';
        mostrarAyuda('Elige psicólogo y fecha para ver las horas.');

        if (!especialidad.value) {
            psicologo.replaceChildren(new Option('Primero elige una especialidad...', ''));
            psicologo.disabled = true;
            return;
        }

        psicologo.disabled = true;
        psicologo.replaceChildren(new Option('Cargando...', ''));

        try {
            const lista = await pedirJson(form.dataset.urlPsicologos.replace('__ID__', especialidad.value));

            psicologo.replaceChildren(
                new Option(lista.length ? 'Selecciona un psicólogo...' : 'No hay psicólogos disponibles para esta especialidad', ''),
                ...lista.map((p) => new Option(p.nombre, p.id, false, String(p.id) === inicial)),
            );
            psicologo.disabled = lista.length === 0;
        } catch {
            psicologo.replaceChildren(new Option('No se pudo cargar la lista. Intenta de nuevo.', ''));
        }

        if (psicologo.value) await cargarHoras();
    }

    async function cargarHoras() {
        if (!psicologo.value || !fecha.value) {
            mostrarAyuda('Elige psicólogo y fecha para ver las horas.');
            return;
        }

        mostrarAyuda('Consultando la agenda...');

        const parametros = new URLSearchParams({ psicologo_id: psicologo.value, fecha: fecha.value });
        if (form.dataset.citaId) parametros.set('cita_id', form.dataset.citaId);

        try {
            const agenda = await pedirJson(`${form.dataset.urlAgenda}?${parametros}`);

            if (agenda.length === 0) {
                mostrarAyuda('El psicólogo no atiende ese día. Prueba con otra fecha.');
                return;
            }

            ayuda?.classList.toggle('hidden', agenda.some((f) => f.estado === 'libre'));
            if (ayuda && !agenda.some((f) => f.estado === 'libre')) {
                ayuda.textContent = 'Todas las horas de ese día están ocupadas. Prueba con otra fecha.';
            }

            horas.replaceChildren(...agenda.map((franja) => crearFranja(franja, franja.hora === horaInicial)));
            leyenda?.classList.remove('hidden');
            horaInicial = '';
        } catch {
            mostrarAyuda('No se pudo consultar la agenda. Intenta de nuevo.');
        }
    }

    especialidad?.addEventListener('change', cargarPsicologos);
    psicologo?.addEventListener('change', cargarHoras);
    fecha?.addEventListener('change', cargarHoras);

    // Al volver con errores de validacion se restauran las selecciones previas.
    if (especialidad?.value) {
        cargarPsicologos();
    } else if (psicologo?.value && fecha?.value) {
        cargarHoras();
    }
}

/**
 * Cada hora es un radio estilizado. Las ocupadas o bloqueadas se muestran
 * deshabilitadas para que no puedan elegirse (CP-UT-44).
 */
function crearFranja({ hora, estado }, marcada) {
    const etiqueta = document.createElement('label');
    etiqueta.className = `hora-chip hora-${estado}`;
    etiqueta.dataset.estado = estado;
    etiqueta.title = ETIQUETAS[estado] ?? estado;

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'hora';
    radio.value = hora;
    radio.required = true;
    radio.disabled = estado !== 'libre';
    radio.checked = marcada && estado === 'libre';
    radio.className = 'sr-only';
    radio.setAttribute('aria-label', `${hora} - ${ETIQUETAS[estado] ?? estado}`);

    etiqueta.append(radio, document.createTextNode(hora));
    return etiqueta;
}

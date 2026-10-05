/**
 * Formulario de reserva y reprogramacion de citas (RF-01, RF-02, RF-03).
 * Especialidad -> psicologos que la atienden -> horas libres en tiempo real.
 *
 * Marcado esperado:
 *   <form data-reserva data-url-psicologos=".../especialidades/__ID__/psicologos"
 *         data-url-horas=".../horas-disponibles" data-cita-id="(opcional)">
 *     <select data-especialidad>  <select data-psicologo data-inicial="">  (o input hidden data-psicologo)
 *     <input type="date" data-fecha>
 *     <div data-horas data-inicial="">
 */
export function iniciarReservas() {
    document.querySelectorAll('form[data-reserva]').forEach(iniciarFormulario);
}

function iniciarFormulario(form) {
    const especialidad = form.querySelector('[data-especialidad]');
    const psicologo = form.querySelector('[data-psicologo]');
    const fecha = form.querySelector('[data-fecha]');
    const horas = form.querySelector('[data-horas]');
    const ayuda = form.querySelector('[data-horas-ayuda]');

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
        if (ayuda) {
            ayuda.textContent = texto;
            ayuda.classList.remove('hidden');
        }
    };

    async function cargarPsicologos() {
        if (!especialidad || psicologo.type === 'hidden') return;

        const inicial = psicologo.dataset.inicial || '';
        psicologo.dataset.inicial = '';
        mostrarAyuda('Elige psicologo y fecha para ver las horas libres.');

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
                new Option(lista.length ? 'Selecciona un psicologo...' : 'No hay psicologos disponibles para esta especialidad', ''),
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
            mostrarAyuda('Elige psicologo y fecha para ver las horas libres.');
            return;
        }

        mostrarAyuda('Buscando horas disponibles...');

        const parametros = new URLSearchParams({ psicologo_id: psicologo.value, fecha: fecha.value });
        if (form.dataset.citaId) parametros.set('cita_id', form.dataset.citaId);

        try {
            const lista = await pedirJson(`${form.dataset.urlHoras}?${parametros}`);

            if (lista.length === 0) {
                mostrarAyuda('No hay horas disponibles ese dia. Prueba con otra fecha.');
                return;
            }

            ayuda?.classList.add('hidden');
            horas.replaceChildren(...lista.map((hora) => crearOpcionHora(hora, hora === horaInicial)));
            horaInicial = '';
        } catch {
            mostrarAyuda('No se pudieron consultar las horas. Intenta de nuevo.');
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

function crearOpcionHora(hora, marcada) {
    const etiqueta = document.createElement('label');
    etiqueta.className = 'hora-chip';

    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'hora';
    radio.value = hora;
    radio.required = true;
    radio.checked = marcada;
    radio.className = 'sr-only';

    etiqueta.append(radio, document.createTextNode(hora));
    return etiqueta;
}

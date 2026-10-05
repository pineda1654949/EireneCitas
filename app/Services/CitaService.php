<?php

namespace App\Services;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Exceptions\ReglaDeNegocioException;
use App\Models\Cita;
use App\Models\HistorialClinico;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Reprogramacion;
use App\Models\User;
use App\Notifications\CitaCancelada;
use App\Notifications\CitaConfirmada;
use App\Notifications\CitaRegistrada;
use App\Notifications\CitaReprogramada;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso del ciclo de vida de una cita (RF-01, RF-03, RF-04, RF-06).
 * Cada operacion valida sus reglas de negocio y se ejecuta dentro de una
 * transaccion; las notificaciones se envian solo si la transaccion se confirma.
 */
class CitaService
{
    public function __construct(private readonly AgendaService $agenda) {}

    /**
     * RF-01: registro de una cita.
     *
     * @param  array{psicologo_id: int|string, especialidad_id: int|string, fecha: string, hora: string, promocion_id?: int|string|null, motivo_consulta?: string|null}  $datos
     */
    public function registrar(Paciente $paciente, array $datos, User $creador): Cita
    {
        $cita = DB::transaction(function () use ($paciente, $datos, $creador) {
            $psicologo = $this->bloquearAgenda((int) $datos['psicologo_id']);

            if (! $psicologo->especialidades()->whereKey($datos['especialidad_id'])->exists()) {
                throw new ReglaDeNegocioException('El psicólogo seleccionado no atiende esa especialidad.', 'psicologo_id');
            }

            $this->exigirDisponibilidad($psicologo->id, $datos['fecha'], $datos['hora']);
            $this->exigirPacienteLibre($paciente->id, $datos['fecha'], $datos['hora']);

            return Cita::create([
                'paciente_id' => $paciente->id,
                'psicologo_id' => $psicologo->id,
                'especialidad_id' => $datos['especialidad_id'],
                'promocion_id' => $datos['promocion_id'] ?? null,
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'motivo_consulta' => $datos['motivo_consulta'] ?? null,
                'estado' => EstadoCita::Pendiente,
                'creado_por' => $creador->id,
            ]);
        });

        $this->notificar($cita, new CitaRegistrada($cita), alPsicologo: true);

        return $cita;
    }

    /**
     * RF-03: reprogramacion, con limite maximo configurable (3 por defecto).
     */
    public function reprogramar(Cita $cita, string $fecha, string $hora, ?string $motivo, User $usuario): Cita
    {
        if (! $cita->puedeReprogramarse()) {
            throw new ReglaDeNegocioException(
                'Esta cita ya no puede reprogramarse: alcanzó el límite de '.Cita::maxReprogramaciones().' reprogramaciones o ya fue cerrada.',
                'fecha',
            );
        }

        if ($cita->fecha->toDateString() === $fecha && $cita->hora_corta === substr($hora, 0, 5)) {
            throw new ReglaDeNegocioException('Elige una fecha u hora distinta a la actual de la cita.', 'hora');
        }

        DB::transaction(function () use ($cita, $fecha, $hora, $motivo, $usuario) {
            $this->bloquearAgenda($cita->psicologo_id);
            $this->exigirDisponibilidad($cita->psicologo_id, $fecha, $hora, $cita->id);
            $this->exigirPacienteLibre($cita->paciente_id, $fecha, $hora, $cita->id);

            Reprogramacion::create([
                'cita_id' => $cita->id,
                'realizado_por' => $usuario->id,
                'tipo' => Reprogramacion::TIPO_REPROGRAMACION,
                'fecha_anterior' => $cita->fecha,
                'hora_anterior' => $cita->hora,
                'fecha_nueva' => $fecha,
                'hora_nueva' => $hora,
                'motivo' => $motivo,
            ]);

            $cita->update([
                'fecha' => $fecha,
                'hora' => $hora,
                'estado' => EstadoCita::Reprogramada,
                'numero_reprogramaciones' => $cita->numero_reprogramaciones + 1,
            ]);
        });

        $this->notificar($cita, new CitaReprogramada($cita), alPsicologo: true);

        return $cita;
    }

    /**
     * RF-03: cancelacion, dejando constancia en el historial de cambios.
     */
    public function cancelar(Cita $cita, ?string $motivo, User $usuario): Cita
    {
        if (! $cita->puedeCancelarse()) {
            throw new ReglaDeNegocioException('Esta cita ya fue cancelada o atendida; no puede cancelarse.', 'estado');
        }

        DB::transaction(function () use ($cita, $motivo, $usuario) {
            Reprogramacion::create([
                'cita_id' => $cita->id,
                'realizado_por' => $usuario->id,
                'tipo' => Reprogramacion::TIPO_CANCELACION,
                'fecha_anterior' => $cita->fecha,
                'hora_anterior' => $cita->hora,
                'motivo' => $motivo,
            ]);

            $cita->update(['estado' => EstadoCita::Cancelada]);
        });

        $this->notificar($cita, new CitaCancelada($cita), alPsicologo: true);

        return $cita;
    }

    /**
     * RF-04: confirmacion manual, solo con un pago ya validado.
     */
    public function confirmar(Cita $cita): Cita
    {
        if (! $cita->estado->admiteConfirmacion()) {
            throw new ReglaDeNegocioException('Solo se pueden confirmar citas pendientes o reprogramadas.', 'estado');
        }

        if (! $cita->pagoConfirmado()) {
            throw new ReglaDeNegocioException('No se puede confirmar la cita: primero registra y valida su pago.', 'estado');
        }

        $cita->update(['estado' => EstadoCita::Confirmada]);

        $this->notificar($cita, new CitaConfirmada($cita));

        return $cita;
    }

    /**
     * @param  array{monto: float|int|string, metodo_pago: string, numero_comprobante?: string|null}  $datos
     */
    public function registrarPago(Cita $cita, array $datos): Pago
    {
        if ($cita->estado->estaCerrada()) {
            throw new ReglaDeNegocioException('No se pueden registrar pagos de una cita cancelada o atendida.', 'monto');
        }

        return $cita->pagos()->create([
            'monto' => $datos['monto'],
            'metodo_pago' => $datos['metodo_pago'],
            'numero_comprobante' => $datos['numero_comprobante'] ?? null,
            'estado' => EstadoPago::Pendiente,
        ]);
    }

    /**
     * Valida el pago y, si la cita sigue abierta, la confirma (RF-04).
     *
     * @return bool true si la cita quedo confirmada
     */
    public function validarPago(Pago $pago, User $usuario): bool
    {
        if ($pago->estado !== EstadoPago::Pendiente) {
            throw new ReglaDeNegocioException('Este pago ya fue procesado.', 'pago');
        }

        $cita = $pago->cita;

        $confirmada = DB::transaction(function () use ($pago, $cita, $usuario) {
            $pago->update([
                'estado' => EstadoPago::Confirmado,
                'fecha_pago' => now(),
                'validado_por' => $usuario->id,
            ]);

            if (! $cita->estado->admiteConfirmacion()) {
                return false;
            }

            $cita->update(['estado' => EstadoCita::Confirmada]);

            return true;
        });

        if ($confirmada) {
            $this->notificar($cita, new CitaConfirmada($cita));
        }

        return $confirmada;
    }

    public function rechazarPago(Pago $pago, User $usuario): void
    {
        if ($pago->estado !== EstadoPago::Pendiente) {
            throw new ReglaDeNegocioException('Este pago ya fue procesado.', 'pago');
        }

        $pago->update(['estado' => EstadoPago::Rechazado, 'validado_por' => $usuario->id]);
    }

    /**
     * RF-06: el psicologo registra la sesion y la cita queda atendida.
     */
    public function atenderSesion(Cita $cita, string $notas, ?string $avance, User $psicologo): HistorialClinico
    {
        if (! $cita->estado->estaActiva() && $cita->estado !== EstadoCita::Atendida) {
            throw new ReglaDeNegocioException('No se puede registrar la sesión de una cita cancelada.', 'notas_sesion');
        }

        if ($cita->fecha->isAfter(today())) {
            throw new ReglaDeNegocioException('La sesión aún no se realiza: solo puede registrarse a partir del día de la cita.', 'notas_sesion');
        }

        return DB::transaction(function () use ($cita, $notas, $avance, $psicologo) {
            $historial = HistorialClinico::updateOrCreate(
                ['cita_id' => $cita->id],
                [
                    'paciente_id' => $cita->paciente_id,
                    'psicologo_id' => $psicologo->id,
                    'notas_sesion' => $notas,
                    'avance' => $avance,
                ],
            );

            $cita->update(['estado' => EstadoCita::Atendida]);

            return $historial;
        });
    }

    /**
     * Bloquea la fila del psicologo hasta el fin de la transaccion: dos
     * reservas simultaneas del mismo horario se procesan una tras otra y la
     * segunda ve la cita de la primera (evita la doble reserva).
     */
    private function bloquearAgenda(int $psicologoId): User
    {
        $psicologo = User::psicologos()->activos()->whereKey($psicologoId)->lockForUpdate()->first();

        if (! $psicologo) {
            throw new ReglaDeNegocioException('El psicólogo seleccionado no está disponible.', 'psicologo_id');
        }

        return $psicologo;
    }

    private function exigirDisponibilidad(int $psicologoId, string $fecha, string $hora, ?int $ignorarCitaId = null): void
    {
        if (! $this->agenda->estaDisponible($psicologoId, $fecha, $hora, $ignorarCitaId)) {
            throw new ReglaDeNegocioException(
                'Ese horario no está disponible para el psicólogo. Elige otro horario disponible.',
                'hora',
            );
        }
    }

    /**
     * Un paciente no puede tener dos citas activas a la misma hora, aunque
     * sean con psicologos distintos (DEF-010).
     */
    private function exigirPacienteLibre(int $pacienteId, string $fecha, string $hora, ?int $ignorarCitaId = null): void
    {
        $ocupado = Cita::activas()
            ->where('paciente_id', $pacienteId)
            ->whereDate('fecha', $fecha)
            ->where('hora', Horario::normalizarHora(substr($hora, 0, 5)))
            ->when($ignorarCitaId, fn ($q) => $q->whereKeyNot($ignorarCitaId))
            ->exists();

        if ($ocupado) {
            throw new ReglaDeNegocioException('El paciente ya tiene otra cita a esa misma hora.', 'hora');
        }
    }

    private function notificar(Cita $cita, Notification $notificacion, bool $alPsicologo = false): void
    {
        $cita->loadMissing(['paciente.user', 'psicologo']);

        $cita->paciente->notify($notificacion);

        if ($alPsicologo) {
            $cita->psicologo->notify($notificacion);
        }
    }
}

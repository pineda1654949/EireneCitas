@use('App\Enums\EstadoPago')

@php
    $usuario = auth()->user();
    $puedeGestionarPagos = $usuario->can('gestionarPagos', $cita) && $cita->estado->admiteConfirmacion();
@endphp

<x-layouts.app :titulo="'Cita #'.$cita->id" :subtitulo="ucfirst($cita->fecha->isoFormat('dddd D [de] MMMM [de] YYYY')).' · '.$cita->hora_corta.' h'">
    <x-slot:acciones>
        <a href="{{ route('citas.index') }}" class="btn btn-ghost"><x-heroicon-m-arrow-left class="size-4" /> Volver</a>
    </x-slot:acciones>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card titulo="Datos de la cita">
                <x-slot:acciones>
                    <x-estado-cita :estado="$cita->estado" class="text-sm" />
                </x-slot:acciones>

                <dl class="divide-y divide-slate-100">
                    <x-dato etiqueta="Paciente">{{ $cita->paciente->nombre_completo }}</x-dato>
                    <x-dato etiqueta="Psicólogo">{{ $cita->psicologo->nombre_completo }}</x-dato>
                    <x-dato etiqueta="Especialidad">{{ $cita->especialidad?->nombre ?? '—' }}</x-dato>
                    <x-dato etiqueta="Promoción">{{ $cita->promocion?->nombre ?? 'Sesión individual' }}</x-dato>
                    <x-dato etiqueta="Fecha y hora">{{ $cita->fecha->format('d/m/Y') }} · {{ $cita->hora_corta }} h</x-dato>
                    <x-dato etiqueta="Motivo de consulta"><span class="font-normal whitespace-pre-line">{{ $cita->motivo_consulta ?: '—' }}</span></x-dato>
                    <x-dato etiqueta="Reprogramaciones">{{ $cita->numero_reprogramaciones }} de {{ App\Models\Cita::maxReprogramaciones() }}</x-dato>
                    @if ($cita->enlace_meet)
                        <x-dato etiqueta="Enlace de sesión">
                            <a href="{{ $cita->enlace_meet }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:underline">{{ $cita->enlace_meet }}</a>
                        </x-dato>
                    @endif
                </dl>
            </x-card>

            @if ($cita->historialClinico && $usuario->can('atender', $cita))
                <x-card titulo="Registro clínico de la sesión" descripcion="Información confidencial">
                    <x-slot:acciones>
                        <x-badge tono="sky">Avance: {{ $cita->historialClinico->avance ?: 'Sin especificar' }}</x-badge>
                    </x-slot:acciones>
                    <p class="text-sm whitespace-pre-line text-slate-700">{{ $cita->historialClinico->notas_sesion }}</p>
                </x-card>
            @endif

            @if ($cita->reprogramaciones->isNotEmpty())
                <x-card titulo="Historial de cambios">
                    <ol class="relative space-y-6 border-l border-slate-200 pl-6">
                        @foreach ($cita->reprogramaciones->sortByDesc('created_at') as $cambio)
                            <li class="relative">
                                <span @class([
                                    'absolute -left-[31px] flex size-4 items-center justify-center rounded-full ring-4 ring-white',
                                    'bg-violet-500' => $cambio->tipo === 'reprogramacion',
                                    'bg-rose-500' => $cambio->tipo === 'cancelacion',
                                ])></span>
                                <p class="text-sm font-medium text-slate-900">
                                    @if ($cambio->tipo === 'reprogramacion')
                                        Reprogramada del {{ $cambio->fecha_anterior?->format('d/m/Y') }} {{ substr((string) $cambio->hora_anterior, 0, 5) }}
                                        al {{ $cambio->fecha_nueva?->format('d/m/Y') }} {{ substr((string) $cambio->hora_nueva, 0, 5) }}
                                    @else
                                        Cancelada
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $cambio->created_at->format('d/m/Y H:i') }} · por {{ $cambio->usuario?->nombre_completo ?? 'Sistema' }}
                                </p>
                                @if ($cambio->motivo)
                                    <p class="mt-1 text-sm text-slate-600">“{{ $cambio->motivo }}”</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </x-card>
            @endif
        </div>

        <aside class="space-y-6">
            <x-card titulo="Acciones">
                <div class="grid gap-2">
                    @if ($puedeGestionarPagos)
                        <a href="{{ route('pagos.create', $cita) }}" class="btn btn-secondary">
                            <x-heroicon-o-credit-card class="size-5" /> Registrar pago
                        </a>
                    @endif

                    @can('confirmar', $cita)
                        @if ($cita->puedeConfirmarse())
                            <form method="POST" action="{{ route('citas.confirmar', $cita) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-success w-full"><x-heroicon-o-check-circle class="size-5" /> Confirmar cita</button>
                            </form>
                        @elseif ($cita->estado->admiteConfirmacion())
                            <p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800">Para confirmar la cita primero registra y valida un pago.</p>
                        @endif
                    @endcan

                    @if ($usuario->can('atender', $cita) && ! $cita->estado->estaCerrada() && ! $cita->fecha->isAfter(today()))
                        <a href="{{ route('psicologo.historial.create', $cita) }}" class="btn btn-primary">
                            <x-heroicon-o-document-text class="size-5" /> Atender sesión
                        </a>
                    @endif

                    @if ($usuario->esPsicologo() && $usuario->can('verHistorial', $cita->paciente))
                        <a href="{{ route('psicologo.historial.paciente', $cita->paciente) }}" class="btn btn-secondary">
                            <x-heroicon-o-folder-open class="size-5" /> Historia clínica del paciente
                        </a>
                    @endif

                    @if ($usuario->can('reprogramar', $cita) && $cita->puedeReprogramarse())
                        <a href="{{ route('citas.reprogramar.form', $cita) }}" class="btn btn-secondary">
                            <x-heroicon-o-arrow-path class="size-5" /> Reprogramar
                            <span class="text-xs font-normal text-slate-500">({{ $cita->reprogramacionesRestantes() }} disponibles)</span>
                        </a>
                    @endif

                    @if ($usuario->can('cancelar', $cita) && $cita->puedeCancelarse())
                        <a href="{{ route('citas.cancelar.form', $cita) }}" class="btn btn-ghost text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                            <x-heroicon-o-x-circle class="size-5" /> Cancelar cita
                        </a>
                    @endif

                    @if ($cita->estado->estaCerrada())
                        <p class="text-center text-sm text-slate-500">Esta cita está {{ mb_strtolower($cita->estado->etiqueta()) }}; no admite más cambios.</p>
                    @endif
                </div>
            </x-card>

            @if ($usuario->can('gestionarPagos', $cita) || $cita->pagos->isNotEmpty())
                <x-card titulo="Pagos" :padding="false">
                    @forelse ($cita->pagos as $pago)
                        <div class="border-b border-slate-100 px-5 py-4 last:border-0 sm:px-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900 tabular-nums">S/ {{ number_format((float) $pago->monto, 2) }}</p>
                                    <p class="text-xs text-slate-500">{{ $pago->metodo_pago->etiqueta() }} @if ($pago->numero_comprobante) · {{ $pago->numero_comprobante }} @endif</p>
                                </div>
                                <x-estado-pago :estado="$pago->estado" />
                            </div>

                            @if ($pago->estado === EstadoPago::Pendiente && $usuario->can('gestionarPagos', $cita))
                                <div class="mt-3 flex gap-2">
                                    <form method="POST" action="{{ route('pagos.validar', $pago) }}">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm">Validar</button>
                                    </form>
                                    <form method="POST" action="{{ route('pagos.rechazar', $pago) }}" data-confirm="¿Marcar este pago como rechazado?">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-secondary btn-sm">Rechazar</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-500 sm:px-6">Sin pagos registrados.</p>
                    @endforelse
                </x-card>
            @endif
        </aside>
    </div>
</x-layouts.app>

@use('App\Enums\EstadoPago')

<x-layouts.app titulo="Pagos" subtitulo="Valida los pagos para confirmar las citas (RF-04).">
    <x-card :padding="false">
        <nav class="flex gap-1 overflow-x-auto border-b border-slate-100 px-4 pt-3 sm:px-5" aria-label="Filtrar por estado">
            @foreach (['' => 'Todos'] + collect(EstadoPago::cases())->mapWithKeys(fn ($e) => [$e->value => $e->etiqueta()])->all() as $valor => $nombre)
                @php $activo = ($filtros['estado'] ?? '') === $valor; @endphp
                <a href="{{ route('pagos.index', array_filter(['estado' => $valor])) }}"
                   @class(['border-b-2 px-3 pb-3 text-sm font-medium whitespace-nowrap',
                           'border-brand-600 text-brand-700' => $activo,
                           'border-transparent text-slate-500 hover:text-slate-700' => ! $activo])>
                    {{ $nombre }}
                </a>
            @endforeach
        </nav>

        @if ($pagos->isEmpty())
            <x-vacio icono="banknotes" titulo="No hay pagos en esta categoría" />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Registrado</th>
                            <th scope="col">Paciente</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Método</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pagos as $pago)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <div>{{ $pago->created_at?->format('d/m/Y H:i') }}</div>
                                    <a href="{{ route('citas.show', $pago->cita) }}" class="text-xs text-brand-600 hover:underline">Cita #{{ $pago->cita_id }}</a>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $pago->cita->paciente->nombre_completo }}</div>
                                    <div class="text-xs text-slate-500">con {{ $pago->cita->psicologo->nombre_completo }}</div>
                                </td>
                                <td class="font-semibold text-slate-900 tabular-nums">S/ {{ number_format((float) $pago->monto, 2) }}</td>
                                <td>
                                    <div>{{ $pago->metodo_pago->etiqueta() }} <span class="text-xs text-slate-500">· {{ $pago->etiquetaCuota() }}</span></div>
                                    @if ($pago->numero_comprobante) <div class="text-xs text-slate-500">{{ $pago->numero_comprobante }}</div> @endif
                                    @if ($pago->tieneComprobante())
                                        <a href="{{ route('pagos.comprobante', $pago) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:underline">
                                            <x-heroicon-o-paper-clip class="size-3.5" /> Ver voucher
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <x-estado-pago :estado="$pago->estado" />
                                    @if ($pago->validadoPor) <div class="mt-1 text-xs text-slate-500">por {{ $pago->validadoPor->name }}</div> @endif
                                </td>
                                <td class="whitespace-nowrap text-right">
                                    @if ($pago->estado === EstadoPago::Pendiente)
                                        <form method="POST" action="{{ route('pagos.validar', $pago) }}" class="inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm"><x-heroicon-m-check class="size-4" /> Validar</button>
                                        </form>
                                        <form method="POST" action="{{ route('pagos.rechazar', $pago) }}" class="inline" data-confirm="¿Marcar este pago como rechazado?">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-secondary btn-sm">Rechazar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pagos->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $pagos->links() }}</div>
            @endif
        @endif
    </x-card>
</x-layouts.app>

@props(['citas', 'paciente' => true, 'psicologo' => true, 'especialidad' => false, 'vacio' => 'No hay citas para mostrar.'])

@if ($citas->isEmpty())
    <x-vacio icono="calendar" :titulo="$vacio" />
@else
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    @if ($paciente) <th scope="col">Paciente</th> @endif
                    @if ($psicologo) <th scope="col">Psicólogo</th> @endif
                    @if ($especialidad) <th scope="col">Especialidad</th> @endif
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($citas as $cita)
                    <tr>
                        <td class="whitespace-nowrap">
                            <div class="font-medium text-slate-900">{{ $cita->fecha->isoFormat('ddd D MMM YYYY') }}</div>
                            <div class="text-xs text-slate-500">{{ $cita->hora_corta }} h</div>
                        </td>
                        @if ($paciente) <td>{{ $cita->paciente->nombre_completo }}</td> @endif
                        @if ($psicologo) <td>{{ $cita->psicologo->nombre_completo }}</td> @endif
                        @if ($especialidad) <td>{{ $cita->especialidad?->nombre ?? '—' }}</td> @endif
                        <td><x-estado-cita :estado="$cita->estado" /></td>
                        <td class="text-right">
                            <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost btn-sm">
                                Ver detalle <x-heroicon-m-chevron-right class="size-4" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

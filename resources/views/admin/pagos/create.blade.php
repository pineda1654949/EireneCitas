@php
    $esPaciente = auth()->user()->esPaciente();
@endphp

<x-layouts.app :titulo="$esPaciente ? 'Reportar pago' : 'Registrar pago'" :subtitulo="'Cita #'.$cita->id.' · '.$cita->paciente->nombre_completo">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <form method="POST" action="{{ route('pagos.store', $cita) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="monto" label="Monto (S/)" type="number" min="0.01" step="0.01" required
                                  :value="$cita->promocion && ! $planVigente && $maxCuotas === 1 ? $cita->promocion->precio : null" />
                    <x-form.select name="metodo_pago" label="Método de pago" required>
                        <option value="">Selecciona...</option>
                        @foreach ($metodos as $metodo)
                            <option value="{{ $metodo->value }}" @selected(old('metodo_pago') === $metodo->value)>{{ $metodo->etiqueta() }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                {{-- Plan de cuotas (RF-03): solo si la promocion lo permite --}}
                @if ($planVigente)
                    <input type="hidden" name="total_cuotas" value="{{ $planVigente }}">
                    <p class="rounded-lg bg-sky-50 p-3 text-sm text-sky-800 ring-1 ring-sky-600/20">
                        Plan de pago en {{ $planVigente }} {{ $planVigente === 1 ? 'cuota' : 'cuotas' }}: este registro será la
                        <strong>cuota {{ $cuotasRegistradas + 1 }} de {{ $planVigente }}</strong>.
                    </p>
                @elseif ($maxCuotas > 1)
                    <x-form.select name="total_cuotas" label="Modalidad de pago" required
                                   :hint="'La promoción «'.$cita->promocion?->nombre.'» permite pagar hasta en '.$maxCuotas.' cuotas.'">
                        @for ($n = 1; $n <= $maxCuotas; $n++)
                            <option value="{{ $n }}" @selected((int) old('total_cuotas', 1) === $n)>{{ $n === 1 ? 'Al contado' : "En {$n} cuotas" }}</option>
                        @endfor
                    </x-form.select>
                @endif

                <x-form.input name="numero_comprobante" label="N.º de operación" hint="Opcional. Facilita la conciliación." />

                <div>
                    <label for="comprobante" class="form-label">
                        Voucher del pago @if ($esPaciente) <span class="text-rose-500" aria-hidden="true">*</span> @endif
                    </label>
                    <input id="comprobante" type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf" @required($esPaciente)
                           @error('comprobante') aria-invalid="true" @enderror
                           class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium hover:file:bg-slate-200">
                    @error('comprobante')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-hint">Foto (JPG o PNG) o PDF, de hasta 5 MB.{{ $esPaciente ? '' : ' Opcional si el pago fue en efectivo.' }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">{{ $esPaciente ? 'Enviar voucher' : 'Registrar pago' }}</button>
                </div>
            </form>
        </x-card>

        <aside>
            <x-card titulo="Resumen de la cita">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Paciente</dt><dd class="font-medium text-slate-900">{{ $cita->paciente->nombre_completo }}</dd></div>
                    <div><dt class="text-slate-500">Psicólogo</dt><dd class="font-medium text-slate-900">{{ $cita->psicologo->nombre_completo }}</dd></div>
                    <div><dt class="text-slate-500">Fecha</dt><dd class="font-medium text-slate-900">{{ $cita->fecha->format('d/m/Y') }} · {{ $cita->hora_corta }} h</dd></div>
                    <div><dt class="text-slate-500">Promoción</dt><dd class="font-medium text-slate-900">
                        {{ $cita->promocion?->nombre ?? 'Sesión individual' }}
                        @if ($cita->promocion) <span class="block text-xs font-normal text-slate-500">S/ {{ number_format((float) $cita->promocion->precio, 2) }}</span> @endif
                    </dd></div>
                </dl>
                <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                    El pago queda <strong>pendiente</strong> hasta que la clínica revise el voucher. Al validarlo, la cita se confirma automáticamente.
                </p>
            </x-card>
        </aside>
    </div>
</x-layouts.app>

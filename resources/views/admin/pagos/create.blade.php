<x-layouts.app titulo="Registrar pago" :subtitulo="'Cita #'.$cita->id.' · '.$cita->paciente->nombre_completo">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <form method="POST" action="{{ route('pagos.store', $cita) }}" class="space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="monto" label="Monto (S/)" type="number" min="0.01" step="0.01" required
                                  :value="$cita->promocion?->precio" />
                    <x-form.select name="metodo_pago" label="Método de pago" required>
                        <option value="">Selecciona...</option>
                        @foreach ($metodos as $metodo)
                            <option value="{{ $metodo->value }}" @selected(old('metodo_pago') === $metodo->value)>{{ $metodo->etiqueta() }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <x-form.input name="numero_comprobante" label="N.º de operación o comprobante" hint="Opcional. Facilita la conciliación." />

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar pago</button>
                </div>
            </form>
        </x-card>

        <aside>
            <x-card titulo="Resumen de la cita">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Paciente</dt><dd class="font-medium text-slate-900">{{ $cita->paciente->nombre_completo }}</dd></div>
                    <div><dt class="text-slate-500">Psicólogo</dt><dd class="font-medium text-slate-900">{{ $cita->psicologo->nombre_completo }}</dd></div>
                    <div><dt class="text-slate-500">Fecha</dt><dd class="font-medium text-slate-900">{{ $cita->fecha->format('d/m/Y') }} · {{ $cita->hora_corta }} h</dd></div>
                    <div><dt class="text-slate-500">Promoción</dt><dd class="font-medium text-slate-900">{{ $cita->promocion?->nombre ?? 'Sesión individual' }}</dd></div>
                </dl>
                <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">El pago queda <strong>pendiente</strong>; al validarlo, la cita se confirma automáticamente.</p>
            </x-card>
        </aside>
    </div>
</x-layouts.app>

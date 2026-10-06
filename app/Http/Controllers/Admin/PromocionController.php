<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromocionRequest;
use App\Models\Promocion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromocionController extends Controller
{
    public function index(): View
    {
        $promociones = Promocion::withCount('citas')->orderBy('nombre')->paginate(15);

        return view('admin.promociones.index', compact('promociones'));
    }

    public function create(): View
    {
        return view('admin.promociones.create', ['promocion' => new Promocion(['activa' => true])]);
    }

    public function store(PromocionRequest $request): RedirectResponse
    {
        Promocion::create($this->datos($request));

        return redirect()->route('admin.promociones.index')->with('status', 'Promoción creada correctamente.');
    }

    public function edit(Promocion $promocion): View
    {
        return view('admin.promociones.edit', compact('promocion'));
    }

    public function update(PromocionRequest $request, Promocion $promocion): RedirectResponse
    {
        $promocion->update($this->datos($request));

        return redirect()->route('admin.promociones.index')->with('status', 'Promoción actualizada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datos(PromocionRequest $request): array
    {
        $permiteCuotas = $request->boolean('permite_cuotas');

        return [
            ...$request->safe()->except(['max_cuotas']),
            'activa' => $request->boolean('activa'),
            'permite_cuotas' => $permiteCuotas,
            'max_cuotas' => $permiteCuotas ? $request->integer('max_cuotas') : 1,
        ];
    }

    public function destroy(Promocion $promocion): RedirectResponse
    {
        // Las citas conservan su referencia como "sin promocion" (nullOnDelete).
        $promocion->delete();

        return back()->with('status', 'Promoción eliminada.');
    }
}

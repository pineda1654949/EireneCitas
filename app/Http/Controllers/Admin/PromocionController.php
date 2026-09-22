<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $promociones = Promocion::orderBy('nombre')->paginate(15);
        return view('admin.promociones.index', compact('promociones'));
    }

    public function create()
    {
        return view('admin.promociones.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'numero_sesiones' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'activa' => 'boolean',
        ]);
        $datos['activa'] = $request->boolean('activa', true);

        Promocion::create($datos);

        return redirect()->route('admin.promociones.index')->with('status', 'Promocion creada correctamente.');
    }

    public function edit(Promocion $promocion)
    {
        return view('admin.promociones.edit', compact('promocion'));
    }

    public function update(Request $request, Promocion $promocion)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'numero_sesiones' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'activa' => 'boolean',
        ]);
        $datos['activa'] = $request->boolean('activa');

        $promocion->update($datos);

        return redirect()->route('admin.promociones.index')->with('status', 'Promocion actualizada.');
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();
        return back()->with('status', 'Promocion eliminada.');
    }
}

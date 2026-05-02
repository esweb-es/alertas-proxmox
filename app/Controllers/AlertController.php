<?php

namespace App\Controllers;

use App\Models\AlertModel;

class AlertController extends BaseController
{
    // ---------------------------------------------------------------------
    // Marcar una alerta como solucionada
    // ---------------------------------------------------------------------
    public function resolve($id)
    {
        $alertModel = new AlertModel();
        $alerta = $alertModel->find($id);

        if (! $alerta) {
            return redirect()->back()->with('error', 'Alerta no encontrada.');
        }

        // Cambiar el estado a "resolved"
        $alertModel->update($id, ['status' => 'resolved']);

        return redirect()->back()->with('message', 'Alerta marcada como solucionada.');
    }

    // ---------------------------------------------------------------------
    // Eliminar una alerta (Soft Delete)
    // ---------------------------------------------------------------------
    public function delete($id)
    {
        $alertModel = new AlertModel();
        $alerta = $alertModel->find($id);

        if (! $alerta) {
            return redirect()->back()->with('error', 'Alerta no encontrada.');
        }

        // REGLA: No se pueden borrar alertas que no estén solucionadas, 
        // A MENOS que sean de severidad informativa (info, notice, debug)
        $informativas = ['info', 'notice', 'debug'];
        if ($alerta->status !== 'resolved' && !in_array($alerta->severity, $informativas)) {
            return redirect()->back()->with('error', 'No se puede eliminar una alerta crítica que no ha sido solucionada.');
        }

        // Soft delete se ejecuta automáticamente por el modelo
        $alertModel->delete($id);

        return redirect()->back()->with('message', 'Alerta eliminada correctamente.');
    }

    // ---------------------------------------------------------------------
    // Acciones masivas (Borrar / Resolver)
    // ---------------------------------------------------------------------
    public function bulkAction()
    {
        $ids = $this->request->getPost('ids');
        $action = $this->request->getPost('action');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No se seleccionaron alertas.');
        }

        $alertModel = new AlertModel();

        if ($action === 'delete') {
            // REGLA: Filtrar las que están solucionadas O son informativas
            $alertas = $alertModel->whereIn('id', $ids)->findAll();
            $idsParaBorrar = [];
            $informativas = ['info', 'notice', 'debug'];
            
            foreach ($alertas as $alerta) {
                if ($alerta->status === 'resolved' || in_array($alerta->severity, $informativas)) {
                    $idsParaBorrar[] = $alerta->id;
                }
            }

            if (empty($idsParaBorrar)) {
                return redirect()->back()->with('error', 'Solo se pueden borrar alertas resueltas o informativas.');
            }

            $alertModel->delete($idsParaBorrar);
            $countOmitidos = count($ids) - count($idsParaBorrar);
            $msg = count($idsParaBorrar) . ' alertas eliminadas correctamente.';
            if ($countOmitidos > 0) {
                $msg .= " ($countOmitidos omitidas por ser críticas pendientes).";
            }
            
        } elseif ($action === 'resolve') {
            $alertModel->update($ids, ['status' => 'resolved']);
            $msg = count($ids) . ' alertas marcadas como solucionadas.';
        } else {
            return redirect()->back()->with('error', 'Acción no válida.');
        }

        return redirect()->back()->with('message', $msg);
    }
}

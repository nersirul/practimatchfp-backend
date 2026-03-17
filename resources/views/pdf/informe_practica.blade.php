<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Prácticas FCT</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0F172A; padding-bottom: 10px; margin-bottom: 30px; }
        .title { color: #0F172A; font-size: 24px; font-weight: bold; }
        .section { margin-bottom: 20px; padding: 15px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        table { w-full; border-collapse: collapse; width: 100%; }
        td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; width: 150px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #64748b; }
        .apto { color: green; font-weight: bold; }
        .no-apto { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INFORME OFICIAL DE PRÁCTICAS (FCT)</div>
        <p>Documento generado automáticamente por PractiMatchFP</p>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL ALUMNO</div>
        <table>
            <tr><td class="label">Nombre Completo:</td><td>{{ $practica->alumno->nombre }} {{ $practica->alumno->apellidos }}</td></tr>
            <tr><td class="label">Ciclo Formativo:</td><td>{{ $practica->alumno->ciclo }}</td></tr>
            <tr><td class="label">Email de Contacto:</td><td>{{ $practica->alumno->email }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">DATOS DE LA EMPRESA Y PUESTO</div>
        <table>
            <tr><td class="label">Empresa:</td><td>{{ $practica->oferta->empresa->nombre_comercial }} (CIF: {{ $practica->oferta->empresa->cif }})</td></tr>
            <tr><td class="label">Puesto Formativo:</td><td>{{ $practica->oferta->titulo }}</td></tr>
            <tr><td class="label">Ubicación:</td><td>{{ $practica->oferta->empresa->ciudad }} - {{ $practica->oferta->modalidad }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">EVALUACIÓN FINAL</div>
        <table>
            <tr><td class="label">Profesor/a Tutor:</td><td>{{ $practica->profesor->nombre ?? 'Asignado al finalizar' }} {{ $practica->profesor->apellidos ?? '' }}</td></tr>
            <tr><td class="label">Calificación:</td>
                <td class="{{ $practica->valoracion->calificacion == 'APTO' ? 'apto' : 'no-apto' }}">
                    {{ $practica->valoracion->calificacion }} (Nota Numérica: {{ $practica->valoracion->nota_numerica }}/10)
                </td>
            </tr>
            <tr><td class="label">Comentarios:</td><td>{{ $practica->valoracion->comentarios_profesor ?? 'Sin comentarios adicionales.' }}</td></tr>
        </table>
    </div>

    <div class="footer">
        Firma del Tutor/a: ___________________________ <br><br>
        Generado el {{ date('d/m/Y') }}
    </div>
</body>
</html>
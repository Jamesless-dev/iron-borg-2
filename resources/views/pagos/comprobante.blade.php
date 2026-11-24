<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $c['numero'] }} | Iron-Borg</title>
    <style>
        *{box-sizing:border-box}
        body{font-family:'Courier New',monospace;background:#f2f3f7;margin:0;padding:20px;color:#111}
        .ticket{width:302px;margin:0 auto;background:#fff;padding:22px 20px;border-radius:8px;box-shadow:0 6px 24px -12px rgba(0,0,0,.4)}
        .c{text-align:center}
        .marca{font-size:20px;font-weight:700;letter-spacing:3px}
        .sub{font-size:9px;letter-spacing:2px;color:#666;margin-bottom:12px}
        .tipo{font-size:11px;margin-top:10px}
        .num{font-size:18px;font-weight:700;margin:4px 0 12px}
        .fila{display:flex;justify-content:space-between;gap:10px;font-size:11px;padding:4px 0;border-bottom:1px dotted #ccc}
        .fila b{font-weight:700}
        .total{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:10px;border-top:2px solid #111;font-size:12px;font-weight:700}
        .total span:last-child{font-size:19px}
        .pie{margin-top:12px;font-size:9px;color:#666;text-align:center;line-height:1.5}
        .acciones{width:302px;margin:16px auto 0;display:flex;gap:8px}
        .acciones button{flex:1;padding:10px;border:0;border-radius:8px;font-weight:700;cursor:pointer;font-family:inherit}
        .p{background:linear-gradient(135deg,#667EEA,#764BA2);color:#fff}
        .s{background:#e6e8f0;color:#333}
        @media print{
            body{background:#fff;padding:0}
            .ticket{box-shadow:none;width:auto;border-radius:0}
            .acciones{display:none}
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="c">
            <div class="marca">IRON-BORG</div>
            <div class="sub">GYM MANAGEMENT</div>
            <div class="tipo">COMPROBANTE DE PAGO</div>
            <div class="num">N.° {{ $c['numero'] }}</div>
        </div>
        <div class="fila"><span>Fecha y hora</span><b>{{ $c['fechaEmision'] }}</b></div>
        <div class="fila"><span>Miembro</span><b>{{ $c['miembro'] }}</b></div>
        <div class="fila"><span>DNI</span><b>{{ $c['dni'] }}</b></div>
        <div class="fila"><span>Código</span><b>{{ $c['codigo'] }}</b></div>
        <div class="fila"><span>Plan</span><b>{{ $c['plan'] }}</b></div>
        <div class="fila"><span>Vigencia</span><b>{{ $c['vigencia'] }}</b></div>
        <div class="fila"><span>Método</span><b>{{ $c['metodoPago'] }}</b></div>
        <div class="fila"><span>Atendió</span><b>{{ $c['atendidoPor'] }}</b></div>
        <div class="total"><span>TOTAL PAGADO</span><span>S/ {{ $c['montoFmt'] }}</span></div>
        <div class="pie">
            Saldo pendiente: S/ {{ $c['saldo'] }}<br>
            Comprobante interno · no válido como comprobante SUNAT<br>
            Gracias por su pago
        </div>
    </div>

    <div class="acciones">
        <button class="p" onclick="window.print()">Imprimir</button>
        <button class="s" onclick="window.close()">Cerrar</button>
    </div>

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 350));</script>
</body>
</html>
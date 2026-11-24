<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iron-Borg | @yield('title', 'Sistema de Gestión')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f4f6fb; margin: 0; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #1a1f2e;
            color: #c5c9d6;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            padding: 0;
            z-index: 100;
        }

        .sidebar-logo {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            padding: 25px 20px;
            text-align: center;
        }
        .sidebar-logo h3 {
            color: #fff;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0;
            font-size: 1.5rem;
        }
        .sidebar-logo small {
            color: rgba(255,255,255,0.7);
            font-size: 0.65rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .sidebar-user {
            text-align: center;
            padding: 18px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-user .user-name {
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }
        .sidebar-user .badge-rol {
            background-color: #667EEA;
            color: #fff;
            font-size: 0.65rem;
            padding: 3px 10px;
            border-radius: 12px;
            letter-spacing: 1px;
        }

        .sidebar-menu-label {
            font-size: 0.65rem;
            letter-spacing: 1.5px;
            color: #6b7280;
            padding: 18px 20px 8px;
            text-transform: uppercase;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: #c5c9d6;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }
        .sidebar-menu a:hover {
            background-color: rgba(255,255,255,0.05);
            color: #fff;
        }
        .sidebar-menu a.active {
            background-color: #667EEA;
            color: #fff;
            border-left-color: #fff;
        }
        .sidebar-menu a i { font-size: 1.05rem; }

        .sidebar-footer {
            margin-top: auto;
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .btn-logout {
            width: 100%;
            background: transparent;
            border: 1px solid rgba(239,68,68,0.4);
            color: #ef4444;
            padding: 9px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background-color: rgba(239,68,68,0.1);
            color: #ef4444;
        }

        /* ===== CONTENIDO PRINCIPAL ===== */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background-color: #fff;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .topbar-datetime {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 500;
        }

        /* ===== ÁREA DE CONTENIDO ===== */
        .content-area {
            padding: 30px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .page-subtitle {
            color: #6b7280;
            font-size: 0.88rem;
            margin-bottom: 25px;
        }

        /* ===== TARJETAS INDICADOR ===== */
        .card-indicador {
            background: #fff;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f5;
            height: 100%;
        }
        .card-indicador .icon-box {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 14px;
        }
        .card-indicador .icon-box.blue   { background: rgba(102,126,234,0.12); color: #667EEA; }
        .card-indicador .icon-box.green  { background: rgba(16,185,129,0.12); color: #10b981; }
        .card-indicador .icon-box.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }

        .card-indicador .label {
            font-size: 0.82rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .card-indicador .value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #111827;
        }

        /* ===== CARD GRÁFICO ===== */
        .card-grafico {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f5;
            margin-top: 25px;
        }
        .card-grafico h5 {
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
        }

        /* ===== ALERTAS ===== */
        .alert-custom { border-radius: 10px; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; min-height: auto; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <h3>IRON-BORG</h3>
        <small>GYM MANAGEMENT</small>
    </div>

    <div class="sidebar-user">
        <div class="user-name">{{ auth()->user()->nombreUsuario }}</div>
        <span class="badge-rol">{{ strtoupper(auth()->user()->rol) }}</span>
    </div>

    <div class="sidebar-menu-label">Operaciones</div>
    <nav class="sidebar-menu">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap-fill"></i> Dashboard
        </a>
        <a href="{{ route('miembros.index') }}" class="{{ request()->routeIs('miembros.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Miembros
        </a>
        <a href="{{ route('membresias.index') }}" class="{{ request()->routeIs('membresias.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Membresías
        </a>
        <a href="{{ route('asistencias.index') }}" class="{{ request()->routeIs('asistencias.*') ? 'active' : '' }}">
            <i class="bi bi-clock"></i> Asistencia
        </a>
        <a href="{{ route('pagos.index') }}" class="{{ request()->routeIs('pagos.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Pagos y Comprobantes
        </a>

        {{-- Solo Administrador (RNF-02) --}}
        @if(auth()->user()->rol === 'Administrador')
            <div class="sidebar-menu-label mt-2">Administración</div>
            <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Reportes
            </a>
            <a href="{{ route('historial.index') }}" class="{{ request()->routeIs('historial.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Historial
            </a>
            <a href="{{ route('configuracion.index') }}" class="{{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Configuración
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</aside>

<!-- ===== CONTENIDO PRINCIPAL ===== -->
<div class="main-content">

    <!-- Topbar con fecha/hora (CUS-05 paso 1) -->
    <div class="topbar">
        <div></div>
        <div class="topbar-datetime">
            <i class="bi bi-calendar3"></i> <span id="fecha">--/--/----</span>
            &nbsp;·&nbsp;
            <i class="bi bi-clock"></i> <span id="reloj">--:--:--</span>
        </div>
    </div>

    <!-- Alertas globales -->
    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-custom alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Reloj en tiempo real (CUS-05 paso 1)
    function actualizarReloj() {
        const ahora = new Date();
        document.getElementById('reloj').textContent = ahora.toLocaleTimeString('es-PE');
        document.getElementById('fecha').textContent = ahora.toLocaleDateString('es-PE');
    }
    setInterval(actualizarReloj, 1000);
    actualizarReloj();
</script>
@stack('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iron-Borg | Inicio de Sesión</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            background-color: #f8f9fa;
        }

        /* Panel izquierdo - Negro con logo */
        .panel-izquierdo {
            background-color: #000000;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex: 1;
        }

        .logo-iron {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: 4px;
            margin-bottom: 15px;
        }

        .logo-sub {
            color: #4A6CF7;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Panel derecho - Formulario */
        .panel-derecho {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background-color: #ffffff;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            color: #1a1a2e;
            letter-spacing: 1px;
        }

        .login-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 35px;
        }

        .form-label-custom {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .form-control-custom {
            border: 1.5px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            border-color: #4A6CF7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
        }

        .form-control-custom.is-invalid {
            border-color: #dc3545;
        }

        .btn-ingresar {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 1px;
            padding: 14px;
            border-radius: 10px;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .btn-ingresar:hover {
            background: linear-gradient(135deg, #5568D3 0%, #653A8F 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-ingresar:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .alert-custom {
            border-radius: 8px;
            font-size: 0.9rem;
            border: none;
        }

        /* Responsive: en pantallas pequeñas, apilar verticalmente */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .panel-izquierdo {
                min-height: 200px;
                padding: 30px 20px;
            }

            .logo-iron {
                font-size: 2.5rem;
            }

            .panel-derecho {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Panel Izquierdo: Logo -->
    <div class="panel-izquierdo">
        <div class="logo-iron">IRON-BORG</div>
        <div class="logo-sub">GYM MANAGEMENT SYSTEM</div>
    </div>

    <!-- Panel Derecho: Formulario -->
    <div class="panel-derecho">
        <div class="login-container">
            <h1 class="login-title">INICIO DE SESIÓN</h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

            {{-- Mensaje de cierre por inactividad (CUS-04 FA-02) --}}
            @if(session('auth_error'))
                <div class="alert alert-warning text-center mb-4 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> {{ session('auth_error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf

                <!-- Campo Usuario -->
                <div class="mb-3">
                    <label for="nombreUsuario" class="form-label form-label-custom">Usuario</label>
                    <input type="text" 
                            class="form-control form-control-custom @error('nombreUsuario') is-invalid @enderror" 
                            id="nombreUsuario" 
                            name="nombreUsuario" 
                            value="{{ old('nombreUsuario') }}" 
                            placeholder="Ingrese su usuario" 
                            required 
                            autofocus>
                    @error('nombreUsuario')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Campo Contraseña -->
                <div class="mb-4">
                    <label for="passwordHash" class="form-label form-label-custom">Contraseña</label>
                    <input type="password" 
                            class="form-control form-control-custom @error('passwordHash') is-invalid @enderror" 
                            id="passwordHash" 
                            name="passwordHash" 
                            placeholder="Ingrese su contraseña" 
                            required>
                    @error('passwordHash')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botón Ingresar -->
                <button type="submit" class="btn btn-ingresar" id="btnIngresar">
                    INGRESAR
                </button>

                <!-- Mensaje de error global (cuenta bloqueada, etc.) -->
                @if(session('auth_error'))
                    <div class="alert alert-danger alert-custom mt-3 text-center">
                        {{ session('auth_error') }}
                    </div>
                @endif

                <!-- Mensaje de errores generales -->
                @if($errors->any() && !$errors->has('nombreUsuario') && !$errors->has('passwordHash'))
                    <div class="alert alert-danger alert-custom mt-3 text-center">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
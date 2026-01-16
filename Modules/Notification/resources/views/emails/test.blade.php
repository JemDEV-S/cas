<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Correo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1a56db;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1a56db;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            padding: 20px 0;
        }
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box .icon {
            font-size: 48px;
            color: #28a745;
        }
        .success-box h2 {
            color: #155724;
            margin: 10px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Convocatorias CAS</h1>
            <p>Municipalidad Distrital de San Jeronimo - Cusco</p>
        </div>

        <div class="content">
            <p>Estimado/a <strong>{{ $recipientName }}</strong>,</p>

            <div class="success-box">
                <div class="icon">&#10004;</div>
                <h2>Configuracion Exitosa</h2>
                <p>El servicio de correo electronico esta funcionando correctamente.</p>
            </div>

            <p>Este es un correo de prueba enviado desde el Sistema de Convocatorias CAS para verificar que la configuracion del servidor de correo SMTP esta funcionando correctamente.</p>

            <table class="info-table">
                <tr>
                    <td>Fecha de envio:</td>
                    <td>{{ now()->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td>Servidor SMTP:</td>
                    <td>{{ config('mail.mailers.smtp.host') }}</td>
                </tr>
                <tr>
                    <td>Puerto:</td>
                    <td>{{ config('mail.mailers.smtp.port') }}</td>
                </tr>
                <tr>
                    <td>Encriptacion:</td>
                    <td>{{ config('mail.mailers.smtp.encryption') ?? 'Ninguna' }}</td>
                </tr>
            </table>

            <p>Si recibiste este correo, significa que el modulo de notificaciones esta listo para ser utilizado.</p>
        </div>

        <div class="footer">
            <p>Este es un correo automatico generado por el Sistema de Convocatorias CAS.</p>
            <p>Municipalidad Distrital de San Jeronimo - Cusco</p>
            <p>&copy; {{ date('Y') }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>

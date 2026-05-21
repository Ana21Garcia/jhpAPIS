<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperacion de contrasena</title>
</head>
<body style="margin:0;padding:0;background:#eef4f7;font-family:Arial,Helvetica,sans-serif;color:#15323f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4f7;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border:1px solid #d8e3e8;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="background:#0f5f7a;padding:28px 32px;color:#ffffff;">
                            <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">JHP Motos POS</div>
                            <h1 style="margin:10px 0 0;font-size:26px;line-height:1.25;">Recuperacion de contrasena</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">
                                Hola {{ $usuario->nombre_completo ?? $usuario->name ?? $usuario->cli_nombre ?? $usuario->correo ?? $usuario->email ?? 'usuario' }},
                            </p>
                            <p style="margin:0 0 22px;font-size:16px;line-height:1.6;color:#456071;">
                                Recibimos una solicitud para restablecer la contrasena de tu cuenta. Usa el siguiente token en la pantalla de recuperacion del sistema.
                            </p>

                            <div style="background:#f8fbfc;border:1px solid #d8e3e8;border-radius:10px;padding:22px;text-align:center;margin:24px 0;">
                                <div style="font-size:13px;font-weight:700;text-transform:uppercase;color:#6d8390;margin-bottom:10px;">Token de recuperacion</div>
                                <div style="font-family:Consolas,Monaco,monospace;font-size:26px;font-weight:800;letter-spacing:.08em;color:#0f5f7a;word-break:break-all;">{{ $token }}</div>
                            </div>

                            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#456071;">
                                Tambien puedes abrir el enlace de recuperacion:
                            </p>
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:#0f5f7a;color:#ffffff;text-decoration:none;font-weight:700;border-radius:8px;padding:13px 22px;">Restablecer contrasena</a>
                            </p>

                            <div style="background:#fff7ed;border-left:4px solid #d9822b;border-radius:8px;padding:14px 16px;color:#6b3d0c;font-size:14px;line-height:1.6;">
                                Este token vence en 24 horas y solo puede usarse una vez. Si no solicitaste este cambio, ignora este correo.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fbfc;border-top:1px solid #d8e3e8;padding:18px 32px;color:#6d8390;font-size:12px;line-height:1.5;text-align:center;">
                            Enviado automaticamente por JHP Motos POS.<br>
                            No respondas este correo.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

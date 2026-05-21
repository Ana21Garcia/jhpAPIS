<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    public function enviarCita(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['nullable', 'string', 'max:25'],
            'cliente' => ['required', 'string', 'max:160'],
            'folio' => ['nullable', 'string', 'max:50'],
            'fecha' => ['required', 'string', 'max:40'],
            'hora' => ['required', 'string', 'max:20'],
            'servicio' => ['required', 'string', 'max:180'],
            'empleado' => ['nullable', 'string', 'max:160'],
            'comentarios' => ['nullable', 'string', 'max:500'],
        ]);

        $baseUrl = rtrim((string) config('services.openwa.base_url'), '/');
        $apiKey = config('services.openwa.api_key');
        $sessionId = config('services.openwa.session_id');

        if (!$baseUrl || !$apiKey || !$sessionId) {
            return response()->json([
                'message' => 'Falta configurar OPENWA_BASE_URL, OPENWA_API_KEY u OPENWA_SESSION_ID.',
            ], 422);
        }

        $telefono = $data['telefono'] ?? $this->buscarTelefonoCliente($data['cliente']);
        $chatId = $this->buildChatId($telefono);

        if (!$chatId) {
            return response()->json([
                'message' => 'No se encontro un telefono valido para enviar WhatsApp.',
            ], 422);
        }

        $message = $this->buildAppointmentMessage($data);
        $response = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
        ])->post("{$baseUrl}/sessions/{$sessionId}/messages/send-text", [
            'chatId' => $chatId,
            'text' => $message,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'OpenWA no pudo enviar el mensaje.',
                'status' => $response->status(),
                'openwa' => $response->json() ?? $response->body(),
            ], 502);
        }

        return response()->json([
            'message' => 'Mensaje de WhatsApp enviado correctamente.',
            'data' => $response->json(),
        ]);
    }

    private function buscarTelefonoCliente(string $cliente): ?string
    {
        $name = trim($cliente);
        if ($name === '') {
            return null;
        }

        $record = Cliente::query()
            ->whereRaw("TRIM(CONCAT(cli_nombre, ' ', cli_apaterno, ' ', COALESCE(cli_amaterno, ''))) = ?", [$name])
            ->orWhereRaw("TRIM(CONCAT(cli_nombre, ' ', cli_apaterno)) = ?", [$name])
            ->orWhere('cli_nombre', $name)
            ->first();

        return $record?->cli_telefono;
    }

    private function buildChatId(?string $telefono): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $telefono);
        if (!$digits) {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) config('services.openwa.country_code', '52'));
        if ($countryCode && strlen($digits) === 10) {
            $digits = $countryCode . $digits;
        }

        return Str::endsWith($digits, '@c.us') ? $digits : "{$digits}@c.us";
    }

    private function buildAppointmentMessage(array $data): string
    {
        $lines = [
            'JHP Motocicletas',
            'Tu cita ha sido programada.',
            '',
            'Folio: ' . ($data['folio'] ?? 'Pendiente'),
            'Cliente: ' . $data['cliente'],
            'Fecha: ' . $data['fecha'],
            'Hora: ' . $data['hora'],
            'Servicio: ' . $data['servicio'],
        ];

        if (!empty($data['empleado'])) {
            $lines[] = 'Empleado: ' . $data['empleado'];
        }

        if (!empty($data['comentarios'])) {
            $lines[] = 'Comentarios: ' . $data['comentarios'];
        }

        $lines[] = '';
        $lines[] = 'Gracias por confiar en JHP.';

        return implode("\n", $lines);
    }
}

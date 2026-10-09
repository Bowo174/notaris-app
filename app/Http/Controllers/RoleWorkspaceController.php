<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFile;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoleWorkspaceController extends Controller
{
    public function dashboard(Request $request): View
    {
        $role = $this->serviceType($request);
        $context = $this->viewContext($role);
        $servicesQuery = Service::query()->where('service_type', $role)->orderBy('name');
        $servicesQuery->withCount($role === 'PPAT' ? 'orders' : 'clients');
        $services = $servicesQuery->get();

        $orderCount = $role === 'PPAT'
            ? Order::query()->where(function ($query) {
                $query->whereHas('service', fn ($service) => $service->where('service_type', 'PPAT'))
                    ->orWhere(fn ($custom) => $custom->whereNull('service_id')->whereNotNull('other_service'));
            })->count()
            : null;

        return view('workspace.dashboard', $context + ['services' => $services, 'orderCount' => $orderCount]);
    }

    public function service(Request $request, Service $service): View
    {
        $role = $this->serviceType($request);
        abort_unless($service->service_type === $role, 404);

        if ($role === 'PPAT') {
            $orders = Order::query()
                ->where('service_id', $service->id)
                ->withCount('files')
                ->latest()
                ->paginate(15);

            return view('workspace.service', $this->viewContext($role) + [
                'service' => $service,
                'orders' => $orders,
                'clients' => collect(),
            ]);
        }

        $clients = Client::query()
            ->where('service_id', $service->id)
            ->withCount('files')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('workspace.service', $this->viewContext($role) + [
            'service' => $service,
            'clients' => $clients,
        ]);
    }

    public function clients(Request $request): View
    {
        $role = $this->serviceType($request);
        $clients = Client::query()
            ->with(['service:id,name,service_type'])
            ->withCount('files')
            ->whereHas('service', fn ($query) => $query->where('service_type', $role))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('workspace.clients', $this->viewContext($role) + ['clients' => $clients]);
    }

    public function client(Request $request, Client $client): View
    {
        $role = $this->serviceType($request);
        $client->load(['service:id,name,service_type', 'files:id,client_id,label,original_name,size']);
        abort_unless($client->service?->service_type === $role, 404);

        return view('workspace.client', $this->viewContext($role) + ['client' => $client]);
    }

    public function previewFile(Request $request, ClientFile $clientFile)
    {
        $role = $this->serviceType($request);
        $clientFile->load('client.service');
        abort_unless($clientFile->client?->service?->service_type === $role, 404);
        abort_unless(Storage::disk('local')->exists($clientFile->path), 404);
        $isPdf = strtolower(pathinfo($clientFile->original_name, PATHINFO_EXTENSION)) === 'pdf';

        return Storage::disk('local')->response($clientFile->path, $clientFile->original_name, [
            'Content-Type' => $clientFile->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ], $isPdf ? 'inline' : 'attachment');
    }

    private function serviceType(Request $request): string
    {
        if ($request->user()->hasRole('Notaris')) {
            return 'Notaris';
        }

        if ($request->user()->hasRole('PPAT')) {
            return 'PPAT';
        }

        abort(403);
    }

    private function viewContext(string $role): array
    {
        $workspaceRoute = strtolower($role);

        return [
            'role' => $role,
            'workspaceRoute' => $workspaceRoute,
            'dashboardRoute' => $workspaceRoute.'.dashboard',
            'accessibleServices' => Service::query()
                ->where('service_type', $role)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'service_type']),
        ];
    }
}

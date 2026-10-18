@extends('backoffice.admin.layouts.app')
@section('content')
<div class="container-fluid py-3">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('media_storage_report'))
    <div class="card border-info shadow-sm mb-4">
        <div class="card-header bg-light font-weight-bold">
            <i class="fas fa-stethoscope text-info mr-2"></i>Media Storage Doctor - Diagnostic Report
        </div>
        <div class="card-body">
            <pre class="mb-0 p-3 bg-light border rounded" style="white-space:pre-wrap; overflow-wrap:anywhere; max-height:400px; overflow-y:auto;">{{ session('media_storage_report') }}</pre>
            <small class="text-muted d-block mt-2">Private to admins with the system_tools_manage permission. A passing check does not guarantee all historic media files exist or that the webserver serves the public URL.</small>
        </div>
    </div>
    @endif
    @if(session('media_storage_repair_report'))
    <div class="card border-warning shadow-sm mb-4">
        <div class="card-header bg-light font-weight-bold">
            <i class="fas fa-tools text-warning mr-2"></i>Media Webroot Repair — Result
        </div>
        <div class="card-body">
            <pre class="mb-0 p-3 bg-light border rounded" style="white-space:pre-wrap; overflow-wrap:anywhere; max-height:450px; overflow-y:auto;">{{ session('media_storage_repair_report') }}</pre>
            <small class="text-muted d-block mt-2">Visible to authorized administrators only. No files are deleted automatically.</small>
        </div>
    </div>
    @endif
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1"><i class="fas fa-terminal text-primary mr-2"></i>System
                        Commands</h4>
                    <p class="text-muted mb-0 small">Run common Laravel maintenance commands directly from the admin
                        panel.</p>
                </div>
                <div class="mt-2 mt-md-0">
                    @if($isLocal)
                    <span class="badge badge-success px-3 py-2"><i class="fas fa-laptop-code mr-1"></i>Local
                        Environment</span>
                    @else
                    <span class="badge badge-warning px-3 py-2"><i
                            class="fas fa-server mr-1"></i>{{ strtoupper(app()->environment()) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-shield-alt mr-2"></i>These commands are restricted to administrators with
        <code>system_tools_manage</code> permission. Destructive database commands are available only in the
        <code>local</code> environment.
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-broom text-primary mr-2"></i>Cache Management
            </h5>
        </div>
        <div class="card-body">
            @php
            $cacheCommands = [
            ['route' => 'command.clear-cache', 'label' => 'Clear Cache', 'description' => 'cache:clear', 'icon' => 'fas
            fa-broom', 'class' => 'btn-outline-primary'],
            ['route' => 'command.clear-config', 'label' => 'Clear Config', 'description' => 'config:clear', 'icon' =>
            'fas fa-cog', 'class' => 'btn-outline-primary'],
            ['route' => 'command.clear-route', 'label' => 'Clear Route', 'description' => 'route:clear', 'icon' => 'fas
            fa-route', 'class' => 'btn-outline-primary'],
            ['route' => 'command.clear-view', 'label' => 'Clear View', 'description' => 'view:clear', 'icon' => 'fas
            fa-eye-slash', 'class' => 'btn-outline-primary'],
            ['route' => 'command.clear-events', 'label' => 'Clear Events', 'description' => 'event:clear', 'icon' =>
            'fas fa-calendar-times', 'class' => 'btn-outline-info'],
            ['route' => 'command.optimize', 'label' => 'Optimize', 'description' => 'optimize', 'icon' => 'fas fa-bolt',
            'class' => 'btn-outline-success'],
            ['route' => 'command.optimize-clear', 'label' => 'Optimize Clear', 'description' => 'optimize:clear', 'icon'
            => 'fas fa-sync-alt', 'class' => 'btn-outline-warning'],
            ];
            @endphp
            <div class="row">
                @foreach($cacheCommands as $command)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <form method="POST" action="{{ route($command['route']) }}" class="h-100">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="command-card btn {{ $command['class'] }} btn-block">
                            <i class="{{ $command['icon'] }} fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">{{ $command['label'] }}</span>
                            <small class="d-block mt-1">{{ $command['description'] }}</small>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-photo-video text-info mr-2"></i>Media Storage
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-3">
                    @if($hasMediaStorageDoctor)
                    <form method="POST" action="{{ route('command.media-storage-doctor') }}">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="command-card btn btn-outline-info btn-block">
                            <i class="fas fa-stethoscope fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">Media Storage Doctor</span>
                            <small class="d-block mt-1">Check public media storage</small>
                        </button>
                    </form>
                    @else
                    <button type="button" class="command-card btn btn-outline-secondary btn-block" disabled>
                        <i class="fas fa-stethoscope fa-2x mb-2"></i>
                        <span class="d-block font-weight-bold">Media Storage Doctor</span>
                        <small class="d-block mt-1">Custom command not installed</small>
                    </button>
                    @endif
                </div>
            </div>
            <hr>
            <h6 class="font-weight-bold mb-2"><i class="fas fa-link text-warning mr-2"></i>Storage Link Repair (No Terminal)</h6>
            <div class="alert alert-warning">
                <strong>Before you start:</strong> Back up your website and public media in cPanel File Manager.
                Set <code>PUBLIC_MEDIA_ROOT</code> to Laravel <code>website/storage/app/public</code>,
                <code>PUBLIC_MEDIA_LINK</code> to your live webroot <code>storage</code>, then click <strong>Clear Config</strong> above.
                Do not remove your existing <code>storage</code> folder yourself.
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.media-storage-inspect') }}">
                        @csrf
                        <button type="submit" class="command-card btn btn-outline-primary btn-block">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">1. Inspect Storage</span>
                            <small class="d-block mt-1">Read-only: inspect files, paths and conflicts</small>
                        </button>
                    </form>
                </div>
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.media-storage-stage') }}"
                        onsubmit="return confirm('Stage public media in safe batches? Existing files will never be overwritten.');">
                        @csrf
                        <button type="submit" class="command-card btn btn-outline-info btn-block">
                            <i class="fas fa-copy fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">2. Stage Existing Public Media</span>
                            <small class="d-block mt-1">Copy verified media; repeat until complete</small>
                        </button>
                    </form>
                </div>
            </div>
            <div class="border rounded p-3 mt-1">
                <h6 class="font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>3. Activate Storage Link — only after Inspect &amp; Stage</h6>
                <p class="small text-muted">This moves the old real folder to a private backup OUTSIDE the public webroot, then runs Laravel <code>storage:link</code>. If host symlinks are disabled, automatic rollback is attempted. Never run before verifying the report.</p>
                <form method="POST" action="{{ route('command.media-storage-activate') }}"
                      onsubmit="return confirm('Final confirmation: Activate storage symlink and move the existing public folder to private backup?');">
                    @csrf
                    <div class="form-group form-check">
                        <input class="form-check-input" type="checkbox" name="acknowledge" value="1" id="media-repair-ack" required>
                        <label class="form-check-label" for="media-repair-ack">I have backed up my media and verified the Inspect/Stage reports.</label>
                    </div>
                    <div class="form-group">
                        <label for="media-repair-confirm">Type <strong>LINK STORAGE</strong> to confirm:</label>
                        <input id="media-repair-confirm" type="text" name="confirmation" class="form-control"
                               placeholder="LINK STORAGE" autocomplete="off" required maxlength="32">
                    </div>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-link mr-1"></i> Activate Storage Link &amp; Keep Private Backup
                    </button>
                </form>
            </div>
            @if(! $hasMediaStorageDoctor)
            <div class="alert alert-light border mb-0 mt-2">
                <i class="fas fa-info-circle text-info mr-1"></i><code>media:storage-doctor</code> is not registered in
                this Laravel project. The button has been disabled to prevent an Artisan command exception.
            </div>
            @endif
        </div>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-database text-success mr-2"></i>Database
                Commands</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.migrate') }}"
                        onsubmit="return confirm('Run pending database migrations?');">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="command-card btn btn-outline-success btn-block">
                            <i class="fas fa-database fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">Run Migrations</span>
                            <small class="d-block mt-1">migrate --force</small>
                        </button>
                    </form>
                </div>
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.seed') }}"
                        onsubmit="return confirm('Run database seeders?');">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="command-card btn btn-outline-info btn-block">
                            <i class="fas fa-seedling fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">Seed Database</span>
                            <small class="d-block mt-1">db:seed --force</small>
                        </button>
                    </form>
                </div>
            </div>
            <hr>
            <h6 class="font-weight-bold text-danger mb-3"><i class="fas fa-exclamation-triangle mr-1"></i>Destructive
                Database Commands</h6>
            @if($isLocal)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>These commands permanently remove the current database
                tables and data.
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.migrate-fresh') }}"
                        onsubmit="return confirm('WARNING: This will DELETE all database tables and data. Continue?');">
                        @csrf
                        <button type="submit" class="command-card btn btn-outline-danger btn-block">
                            <i class="fas fa-trash-alt fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">Migrate Fresh</span>
                            <small class="d-block mt-1">migrate:fresh</small>
                        </button>
                    </form>
                </div>
                <div class="col-lg-6 col-md-6 mb-3">
                    <form method="POST" action="{{ route('command.migrate-fresh-seed') }}"
                        onsubmit="return confirm('WARNING: This will DELETE all existing data, rebuild the database and run seeders. Continue?');">
                        @csrf
                        <button type="submit" class="command-card btn btn-danger btn-block">
                            <i class="fas fa-skull-crossbones fa-2x mb-2"></i>
                            <span class="d-block font-weight-bold">Migrate Fresh & Seed</span>
                            <small class="d-block mt-1">migrate:fresh --seed</small>
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-secondary mb-0">
                <i class="fas fa-lock mr-2"></i>Fresh database commands are disabled because
                <code>APP_ENV={{ app()->environment() }}</code>.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('css')
<style>
.command-card {
    width: 100%;
    min-height: 120px;
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    white-space: normal;
    transition: transform .2s ease-in-out, box-shadow .2s ease-in-out
}

.command-card:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, .08)
}

.command-card small {
    opacity: .8
}

.command-card:disabled {
    cursor: not-allowed;
    opacity: .65
}
</style>
@endpush
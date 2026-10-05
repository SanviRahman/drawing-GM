@extends('backoffice.admin.layouts.app')

@section('meta_title', 'Dashboard')

@section('page_content')
<div class="container-fluid pb-4 dashboard-shell">
    <div class="dashboard-welcome card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="text-uppercase small font-weight-bold text-primary mb-2">Operations overview</div>
                    <h2 class="font-weight-bold text-dark mb-2">Welcome back, {{ $admin->name ?? 'Admin' }}.</h2>
                    <p class="text-muted mb-0">Monitor leads, publishing, campaigns, media and platform health from one place.</p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div class="small text-muted mb-2"><i class="far fa-calendar-alt mr-1"></i>{{ now()->format('l, d M Y') }}</div>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm font-weight-bold px-3">
                        <i class="fas fa-external-link-alt mr-1"></i>View Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($cards))
        <div class="row">
            @foreach($cards as $card)
                <div class="col-xl-3 col-md-6 mb-4">
                    @if(!empty($card['route']))
                        <a href="{{ route($card['route']) }}" class="dashboard-card-link text-decoration-none">
                    @endif
                    <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="dashboard-stat-icon bg-{{ $card['class'] }}-soft text-{{ $card['class'] }} mr-3">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted small font-weight-bold text-uppercase mb-1">{{ $card['label'] }}</div>
                                <div class="h3 font-weight-bold text-dark mb-1">{{ number_format((int) $card['value']) }}</div>
                                <div class="small text-muted text-truncate">{{ $card['hint'] }}</div>
                            </div>
                        </div>
                    </div>
                    @if(!empty($card['route']))
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($leadOverview)
        <div class="row">
            <div class="col-xl-7 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-stream text-primary mr-2"></i>Lead Pipeline</h3>
                        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">View Leads</a>
                    </div>
                    <div class="card-body pt-1">
                        <div class="row mb-3">
                            <div class="col-6 col-md-3 mb-2">
                                <div class="dashboard-mini-metric"><span>Today</span><strong>{{ $leadOverview['today'] }}</strong></div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="dashboard-mini-metric"><span>Last 7 days</span><strong>{{ $leadOverview['last7'] }}</strong></div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="dashboard-mini-metric"><span>Unassigned</span><strong>{{ $leadOverview['unassigned'] }}</strong></div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="dashboard-mini-metric"><span>Won / 30d</span><strong>{{ $leadOverview['won30'] }}</strong></div>
                            </div>
                        </div>

                        @foreach($leadOverview['pipeline'] as $item)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark"><span class="badge badge-{{ $item['class'] }} mr-2">{{ $item['label'] }}</span></span>
                                    <span class="small text-muted">{{ $item['count'] }} · {{ number_format($item['percent'], 1) }}%</span>
                                </div>
                                <div class="progress dashboard-progress">
                                    <div class="progress-bar bg-{{ $item['class'] }}" role="progressbar" style="width: {{ $item['percent'] }}%" aria-valuenow="{{ $item['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach

                        @if(!empty($leadOverview['sources']))
                            <hr>
                            <div class="text-muted small font-weight-bold text-uppercase mb-2">Recent lead sources</div>
                            <div class="d-flex flex-wrap" style="gap:8px;">
                                @foreach($leadOverview['sources'] as $source)
                                    <span class="badge badge-light border px-2 py-2">{{ $source['source'] }} <strong class="ml-1">{{ $source['count'] }}</strong></span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-clock text-info mr-2"></i>Recent Leads</h3>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentLeads as $lead)
                            <a href="{{ route('admin.leads.index', ['search' => $lead->reference]) }}" class="dashboard-list-row d-flex align-items-center px-3 py-3 text-decoration-none border-top">
                                <div class="dashboard-avatar mr-3">{{ strtoupper(substr($lead->name, 0, 1)) }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="font-weight-bold text-dark text-truncate pr-2">{{ $lead->name }}</div>
                                        <span class="badge badge-{{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span>
                                    </div>
                                    <div class="small text-muted text-truncate">{{ $lead->reference }}@if($lead->location) · {{ $lead->location->name }}@endif</div>
                                    <div class="small text-muted">{{ $lead->created_at?->diffForHumans() }}@if($lead->assignee) · Assigned to {{ $lead->assignee->name }}@endif</div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-muted py-5"><i class="far fa-inbox fa-2x mb-2 d-block"></i>No leads yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        @if(!empty($contentOverview))
            <div class="col-xl-7 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-layer-group text-success mr-2"></i>Content Publishing</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr><th>Module</th><th class="text-center">Published / Live</th><th class="text-center">Draft</th><th class="text-center">Total</th></tr>
                                </thead>
                                <tbody>
                                @foreach($contentOverview as $item)
                                    <tr>
                                        <td class="align-middle"><a href="{{ route($item['route']) }}" class="font-weight-bold text-dark"><i class="{{ $item['icon'] }} text-primary mr-2"></i>{{ $item['label'] }}</a></td>
                                        <td class="text-center align-middle"><span class="badge badge-success px-2">{{ $item['published'] }}</span></td>
                                        <td class="text-center align-middle"><span class="badge badge-warning px-2">{{ $item['draft'] }}</span></td>
                                        <td class="text-center align-middle font-weight-bold">{{ $item['total'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-bolt text-warning mr-2"></i>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($quickActions as $action)
                            <div class="col-6 mb-3">
                                <a href="{{ route($action['route']) }}" class="dashboard-quick-action text-decoration-none">
                                    <span class="dashboard-quick-icon text-{{ $action['class'] }}"><i class="{{ $action['icon'] }}"></i></span>
                                    <span>{{ $action['label'] }}</span>
                                </a>
                            </div>
                        @endforeach
                        <div class="col-6 mb-3">
                            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="dashboard-quick-action text-decoration-none">
                                <span class="dashboard-quick-icon text-primary"><i class="fas fa-external-link-alt"></i></span>
                                <span>Public Website</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if($trackingHealth)
            <div class="col-xl-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-chart-line text-primary mr-2"></i>Tracking Health</h3>
                        <div><span class="badge badge-success">{{ $trackingHealth['enabled'] }} enabled</span>@if($trackingHealth['attention']) <span class="badge badge-danger">{{ $trackingHealth['attention'] }} need attention</span>@endif</div>
                    </div>
                    <div class="card-body p-0">
                        @forelse($trackingHealth['providers'] as $provider)
                            <div class="dashboard-health-row d-flex align-items-center px-3 py-3 border-top">
                                <span class="health-dot {{ $provider['enabled'] && $provider['configured'] ? 'bg-success' : ($provider['enabled'] ? 'bg-danger' : 'bg-secondary') }} mr-3"></span>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark">{{ $provider['label'] }}</div>
                                    <div class="small text-muted">{{ $provider['rules'] }} enabled rule(s) · {{ $provider['test_mode'] ? 'Test mode' : 'Live mode' }}</div>
                                </div>
                                <span class="badge badge-{{ $provider['enabled'] ? ($provider['configured'] ? 'success' : 'danger') : 'secondary' }}">{{ $provider['enabled'] ? ($provider['configured'] ? 'Healthy' : 'Needs config') : 'Disabled' }}</span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">No tracking providers configured.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-heartbeat text-danger mr-2"></i>Platform Snapshot</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($systemSnapshot as $item)
                            <div class="col-md-6 mb-3">
                                <a href="{{ route($item['route']) }}" class="dashboard-snapshot-item text-decoration-none">
                                    <i class="{{ $item['icon'] }} text-primary"></i>
                                    <div><strong>{{ number_format($item['value']) }}</strong><span>{{ $item['label'] }}</span></div>
                                </a>
                            </div>
                        @endforeach
                        @if($mediaHealth)
                            <div class="col-md-6 mb-3">
                                <div class="dashboard-snapshot-item">
                                    <i class="fas fa-video {{ $mediaHealth['failed'] ? 'text-danger' : 'text-success' }}"></i>
                                    <div><strong>{{ $mediaHealth['failed'] }}</strong><span>Media/video failures</span></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="dashboard-snapshot-item">
                                    <i class="fas fa-spinner text-info"></i>
                                    <div><strong>{{ $mediaHealth['processing'] }}</strong><span>Media/video processing</span></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($recentAuditLogs->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-history text-secondary mr-2"></i>Recent Administrative Activity</h3>
                <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">View Audit Logs</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light"><tr><th>Action</th><th>Actor</th><th>Entity</th><th class="text-right">When</th></tr></thead>
                        <tbody>
                        @foreach($recentAuditLogs as $log)
                            <tr>
                                <td class="align-middle"><code>{{ $log->action }}</code></td>
                                <td class="align-middle">{{ $log->actor?->name ?? class_basename($log->actor_type ?: 'System') }}</td>
                                <td class="align-middle">{{ class_basename($log->auditable_type) }}@if($log->auditable_id) #{{ $log->auditable_id }}@endif</td>
                                <td class="align-middle text-right text-muted small">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('page_css')
<style>
.dashboard-shell { --dash-border:#e9ecef; }
.dashboard-welcome { background:linear-gradient(135deg,#ffffff 0%,#f4f8ff 100%); border-left:4px solid #007bff !important; }
.dashboard-card-link:hover .dashboard-stat-card { transform:translateY(-2px); box-shadow:0 .5rem 1.2rem rgba(0,0,0,.09)!important; }
.dashboard-stat-card { transition:transform .18s ease,box-shadow .18s ease; }
.dashboard-stat-icon { width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex:0 0 auto; }
.bg-primary-soft{background:#eaf2ff}.bg-warning-soft{background:#fff6dd}.bg-success-soft{background:#e7f8ee}.bg-info-soft{background:#e7f7fb}.bg-danger-soft{background:#fdecec}.bg-secondary-soft{background:#f0f1f3}
.min-w-0{min-width:0}.dashboard-mini-metric{border:1px solid var(--dash-border);border-radius:10px;padding:10px 12px;background:#fbfcfe}.dashboard-mini-metric span{display:block;color:#6c757d;font-size:.75rem;font-weight:700;text-transform:uppercase}.dashboard-mini-metric strong{font-size:1.2rem;color:#343a40}.dashboard-progress{height:7px;border-radius:10px;background:#f0f2f5}.dashboard-avatar{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#eaf2ff;color:#007bff;font-weight:800;flex:0 0 auto}.dashboard-list-row:hover{background:#f8fbff}.dashboard-quick-action{height:88px;border:1px solid var(--dash-border);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-weight:700;color:#343a40;transition:.18s ease}.dashboard-quick-action:hover{border-color:#b9d4ff;background:#f8fbff;transform:translateY(-1px)}.dashboard-quick-icon{font-size:1.25rem;margin-bottom:6px}.dashboard-health-row:first-child{border-top:0!important}.health-dot{width:10px;height:10px;border-radius:50%;flex:0 0 auto}.dashboard-snapshot-item{border:1px solid var(--dash-border);border-radius:10px;padding:12px;display:flex;align-items:center;color:#343a40;height:100%}.dashboard-snapshot-item>i{width:34px;font-size:1.15rem}.dashboard-snapshot-item strong{display:block;font-size:1.1rem;line-height:1.1}.dashboard-snapshot-item span{display:block;color:#6c757d;font-size:.78rem;margin-top:3px}.dashboard-snapshot-item:hover{background:#f8fbff}
@media(max-width:767.98px){.dashboard-welcome .card-body{padding:1.25rem!important}.dashboard-shell{padding-left:0;padding-right:0}.dashboard-stat-icon{width:46px;height:46px}.dashboard-quick-action{height:80px}}
</style>
@endpush

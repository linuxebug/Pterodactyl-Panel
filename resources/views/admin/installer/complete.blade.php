@extends('layouts.admin')

@section('title')
    Installation Complete
@endsection

@section('content-header')
    <h1>Installation Complete<small>Your panel is ready.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Installation Complete</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> The Pterodactyl Panel has been installed successfully.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        @if(!$adminSetupComplete)
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Create Administrator Account</h3>
            </div>
            <div class="box-body">
                <form id="adminForm" action="{{ route('admin.installer.create-admin') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="pUsername" class="form-label">Username</label>
                        <input type="text" name="username" id="pUsername" class="form-control" required />
                        <p class="text-muted small">3-32 characters. Alphanumeric, dashes, underscores, dots, and spaces.</p>
                    </div>
                    <div class="form-group">
                        <label for="pEmail" class="form-label">Email</label>
                        <input type="email" name="email" id="pEmail" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="pPassword" class="form-label">Password</label>
                        <input type="password" name="password" id="pPassword" class="form-control" required />
                        <p class="text-muted small">Minimum 8 characters.</p>
                    </div>
                    <div class="form-group">
                        <label for="pPasswordConfirm" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="pPasswordConfirm" class="form-control" required />
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success pull-right" id="createAdminBtn">Create Admin Account</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Connect to Your Panel</h3>
            </div>
            <div class="box-body">
                <h4>Connection Options</h4>
                <p>After installation, you need to connect to your panel. Choose the option that matches your setup:</p>

                <div class="list-group">
                    <a href="{{ route('admin.installer.connection') }}" class="list-group-item">
                        <h5 class="mb-0">Option 1: Connect Your Domain</h5>
                        <small class="text-muted">Configure a domain name with HTTPS and reverse proxy support.</small>
                    </a>
                    <a href="{{ route('admin.installer.connection') }}" class="list-group-item">
                        <h5 class="mb-0">Option 2: Use Locally</h5>
                        <small class="text-muted">Access the panel on your local network.</small>
                    </a>
                    <a href="{{ route('admin.installer.connection') }}" class="list-group-item">
                        <h5 class="mb-0">Option 3: GitHub / CodeSandbox</h5>
                        <small class="text-muted">Development/testing connection options.</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Environment Details</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-sm">
                    <tr><td>Environment</td><td>{{ $environment['environment'] }}</td></tr>
                    <tr><td>Panel Port</td><td>{{ $environment['panel_port'] }}</td></tr>
                    @if($environment['public_url'])
                    <tr><td>Public URL</td><td>{{ $environment['public_url'] }}</td></tr>
                    @endif
                    @if($environment['has_docker'])
                    <tr><td>Docker</td><td><span class="text-green">Available</span></td></tr>
                    @else
                    <tr><td>Docker</td><td><span class="text-red">Not available</span></td></tr>
                    @endif
                </table>

                @if($environment['limitations'])
                <div class="alert alert-warning">
                    <strong>Important Limitations</strong>
                    <ul class="small">
                        @foreach($environment['limitations'] as $limitation)
                        <li>{{ $limitation }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
@parent
<script>
$(function() {
    $('#adminForm').on('submit', function() {
        $('#createAdminBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-pulse"></i> Creating...');
    });
});
</script>
@endsection

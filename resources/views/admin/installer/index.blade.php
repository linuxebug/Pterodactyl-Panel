@extends('layouts.admin')

@section('title')
    Panel Installer
@endsection

@section('content-header')
    <h1>Panel Installer<small>Install and configure the Pterodactyl Panel.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Panel Installer</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        @if($installed)
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> The panel has already been installed. You can manage the installation from below.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> The panel is not yet installed. Complete the installation to continue.
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Installation Requirements</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="requirements-list">
                        <tr>
                            <td>PHP Version (>= 8.2)</td>
                            <td id="req-php"><i class="fa fa-refresh fa-spin"></i> Checking...</td>
                        </tr>
                        <tr>
                            <td>Composer Dependencies</td>
                            <td id="req-composer"><i class="fa fa-refresh fa-spin"></i> Checking...</td>
                        </tr>
                        <tr>
                            <td>Node Dependencies</td>
                            <td id="req-node"><i class="fa fa-refresh fa-spin"></i> Checking...</td>
                        </tr>
                        <tr>
                            <td>Storage Directory Writable</td>
                            <td id="req-storage"><i class="fa fa-refresh fa-spin"></i> Checking...</td>
                        </tr>
                        <tr>
                            <td>Database (.env configured)</td>
                            <td id="req-database">
                                @if($hasEnvFile)
                                    <i class="fa fa-check text-green"></i> Configured
                                @else
                                    <i class="fa fa-times text-red"></i> Not configured
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(!$installed)
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Installation Configuration</h3>
            </div>
            <div class="box-body">
                <form id="installForm" action="{{ route('admin.installer.install') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="pAppName" class="form-label">Application Name</label>
                        <input type="text" name="app_name" id="pAppName" class="form-control" value="{{ old('app_name', 'Pterodactyl') }}" />
                    </div>

                    <div class="form-group">
                        <label for="pAppUrl" class="form-label">Application URL</label>
                        <input type="url" name="app_url" id="pAppUrl" class="form-control" value="{{ old('app_url', 'http://localhost:8686') }}" />
                        <p class="text-muted small">The URL where the panel will be accessible.</p>
                    </div>

                    <div class="form-group">
                        <label for="pPanelPort" class="form-label">Panel Port</label>
                        <input type="number" name="panel_port" id="pPanelPort" class="form-control" value="{{ old('panel_port', 8686) }}" min="1" max="65535" />
                        <p class="text-muted small">The port the panel will listen on. <button type="button" id="checkPortBtn" class="btn btn-xs btn-default">Check Port</button></p>
                        <span id="port-check-result" class="text-muted small"></span>
                    </div>

                    <hr/>

                    <h4>Database Configuration</h4>
                    <div class="form-group">
                        <label for="pDbHost" class="form-label">Database Host</label>
                        <input type="text" name="db_host" id="pDbHost" class="form-control" value="{{ old('db_host', '127.0.0.1') }}" />
                    </div>
                    <div class="form-group">
                        <label for="pDbPort" class="form-label">Database Port</label>
                        <input type="number" name="db_port" id="pDbPort" class="form-control" value="{{ old('db_port', '3306') }}" min="1" max="65535" />
                    </div>
                    <div class="form-group">
                        <label for="pDbDatabase" class="form-label">Database Name</label>
                        <input type="text" name="db_database" id="pDbDatabase" class="form-control" value="{{ old('db_database') }}" />
                    </div>
                    <div class="form-group">
                        <label for="pDbUsername" class="form-label">Database Username</label>
                        <input type="text" name="db_username" id="pDbUsername" class="form-control" value="{{ old('db_username', 'pterodactyl') }}" />
                    </div>
                    <div class="form-group">
                        <label for="pDbPassword" class="form-label">Database Password</label>
                        <input type="password" name="db_password" id="pDbPassword" class="form-control" />
                        <p class="text-muted small">Leave blank if no password is set.</p>
                    </div>

                    <hr/>

                    <h4>Mail Configuration (Optional)</h4>
                    <div class="form-group">
                        <label for="pMailHost" class="form-label">SMTP Host</label>
                        <input type="text" name="mail_host" id="pMailHost" class="form-control" value="{{ old('mail_host') }}" />
                    </div>
                    <div class="form-group">
                        <label for="pMailUsername" class="form-label">SMTP Username</label>
                        <input type="text" name="mail_username" id="pMailUsername" class="form-control" value="{{ old('mail_username') }}" />
                    </div>
                    <div class="form-group">
                        <label for="pMailPassword" class="form-label">SMTP Password</label>
                        <input type="password" name="mail_password" id="pMailPassword" class="form-control" />
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-success pull-right" id="installButton">Install Panel</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    <div class="col-sm-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Detected Environment</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-sm">
                    <tr><td>Environment</td><td>{{ $environment['environment'] }}</td></tr>
                    <tr><td>Docker Available</td><td>{{ $environment['has_docker'] ? 'Yes' : 'No' }}</td></tr>
                    <tr><td>Panel Port</td><td>{{ $environment['panel_port'] }}</td></tr>
                    @if($environment['public_url'])
                    <tr><td>Public URL</td><td>{{ $environment['public_url'] }}</td></tr>
                    @endif
                    @if($environment['limitations'])
                    <tr><td>Limitations</td><td>
                        <ul class="text-danger small">
                            @foreach($environment['limitations'] as $limitation)
                            <li>{{ $limitation }}</li>
                            @endforeach
                        </ul>
                    </td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
@parent
<script>
$(function() {
    $('#pPanelPort').on('change', function() {
        var port = $(this).val();
        $('#port-check-result').html('<i class="fa fa-refresh fa-spin"></i> Checking port...');
        $.get('{{ route('admin.installer.check-port') }}', { port: port }, function(data) {
            if (data.available) {
                $('#port-check-result').html('<i class="fa fa-check text-green"></i> ' + data.message);
            } else {
                $('#port-check-result').html('<i class="fa fa-times text-red"></i> ' + data.message);
            }
        }).fail(function() {
            $('#port-check-result').html('<i class="fa fa-times text-red"></i> Failed to check port.');
        });
    });

    $('#installForm').on('submit', function() {
        $('#installButton').prop('disabled', true).html('<i class="fa fa-spinner fa-pulse"></i> Installing...');
    });
});
</script>
@endsection

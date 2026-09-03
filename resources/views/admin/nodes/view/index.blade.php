@extends('layouts.admin')

@section('title')
    {{ $node->name }}
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>A quick overview of your node.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nodes') }}">Nodes</a></li>
        <li class="active">{{ $node->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li class="active"><a href="{{ route('admin.nodes.view', $node->id) }}">About</a></li>
                <li><a href="{{ route('admin.nodes.view.settings', $node->id) }}">Settings</a></li>
                <li><a href="{{ route('admin.nodes.view.configuration', $node->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.nodes.view.allocation', $node->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.nodes.view.servers', $node->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Information</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <tr>
                                <td>Daemon Version</td>
                                <td><code data-attr="info-version"><i class="fa fa-refresh fa-fw fa-spin"></i></code> (Latest: <code>{{ $version->getDaemon() }}</code>)</td>
                            </tr>
                            <tr>
                                <td>System Information</td>
                                <td data-attr="info-system"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                            <tr>
                                <td>Total CPU Threads</td>
                                <td data-attr="info-cpus"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @if ($node->description)
                <div class="col-xs-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            Description
                        </div>
                        <div class="box-body table-responsive">
                            <pre>{{ $node->description }}</pre>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Delete Node</h3>
                    </div>
                    <div class="box-body">
                        <p class="no-margin">Deleting a node is a irreversible action and will immediately remove this node from the panel. There must be no servers associated with this node in order to continue.</p>
                    </div>
                    <div class="box-footer">
                        <form action="{{ route('admin.nodes.view.delete', $node->id) }}" method="POST">
                            {!! csrf_field() !!}
                            {!! method_field('DELETE') !!}
                            <button type="submit" class="btn btn-danger btn-sm pull-right" {{ ($node->servers_count < 1) ?: 'disabled' }}>Yes, Delete This Node</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
            <div class="col-sm-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Node Information</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover">
                    <tr>
                        <td>Node Name</td>
                        <td>{{ $node->name }}</td>
                    </tr>
                    <tr>
                        <td>Node ID</td>
                        <td><code>{{ $node->node_identifier ?? 'Not set' }}</code>
                            @if($node->node_identifier)
                            <button type="button" class="btn btn-xs btn-default copy-btn"
                                data-clipboard-text="{{ $node->node_identifier }}"
                                data-toggle="tooltip" title="Copy Node ID">
                                Copy
                            </button>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>FQDN</td>
                        <td>{{ $node->fqdn }}</td>
                    </tr>
                    <tr>
                        <td>Connection Address</td>
                        <td>{{ $node->getConnectionAddress() }}</td>
                    </tr>
                    <tr>
                        <td>Daemon Port</td>
                        <td>{{ $node->daemonListen }}</td>
                    </tr>
                    <tr>
                        <td>SFTP Port</td>
                        <td>{{ $node->daemonSFTP }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td data-attr="node-status"><i class="fa fa-refresh fa-fw fa-spin"></i> Loading...</td>
                    </tr>
                    <tr>
                        <td>Last Heartbeat</td>
                        <td data-attr="node-heartbeat"><i class="fa fa-clock-o"></i> --</td>
                    </tr>
                </table>

                <div class="text-center">
                    <button type="button" id="testConnectionBtn" class="btn btn-sm btn-default">
                        <i class="fa fa-plug"></i> Test Connection
                    </button>
                </div>
            </div>
        </div>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Node Installation</h3>
            </div>
            <div class="box-body">
                <p class="text-muted small">
                    Copy the installation command below and run it on the server where you want to install Wings.
                </p>
                <div class="form-group">
                    <label>Installation Command</label>
                    <div class="input-group">
                        <input type="text" id="installationCommand" class="form-control" readonly
                            value="{{ $installationCommand }}" />
                        <span class="input-group-btn">
                            <button type="button" id="copyInstallCommand" class="btn btn-default"
                                data-toggle="tooltip" title="Copy to clipboard">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </span>
                    </div>
                </div>
                <p class="text-muted small">The node status will turn online only after the real Wings daemon connects.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function updateNodeStatus() {
        $.ajax({
            method: 'GET',
            url: '/admin/nodes/view/{{ $node->id }}/status',
            timeout: 5000,
        }).done(function (data) {
            var statusEl = $('[data-attr="node-status"]');
            var heartbeatEl = $('[data-attr="node-heartbeat"]');

            if (data.status === 'online') {
                statusEl.html('<span class="text-success"><i class="fa fa-circle"></i> Online</span>');
            } else {
                statusEl.html('<span class="text-danger"><i class="fa fa-circle"></i> Offline</span>');
            }

            if (data.last_heartbeat) {
                heartbeatEl.html(escapeHtml(data.last_heartbeat));
            } else {
                heartbeatEl.html('<span class="text-muted">Never</span>');
            }

            if (data.connection_error) {
                statusEl.append('<br><small class="text-danger">' + escapeHtml(data.connection_error) + '</small>');
            }
        }).fail(function (jqXHR) {
            $('[data-attr="node-status"]').html('<span class="text-danger"><i class="fa fa-circle"></i> Offline</span>');
        });
    }

    // Copy to clipboard functionality
    $('.copy-btn').on('click', function() {
        var text = $(this).data('clipboard-text');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                var originalText = $(this).text();
                $(this).text('Copied!').prop('disabled', true);
                var btn = $(this);
                setTimeout(function() {
                    btn.text(originalText).prop('disabled', false);
                }, 2000);
            }.bind(this)).catch(function(err) {
                swal({
                    title: 'Error',
                    text: 'Failed to copy: ' + err.message,
                    type: 'error'
                });
            });
        } else {
            var tempInput = $('<input>').val(text);
            $('body').append(tempInput);
            tempInput[0].select();
            document.execCommand('copy');
            tempInput.remove();
            swal({
                title: 'Copied!',
                text: 'Node ID copied to clipboard.',
                type: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    // Copy installation command
    $('#copyInstallCommand').on('click', function() {
        var cmd = $('#installationCommand').val();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(cmd).then(function() {
                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('<i class="fa fa-check"></i> Copied!').prop('disabled', true);
                setTimeout(function() {
                    btn.html(originalHtml).prop('disabled', false);
                }, 2000);
            }.bind(this)).catch(function(err) {
                swal({
                    title: 'Error',
                    text: 'Failed to copy: ' + err.message,
                    type: 'error'
                });
            });
        } else {
            var tempInput = $('<input>').val(cmd);
            $('body').append(tempInput);
            tempInput[0].select();
            document.execCommand('copy');
            tempInput.remove();
            swal({
                title: 'Copied!',
                text: 'Installation command copied to clipboard.',
                type: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    // Test connection button
    $('#testConnectionBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-pulse"></i> Testing...');

        $.ajax({
            method: 'POST',
            url: '/admin/nodes/view/{{ $node->id }}/test-connection',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            timeout: 10000,
        }).done(function (data) {
            if (data.status === 'success') {
                swal({
                    title: 'Success',
                    text: data.message,
                    type: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                swal({
                    title: 'Connection Failed',
                    text: data.reason || data.message,
                    type: 'error'
                });
            }
            updateNodeStatus();
        }).fail(function (jqXHR) {
            var errorMsg = 'Unable to connect to Wings';
            var reason = 'Connection refused';
            try {
                var resp = jqXHR.responseJSON;
                if (resp && resp.reason) {
                    reason = resp.reason;
                }
            } catch(e) {}
            swal({
                title: 'Connection Failed',
                text: errorMsg + '\n\nReason: ' + reason,
                type: 'error'
            });
            updateNodeStatus();
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
        });
    });

    // System info (existing)
    (function getInformation() {
        $.ajax({
            method: 'GET',
            url: '/admin/nodes/view/{{ $node->id }}/system-information',
            timeout: 5000,
        }).done(function (data) {
            $('[data-attr="info-version"]').html(escapeHtml(data.version));
            $('[data-attr="info-system"]').html(escapeHtml(data.system.type) + ' (' + escapeHtml(data.system.arch) + ') <code>' + escapeHtml(data.system.release) + '</code>');
            $('[data-attr="info-cpus"]').html(data.system.cpus);
        }).fail(function (jqXHR) {
        }).always(function() {
            setTimeout(getInformation, 10000);
        });
    })();

    // Node status
    updateNodeStatus();
    setInterval(updateNodeStatus, 30000);
    </script>
@endsection

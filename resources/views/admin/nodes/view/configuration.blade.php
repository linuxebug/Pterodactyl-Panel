@extends('layouts.admin')

@section('title')
    {{ $node->name }}: Configuration
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>Your daemon configuration file.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nodes') }}">Nodes</a></li>
        <li><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></li>
        <li class="active">Configuration</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.nodes.view', $node->id) }}">About</a></li>
                <li><a href="{{ route('admin.nodes.view.settings', $node->id) }}">Settings</a></li>
                <li class="active"><a href="{{ route('admin.nodes.view.configuration', $node->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.nodes.view.allocation', $node->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.nodes.view.servers', $node->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Configuration File</h3>
            </div>
            <div class="box-body">
                <pre class="no-margin">{{ $node->getYamlConfiguration() }}</pre>
            </div>
            <div class="box-footer">
                <p class="no-margin">This file should be placed in your daemon's root directory (usually <code>/etc/pterodactyl</code>) in a file called <code>config.yml</code>.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Node Installation</h3>
            </div>
            <div class="box-body">
                <p class="text-muted small">
                    Use the button below to generate a copyable installation command for configuring Wings on the target server.
                </p>
                <p><strong>Node ID:</strong> <code>{{ $node->node_identifier ?? 'Not set' }}</code></p>
            </div>
            <div class="box-footer">
                <button type="button" id="copyInstallCommand" class="btn btn-sm btn-block btn-success" style="width:100%;">
                    <i class="fa fa-copy"></i> Copy Installation Command
                </button>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Test Connection</h3>
            </div>
            <div class="box-body">
                <p class="text-muted small">
                    Test the connection to the Wings daemon on this node.
                </p>
            </div>
            <div class="box-footer">
                <button type="button" id="testConnectionBtn" class="btn btn-sm btn-block btn-default" style="width:100%;">
                    <i class="fa fa-plug"></i> Test Connection
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    var installationCommand = "curl -fsSL {{ config('app.url') }}/node-install/{{ $node->node_identifier ?? $node->uuid }} | sudo bash";

    $('#copyInstallCommand').on('click', function (event) {
        event.preventDefault();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(installationCommand).then(function() {
                var btn = $('#copyInstallCommand');
                var originalHtml = btn.html();
                btn.html('<i class="fa fa-check"></i> Copied!').prop('disabled', true);
                setTimeout(function() {
                    btn.html(originalHtml).prop('disabled', false);
                }, 2000);
            }).catch(function(err) {
                swal({
                    title: 'Error',
                    text: 'Failed to copy: ' + err.message,
                    type: 'error'
                });
            });
        } else {
            var tempInput = $('<input>').val(installationCommand);
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

    $('#testConnectionBtn').on('click', function (event) {
        event.preventDefault();
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
        }).fail(function (jqXHR) {
            var reason = 'Connection refused';
            try {
                var resp = jqXHR.responseJSON;
                if (resp && resp.reason) {
                    reason = resp.reason;
                }
            } catch(e) {}
            swal({
                title: 'Connection Failed',
                html: '<strong>Unable to connect to Wings</strong><br><br>Reason: ' + reason,
                type: 'error'
            });
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fa fa-plug"></i> Test Connection');
        });
    });

    // Copy node identifier buttons
    $('.copy-identifier-btn').on('click', function() {
        var text = $(this).data('clipboard-text');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                var btn = $(this);
                btn.text('Copied!').prop('disabled', true);
                setTimeout(function() {
                    btn.text('Copy').prop('disabled', false);
                }, 2000);
            }.bind(this)).catch(function(err) {
                swal({ title: 'Error', text: 'Failed to copy: ' + err.message, type: 'error' });
            });
        } else {
            var tempInput = $('<input>').val(text);
            $('body').append(tempInput);
            tempInput[0].select();
            document.execCommand('copy');
            tempInput.remove();
            swal({ title: 'Copied!', text: 'Node ID copied to clipboard.', type: 'success', timer: 1500, showConfirmButton: false });
        }
    });
    </script>
@endsection

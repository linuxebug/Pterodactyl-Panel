@extends('layouts.admin')

@section('title')
    Hosting Settings
@endsection

@section('content-header')
    <h1>Hosting Settings<small>Configure hosting-level branding and settings.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Hosting Settings</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        <form id="logoForm" action="{{ route('admin.hosting.settings.logo') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Hosting Logo</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pLogo" class="form-label">Upload Logo</label>
                        <input type="file" name="logo" id="pLogo" class="form-control" accept=".png,.jpg,.jpeg,.webp,.svg" />
                        <p class="text-muted small">Allowed file types: PNG, JPG, JPEG, WebP, SVG. Maximum file size: 2 MB.</p>
                        @if(auth()->user()->root_admin)
                            @foreach($errors->get('logo') as $error)
                                <div class="text-danger small">{{ $error }}</div>
                            @endforeach
                        @endif
                    </div>

                    @if($hasLogo)
                    <div class="form-group">
                        <label class="form-label">Current Logo Preview</label>
                        <div class="text-center">
                            <img src="{{ $logo }}" alt="Current Logo" style="max-height: 100px; max-width: 100%;" class="img-responsive" />
                        </div>
                        <p class="text-muted small">Current logo: {{ $logo }}</p>
                    </div>
                    @else
                    <div class="form-group">
                        <label class="form-label">Current Logo Preview</label>
                        <p class="text-muted">No logo has been uploaded.</p>
                    </div>
                    @endif
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-sm btn-success">Save Changes</button>
                    @if($hasLogo)
                    <a href="{{ route('admin.hosting.settings.logo.remove') }}"
                       onclick="event.preventDefault(); if(confirm('Are you sure you want to remove the logo?')) { document.getElementById('removeLogoForm').submit(); }"
                       class="btn btn-sm btn-danger">Remove Logo</a>
                    @endif
                    <form id="removeLogoForm" action="{{ route('admin.hosting.settings.logo.remove') }}" method="POST" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </form>
    </div>
    <div class="col-sm-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Logo Preview</h3>
            </div>
            <div class="box-body text-center">
                @if($hasLogo)
                    <img src="{{ $logo }}" alt="Logo Preview" style="max-height: 80px; max-width: 100%;" class="img-responsive" />
                @else
                    <div class="text-muted">
                        <i class="fa fa-image fa-3x"></i>
                        <p>No logo uploaded</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

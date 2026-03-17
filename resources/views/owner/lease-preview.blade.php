@extends('layouts.app')

@section('title', 'Lease Agreement Preview')

@push('styles')
<style>
.lease-preview-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.lease-preview-header h1 {
    margin: 0;
}

.lease-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
}

.lease-iframe {
    width: 100%;
    height: 100vh;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
</style>
@endpush

@section('content')
<div class="lease-container">
    <div class="lease-preview-header">
        <h1>📄 Lease Agreement Preview</h1>
        <a href="{{ route('owner.tenants') }}" style="color: white; text-decoration: none; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 6px;">← Back</a>
    </div>

    <iframe 
        srcdoc="{!! addslashes($leaseHtml) !!}" 
        class="lease-iframe"
        sandbox="allow-same-origin"
    ></iframe>
</div>
@endsection

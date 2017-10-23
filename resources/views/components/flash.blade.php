@if(Session::has('status'))
    <alert type="success">{{ Session::get('status') }}</alert>
@endif
@foreach(['primary', 'secondary', 'success', 'danger', 'warning'] as $type)
    @if(Session::has($type))
        @foreach(Session::get($type) as $message)
            <alert type="{{ $type }}">{{ $message }}</alert>
        @endforeach
    @endif
@endforeach
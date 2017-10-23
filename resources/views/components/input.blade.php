<div class="form-group{{ $errors->has($id) ? ' has-error' : '' }}">
    <label for="{{ $id }}" class="col-md-4 control-label">{{ __("validation.attributes.$id") }}</label>

    <div class="col-md-6">
        <input id="{{ $id }}" type="{{ isset($type) ? $type : 'text' }}" class="form-control" name="{{ $id }}"
               value="{{ $value or old($id) }}"{{ isset($autofocus) && $autofocus ? " autofocus" : "" }}{{ isset($required) && $required ? " required" : "" }}>

        @if ($errors->has($id))
            <span class="help-block">
                <strong>{{ $errors->first($id) }}</strong>
            </span>
        @endif
    </div>
</div>
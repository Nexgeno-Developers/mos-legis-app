<form class="form" id="edit" action="{{ route($module . '.update', $booking->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group mb-2">
                <label for="booking_status" class="form-label">{{ __('labels.booking_status') }} <span class="text-danger">*</span></label>
                <select name="booking_status" id="booking_status" class="form-select select2" required>
                    @foreach (['reserved', 'active', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" {{ $booking->booking_status === $status ? 'selected' : '' }}>
                        {{ humanize($status) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{ __('labels.update') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    initValidate('#edit');
    initSelect2('.select2');

    $("#edit").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackUpdateForm);
    });

    const callbackUpdateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>

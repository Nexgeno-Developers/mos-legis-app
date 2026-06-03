<form class="form" id="create" action="{{ route($module . '.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="property_id" class="form-label">{{ __('labels.property') }} <span class="text-danger">*</span></label>
                <select name="property_id" id="property_id" class="form-select select2" required>
                    <option value="">{{ __('labels.select_property') }}</option>
                    @foreach ($properties as $property)
                        <option value="{{ $property->id }}" {{ (string) $selectedPropertyId === (string) $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="cabin_id" class="form-label">{{ __('labels.cabin') }} <span class="text-danger">*</span></label>
                <select name="cabin_id" id="cabin_id" class="form-select select2" required>
                    <option value="">{{ __('labels.select_cabin') }}</option>
                </select>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="seat_no" class="form-label">{{ __('labels.seat_no') }} <span class="text-danger">*</span></label>
                <input name="seat_no" type="text" class="form-control" maxlength="50" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="status" class="form-label">{{ __('labels.status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-select select2" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <div class="col-sm-12">
            <hr>
            <h6 class="text-primary mb-2">{{ __('labels.pricing') }}</h6>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="pricing_monthly" class="form-label">{{ __('labels.monthly') }} <span class="text-danger">*</span></label>
                <input name="pricing[monthly]" id="pricing_monthly" type="number" class="form-control" min="0" step="0.01" required>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group mb-2">
                <label for="pricing_yearly" class="form-label">{{ __('labels.yearly') }} <span class="text-danger">*</span></label>
                <input name="pricing[yearly]" id="pricing_yearly" type="number" class="form-control" min="0" step="0.01" required>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="text-center mt-1">
                <button type="submit" class="btn btn-primary">{{ __('labels.create') }}</button>
            </div>
        </div>
    </div>
</form>

@include('backend.seats.partials.cabin-filter-script')

<script>
$(document).ready(function() {
    const cabins = @json($cabins);
    initValidate('#create');
    initSelect2('.select2');
    initSeatCabinFilter('#property_id', '#cabin_id', cabins, '{{ $selectedPropertyId }}', '{{ $selectedCabinId }}');

    $("#create").submit(function(e) {
        var form = $(this);
        ajaxSubmit(e, form, callbackCreateForm);
    });

    const callbackCreateForm = function(response) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
});
</script>

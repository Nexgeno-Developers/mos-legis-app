<script>
function initSeatCabinFilter(propertySelector, cabinSelector, cabins, selectedPropertyId, selectedCabinId) {
    const $property = $(propertySelector);
    const $cabin = $(cabinSelector);

    function rebuildCabins(propertyId, cabinIdToSelect) {
        const currentValue = cabinIdToSelect !== undefined ? cabinIdToSelect : $cabin.val();
        $cabin.empty().append(new Option('{{ __('labels.select_cabin') }}', '', false, false));

        cabins.forEach(function (cabin) {
            if (!propertyId || String(cabin.property_id) === String(propertyId)) {
                const option = new Option(cabin.name, cabin.id, false, String(cabin.id) === String(currentValue));
                $cabin.append(option);
            }
        });

        if ($cabin.hasClass('select2-hidden-accessible')) {
            $cabin.trigger('change.select2');
        }
    }

    $property.on('change', function () {
        rebuildCabins($(this).val(), '');
    });

    rebuildCabins(selectedPropertyId || $property.val(), selectedCabinId || $cabin.val());
}
</script>

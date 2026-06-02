@php
    $oldCategoryIds = $oldCategoryIds ?? [];
    $oldCategoryIds = is_array($oldCategoryIds) ? $oldCategoryIds : [];
    $categoriesByParent = $categories->groupBy(function ($category) {
        return $category->parent_id ?? 0;
    });

    $renderTree = function ($parentId, $level = 0) use (&$renderTree, $categoriesByParent, $oldCategoryIds) {
        $children = $categoriesByParent->get($parentId, collect());
        if ($children->isEmpty()) {
            return '';
        }

        $html = '<ul class="list-unstyled mb-0 ps-0">';
        foreach ($children as $child) {
            $checked = in_array($child->id, $oldCategoryIds, true) ? 'checked' : '';
            $indent = $level * 12;
            $html .= '<li class="category-node" style="margin-left:' . $indent . 'px;">';
            $html .= '<div class="form-check">';
            $html .= '<input class="form-check-input post-category-checkbox" type="checkbox" name="category_ids[]" value="' . $child->id . '" id="post-category-' . $child->id . '" data-name="' . e($child->name) . '" data-slug="' . e($child->slug) . '" ' . $checked . '>';
            $html .= '<label class="form-check-label" for="post-category-' . $child->id . '">' . e($child->name) . '</label>';
            $html .= '</div>';
            $html .= $renderTree($child->id, $level + 1);
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    };
@endphp

<div class="form-group mb-2 post-category-select">
    <label class="form-label">Categories <span class="text-danger">*</span></label>
    <div class="dropdown w-100">
        <button class="btn btn-light border w-100 text-start d-flex align-items-center justify-content-between dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false">
            <span class="category-selected-label">Select Categories</span>
        </button>
        <div class="dropdown-menu w-100 p-2 category-dropdown-menu">
            {!! $renderTree(0) !!}
        </div>
    </div>
    <small class="text-muted">Select one or more categories.</small>
</div>

<script>
(function() {
    const wrapper = document.querySelector('.post-category-select');
    if (!wrapper) return;

    const labelEl = wrapper.querySelector('.category-selected-label');
    const updateLabel = () => {
        const checked = Array.from(wrapper.querySelectorAll('.post-category-checkbox:checked'));
        if (checked.length === 0) {
            labelEl.textContent = 'Select Categories';
            return;
        }
        if (checked.length === 1) {
            labelEl.textContent = checked[0].dataset.name || '1 selected';
            return;
        }
        labelEl.textContent = checked.length + ' selected';
    };

    wrapper.addEventListener('change', function(e) {
        if (!e.target.classList.contains('post-category-checkbox')) return;
        updateLabel();
    });

    updateLabel();
})();
</script>

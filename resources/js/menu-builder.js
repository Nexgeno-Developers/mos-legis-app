/**
 * Admin → Menus: drag-and-drop ordering. Groups stay at the top level; links can be
 * dropped at the top level or inside a group. Every drop saves the whole order.
 */
import Sortable from 'sortablejs';

export function initMenuBuilder(tree) {
    if (tree.hasAttribute('data-readonly')) return;

    const status = document.querySelector('[data-order-status]');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const setStatus = (text, tone = 'text-muted-foreground') => {
        if (!status) return;
        status.textContent = text;
        status.className = `text-sm ${tone}`;
    };

    const collect = () => {
        const items = [];
        [...tree.children].forEach((li, position) => {
            items.push({ id: Number(li.dataset.id), parent_id: null, sort_order: position });
            li.querySelectorAll(':scope > [data-children] > li').forEach((child, childPosition) => {
                items.push({ id: Number(child.dataset.id), parent_id: Number(li.dataset.id), sort_order: childPosition });
            });
        });
        return items;
    };

    const refreshCounts = () => {
        tree.querySelectorAll(':scope > li[data-group="1"]').forEach((group) => {
            const count = group.querySelectorAll(':scope > [data-children] > li').length;
            const label = group.querySelector('[data-link-count]');
            if (label) label.textContent = count === 0 ? 'No links yet — hidden' : `${count} link${count === 1 ? '' : 's'}`;
        });
    };

    const save = async () => {
        refreshCounts();
        setStatus('Saving order…');
        try {
            const response = await fetch(tree.dataset.reorderUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ items: collect() }),
            });
            const body = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(body.errors?.items?.[0] ?? body.message ?? 'Could not save the order.');
            setStatus('✓ Order saved', 'text-success');
        } catch (error) {
            setStatus(`${error.message} Reloading…`, 'text-destructive');
            setTimeout(() => window.location.reload(), 1500);
        }
    };

    const options = {
        group: 'menu',
        handle: '[data-handle]',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        ghostClass: 'menu-ghost',
        chosenClass: 'menu-chosen',
        // A group cannot be dropped inside another group.
        onMove: (event) => !(event.dragged.dataset.group === '1' && event.to.hasAttribute('data-children')),
        onEnd: (event) => {
            if (event.from === event.to && event.oldIndex === event.newIndex) return;
            save();
        },
    };

    Sortable.create(tree, options);
    tree.querySelectorAll('[data-children]').forEach((list) => Sortable.create(list, options));
}

<div class="a-toolbar">
    <p style="color:var(--a-muted); margin:0; font-size:13px;">These appear in the order status dropdown and as colored pills throughout the admin and storefront.</p>
    <button type="button" class="a-btn a-btn-primary" data-modal-open="statusModal" onclick="prepStatusModal()">+ Add Status</button>
</div>

<div class="a-table-wrap">
    <table class="a-table">
        <thead><tr><th>Status</th><th>Color</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($statuses as $s): ?>
        <tr>
            <td><span class="status-pill" style="background:<?= e($s['color']) ?>;"><?= e($s['name']) ?></span></td>
            <td><?= e($s['color']) ?></td>
            <td style="display:flex; gap:8px;">
                <button type="button" class="a-btn a-btn-outline a-btn-sm" onclick='openStatusModal(<?= json_encode($s) ?>)'>Edit</button>
                <?php if (!$s['is_default']): ?>
                <form method="post" data-confirm="Delete this status? Orders using it must be reassigned first."><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button type="submit" class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="a-modal" id="statusModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3 id="statusModalTitle">Add Status</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <form method="post" id="statusForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="statusAction" value="create">
            <input type="hidden" name="id" id="statusId">
            <div class="a-field"><label>Name</label><input type="text" name="name" id="statusName" class="a-input" required></div>
            <div class="a-field"><label>Color</label><input type="color" name="color" id="statusColor" class="a-input" value="#141414" style="padding:2px;"></div>
            <div class="a-field"><label>Sort Order</label><input type="number" name="sort_order" id="statusSort" class="a-input" value="0"></div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center;">Save Status</button>
        </form>
    </div>
</div>

<script>
function prepStatusModal() {
    document.getElementById('statusModalTitle').textContent = 'Add Status';
    document.getElementById('statusAction').value = 'create';
    document.getElementById('statusForm').reset();
    document.getElementById('statusId').value = '';
}
function openStatusModal(s) {
    document.getElementById('statusModalTitle').textContent = 'Edit Status';
    document.getElementById('statusAction').value = 'update';
    document.getElementById('statusId').value = s.id;
    document.getElementById('statusName').value = s.name;
    document.getElementById('statusColor').value = s.color;
    document.getElementById('statusSort').value = s.sort_order;
    document.getElementById('statusModal').classList.add('is-open');
}
</script>

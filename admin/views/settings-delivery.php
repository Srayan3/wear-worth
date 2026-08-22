<div class="a-toolbar">
    <p style="color:var(--a-muted); margin:0; font-size:13px;">Checkout automatically applies the charge for the customer's selected zone.</p>
    <button type="button" class="a-btn a-btn-primary" onclick="prepZoneModal()" data-modal-open="zoneModal">+ Add Zone</button>
</div>

<div class="a-table-wrap">
    <table class="a-table">
        <thead><tr><th>Zone</th><th>Charge</th><th>Default</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($zones)): ?><tr><td colspan="5" class="a-empty">No delivery zones yet.</td></tr><?php endif; ?>
        <?php foreach ($zones as $z): ?>
        <tr>
            <td style="font-weight:600;"><?= e($z['name']) ?></td>
            <td><?= money($z['charge']) ?></td>
            <td><?= $z['is_default'] ? '<span class="a-badge a-badge-on">Default</span>' : '' ?></td>
            <td><span class="a-badge <?= $z['is_active'] ? 'a-badge-on' : 'a-badge-off' ?>"><?= $z['is_active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="display:flex; gap:8px;">
                <button type="button" class="a-btn a-btn-outline a-btn-sm" data-zone='<?= e(json_encode($z)) ?>' onclick="openZoneModal(JSON.parse(this.dataset.zone))">Edit</button>
                <form method="post" data-confirm="Remove this delivery zone?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $z['id'] ?>"><button type="submit" class="a-btn a-btn-danger a-btn-sm">Delete</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="a-modal" id="zoneModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3 id="zoneModalTitle">Add Delivery Zone</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <form method="post" id="zoneForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="zoneAction" value="create">
            <input type="hidden" name="id" id="zoneId">
            <div class="a-field"><label>Zone Name</label><input type="text" name="name" id="zoneName" class="a-input" required></div>
            <div class="a-field"><label>Delivery Charge (৳)</label><input type="number" step="0.01" name="charge" id="zoneCharge" class="a-input" required></div>
            <div class="a-field-row">
                <label class="a-checkbox-row"><input type="checkbox" name="is_default" id="zoneDefault"> Default zone</label>
                <label class="a-checkbox-row"><input type="checkbox" name="is_active" id="zoneActive" checked> Active</label>
            </div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center;">Save Zone</button>
        </form>
    </div>
</div>

<script>
function prepZoneModal() {
    document.getElementById('zoneModalTitle').textContent = 'Add Delivery Zone';
    document.getElementById('zoneAction').value = 'create';
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneId').value = '';
}
function openZoneModal(z) {
    document.getElementById('zoneModalTitle').textContent = 'Edit Delivery Zone';
    document.getElementById('zoneAction').value = 'update';
    document.getElementById('zoneId').value = z.id;
    document.getElementById('zoneName').value = z.name;
    document.getElementById('zoneCharge').value = z.charge;
    document.getElementById('zoneDefault').checked = z.is_default == 1;
    document.getElementById('zoneActive').checked = z.is_active == 1;
    document.getElementById('zoneModal').classList.add('is-open');
}
</script>

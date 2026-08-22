<div class="a-toolbar">
    <div>
        <p style="color:var(--a-muted); margin:0; font-size:13px;">Gender → Category → Subcategory. Products are assigned to a subcategory.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <button type="button" class="a-btn a-btn-outline" data-modal-open="genderModal">Manage Genders</button>
        <button type="button" class="a-btn a-btn-primary" data-modal-open="categoryModal" onclick="prepCategoryModal()">+ Add Category</button>
    </div>
</div>

<?php if (empty($categories)): ?>
    <div class="a-card a-empty">No categories yet. Create your first one above.</div>
<?php endif; ?>

<?php foreach ($categories as $cat): ?>
<div class="a-tree-cat">
    <div class="a-tree-cat__head">
        <div class="a-tree-cat__title">
            <?= e($cat['name']) ?>
            <span class="a-badge <?= $cat['is_active'] ? 'a-badge-on' : 'a-badge-off' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></span>
            <span style="font-weight:400; font-size:12px; color:var(--a-muted);"><?= (int) $cat['subcategory_count'] ?> subcategories · <?= e($cat['gender_name']) ?></span>
        </div>
        <div class="a-tree-cat__actions">
            <button type="button" class="a-btn a-btn-outline a-btn-sm" data-category-id="<?= (int) $cat['id'] ?>" data-category-name="<?= e($cat['name']) ?>" onclick="openSubcategoryModal(parseInt(this.dataset.categoryId, 10), this.dataset.categoryName)">+ Subcategory</button>
            <button type="button" class="a-btn a-btn-outline a-btn-sm" data-category='<?= e(json_encode($cat + ["image_url" => $cat["image"] ? url($cat["image"]) : ""])) ?>' onclick="openCategoryModal(JSON.parse(this.dataset.category))">Edit</button>
            <form method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_category">
                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                <button type="submit" class="a-btn a-btn-outline a-btn-sm"><?= $cat['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <form method="post" style="display:inline;" data-confirm="Delete this category and all its subcategories/products? This cannot be undone.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                <button type="submit" class="a-btn a-btn-danger a-btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php foreach ($subcategoriesByCategory[$cat['id']] ?? [] as $sub): ?>
    <div class="a-tree-sub">
        <div>
            <?= e($sub['name']) ?>
            <span class="a-badge <?= $sub['is_active'] ? 'a-badge-on' : 'a-badge-off' ?>" style="margin-left:8px;"><?= $sub['is_active'] ? 'Active' : 'Hidden' ?></span>
            <span style="font-size:12px; color:var(--a-muted); margin-left:8px;"><?= (int) $sub['product_count'] ?> products</span>
        </div>
        <div class="a-tree-sub__actions">
            <button type="button" class="a-btn a-btn-outline a-btn-sm" data-category-id="<?= (int) $cat['id'] ?>" data-category-name="<?= e($cat['name']) ?>" data-subcategory='<?= e(json_encode($sub)) ?>' onclick="openSubcategoryModal(parseInt(this.dataset.categoryId, 10), this.dataset.categoryName, JSON.parse(this.dataset.subcategory))">Edit</button>
            <form method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_subcategory">
                <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
                <button type="submit" class="a-btn a-btn-outline a-btn-sm"><?= $sub['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <form method="post" style="display:inline;" data-confirm="Delete this subcategory? Products inside it will also be deleted.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_subcategory">
                <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
                <button type="submit" class="a-btn a-btn-danger a-btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($subcategoriesByCategory[$cat['id']])): ?>
        <div class="a-tree-sub" style="color:var(--a-muted);">No subcategories yet.</div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Category modal -->
<div class="a-modal" id="categoryModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3 id="categoryModalTitle">Add Category</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <form method="post" id="categoryForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="categoryAction" value="create_category">
            <input type="hidden" name="id" id="categoryId">
            <div class="a-field">
                <label>Gender</label>
                <select name="gender_id" id="categoryGenderId" class="a-select" required>
                    <?php foreach ($genders as $g): ?><option value="<?= (int) $g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="a-field"><label>Category Name</label><input type="text" name="name" id="categoryName" class="a-input" required></div>
            <div class="a-field"><label>Description</label><textarea name="description" id="categoryDescription" class="a-input"></textarea></div>
            <div class="a-field">
                <label>Tile Image <span style="text-transform:none; font-weight:400;">(shown on the homepage — 3:4 portrait works best)</span></label>
                <img id="categoryImagePreview" src="" alt="" style="display:none; width:70px; height:93px; object-fit:cover; border-radius:6px; margin-bottom:8px; border:1px solid var(--a-border);">
                <input type="file" name="image" class="a-input" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="a-field-row">
                <div class="a-field"><label>Sort Order</label><input type="number" name="sort_order" id="categorySortOrder" class="a-input" value="0"></div>
                <div class="a-field">
                    <label>Status</label>
                    <label class="a-checkbox-row"><input type="checkbox" name="is_active" id="categoryIsActive" checked> Active (visible on storefront)</label>
                </div>
            </div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center;">Save Category</button>
        </form>
    </div>
</div>

<!-- Subcategory modal -->
<div class="a-modal" id="subcategoryModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3 id="subcategoryModalTitle">Add Subcategory</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <form method="post" id="subcategoryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="subcategoryAction" value="create_subcategory">
            <input type="hidden" name="id" id="subcategoryId">
            <input type="hidden" name="category_id" id="subcategoryCategoryId">
            <p style="font-size:13px; color:var(--a-muted); margin-top:-6px;">Under: <strong id="subcategoryParentName"></strong></p>
            <div class="a-field"><label>Subcategory Name</label><input type="text" name="name" id="subcategoryName" class="a-input" required></div>
            <div class="a-field"><label>Description</label><textarea name="description" id="subcategoryDescription" class="a-input"></textarea></div>
            <div class="a-field-row">
                <div class="a-field"><label>Sort Order</label><input type="number" name="sort_order" id="subcategorySortOrder" class="a-input" value="0"></div>
                <div class="a-field">
                    <label>Status</label>
                    <label class="a-checkbox-row"><input type="checkbox" name="is_active" id="subcategoryIsActive" checked> Active (visible on storefront)</label>
                </div>
            </div>
            <button type="submit" class="a-btn a-btn-primary" style="width:100%; justify-content:center;">Save Subcategory</button>
        </form>
    </div>
</div>

<!-- Gender modal -->
<div class="a-modal" id="genderModal">
    <div class="a-modal__scrim" data-modal-close></div>
    <div class="a-modal__panel">
        <div class="a-modal__head"><h3>Genders</h3><button class="a-modal__close" data-modal-close>✕</button></div>
        <ul style="list-style:none; margin:0 0 18px; padding:0;">
            <?php foreach ($genders as $g): ?>
                <li style="padding:8px 0; border-bottom:1px solid var(--a-border);"><?= e($g['name']) ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" style="display:flex; gap:10px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_gender">
            <input type="text" name="name" class="a-input" placeholder="e.g. Girls" required>
            <button type="submit" class="a-btn a-btn-primary">Add</button>
        </form>
    </div>
</div>

<script>
function prepCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categoryAction').value = 'create_category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryImagePreview').style.display = 'none';
}
function openCategoryModal(cat) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryAction').value = 'update_category';
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('categoryGenderId').value = cat.gender_id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDescription').value = cat.description || '';
    document.getElementById('categorySortOrder').value = cat.sort_order;
    document.getElementById('categoryIsActive').checked = cat.is_active == 1;
    var preview = document.getElementById('categoryImagePreview');
    if (cat.image_url) {
        preview.src = cat.image_url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    document.getElementById('categoryModal').classList.add('is-open');
}
function openSubcategoryModal(categoryId, categoryName, sub) {
    document.getElementById('subcategoryParentName').textContent = categoryName;
    document.getElementById('subcategoryCategoryId').value = categoryId;
    if (sub) {
        document.getElementById('subcategoryModalTitle').textContent = 'Edit Subcategory';
        document.getElementById('subcategoryAction').value = 'update_subcategory';
        document.getElementById('subcategoryId').value = sub.id;
        document.getElementById('subcategoryName').value = sub.name;
        document.getElementById('subcategoryDescription').value = sub.description || '';
        document.getElementById('subcategorySortOrder').value = sub.sort_order;
        document.getElementById('subcategoryIsActive').checked = sub.is_active == 1;
    } else {
        document.getElementById('subcategoryModalTitle').textContent = 'Add Subcategory';
        document.getElementById('subcategoryAction').value = 'create_subcategory';
        document.getElementById('subcategoryForm').reset();
        document.getElementById('subcategoryId').value = '';
    }
    document.getElementById('subcategoryModal').classList.add('is-open');
}
</script>

<div data-tabs>
    <div class="a-tabs__nav">
        <button type="button" class="is-active" data-tab="tabBasic">Basic Info</button>
        <button type="button" data-tab="tabImages" <?= !$id ? 'disabled title="Save the product first"' : '' ?>>Images <?= $id ? '(' . count($images) . ')' : '' ?></button>
        <button type="button" data-tab="tabVariations" <?= !$id ? 'disabled' : '' ?>>Variations <?= $id ? '(' . count($variations) . ')' : '' ?></button>
        <button type="button" data-tab="tabSizeChart" <?= !$id ? 'disabled' : '' ?>>Size Chart</button>
    </div>

    <!-- ============ BASIC INFO ============ -->
    <div class="a-tabs__panel is-active" id="tabBasic">
        <form method="post" action="<?= admin_url('product-form.php' . ($id ? '?id=' . $id : '')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_basic">

            <div class="a-card a-card--solid" style="margin-bottom:18px;">
                <div class="a-field-row">
                    <div class="a-field"><label>Product Name</label><input type="text" name="name" class="a-input" value="<?= e($product['name'] ?? '') ?>" required></div>
                    <div class="a-field"><label>SKU</label><input type="text" name="sku" class="a-input" value="<?= e($product['sku'] ?? '') ?>" required></div>
                </div>
                <div class="a-field">
                    <label>Subcategory</label>
                    <select name="subcategory_id" class="a-select" required>
                        <option value="">Select subcategory…</option>
                        <?php foreach ($subcategories as $sub): ?>
                            <option value="<?= (int) $sub['id'] ?>" <?= (($product['subcategory_id'] ?? null) == $sub['id']) ? 'selected' : '' ?>><?= e($sub['category_name']) ?> → <?= e($sub['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="a-field"><label>Short Description</label><input type="text" name="short_description" class="a-input" value="<?= e($product['short_description'] ?? '') ?>" maxlength="500"></div>
                <div class="a-field"><label>Full Description</label><textarea name="description" class="a-input" rows="6"><?= e($product['description'] ?? '') ?></textarea></div>
            </div>

            <div class="a-card a-card--solid" style="margin-bottom:18px;">
                <div class="a-field-row-3">
                    <div class="a-field"><label>Price (৳)</label><input type="number" step="0.01" name="price" class="a-input" value="<?= e((string) ($product['price'] ?? '')) ?>" required></div>
                    <div class="a-field"><label>Discount Price (৳)</label><input type="number" step="0.01" name="discount_price" class="a-input" value="<?= e((string) ($product['discount_price'] ?? '')) ?>"></div>
                    <div class="a-field"><label>Stock Quantity</label><input type="number" name="stock_quantity" class="a-input" value="<?= e((string) ($product['stock_quantity'] ?? 0)) ?>"></div>
                </div>
                <div class="a-field">
                    <label>Stock Status</label>
                    <select name="stock_status" class="a-select">
                        <option value="in_stock" <?= ($product['stock_status'] ?? 'in_stock') === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                        <option value="out_of_stock" <?= ($product['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
            </div>

            <div class="a-card a-card--solid" style="margin-bottom:18px;">
                <div class="a-field-row">
                    <label class="a-checkbox-row"><input type="checkbox" name="has_variations" <?= !empty($product['has_variations']) ? 'checked' : '' ?>> Has size/color variations</label>
                    <label class="a-checkbox-row"><input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>> Active (visible on storefront)</label>
                </div>
                <div class="a-field-row-3">
                    <label class="a-checkbox-row"><input type="checkbox" name="is_featured" <?= !empty($product['is_featured']) ? 'checked' : '' ?>> Featured</label>
                    <label class="a-checkbox-row"><input type="checkbox" name="is_new_arrival" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?>> New Arrival</label>
                    <label class="a-checkbox-row"><input type="checkbox" name="is_popular" <?= !empty($product['is_popular']) ? 'checked' : '' ?>> Popular</label>
                </div>
            </div>

            <button type="submit" class="a-btn a-btn-primary"><?= $id ? 'Save Changes' : 'Create Product & Continue' ?></button>
            <a href="<?= admin_url('products.php') ?>" class="a-btn a-btn-outline">Cancel</a>
        </form>
    </div>

    <!-- ============ IMAGES ============ -->
    <?php if ($id): ?>
    <div class="a-tabs__panel" id="tabImages">
        <div class="a-card a-card--solid">
            <p class="a-hint" style="margin-bottom:14px;">Drag tiles to reorder. The first image is the primary image shown in listings. JPG, PNG or WEBP, up to 4MB.</p>
            <div class="a-image-grid" id="imageGrid" data-product-id="<?= (int) $id ?>">
                <?php foreach ($images as $img): ?>
                <div class="a-image-tile <?= $img['is_primary'] ? 'is-primary' : '' ?>" draggable="true" data-image-id="<?= (int) $img['id'] ?>">
                    <?php if ($img['is_primary']): ?><span class="a-image-tile__primary-flag">Primary</span><?php endif; ?>
                    <img src="<?= product_image_url($img['image_path']) ?>" alt="">
                    <div class="a-image-tile__bar">
                        <button type="button" data-set-primary>Set Primary</button>
                        <button type="button" data-delete-image>Delete</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <label class="a-btn a-btn-outline" style="cursor:pointer;">
                + Upload Image
                <input type="file" id="imageUploadInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
            </label>
            <span id="uploadStatus" style="margin-left:10px; font-size:12.5px; color:var(--a-muted);"></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============ VARIATIONS ============ -->
    <?php if ($id): ?>
    <div class="a-tabs__panel" id="tabVariations">
        <?php if (!empty($product['has_variations']) && empty($variations)): ?>
        <div class="a-flash a-flash--error">
            <strong>Nothing to buy yet:</strong> this product is marked "Has size/color variations" but has zero saved options.
            Customers see "select a size/color" with no way to pick one. Add at least one row below (with a Size and/or Color filled in),
            or uncheck "Has size/color variations" in the Basic Info tab if this product shouldn't use options at all.
        </div>
        <?php endif; ?>
        <form method="post" action="<?= admin_url('product-form.php?id=' . $id) ?>" id="variationsForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_variations">
            <div class="a-card a-card--solid">
                <p class="a-hint" style="margin-bottom:14px;">Leave size or color blank if the product doesn't use that dimension. Price is optional — it overrides the base price only for that combination.</p>
                <div id="variationRows">
                    <?php foreach ($variations as $v): ?>
                    <div class="a-repeater-row a-repeater-row--size">
                        <input type="text" name="var_size[]" class="a-input" placeholder="Size (e.g. M)" value="<?= e($v['size_label'] ?? '') ?>">
                        <input type="text" name="var_color[]" class="a-input" placeholder="Color name" value="<?= e($v['color_name'] ?? '') ?>">
                        <input type="color" name="var_hex[]" class="a-input" style="padding:2px;" value="<?= e($v['color_hex'] ?: '#141414') ?>">
                        <input type="text" name="var_sku[]" class="a-input" placeholder="Variant SKU" value="<?= e($v['sku'] ?? '') ?>">
                        <input type="number" name="var_price[]" class="a-input" placeholder="Price override" step="0.01" value="<?= e((string) ($v['price_override'] ?? '')) ?>">
                        <input type="number" name="var_qty[]" class="a-input" placeholder="Stock" value="<?= e((string) $v['stock_quantity']) ?>" style="max-width:80px;">
                        <button type="button" class="a-repeater-remove" onclick="this.closest('.a-repeater-row').remove()">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="a-btn a-btn-outline a-btn-sm" id="addVariationRow" style="margin-bottom:16px;">+ Add Row</button>
                <br>
                <button type="submit" class="a-btn a-btn-primary">Save Variations</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ============ SIZE CHART ============ -->
    <?php if ($id): ?>
    <?php $chartType = $product['size_chart_type'] ?? 'clothing'; ?>
    <div class="a-tabs__panel" id="tabSizeChart">
        <form method="post" action="<?= admin_url('product-form.php?id=' . $id) ?>" id="sizeChartForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_size_chart">
            <div class="a-card a-card--solid">
                <div class="a-field" style="max-width:340px;">
                    <label>Size Chart Type</label>
                    <select name="size_chart_type" id="sizeChartType" class="a-select">
                        <option value="clothing" <?= $chartType === 'clothing' ? 'selected' : '' ?>>Clothing — S / M / L measurements</option>
                        <option value="footwear" <?= $chartType === 'footwear' ? 'selected' : '' ?>>Footwear — size conversion</option>
                    </select>
                    <p class="a-hint">Switches which kind of size guide table shows on this product's page.</p>
                </div>

                <!-- Clothing: Size / Chest / Waist / Hip / Length (inches) -->
                <div id="clothingSizeChart" style="<?= $chartType === 'footwear' ? 'display:none;' : '' ?>">
                    <p class="a-hint" style="margin-bottom:14px;">Measurements in inches — shown to shoppers as a size guide table on the product page.</p>
                    <div id="sizeChartRows">
                        <?php foreach ($sizeChart as $row): ?>
                        <div class="a-repeater-row">
                            <input type="text" name="sc_size[]" class="a-input" placeholder="Size (e.g. M)" value="<?= e($row['size_label']) ?>" <?= $chartType !== 'clothing' ? 'disabled' : '' ?>>
                            <input type="number" step="0.1" name="sc_chest[]" class="a-input" placeholder="Chest (in)" value="<?= e((string) ($row['chest_in'] ?? '')) ?>" <?= $chartType !== 'clothing' ? 'disabled' : '' ?>>
                            <input type="number" step="0.1" name="sc_waist[]" class="a-input" placeholder="Waist (in)" value="<?= e((string) ($row['waist_in'] ?? '')) ?>" <?= $chartType !== 'clothing' ? 'disabled' : '' ?>>
                            <input type="number" step="0.1" name="sc_hip[]" class="a-input" placeholder="Hip (in)" value="<?= e((string) ($row['hip_in'] ?? '')) ?>" <?= $chartType !== 'clothing' ? 'disabled' : '' ?>>
                            <input type="number" step="0.1" name="sc_length[]" class="a-input" placeholder="Length (in)" value="<?= e((string) ($row['length_in'] ?? '')) ?>" <?= $chartType !== 'clothing' ? 'disabled' : '' ?>>
                            <button type="button" class="a-repeater-remove" onclick="this.closest('.a-repeater-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="a-btn a-btn-outline a-btn-sm" id="addSizeChartRow" style="margin-bottom:16px;">+ Add Size</button>
                </div>

                <!-- Footwear: Brand Size / UK-Bata / EU-Apex / US -->
                <div id="footwearSizeChart" style="<?= $chartType === 'footwear' ? '' : 'display:none;' ?>">
                    <p class="a-hint" style="margin-bottom:14px;">Your brand's own size, then the equivalent UK/Bata, EU/Apex, and US sizes.</p>
                    <div id="footwearChartRows">
                        <?php foreach ($sizeChart as $row): ?>
                        <div class="a-repeater-row a-repeater-row--4col">
                            <input type="text" name="sc_brand_size[]" class="a-input" placeholder="Brand Size (e.g. 38)" value="<?= e($row['size_label']) ?>" <?= $chartType !== 'footwear' ? 'disabled' : '' ?>>
                            <input type="text" name="sc_uk[]" class="a-input" placeholder="UK / Bata" value="<?= e($row['uk_size'] ?? '') ?>" <?= $chartType !== 'footwear' ? 'disabled' : '' ?>>
                            <input type="text" name="sc_eu[]" class="a-input" placeholder="EU / Apex" value="<?= e($row['eu_size'] ?? '') ?>" <?= $chartType !== 'footwear' ? 'disabled' : '' ?>>
                            <input type="text" name="sc_us[]" class="a-input" placeholder="US" value="<?= e($row['us_size'] ?? '') ?>" <?= $chartType !== 'footwear' ? 'disabled' : '' ?>>
                            <button type="button" class="a-repeater-remove" onclick="this.closest('.a-repeater-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="a-btn a-btn-outline a-btn-sm" id="addFootwearChartRow" style="margin-bottom:16px;">+ Add Size</button>
                </div>

                <br>
                <button type="submit" class="a-btn a-btn-primary">Save Size Chart</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php $extraScripts = ['assets/js/product-form.js']; ?>

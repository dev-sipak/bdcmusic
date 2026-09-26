/*=========================================================
   DIGITAL MUSIC DISTRIBUTION - CONDITIONAL FIELDS
   Shows/hides dependent fields based on the Yes/No toggles
   for ISRC, UPC and Copyright Help. No page reload.
   Hidden fields are disabled so they are never submitted, and
   fields marked data-required-when-shown are required only
   while their block is visible.
  =========================================================*/

/**
 * Resolve the current value of a toggle group.
 * @param {string} groupName - the radio group name (e.g. 'isrc')
 * @returns {string} the selected value, or '' when nothing is selected
 */
function getToggleValue(groupName) {
    var selected = document.querySelector(
        'input[type="radio"][name="' + groupName + '"]:checked'
    );
    return selected ? selected.value : '';
}

/**
 * Show or hide one dependent field block.
 * Fields marked with data-required-when-shown become required while the
 * block is visible and lose the attribute again as soon as it is hidden.
 * @param {string} targetName - value of data-toggle-detail
 * @param {boolean} isEnabled
 */
function applyToggleDetail(targetName, isEnabled) {
    var detail = document.querySelector('[data-toggle-detail="' + targetName + '"]');
    if (!detail) return;

    detail.hidden = !isEnabled;

    detail.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.disabled = !isEnabled;
        if (field.hasAttribute('data-required-when-shown')) {
            field.required = isEnabled;
        }
    });
}

/**
 * Wire up one Yes/No toggle group to its dependent field block.
 * @param {string} groupName
 */
function bindToggleGroup(groupName) {
    var group = document.querySelector('[data-toggle-group="' + groupName + '"]');
    if (!group) return;

    var sync = function () {
        applyToggleDetail(groupName, getToggleValue(groupName) === 'Yes');
    };

    group.querySelectorAll('input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', sync);
    });

    sync();
}

document.addEventListener('DOMContentLoaded', function () {
    ['isrc', 'upc', 'copyright_help'].forEach(bindToggleGroup);
});
